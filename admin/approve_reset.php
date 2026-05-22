<?php

include '../includes/db_config.php';

$id = (int)$_GET['id'];

$sql = "UPDATE user

        SET ResetApproved = 1

        WHERE ID = '$id'";

mysqli_query($conn, $sql);

header("Location: manage_users.php");
?>