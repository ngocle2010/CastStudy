<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../includes/db_config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 2) {
    header('Location: ../index.php'); exit();
}

$message = '';

// Lấy danh sách Category và District
$categories = mysqli_query($conn, "SELECT ID, Name FROM categories");
$districts = mysqli_query($conn, "SELECT ID, Name FROM districts");

// Xử lý các Form Submit (Thêm, Sửa, Duyệt, Xóa)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // Nhận các trường dữ liệu chung
    $title       = isset($_POST['title']) ? mysqli_real_escape_string($conn, $_POST['title']) : '';
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    $district_id = isset($_POST['district_id']) ? (int)$_POST['district_id'] : 0;
    $price       = isset($_POST['price']) ? (int)$_POST['price'] : 0;
    $area        = isset($_POST['area']) ? (int)$_POST['area'] : 0;
    $address     = isset($_POST['address']) ? mysqli_real_escape_string($conn, $_POST['address']) : '';
    $phone       = isset($_POST['phone']) ? mysqli_real_escape_string($conn, $_POST['phone']) : '';
    $utilities   = isset($_POST['utilities']) ? mysqli_real_escape_string($conn, $_POST['utilities']) : '';
    $description = isset($_POST['description']) ? mysqli_real_escape_string($conn, $_POST['description']) : '';

    // -- XỬ LÝ UPLOAD ẢNH CHUNG --
    $image_name = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload_dir = '../uploads/rooms/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_name = time() . '_' . rand(100, 999) . '.' . $file_extension;
        $target_file = $upload_dir . $image_name;
        
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_name = ''; 
        }
    }

    // 1. Thêm tin đăng mới
    if ($action === 'add') {
        $user_id = $_SESSION['user']['ID']; 
        
        $sql_add = "INSERT INTO motel (title, category_id, district_id, price, area, address, phone, utilities, description, images, user_id, approve, created_at, count_view) 
                    VALUES ('$title', $category_id, $district_id, $price, $area, '$address', '$phone', '$utilities', '$description', '$image_name', $user_id, 1, NOW(), 0)";
        
        if (mysqli_query($conn, $sql_add)) {
            $message = 'Đã tạo tin đăng mới và tải ảnh lên thành công.';
        } else {
            $message = 'Lỗi khi tạo tin: ' . mysqli_error($conn);
        }
    }

    // 2. Các hành động cần ID (Sửa, Duyệt, Xóa)
    if (isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        if ($id > 0) {
            if ($action === 'edit') {
                $old_image = isset($_POST['old_image']) ? mysqli_real_escape_string($conn, $_POST['old_image']) : '';
                $final_image = ($image_name != '') ? $image_name : $old_image;

                $sql_edit = "UPDATE motel SET 
                                title = '$title', 
                                category_id = $category_id, 
                                district_id = $district_id, 
                                price = $price, 
                                area = $area, 
                                address = '$address', 
                                phone = '$phone', 
                                utilities = '$utilities', 
                                description = '$description',
                                images = '$final_image'
                             WHERE ID = $id";
                if (mysqli_query($conn, $sql_edit)) {
                    $message = 'Đã cập nhật thông tin tin đăng thành công.';
                } else {
                    $message = 'Lỗi cập nhật: ' . mysqli_error($conn);
                }
            } elseif ($action === 'approve') {
                mysqli_query($conn, "UPDATE motel SET approve = 1 WHERE ID = $id");
                $message = 'Đã duyệt tin đăng.';
            } elseif ($action === 'unapprove') {
                mysqli_query($conn, "UPDATE motel SET approve = 0 WHERE ID = $id");
                $message = 'Đã chuyển tin về trạng thái chờ duyệt.';
            } elseif ($action === 'delete') {
                mysqli_query($conn, "DELETE FROM motel WHERE ID = $id");
                $message = 'Đã xóa tin đăng.';
            }
        }
    }
}

// Lấy danh sách tin
$sql = "SELECT motel.*, user.Name AS owner_name, categories.Name AS category_name, districts.Name AS district_name
        FROM motel
        LEFT JOIN user ON motel.user_id = user.ID
        LEFT JOIN categories ON motel.category_id = categories.ID
        LEFT JOIN districts ON motel.district_id = districts.ID
        ORDER BY motel.created_at DESC";
