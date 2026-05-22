<?php

session_start();

include 'includes/db_config.php';

$email = $_POST['email'];

$password = password_hash(

    $_POST['password'],

    PASSWORD_DEFAULT
);





mysqli_query(

    $conn,

    "UPDATE user

     SET

     Password = '$password',

     ResetRequest = 0,

     ResetApproved = 0

     WHERE Email='$email'"
);





unset($_SESSION['reset_email']);

header("Location: login.php?reset=success");
exit();
?>