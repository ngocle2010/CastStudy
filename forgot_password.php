<?php

session_start();

include 'includes/db_config.php';
include 'includes/header.php';

$showResetForm = false;

$email = '';





if(isset($_SESSION['reset_email'])){

    $email = $_SESSION['reset_email'];

    $query = mysqli_query(

        $conn,

        "SELECT *

         FROM user

         WHERE Email='$email'"
    );

    $user = mysqli_fetch_assoc($query);





    if($user['ResetApproved'] == 1){

        $showResetForm = true;
    }
}
?>





<div class="container mt-5">

    <div class="row justify-content-center">

        <div class="col-md-5">





            <div class="card shadow border-0 rounded-4">

                <div class="card-body p-4">





                    <h3 class="text-center text-primary mb-4">

                        Quên mật khẩu

                    </h3>





                    <!-- NHẬP EMAIL -->

                    <?php if(!$showResetForm): ?>





                        <form action="forgot_password_process.php"
                              method="POST">

                            <div class="mb-3">

                                <label>Email xác nhận</label>

                                <input type="email"
                                       name="email"
                                       class="form-control rounded-3"
                                       required>

                            </div>

                            <button class="btn btn-primary w-100 rounded-pill">

                                Gửi yêu cầu

                            </button>

                        </form>





                        <?php if(isset($_SESSION['reset_email'])): ?>

                            <div class="alert alert-warning mt-4">

                                Đang chờ admin xác nhận reset mật khẩu...

                            </div>

                        <?php endif; ?>





                    <?php else: ?>





                    <!-- ĐỔI MẬT KHẨU -->

                    <div class="alert alert-success">

                        Admin đã xác nhận. Bạn có thể đổi mật khẩu mới.

                    </div>





                    <form action="reset_password.php"
                          method="POST">

                        <input type="hidden"
                               name="email"
                               value="<?= $email ?>">

                        <div class="mb-3">

                            <label>Mật khẩu mới</label>

                            <input type="password"
                                   name="password"
                                   class="form-control rounded-3"
                                   required>

                        </div>

                        <button class="btn btn-success w-100 rounded-pill">

                            Đổi mật khẩu

                        </button>

                    </form>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </div>

</div>

<?php include 'includes/footer.php'; ?>