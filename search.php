<?php 
session_start();
require_once 'includes/db_config.php'; 
require_once 'includes/header.php'; 

// 1. Lấy dữ liệu lọc từ URL 
$address_search = $_GET['address'] ?? '';
$district_id = isset($_GET['district']) && $_GET['district'] !== '' ? intval($_GET['district']) : 0;
$category_id = isset($_GET['category']) && $_GET['category'] !== '' ? intval($_GET['category']) : 0;
$price_range = $_GET['price'] ?? '';
$near_vinh = $_GET['near_vinh'] ?? '';
$utility = $_GET['utility'] ?? '';
// Xử lý khoảng giá từ index.php
$min_price = 0;
$max_price = 999999999;
if ($price_range == '1') {
    $max_price = 1500000;
} elseif ($price_range == '2') {
    $min_price = 1500000;
    $max_price = 3000000;
} elseif ($price_range == '3') {
    $min_price = 3000000;
}

// Nếu có district từ index.php, lấy address từ districts
if ($district_id > 0) {
    $district_query = mysqli_query($conn, "SELECT Name FROM districts WHERE ID = $district_id");
    if ($district_query && $row = mysqli_fetch_assoc($district_query)) {
        $address_search = $row['Name'];
    }
}

// Lấy danh sách loại phòng
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY Name ASC");
if (!$categories) {
    die('Lỗi truy vấn categories: ' . mysqli_error($conn));
}

// Lấy danh sách khu vực từ districts
$districts = mysqli_query($conn, "SELECT * FROM districts ORDER BY Name ASC");
if (!$districts) {
    die('Lỗi truy vấn districts: ' . mysqli_error($conn));
}

// Tọa độ ĐH Vinh để tính khoảng cách
$vinhLat = 18.667238; 
$vinhLng = 105.693334; 
$radiusKm = 3;
$distanceSql = "(6371 * acos(cos(radians($vinhLat)) * cos(radians(motel.latitude)) * cos(radians(motel.longitude) - radians($vinhLng)) + sin(radians($vinhLat)) * sin(radians(motel.latitude))))";

// 2. Xây dựng SQL truy vấn
$sql = "SELECT motel.*, categories.Name as category_name, $distanceSql AS distance_km
        FROM motel 
        JOIN categories ON motel.category_id = categories.ID 
        WHERE motel.approve = 1";

$sql .= " AND motel.price BETWEEN $min_price AND $max_price";

if ($category_id > 0) {
    $sql .= " AND motel.category_id = $category_id";
}

if ($district_id > 0) {
    $sql .= " AND motel.district_id = $district_id";
} elseif (!empty($address_search)) {
    $address_search = mysqli_real_escape_string($conn, $address_search);
    $sql .= " AND motel.address LIKE '%$address_search%'";
}
if (!empty($utility)) {
    $utility = mysqli_real_escape_string($conn, $utility);
    $sql .= " AND motel.utilities LIKE '%$utility%'";
}
if ($near_vinh == '1') {
    $sql .= " AND $distanceSql <= $radiusKm ORDER BY distance_km ASC";
} else {
    $sql .= " ORDER BY motel.created_at DESC";
}

$result = mysqli_query($conn, $sql);
if (!$result) {
    die('Lỗi truy vấn: ' . mysqli_error($conn));
}
?>

