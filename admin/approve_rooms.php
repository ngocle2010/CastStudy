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
        if ($_POST['action'] === 'approve') {
            mysqli_query($conn, "UPDATE motel SET approve = 1 WHERE ID = $id");
            $message = 'Đã duyệt tin đăng.';
        } elseif ($_POST['action'] === 'delete') {
            mysqli_query($conn, "DELETE FROM motel WHERE ID = $id");
            $message = 'Đã xóa tin đăng.';
        }
    }
}

$sql = "SELECT motel.*, user.Name AS owner_name, categories.Name AS category_name
        FROM motel
        LEFT JOIN user ON motel.user_id = user.ID
        LEFT JOIN categories ON motel.category_id = categories.ID
        WHERE motel.approve = 0
        ORDER BY motel.created_at DESC";
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
        <h2 class="fw-bold">Duyệt tin đăng</h2>
        <p class="text-muted">Danh sách tin chờ duyệt. Admin có thể duyệt hoặc xóa tin.</p>
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
                        <th>Tiêu đề</th>
                        <th>Người đăng</th>
                        <th>Danh mục</th>
                        <th>Giá</th>
                        <th>Ngày tạo</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($room = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo $room['ID']; ?></td>
                            <td><?php echo htmlspecialchars($room['title']); ?></td>
                            <td><?php echo htmlspecialchars($room['owner_name'] ?? 'Khách'); ?></td>
                            <td><?php echo htmlspecialchars($room['category_name'] ?? 'Không rõ'); ?></td>
                            <td><?php echo number_format((int)$room['price']); ?> đ</td>
                            <td><?php echo htmlspecialchars($room['created_at']); ?></td>
                            <td>
                                <div class="d-flex gap-2">
                                    <form method="post" class="m-0">
                                        <input type="hidden" name="id" value="<?php echo $room['ID']; ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn btn-sm btn-success">Duyệt</button>
                                    </form>
                                    <form method="post" class="m-0" onsubmit="return confirm('Bạn có chắc muốn xóa tin này?');">
                                        <input type="hidden" name="id" value="<?php echo $room['ID']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">Hiện không có tin đăng nào đang chờ duyệt.</div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>