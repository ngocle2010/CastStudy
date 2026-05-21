<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'includes/db_config.php';

$role = (int)($_SESSION['user']['role'] ?? $_SESSION['user']['Role'] ?? 0);

if (!isset($_SESSION['user']) || ($role !== 1 && $role !== 2)) {
    header("Location: index.php");
    exit();
}

$user_id = (int)($_SESSION['user_id'] ?? $_SESSION['user']['ID'] ?? $_SESSION['user']['id'] ?? 0);

if ($user_id <= 0) {
    die("Lỗi: Không xác định được tài khoản đăng nhập.");
}

// lấy dữ liệu form
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$price = (int)($_POST['price'] ?? 0);
$area = (int)($_POST['area'] ?? 0);
$address = trim($_POST['address'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$utilities = trim($_POST['utilities'] ?? '');
$category_id = (int)($_POST['category_id'] ?? 0);
$district_id = (int)($_POST['district_id'] ?? 0);

$latitude = $_POST['lat'] ?? null;
$longitude = $_POST['lng'] ?? null;

// kiểm tra tọa độ bắt buộc
if ($latitude === null || $longitude === null || $latitude === '' || $longitude === '') {
    die("Lỗi: Bạn chưa chọn vị trí phòng trọ trên bản đồ.");
}

$latitude = (float)$latitude;
$longitude = (float)$longitude;

// upload ảnh
$uploadDir = "uploads/rooms/";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$imageNames = [];

if (!empty($_FILES['images']['name'][0])) {
    foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['images']['error'][$key] == 0) {

            $originalName = basename($_FILES['images']['name'][$key]);
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($ext, $allowedExt)) {
                continue;
            }

            $fileName = time() . "_" . uniqid() . "." . $ext;
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($tmp_name, $targetPath)) {
                $imageNames[] = $fileName;
            }
        }
    }
}

$images = implode(",", $imageNames);

// lưu vào DB
$sql = "INSERT INTO motel 
(title, description, price, area, address, images, user_id, category_id, district_id, utilities, phone, approve, latitude, longitude)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?)";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Prepare lỗi: " . mysqli_error($conn));
}

mysqli_stmt_bind_param(
    $stmt,
    "ssiissiiissdd",
    $title,
    $description,
    $price,
    $area,
    $address,
    $images,
    $user_id,
    $category_id,
    $district_id,
    $utilities,
    $phone,
    $latitude,
    $longitude
);

if (!mysqli_stmt_execute($stmt)) {
    die("Execute lỗi: " . mysqli_stmt_error($stmt));
}

mysqli_stmt_close($stmt);

header("Location: post_room.php?success=1");
exit();
?>