<div class="container my-5">
    <div class="card shadow-sm mb-4 border-0 bg-light">
        <div class="card-body p-4">
            <form action="search.php" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="fw-bold mb-1">Khu vực</label>
                    <select name="district" class="form-select">
                        <option value="">Tất cả khu vực</option>
                        <?php while ($dist = mysqli_fetch_assoc($districts)): ?>
                            <option value="<?php echo $dist['ID']; ?>" <?php echo ($district_id == $dist['ID']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($dist['Name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="fw-bold mb-1">Loại phòng</label>
                    <select name="category_id" class="form-select">
                        <option value="">Tất cả loại phòng</option>
                        <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                            <option value="<?php echo $cat['ID']; ?>" <?php echo ($category_id == $cat['ID']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['Name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="fw-bold mb-1">Khoảng giá (VNĐ)</label>
                    <div class="input-group">
                        <input type="number" name="min_price" class="form-control" placeholder="Từ" value="<?php echo $_GET['min_price'] ?? ''; ?>">
                        <span class="input-group-text">-</span>
                        <input type="number" name="max_price" class="form-control" placeholder="Đến" value="<?php echo $_GET['max_price'] ?? ''; ?>">
                    </div>
                </div>
                <div class="col-md-4">
    <label class="fw-bold mb-1">Tiện ích</label>
 
    <select name="utility" class="form-select">
        <option value="">Tất cả tiện ích</option>
        <option value="wifi" <?php echo ($utility == 'wifi') ? 'selected' : ''; ?>>Wifi</option>
        <option value="máy lạnh" <?php echo ($utility == 'máy lạnh') ? 'selected' : ''; ?>>Máy lạnh</option>
        <option value="tủ lạnh" <?php echo ($utility == 'tủ lạnh') ? 'selected' : ''; ?>>Tủ lạnh</option>
        <option value="máy giặt" <?php echo ($utility == 'máy giặt') ? 'selected' : ''; ?>>Máy giặt</option>
        <option value="nóng lạnh" <?php echo ($utility == 'nóng lạnh') ? 'selected' : ''; ?>>Nóng lạnh</option>
    </select>
</div>
                <div class="col-md-12 d-flex align-items-end">
                    <div class="form-check me-3">
                        <input class="form-check-input" type="checkbox" name="near_vinh" value="1" id="near_vinh" <?php echo ($near_vinh == '1') ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="near_vinh">
                            Gần ĐH Vinh (< 3km)
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass me-2"></i>Tìm kiếm</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4">
        <?php if ($result && mysqli_num_rows($result) > 0): ?>
            <?php while ($room = mysqli_fetch_assoc($result)): 
                // Xử lý ảnh: Cắt chuỗi để lấy ảnh đầu tiên và loại bỏ khoảng trắng/xuống dòng dư thừa
                $img_list = explode(',', $room['images']);
                $first_img = !empty($img_list[0]) ? trim($img_list[0]) : 'default.jpg';
                
                // Kiểm tra định dạng đường dẫn ảnh
                $img_src = (strpos($first_img, 'http') !== false) ? $first_img : 'uploads/rooms/' . $first_img;
                if (!file_exists($img_src)) {
                    $img_src = 'uploads/rooms/default.jpg'; // Đảm bảo có file default.jpg
                }

                $is_fav = false;
                $uid = 0;
                if (isset($_SESSION['user_id'])) {
                    $uid = intval($_SESSION['user_id']);
                } elseif (isset($_SESSION['user']['ID'])) {
                    $uid = intval($_SESSION['user']['ID']);
                }
                if ($uid > 0) {
                    $mid = $room['ID'];
                    $check_fav = mysqli_query($conn, "SELECT * FROM favorites WHERE user_id = $uid AND motel_id = $mid");
                    if ($check_fav && mysqli_num_rows($check_fav) > 0) $is_fav = true;
                }
            ?>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm position-relative">
                    <button class="btn btn-white position-absolute top-0 end-0 m-2 rounded-circle shadow-sm" 
                            onclick="toggleWishlist(<?php echo $room['ID']; ?>, this)" 
                            style="z-index: 5; width: 35px; height: 35px; border: none;">
                        <i class="<?php echo $is_fav ? 'fa-solid' : 'fa-regular'; ?> fa-heart text-danger"></i>
                    </button>
                    
                    <div class="position-relative">
                        <img src="<?php echo $img_src; ?>" class="card-img-top object-fit-cover" style="height: 200px;" alt="Ảnh phòng">
                        <?php if ((int)($room['is_rented'] ?? 0) === 1): ?>
                            <span class="position-absolute bottom-0 start-0 m-3 bg-secondary text-white px-2 py-1 rounded small shadow-sm">Đã thuê</span>
                        <?php else: ?>
                            <span class="position-absolute bottom-0 start-0 m-3 bg-success text-white px-2 py-1 rounded small shadow-sm">Còn trống</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="card-body">
                        <h6 class="text-primary fw-bold"><?php echo number_format($room['price'], 0, ',', '.'); ?> đ</h6>
                        <h5 class="card-title text-truncate"><?php echo htmlspecialchars($room['title']); ?></h5>
                        <p class="small text-muted"><i class="fa-solid fa-location-dot me-1"></i><?php echo htmlspecialchars($room['address']); ?></p>
                        
                        <?php if($near_vinh == '1' && isset($room['distance_km'])): ?>
                            <p class="small text-success"><i class="fa-solid fa-route me-1"></i>Cách ĐH Vinh: <?php echo round($room['distance_km'], 1); ?> km</p>
                        <?php endif; ?>

                        <a href="detail.php?id=<?php echo $room['ID']; ?>" class="btn btn-outline-dark w-100">Xem ngay</a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5"><h4>Không có phòng nào khớp với yêu cầu của bạn.</h4></div>
        <?php endif; ?>
    </div>
</div>

<script src="assets/js/main.js"></script>
<?php include 'includes/footer.php'; ?>
