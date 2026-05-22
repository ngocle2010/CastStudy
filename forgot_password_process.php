<?php

session_start();

include 'includes/db_config.php';

$email = mysqli_real_escape_string(
    $conn,
    $_POST['email']
);

$query = mysqli_query(

    $conn,

    "SELECT * FROM user
     WHERE Email='$email'"
);

if(mysqli_num_rows($query) > 0){

    mysqli_query(

        $conn,

        "UPDATE user

         SET ResetRequest = 1

         WHERE Email='$email'"
    );





    $_SESSION['reset_email'] = $email;

    header("Location: forgot_password.php");
    exit();
}

header("Location: forgot_password.php?error=email");
?>