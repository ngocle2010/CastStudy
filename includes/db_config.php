<?php
$conn = mysqli_connect('localhost', 'root', '', 'gtpt2');
if (!$conn) {
    die('ket noi that bai: ' . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");

$checkRentalColumn = mysqli_query($conn, "SHOW COLUMNS FROM motel LIKE 'is_rented'");
if ($checkRentalColumn && mysqli_num_rows($checkRentalColumn) === 0) {
    mysqli_query($conn, "ALTER TABLE motel ADD COLUMN is_rented TINYINT(1) NOT NULL DEFAULT 0 AFTER approve");
}

$checkMessageRoomColumn = mysqli_query($conn, "SHOW COLUMNS FROM messages LIKE 'motel_id'");
if ($checkMessageRoomColumn && mysqli_num_rows($checkMessageRoomColumn) === 0) {
    mysqli_query($conn, "ALTER TABLE messages ADD COLUMN motel_id INT NULL AFTER receiver_id");
    mysqli_query($conn, "ALTER TABLE messages ADD INDEX idx_messages_motel_id (motel_id)");
}

$checkMessageReadColumn = mysqli_query($conn, "SHOW COLUMNS FROM messages LIKE 'is_read'");
if ($checkMessageReadColumn && mysqli_num_rows($checkMessageReadColumn) === 0) {
    mysqli_query($conn, "ALTER TABLE messages ADD COLUMN is_read TINYINT(1) NOT NULL DEFAULT 0 AFTER message");
    mysqli_query($conn, "ALTER TABLE messages ADD INDEX idx_messages_receiver_read (receiver_id, is_read)");
}
?>
