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

$roomId = (int)($_POST['id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$price = (int)($_POST['price'] ?? 0);
$area = (int)($_POST['area'] ?? 0);
$address = trim($_POST['address'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$utilities = trim($_POST['utilities'] ?? '');
$categoryId = (int)($_POST['category_id'] ?? 0);
$districtId = (int)($_POST['district_id'] ?? 0);
$latitude = $_POST['lat'] ?? '';
$longitude = $_POST['lng'] ?? '';

if ($roomId <= 0 || $title === '' || $price <= 0 || $area <= 0 || $address === '' || $phone === '' || $categoryId <= 0 || $districtId <= 0 || $latitude === '' || $longitude === '') {
    header('Location: edit_room.php?id=' . $roomId . '&error=1');
    exit();
}

if ($role === 2) {
    $stmt = mysqli_prepare($conn, "SELECT images FROM motel WHERE ID = ?");
    mysqli_stmt_bind_param($stmt, "i", $roomId);
} else {
    $stmt = mysqli_prepare($conn, "SELECT images FROM motel WHERE ID = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $roomId, $userId);
}
mysqli_stmt_execute($stmt);
$existing = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$existing) {
    header('Location: my-rooms.php');
    exit();
}

$imageNames = array_filter(array_map('trim', explode(',', $existing['images'] ?? '')));
$uploadDir = "uploads/rooms/";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if (!empty($_FILES['images']['name'][0])) {
    foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
        if ($_FILES['images']['error'][$key] !== 0) {
            continue;
        }

        $originalName = basename($_FILES['images']['name'][$key]);
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($ext, $allowedExt, true)) {
            continue;
        }

        $fileName = time() . "_" . uniqid() . "." . $ext;
        if (move_uploaded_file($tmpName, $uploadDir . $fileName)) {
            $imageNames[] = $fileName;
        }
    }
}

$images = implode(",", $imageNames);
$latitude = (float)$latitude;
$longitude = (float)$longitude;

if ($role === 2) {
    $sql = "UPDATE motel
            SET title = ?, description = ?, price = ?, area = ?, address = ?, images = ?, category_id = ?, district_id = ?, utilities = ?, phone = ?, approve = 0, latitude = ?, longitude = ?
            WHERE ID = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssiissiissddi", $title, $description, $price, $area, $address, $images, $categoryId, $districtId, $utilities, $phone, $latitude, $longitude, $roomId);
} else {
    $sql = "UPDATE motel
            SET title = ?, description = ?, price = ?, area = ?, address = ?, images = ?, category_id = ?, district_id = ?, utilities = ?, phone = ?, approve = 0, latitude = ?, longitude = ?
            WHERE ID = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssiissiissddii", $title, $description, $price, $area, $address, $images, $categoryId, $districtId, $utilities, $phone, $latitude, $longitude, $roomId, $userId);
}

if (!$stmt || !mysqli_stmt_execute($stmt)) {
    header('Location: edit_room.php?id=' . $roomId . '&error=1');
    exit();
}

mysqli_stmt_close($stmt);
header('Location: my-rooms.php?updated=1');
exit();
?>
