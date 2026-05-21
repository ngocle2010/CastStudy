<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Thông báo khi admin phản hồi
$notifyCount = 0;
if(isset($_SESSION['user'])){
    include_once 'db_config.php';
    $userID = $_SESSION['user']['ID'];
    $notifyQuery = mysqli_query( $conn,
        "SELECT COUNT(*) as total
         FROM feedbacks
         WHERE UserID='$userID'
         AND AdminReply IS NOT NULL
         AND IsRead = 0"
    );
    $notifyData = mysqli_fetch_assoc($notifyQuery);
    $notifyCount = $notifyData['total'];
}

// Đếm số tin đã lưu để hiển thị ở header
$favorites_count = 0;
if (isset($conn) && (isset($_SESSION['user_id']) || isset($_SESSION['user']['ID']))) {
    $userIdForFav = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : intval($_SESSION['user']['ID']);
    $favQuery = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM favorites WHERE user_id = $userIdForFav");
    if ($favQuery) {
        $favRow = mysqli_fetch_assoc($favQuery);
        $favorites_count = intval($favRow['cnt']);
    }
}

// Mẹo fix link: Kiểm tra nếu đang ở trong folder admin thì lùi ra 1 cấp
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
$path = ($current_dir == 'admin') ? '../' : '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trọ xịn – Giá mịn</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="<?php echo $path; ?>index.php">
            <i class="fa-solid fa-house-chimney-window me-2"></i>Trọ xịn – Giá mịn
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item"><a class="nav-link fw-semibold" href="<?php echo $path; ?>index.php">Trang chủ</a></li>
                <li class="nav-item"><a class="nav-link fw-semibold" href="<?php echo $path; ?>all_room.php">Phòng trọ</a></li>
                <?php if(isset($_SESSION['user'])): ?>
                    <li class="nav-item">
                        <a class="nav-link position-relative fw-semibold" href="<?php echo $path; ?>favorites.php">
                            <i class="fa-solid fa-heart text-danger me-1"></i>
                            Tin đã lưu
                            <span id="wishlistCount" class="badge bg-danger rounded-pill ms-1"><?php echo $favorites_count; ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="<?php echo $path; ?>feedback.php">Liên hệ
                        <i class="fa-solid fa-bell fs-5"></i> 
                        <?php if($notifyCount > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?= $notifyCount ?>
                        </span>
                        <?php endif; ?>
                        </a>
                    </li>
                <?php endif; ?>
                
                <?php if(!isset($_SESSION['user']) && !isset($_SESSION['user_id'])): ?>
                    <li class="nav-item"><a class="nav-link fw-semibold" href="<?php echo $path; ?>login.php">Đăng nhập</a></li>
                    <li class="nav-item">
                        <a class="nav-link text-white btn btn-primary rounded-pill px-4 ms-lg-2 shadow-sm" href="<?php echo $path; ?>register.php">Tham gia ngay</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item dropdown ms-lg-3">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?php echo $path; ?>uploads/avatars/<?php echo $_SESSION['user']['Avatar']; ?>" class="rounded-circle me-2 border border-primary-subtle" width="35" height="35" style="object-fit: cover;">
                        <span class="fw-bold text-dark">Hi, <?php echo $_SESSION['user']['Name']; ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-3 rounded-4 p-2">
                        <li><a class="dropdown-item py-2 rounded-3" href="<?php echo $path; ?>profile.php"><i class="fa-solid fa-user-circle me-2 text-muted"></i> Hồ sơ cá nhân</a></li>
                        <?php $currentRole = (int)($_SESSION['user']['role'] ?? $_SESSION['user']['Role'] ?? 0); ?>
                        <?php if ($currentRole === 1 || $currentRole === 2): ?>
                        <li><a class="dropdown-item py-2 rounded-3" href="<?php echo $path; ?>my-rooms.php"><i class="fa-solid fa-list-check me-2 text-muted"></i> Quản lý tin đăng</a></li>
                        <li><a class="dropdown-item py-2 rounded-3" href="<?php echo $path; ?>post_room.php"><i class="fa-solid fa-circle-plus me-2 text-muted"></i> Đăng tin mới</a></li>
                        <?php endif; ?>
                        
                <?php if(isset($_SESSION['user']['role']) && $_SESSION['user']['role'] == 2): ?> 
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-primary fw-bold rounded-3" href="<?php echo $path; ?>admin/dashboard.php"><i class="fa-solid fa-gauge-high me-2"></i> Quản trị hệ thống</a></li>
                 <?php endif; ?>
        
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item text-danger py-2 rounded-3" href="<?php echo $path; ?>logout.php"><i class="fa-solid fa-power-off me-2"></i> Đăng xuất</a></li>
    </ul>
                </li>
</li>

                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>


<?php if (isset($_SESSION['user'])): ?>
    <!-- Nút icon chat nổi -->
    <div id="chatFloatBtn" onclick="toggleChatBox()">
        <i class="fa-solid fa-comments"></i>
    </div>

    <!-- Khung chat -->
    <div id="chatBox">
        <div class="chat-box-header">
            <span><i class="fa-solid fa-comments me-2"></i>Chat với chủ trọ</span>
            <button onclick="toggleChatBox()">×</button>
        </div>

        <div class="chat-box-body">
            <div class="chat-user-list" id="chatUserList">
                <div class="chat-loading">Đang tải người dùng...</div>
            </div>

            <div class="chat-content">
                <div class="chat-title" id="chatTitle">Chọn người để chat</div>

                <div class="chat-messages" id="chatMessages">
                    <div class="chat-empty">Chưa chọn cuộc trò chuyện</div>
                </div>

                <div class="chat-input-area">
                    <input type="text" id="chatMessageInput" placeholder="Nhập tin nhắn...">

                    <button onclick="sendPopupMessage()">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const currentUserID = <?= (int)$_SESSION['user']['ID'] ?>;
    </script>

    <script src="<?= $path ?>assets/js/chat.js?v=<?= time(); ?>"></script>
<?php endif; ?>
