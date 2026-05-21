<?php
$conn = mysqli_connect('localhost', 'root', '', 'gtpt');
if (!$conn) {
    die('ket noi that bai: ' . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");

$checkRentalColumn = mysqli_query($conn, "SHOW COLUMNS FROM motel LIKE 'is_rented'");
if ($checkRentalColumn && mysqli_num_rows($checkRentalColumn) === 0) {
    mysqli_query($conn, "ALTER TABLE motel ADD COLUMN is_rented TINYINT(1) NOT NULL DEFAULT 0 AFTER approve");
}
?>
