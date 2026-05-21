<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/db_config.php';

$role = (int)($_SESSION['user']['role'] ?? $_SESSION['user']['Role'] ?? 0);
$userId = (int)($_SESSION['user_id'] ?? $_SESSION['user']['ID'] ?? 0);

if (!isset($_SESSION['user']) || ($role !== 1 && $role !== 2) || $userId <= 0) {
    header('Location: index.php');
    exit();
}

$message = '';
$messageType = 'info';
if (isset($_GET['updated'])) {
    $message = 'Đã cập nhật tin đăng. Tin đang chờ duyệt lại.';
    $messageType = 'success';
} elseif (isset($_GET['deleted'])) {
    $message = 'Đã xóa tin đăng.';
    $messageType = 'success';
} elseif (isset($_GET['status_updated'])) {
    $message = 'Đã cập nhật tình trạng thuê của tin đăng.';
    $messageType = 'success';
} elseif (isset($_GET['not_found'])) {
    $message = 'Không tìm thấy tin đăng hoặc bạn không có quyền xử lý.';
    $messageType = 'warning';
} elseif (isset($_GET['error'])) {
    $message = 'Không thể xử lý yêu cầu. Vui lòng thử lại.';
    $messageType = 'danger';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array(($_POST['action'] ?? ''), ['delete', 'mark_rented', 'mark_available'], true)) {
    $roomId = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($roomId > 0) {
        if ($action === 'delete') {
            if ($role === 2) {
                $stmt = mysqli_prepare($conn, "DELETE FROM motel WHERE ID = ?");
                mysqli_stmt_bind_param($stmt, "i", $roomId);
            } else {
                $stmt = mysqli_prepare($conn, "DELETE FROM motel WHERE ID = ? AND user_id = ?");
                mysqli_stmt_bind_param($stmt, "ii", $roomId, $userId);
            }

            if ($stmt && mysqli_stmt_execute($stmt)) {
                $deleted = mysqli_stmt_affected_rows($stmt) > 0;
                mysqli_stmt_close($stmt);
                header('Location: my-rooms.php?' . ($deleted ? 'deleted=1' : 'not_found=1'));
                exit();
            }
        } else {
            $isRented = $action === 'mark_rented' ? 1 : 0;

            if ($role === 2) {
                $stmt = mysqli_prepare($conn, "UPDATE motel SET is_rented = ? WHERE ID = ?");
                mysqli_stmt_bind_param($stmt, "ii", $isRented, $roomId);
            } else {
                $stmt = mysqli_prepare($conn, "UPDATE motel SET is_rented = ? WHERE ID = ? AND user_id = ?");
                mysqli_stmt_bind_param($stmt, "iii", $isRented, $roomId, $userId);
            }

            if ($stmt && mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header('Location: my-rooms.php?status_updated=1');
                exit();
            }
        }
    }

    header('Location: my-rooms.php?error=1');
    exit();
}

