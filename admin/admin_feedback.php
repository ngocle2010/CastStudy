<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/db_config.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 2) {

    header('Location: /index.php');
    exit();
}
include '../includes/header.php';
$sql = "SELECT feedbacks.*, user.Username
        FROM feedbacks
        JOIN user
        ON feedbacks.UserID = user.ID
        ORDER BY feedbacks.ID DESC";
$result = mysqli_query($conn, $sql);
?>

<div class="container my-5">

    <div class="d-flex gap-2">
            <a href="dashboard.php" class="btn btn-outline-secondary shadow-sm">
                <i class="fa-solid fa-house me-1"></i> Về trang chủ
            </a>
    </div>

    <div class="mb-5">
        <h2 class="fw-bold text-dark">
            <i class="fa-solid fa-headset text-primary me-3"></i>
            Quản lý phản hồi hệ thống
        </h2>

        <p class="text-muted">
            Xem và phản hồi các yêu cầu từ người dùng
        </p>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <?php while($row = mysqli_fetch_assoc($result)): ?>
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="fw-bold text-primary mb-1">
                                <?= htmlspecialchars($row['Title']) ?>
                            </h5>

                            <small class="text-muted">
                                <i class="fa-solid fa-user me-1"></i>
                                <?= htmlspecialchars($row['Username']) ?>
                            </small>
                        </div>

                        <?php if($row['Status'] == 'pending'): ?>
                            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">
                                Đang chờ
                            </span>
                        <?php else: ?>
                            <span class="badge bg-success px-3 py-2 rounded-pill">
                                Đã phản hồi
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="mb-4">
                        <div class="fw-bold mb-2"> Nội dung phản hồi:</div>
                        <div class="bg-light rounded-4 p-3">
                            <?= nl2br(htmlspecialchars($row['Content'])) ?>
                        </div>
                    </div>

                    <?php if(!empty($row['AdminReply'])): ?>
                        <div class="alert alert-success rounded-4 border-0">
                            <?= nl2br(htmlspecialchars($row['AdminReply'])) ?>
                        </div>
                    <?php endif; ?>

                    <form action="reply_feedback.php"  method="POST"  class="mt-4">
                        <input type="hidden"   name="id" value="<?= $row['ID'] ?>">
                        <div class="mb-3">
                            <textarea name="reply"  class="form-control rounded-4 p-3"  rows="4"  placeholder="Nhập phản hồi cho người dùng..."  required></textarea>
                        </div>

                        <button type="submit"  class="btn btn-primary rounded-pill px-4 py-2 shadow-sm">
                            <i class="fa-solid fa-paper-plane me-2"></i>
                            Gửi phản hồi
                        </button>
                    </form>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<style>
.card {
    transition: 0.2s ease;
}
.card:hover {
    transform: translateY(-3px);
}
textarea:focus {
    box-shadow: none !important;
    border-color: #4e73df !important;
}
</style>
<?php include '../includes/footer.php'; ?>