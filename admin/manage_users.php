<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../includes/db_config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 2) {
    header('Location: ../index.php'); exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'])) {
    $id = (int)$_POST['id'];
    if ($id > 0) {
        $roleField = 'role';
        $showRole = true;
        $resultField = mysqli_query($conn, "SHOW COLUMNS FROM user LIKE 'role'");
        if (mysqli_num_rows($resultField) === 0) {
            $roleField = 'Role';
        }

        if ($_POST['action'] === 'make_admin') {
            mysqli_query($conn, "UPDATE user SET $roleField = 2 WHERE ID = $id");
            $message = 'Đã nâng quyền thành admin.';
        } elseif ($_POST['action'] === 'make_user') {
            mysqli_query($conn, "UPDATE user SET $roleField = 1 WHERE ID = $id");
            $message = 'Đã chuyển về quyền thành viên.';
        } elseif ($_POST['action'] === 'delete_user' && $id !== $_SESSION['user']['ID']) {
            mysqli_query($conn, "DELETE FROM user WHERE ID = $id");
            $message = 'Đã xóa tài khoản người dùng.';
        }
    }
}

$sql = "SELECT *, COALESCE(role, Role, 1) AS effective_role FROM user ORDER BY ID DESC";
$result = mysqli_query($conn, $sql);

include '../includes/header.php';
?>

<div class="container my-5">

    <div class="d-flex gap-2">
            <a href="dashboard.php" class="btn btn-outline-secondary shadow-sm">
                <i class="fa-solid fa-house me-1"></i> Về trang chủ
            </a>
    </div>

    <div class="mb-4">
        <h2 class="fw-bold">Quản lý thành viên</h2>
        <p class="text-muted">Cấp quyền admin hoặc thành viên, và xóa tài khoản nếu cần.</p>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <div class="table-responsive shadow-sm rounded-4 border">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Họ tên</th>
                        <th>Quyền</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo $user['ID']; ?></td>
                            <td><?php echo htmlspecialchars($user['Username']); ?></td>
                            <td><?php echo htmlspecialchars($user['Email']); ?></td>
                            <td><?php echo htmlspecialchars($user['Name']); ?></td>
                            <td>
                                <?php if ((int)$user['effective_role'] === 2): ?>
                                    <span class="badge bg-primary">Admin</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Thành viên</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex gap-2 flex-wrap">
                                    <?php if ($user['ID'] !== $_SESSION['user']['ID']): ?>
                                        <form method="post" class="m-0">
                                            <input type="hidden" name="id" value="<?php echo $user['ID']; ?>">
                                            <input type="hidden" name="action" value="<?php echo (int)$user['effective_role'] === 2 ? 'make_user' : 'make_admin'; ?>">
                                            <button type="submit" class="btn btn-sm btn-<?php echo (int)$user['effective_role'] === 2 ? 'warning text-dark' : 'success'; ?>">
                                                <?php echo (int)$user['effective_role'] === 2 ? 'Giảm quyền' : 'Nâng quyền'; ?>
                                            </button>
                                        </form>
                                        <form method="post" class="m-0" onsubmit="return confirm('Bạn có chắc muốn xóa tài khoản này?');">
                                            <input type="hidden" name="id" value="<?php echo $user['ID']; ?>">
                                            <input type="hidden" name="action" value="delete_user">
                                            <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted">Tài khoản hiện tại</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">Chưa có thành viên nào.</div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