if ($role === 2) {
    $sql = "SELECT motel.*, categories.Name AS category_name, districts.Name AS district_name, user.Name AS owner_name
            FROM motel
            LEFT JOIN categories ON motel.category_id = categories.ID
            LEFT JOIN districts ON motel.district_id = districts.ID
            LEFT JOIN user ON motel.user_id = user.ID
            ORDER BY motel.created_at DESC";
    $result = mysqli_query($conn, $sql);
} else {
    $sql = "SELECT motel.*, categories.Name AS category_name, districts.Name AS district_name
            FROM motel
            LEFT JOIN categories ON motel.category_id = categories.ID
            LEFT JOIN districts ON motel.district_id = districts.ID
            WHERE motel.user_id = ?
            ORDER BY motel.created_at DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
}

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-start gap-3 mb-4 flex-wrap">
        <div>
            <h2 class="fw-bold mb-1">Quản lý tin đăng</h2>
            <p class="text-muted mb-0">Xem, thêm, sửa, xóa và cập nhật tình trạng thuê của phòng trọ.</p>
        </div>
        <a href="post_room.php" class="btn btn-primary rounded-pill">
            <i class="fa-solid fa-circle-plus me-2"></i>Thêm tin đăng
        </a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if ($result && mysqli_num_rows($result) > 0): ?>
        <div class="table-responsive bg-white shadow-sm rounded-4 border">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Ảnh</th>
                        <th>Tiêu đề</th>
                        <?php if ($role === 2): ?><th>Người đăng</th><?php endif; ?>
                        <th>Giá</th>
                        <th>Khu vực</th>
                        <th>Tình trạng thuê</th>
                        <th>Trạng thái duyệt</th>
                        <th>Ngày tạo</th>
                        <th class="text-end">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($room = mysqli_fetch_assoc($result)): ?>
                        <?php
                            $images = array_filter(array_map('trim', explode(',', $room['images'] ?? '')));
                            $firstImage = !empty($images) ? reset($images) : 'default-room.jpg';
                            $isRented = (int)($room['is_rented'] ?? 0) === 1;
                        ?>
                        <tr>
                            <td style="width: 92px;">
                                <img src="uploads/rooms/<?php echo htmlspecialchars($firstImage); ?>" alt="Ảnh phòng" class="rounded-3 border" style="width:72px;height:56px;object-fit:cover;">
                            </td>
                            <td>
                                <div class="fw-bold"><?php echo htmlspecialchars($room['title']); ?></div>
                                <div class="text-muted small"><?php echo htmlspecialchars($room['category_name'] ?? 'Chưa phân loại'); ?></div>
                            </td>
                            <?php if ($role === 2): ?><td><?php echo htmlspecialchars($room['owner_name'] ?? 'Không rõ'); ?></td><?php endif; ?>
                            <td class="fw-semibold text-danger"><?php echo number_format((int)$room['price'], 0, ',', '.'); ?>đ</td>
                            <td><?php echo htmlspecialchars($room['district_name'] ?? ''); ?></td>
                            <td>
                                <?php if ($isRented): ?>
                                    <span class="badge bg-secondary">Đã thuê</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Còn trống</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ((int)$room['approve'] === 1): ?>
                                    <span class="badge bg-primary">Đã duyệt</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Chờ duyệt</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($room['created_at']); ?></td>
                            <td>
                                <div class="d-flex justify-content-end gap-2 flex-wrap">
                                    <a href="detail.php?id=<?php echo (int)$room['ID']; ?>" class="btn btn-sm btn-outline-primary">Xem</a>
                                    <a href="edit_room.php?id=<?php echo (int)$room['ID']; ?>" class="btn btn-sm btn-outline-secondary">Sửa</a>
                                    <form method="POST" class="m-0">
                                        <input type="hidden" name="id" value="<?php echo (int)$room['ID']; ?>">
                                        <?php if ($isRented): ?>
                                            <input type="hidden" name="action" value="mark_available">
                                            <button type="submit" class="btn btn-sm btn-outline-success">Đánh dấu còn trống</button>
                                        <?php else: ?>
                                            <input type="hidden" name="action" value="mark_rented">
                                            <button type="submit" class="btn btn-sm btn-outline-dark">Đánh dấu đã thuê</button>
                                        <?php endif; ?>
                                    </form>
                                    <form method="POST" class="m-0" onsubmit="return confirm('Bạn có chắc muốn xóa tin đăng này?');">
                                        <input type="hidden" name="id" value="<?php echo (int)$room['ID']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-4 shadow-sm border p-5 text-center">
            <h5 class="fw-bold">Chưa có tin đăng</h5>
            <p class="text-muted">Bắt đầu bằng cách thêm tin phòng trọ mới.</p>
            <a href="post_room.php" class="btn btn-primary rounded-pill">Thêm tin đăng</a>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
