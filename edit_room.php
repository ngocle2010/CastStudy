<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/db_config.php';

$role = (int)($_SESSION['user']['role'] ?? $_SESSION['user']['Role'] ?? 0);
$userId = (int)($_SESSION['user_id'] ?? $_SESSION['user']['ID'] ?? 0);
$roomId = (int)($_GET['id'] ?? 0);

if (!isset($_SESSION['user']) || ($role !== 1 && $role !== 2) || $userId <= 0 || $roomId <= 0) {
    header('Location: index.php');
    exit();
}

if ($role === 2) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM motel WHERE ID = ?");
    mysqli_stmt_bind_param($stmt, "i", $roomId);
} else {
    $stmt = mysqli_prepare($conn, "SELECT * FROM motel WHERE ID = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $roomId, $userId);
}
mysqli_stmt_execute($stmt);
$room = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$room) {
    header('Location: my-rooms.php');
    exit();
}

$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY Name");
$districts = mysqli_query($conn, "SELECT * FROM districts ORDER BY Name");
$roomLat = !empty($room['latitude']) ? (float)$room['latitude'] : 18.667238;
$roomLng = !empty($room['longitude']) ? (float)$room['longitude'] : 105.693334;
$images = array_filter(array_map('trim', explode(',', $room['images'] ?? '')));

include 'includes/header.php';
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

<div class="container my-5">
    <div class="card shadow p-4">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
            <div>
                <h3 class="fw-bold mb-1">Sửa tin đăng</h3>
                <p class="text-muted mb-0">Tin sẽ chuyển về trạng thái chờ duyệt sau khi cập nhật.</p>
            </div>
            <a href="my-rooms.php" class="btn btn-outline-secondary">Quay lại</a>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">Không thể cập nhật tin đăng. Vui lòng kiểm tra lại thông tin.</div>
        <?php endif; ?>

        <form action="update_room_process.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo (int)$room['ID']; ?>">

            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">Tiêu đề</label>
                    <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($room['title']); ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Giá</label>
                    <input type="number" name="price" class="form-control" value="<?php echo (int)$room['price']; ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Diện tích</label>
                    <input type="number" name="area" class="form-control" value="<?php echo (int)$room['area']; ?>" required>
                </div>
            </div>

            <div class="mt-3">
                <label class="form-label">Mô tả</label>
                <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($room['description'] ?? ''); ?></textarea>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <label class="form-label">Địa chỉ</label>
                    <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($room['address'] ?? ''); ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">SDT</label>
                    <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($room['phone'] ?? ''); ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tiện ích</label>
                    <input type="text" name="utilities" class="form-control" value="<?php echo htmlspecialchars($room['utilities'] ?? ''); ?>">
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <label class="form-label">Loại phòng</label>
                    <select name="category_id" class="form-select" required>
                        <?php while ($c = mysqli_fetch_assoc($categories)): ?>
                            <option value="<?php echo (int)$c['ID']; ?>" <?php echo (int)$room['category_id'] === (int)$c['ID'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($c['Name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Khu vực</label>
                    <select name="district_id" class="form-select" required>
                        <?php while ($d = mysqli_fetch_assoc($districts)): ?>
                            <option value="<?php echo (int)$d['ID']; ?>" <?php echo (int)$room['district_id'] === (int)$d['ID'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($d['Name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <label class="form-label fw-bold">Vị trí phòng trọ trên bản đồ</label>
                <div id="map" style="height: 380px; border-radius: 12px;"></div>
                <input type="hidden" name="lat" id="lat" value="<?php echo $roomLat; ?>" required>
                <input type="hidden" name="lng" id="lng" value="<?php echo $roomLng; ?>" required>
                <div class="mt-2 small text-muted">
                    Tọa độ đã chọn:
                    <span id="showLat"><?php echo number_format($roomLat, 6); ?></span>,
                    <span id="showLng"><?php echo number_format($roomLng, 6); ?></span>
                </div>
            </div>

            <div class="mt-4">
                <label class="form-label">Ảnh hiện tại</label>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <?php if (!empty($images)): ?>
                        <?php foreach ($images as $img): ?>
                            <img src="uploads/rooms/<?php echo htmlspecialchars($img); ?>" class="rounded-3 border" style="width:100px;height:80px;object-fit:cover;" alt="Ảnh phòng">
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span class="text-muted">Chưa có ảnh.</span>
                    <?php endif; ?>
                </div>
                <label class="form-label">Thêm ảnh mới</label>
                <input type="file" name="images[]" class="form-control" multiple onchange="previewImages(event)">
                <div id="preview" class="d-flex flex-wrap mt-2"></div>
            </div>

            <button type="submit" class="btn btn-primary w-100 mt-4">Cập nhật tin đăng</button>
        </form>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
function previewImages(event) {
    const preview = document.getElementById('preview');
    preview.innerHTML = "";
    for (const file of event.target.files) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.createElement("img");
            img.src = e.target.result;
            img.style.width = "100px";
            img.style.height = "80px";
            img.style.objectFit = "cover";
            img.style.margin = "5px";
            img.style.borderRadius = "8px";
            preview.appendChild(img);
        };
        reader.readAsDataURL(file);
    }
}

const roomPosition = [<?php echo $roomLat; ?>, <?php echo $roomLng; ?>];
const map = L.map('map').setView(roomPosition, 15);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap'
}).addTo(map);

let roomMarker = L.marker(roomPosition).addTo(map).bindPopup("Vị trí phòng trọ").openPopup();

map.on('click', function(e) {
    const lat = e.latlng.lat;
    const lng = e.latlng.lng;
    document.getElementById('lat').value = lat;
    document.getElementById('lng').value = lng;
    document.getElementById('showLat').innerText = lat.toFixed(6);
    document.getElementById('showLng').innerText = lng.toFixed(6);
    roomMarker.setLatLng(e.latlng).openPopup();
});
</script>

<?php include 'includes/footer.php'; ?>