$result = mysqli_query($conn, $sql);

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="d-flex gap-2">
            <a href="dashboard.php" class="btn btn-outline-secondary shadow-sm">
                <i class="fa-solid fa-house me-1"></i> Về trang chủ
            </a>
            
            <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addRoomModal">
                <i class="fa-solid fa-plus me-1"></i> Tạo tin đăng
            </button>
        </div>

    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="table-responsive shadow-sm rounded-4 border bg-white">
            <table class="table align-middle mb-0 table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Hình ảnh</th>
                        <th>Tiêu đề</th>
                        <th>Người đăng</th>
                        <th>Giá</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($room = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo $room['ID']; ?></td>
                            <td>
                                <?php if(!empty($room['images'])): ?>
                                    <img src="../uploads/rooms/<?php echo $room['images']; ?>" alt="Hình ảnh" class="rounded" style="width: 60px; height: 60px; object-fit: cover;">
                                <?php else: ?>
                                    <span class="text-muted small">Chưa có ảnh</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-truncate" style="max-width: 200px;" title="<?php echo htmlspecialchars($room['title']); ?>">
                                <?php echo htmlspecialchars($room['title']); ?>
                            </td>
                            <td><?php echo htmlspecialchars($room['owner_name'] ?? 'Khách'); ?></td>
                            <td class="text-danger fw-semibold"><?php echo number_format((int)$room['price']); ?> đ</td>
                            <td>
                                <?php if ($room['approve'] == 1): ?>
                                    <span class="badge bg-success rounded-pill px-3">Đã duyệt</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark rounded-pill px-3">Chờ duyệt</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex gap-1 flex-wrap">
                                    <a href="../detail.php?id=<?php echo $room['ID']; ?>" class="btn btn-sm btn-outline-secondary" title="Xem"><i class="fa-solid fa-eye"></i></a>
                                    
                                    <button type="button" class="btn btn-sm btn-outline-info edit-btn" title="Sửa"
                                            data-id="<?php echo $room['ID']; ?>"
                                            data-title="<?php echo htmlspecialchars($room['title']); ?>"
                                            data-category="<?php echo $room['category_id']; ?>"
                                            data-district="<?php echo $room['district_id']; ?>"
                                            data-price="<?php echo $room['price']; ?>"
                                            data-area="<?php echo $room['area']; ?>"
                                            data-address="<?php echo htmlspecialchars($room['address']); ?>"
                                            data-phone="<?php echo htmlspecialchars($room['phone']); ?>"
                                            data-utilities="<?php echo htmlspecialchars($room['utilities']); ?>"
                                            data-description="<?php echo htmlspecialchars($room['description']); ?>"
                                            data-image="<?php echo htmlspecialchars($room['images']); ?>">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>

                                    <form method="post" class="m-0">
                                        <input type="hidden" name="id" value="<?php echo $room['ID']; ?>">
                                        <input type="hidden" name="action" value="<?php echo $room['approve'] == 1 ? 'unapprove' : 'approve'; ?>">
                                        <button type="submit" class="btn btn-sm btn-<?php echo $room['approve'] == 1 ? 'warning' : 'success'; ?>" title="<?php echo $room['approve'] == 1 ? 'Hủy duyệt' : 'Duyệt'; ?>">
                                            <i class="fa-solid <?php echo $room['approve'] == 1 ? 'fa-ban text-dark' : 'fa-check'; ?>"></i>
                                        </button>
                                    </form>

                                    <form method="post" class="m-0" onsubmit="return confirm('Bạn có chắc muốn xóa tin này?');">
                                        <input type="hidden" name="id" value="<?php echo $room['ID']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Xóa"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">Chưa có tin đăng nào trong hệ thống.</div>
    <?php endif; ?>
</div>

<div class="modal fade" id="addRoomModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold">Tạo tin đăng mới</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="post" action="" enctype="multipart/form-data">
          <div class="modal-body">
              <input type="hidden" name="action" value="add">
              
              <div class="mb-3">
                  <label class="form-label fw-semibold">Hình ảnh phòng trọ</label>
                  <input type="file" name="image" class="form-control" accept="image/*">
                  <small class="text-muted">Định dạng hỗ trợ: JPG, PNG, JPEG.</small>
              </div>

              <div class="mb-3">
                  <label class="form-label fw-semibold">Tiêu đề <span class="text-danger">*</span></label>
                  <input type="text" name="title" class="form-control" required>
              </div>

              <div class="row">
                  <div class="col-md-6 mb-3">
                      <label class="form-label fw-semibold">Danh mục</label>
                      <select name="category_id" class="form-select">
                          <?php mysqli_data_seek($categories, 0); while($cat = mysqli_fetch_assoc($categories)): ?>
                              <option value="<?php echo $cat['ID']; ?>"><?php echo $cat['Name']; ?></option>
                          <?php endwhile; ?>
                      </select>
                  </div>
                  <div class="col-md-6 mb-3">
                      <label class="form-label fw-semibold">Khu vực / Quận Huyện</label>
                      <select name="district_id" class="form-select">
                          <?php mysqli_data_seek($districts, 0); while($dist = mysqli_fetch_assoc($districts)): ?>
                              <option value="<?php echo $dist['ID']; ?>"><?php echo $dist['Name']; ?></option>
                          <?php endwhile; ?>
                      </select>
                  </div>
              </div>
              
              <div class="row">
                  <div class="col-md-6 mb-3">
                      <label class="form-label fw-semibold">Giá thuê (VNĐ) <span class="text-danger">*</span></label>
                      <input type="number" name="price" class="form-control" required min="0">
                  </div>
                  <div class="col-md-6 mb-3">
                      <label class="form-label fw-semibold">Diện tích (m²)</label>
                      <input type="number" name="area" class="form-control" min="0">
                  </div>
              </div>

              <div class="row">
                  <div class="col-md-6 mb-3">
                      <label class="form-label fw-semibold">Số điện thoại liên hệ</label>
                      <input type="text" name="phone" class="form-control">
                  </div>
                  <div class="col-md-6 mb-3">
                      <label class="form-label fw-semibold">Địa chỉ cụ thể</label>
                      <input type="text" name="address" class="form-control">
                  </div>
              </div>

              <div class="mb-3">
                  <label class="form-label fw-semibold">Tiện ích</label>
                  <input type="text" name="utilities" class="form-control">
              </div>

              <div class="mb-3">
                  <label class="form-label fw-semibold">Mô tả chi tiết</label>
                  <textarea name="description" class="form-control" rows="3"></textarea>
              </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            <button type="submit" class="btn btn-primary">Đăng tin ngay</button>
          </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="editRoomModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title fw-bold">Chỉnh sửa tin đăng</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="post" action="" enctype="multipart/form-data">
          <div class="modal-body">
              <input type="hidden" name="action" value="edit">
              <input type="hidden" name="id" id="edit_id" value="">
              <input type="hidden" name="old_image" id="edit_old_image" value="">
              
              <div class="mb-3">
                  <label class="form-label fw-semibold">Hình ảnh (Cập nhật ảnh mới)</label>
                  <input type="file" name="image" class="form-control" accept="image/*">
                  <small class="text-muted">Bỏ trống nếu bạn muốn giữ nguyên ảnh hiện tại.</small>
              </div>

              <div class="mb-3">
                  <label class="form-label fw-semibold">Tiêu đề <span class="text-danger">*</span></label>
                  <input type="text" name="title" id="edit_title" class="form-control" required>
              </div>

              <div class="row">
                  <div class="col-md-6 mb-3">
                      <label class="form-label fw-semibold">Danh mục</label>
                      <select name="category_id" id="edit_category" class="form-select">
                          <?php mysqli_data_seek($categories, 0); while($cat = mysqli_fetch_assoc($categories)): ?>
                              <option value="<?php echo $cat['ID']; ?>"><?php echo $cat['Name']; ?></option>
                          <?php endwhile; ?>
                      </select>
                  </div>
                  <div class="col-md-6 mb-3">
                      <label class="form-label fw-semibold">Khu vực / Quận Huyện</label>
                      <select name="district_id" id="edit_district" class="form-select">
                          <?php mysqli_data_seek($districts, 0); while($dist = mysqli_fetch_assoc($districts)): ?>
                              <option value="<?php echo $dist['ID']; ?>"><?php echo $dist['Name']; ?></option>
                          <?php endwhile; ?>
                      </select>
                  </div>
              </div>
              
              <div class="row">
                  <div class="col-md-6 mb-3">
                      <label class="form-label fw-semibold">Giá thuê (VNĐ) <span class="text-danger">*</span></label>
                      <input type="number" name="price" id="edit_price" class="form-control" required min="0">
                  </div>
                  <div class="col-md-6 mb-3">
                      <label class="form-label fw-semibold">Diện tích (m²)</label>
                      <input type="number" name="area" id="edit_area" class="form-control" min="0">
                  </div>
              </div>

              <div class="row">
                  <div class="col-md-6 mb-3">
                      <label class="form-label fw-semibold">Số điện thoại</label>
                      <input type="text" name="phone" id="edit_phone" class="form-control">
                  </div>
                  <div class="col-md-6 mb-3">
                      <label class="form-label fw-semibold">Địa chỉ</label>
                      <input type="text" name="address" id="edit_address" class="form-control">
                  </div>
              </div>

              <div class="mb-3">
                  <label class="form-label fw-semibold">Tiện ích</label>
                  <input type="text" name="utilities" id="edit_utilities" class="form-control">
              </div>

              <div class="mb-3">
                  <label class="form-label fw-semibold">Mô tả</label>
                  <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
              </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
            <button type="submit" class="btn btn-info text-white">Lưu thay đổi</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var editButtons = document.querySelectorAll('.edit-btn');
    
    editButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            document.getElementById('edit_id').value = this.getAttribute('data-id');
            document.getElementById('edit_title').value = this.getAttribute('data-title');
            document.getElementById('edit_category').value = this.getAttribute('data-category');
            document.getElementById('edit_district').value = this.getAttribute('data-district');
            document.getElementById('edit_price').value = this.getAttribute('data-price');
            document.getElementById('edit_area').value = this.getAttribute('data-area');
            document.getElementById('edit_address').value = this.getAttribute('data-address');
            document.getElementById('edit_phone').value = this.getAttribute('data-phone');
            document.getElementById('edit_utilities').value = this.getAttribute('data-utilities');
            document.getElementById('edit_description').value = this.getAttribute('data-description');
            document.getElementById('edit_old_image').value = this.getAttribute('data-image');
            
            var editModal = new bootstrap.Modal(document.getElementById('editRoomModal'));
            editModal.show();
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>