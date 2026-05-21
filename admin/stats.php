<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once '../includes/db_config.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 2) {
    header('Location: ../index.php'); exit();
}

$thisYear = date('Y');

$statsQuery = "SELECT MONTH(created_at) AS month, COUNT(*) AS total
               FROM motel
               WHERE YEAR(created_at) = $thisYear
               GROUP BY MONTH(created_at)
               ORDER BY MONTH(created_at) ASC";
$statsResult = mysqli_query($conn, $statsQuery);

$monthly = array_fill(1, 12, 0);
while ($row = mysqli_fetch_assoc($statsResult)) {
    $monthly[(int)$row['month']] = (int)$row['total'];
}

$topPostsQuery = "SELECT ID, title, count_view, approve FROM motel ORDER BY count_view DESC LIMIT 5";
$topPostsResult = mysqli_query($conn, $topPostsQuery);

include '../includes/header.php';
?>

<div class="container my-5">

    <div class="d-flex gap-2">
            <a href="dashboard.php" class="btn btn-outline-secondary shadow-sm">
                <i class="fa-solid fa-house me-1"></i> Về trang chủ
            </a>
    </div>

    <div class="mb-4">
        <h2 class="fw-bold">Thống kê hệ thống</h2>
        <p class="text-muted">Báo cáo số tin đăng theo tháng và bài đăng được xem nhiều nhất.</p>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm rounded-4 p-4 mb-4">
                <h5 class="fw-bold mb-3">Số tin đăng theo tháng (<?php echo $thisYear; ?>)</h5>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tháng</th>
                                <th>Số tin đăng</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($monthly as $month => $count): ?>
                                <tr>
                                    <td>Tháng <?php echo $month; ?></td>
                                    <td><?php echo $count; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm rounded-4 p-4 mb-4">
                <h5 class="fw-bold mb-3">Top 5 bài đăng theo lượt xem</h5>
                <?php if (mysqli_num_rows($topPostsResult) > 0): ?>
                    <ul class="list-group list-group-flush">
                        <?php while ($post = mysqli_fetch_assoc($topPostsResult)): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?php echo htmlspecialchars($post['title']); ?></strong>
                                    <div class="small text-muted">ID: <?php echo $post['ID']; ?></div>
                                </div>
                                <span class="badge bg-primary rounded-pill"><?php echo (int)$post['count_view']; ?></span>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <div class="text-muted">Chưa có bài đăng.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
