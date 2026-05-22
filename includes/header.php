<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Mẹo fix link: Kiểm tra nếu đang ở trong folder admin thì lùi ra 1 cấp
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
$path = ($current_dir == 'admin') ? '../' : '';

// Tự động kết nối DB chuẩn xác theo vị trí thư mục hiện tại
if (file_exists($path . 'db_config.php')) {
    include_once $path . 'db_config.php';
}

// Thông báo khi admin phản hồi
$notifyCount = 0;
if (isset($_SESSION['user']) && isset($_SESSION['user']['ID']) && isset($conn)) {
    $userID = (int)$_SESSION['user']['ID'];
    $notifyQuery = mysqli_query($conn,
        "SELECT COUNT(*) as total
         FROM feedbacks
         WHERE UserID='$userID'
         AND AdminReply IS NOT NULL
         AND IsRead = 0"
    );
<<<<<<< HEAD

    // Kiểm tra an toàn: Chỉ fetch khi truy vấn thành công (không bị trả về false)
    if ($notifyQuery) {
        $notifyData = mysqli_fetch_assoc($notifyQuery);
        $notifyCount = isset($notifyData['total']) ? intval($notifyData['total']) : 0;
    }
=======
    $notifyData = mysqli_fetch_assoc($notifyQuery);
    $notifyCount = $notifyData['total'];
    $notifyList = mysqli_query( $conn,
        "SELECT *
         FROM feedbacks
         WHERE UserID='$userID'
         AND AdminReply IS NOT NULL
         ORDER BY ID DESC
         LIMIT 5"
    );
>>>>>>> 0066ea24acd33da4deac12022c7b975a9a510208
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

$messageNotifyCount = 0;
$latestMessageNotice = null;
$headerCurrentRole = (int)(($_SESSION['user']['role'] ?? $_SESSION['user']['Role'] ?? 0) ?? 0);
if (isset($conn) && isset($_SESSION['user']) && isset($_SESSION['user']['ID']) && $headerCurrentRole !== 2) {
    $messageUserID = (int)$_SESSION['user']['ID'];
    $messageCountQuery = mysqli_query($conn,
        "SELECT COUNT(*) AS total
         FROM messages
         WHERE receiver_id = $messageUserID
           AND is_read = 0"
    );

    if ($messageCountQuery) {
        $messageCountRow = mysqli_fetch_assoc($messageCountQuery);
        $messageNotifyCount = isset($messageCountRow['total']) ? (int)$messageCountRow['total'] : 0;
    }

    if ($messageNotifyCount > 0) {
        $latestMessageQuery = mysqli_query($conn,
            "SELECT messages.motel_id, user.Name AS sender_name, motel.title AS room_title
             FROM messages
             JOIN user ON user.ID = messages.sender_id
             LEFT JOIN motel ON motel.ID = messages.motel_id
             WHERE messages.receiver_id = $messageUserID
               AND messages.is_read = 0
             ORDER BY messages.created_at DESC
             LIMIT 1"
        );

        if ($latestMessageQuery) {
            $latestMessageNotice = mysqli_fetch_assoc($latestMessageQuery);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trọ xịn – Giá mịn</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700;900&family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $path; ?>assets/css/style.css?v=<?php echo time(); ?>">
    <style>
        * {
            font-family: 'Roboto', 'Noto Sans JP', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif !important;
        }
        html, body {
            font-family: 'Roboto', 'Noto Sans JP', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif !important;
        }
    </style>
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
<<<<<<< HEAD
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="<?php echo $path; ?>feedback.php">Liên hệ
                        <i class="fa-solid fa-bell fs-5"></i>
                        <?php if($notifyCount > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?= $notifyCount ?>
                        </span>
                        <?php endif; ?>
=======
                    <li class="nav-item"><a class="nav-link fw-semibold" href="<?php echo $path; ?>feedback.php">Liên hệ</a></li>
                    <li class="nav-item dropdown">

                        <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown"> 
                            <i class="fa-solid fa-bell fs-5"></i>
                            <?php if($notifyCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?= $notifyCount ?>
                            </span>
                            <?php endif; ?>
>>>>>>> 0066ea24acd33da4deac12022c7b975a9a510208
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 p-2" style="width:350px; max-height:400px; overflow:auto;">
                            <li class="dropdown-header fw-bold text-primary">  Thông báo hệ thống</li>

                            <?php if(mysqli_num_rows($notifyList) > 0): ?>

                            <?php while($noti = mysqli_fetch_assoc($notifyList)): ?>
                            <li>
                                <a class="dropdown-item rounded-3 p-3 mb-2"  href="feedback.php">
                                    <div class="fw-bold text-dark">
                                        <?= $noti['Title'] ?>
                                    </div>
                                    <small class="text-muted">
                                    <?= mb_substr($noti['AdminReply'], 0, 50) ?>...
                                    </small>

                                     <?php if($noti['IsRead'] == 0): ?>
                                    <span class="badge bg-danger ms-2"> Mới </span>
                                     <?php endif; ?>
                                </a>
                            </li>
                            <?php endwhile; ?>

                            <?php else: ?>
                            <li>
                                <div class="dropdown-item text-muted">
                                Không có thông báo
                                </div>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <?php if ($headerCurrentRole !== 2): ?>
                    <li class="nav-item">
                        <?php
                            $messageNoticeLink = $path . 'chat.php';

                            $messageNoticeTitle = 'Tin nhắn';
                            if ($messageNotifyCount > 0 && $latestMessageNotice) {
                                $messageNoticeTitle = 'Bạn nhận được tin nhắn từ ' . ($latestMessageNotice['sender_name'] ?? 'người dùng');
                                if (!empty($latestMessageNotice['room_title'])) {
                                    $messageNoticeTitle .= ' về phòng ' . $latestMessageNotice['room_title'];
                                }
                            }
                        ?>
                        <a class="nav-link position-relative fw-semibold d-flex align-items-center gap-1" href="<?php echo htmlspecialchars($messageNoticeLink); ?>" title="<?php echo htmlspecialchars($messageNoticeTitle); ?>">
                            <i class="fa-solid fa-bell text-warning"></i>
                            Tin nhắn
                            <?php if($messageNotifyCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?php echo $messageNotifyCount; ?>
                            </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if(!isset($_SESSION['user']) && !isset($_SESSION['user_id'])): ?>
                    <li class="nav-item"><a class="nav-link fw-semibold" href="<?php echo $path; ?>login.php">Đăng nhập</a></li>
                    <li class="nav-item">
                        <a class="nav-link text-white btn btn-primary rounded-pill px-4 ms-lg-2 shadow-sm" href="<?php echo $path; ?>register.php">Tham gia ngay</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item dropdown ms-lg-3">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?php echo $path; ?>uploads/avatars/<?php echo isset($_SESSION['user']['Avatar']) ? htmlspecialchars($_SESSION['user']['Avatar']) : 'default.jpg'; ?>" class="rounded-circle me-2 border border-primary-subtle" width="35" height="35" style="object-fit: cover;">
                        <span class="fw-bold text-dark">Hi, <?php echo isset($_SESSION['user']['Name']) ? htmlspecialchars($_SESSION['user']['Name']) : 'User'; ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-3 rounded-4 p-2">
                        <li><a class="dropdown-item py-2 rounded-3" href="<?php echo $path; ?>profile.php"><i class="fa-solid fa-user-circle me-2 text-muted"></i> Hồ sơ cá nhân</a></li>
                        <?php $currentRole = $headerCurrentRole; ?>
                        <?php if ($currentRole === 1 || $currentRole === 2): ?>
                        <li><a class="dropdown-item py-2 rounded-3" href="<?php echo $path; ?>my-rooms.php"><i class="fa-solid fa-list-check me-2 text-muted"></i> Quản lý tin đăng</a></li>
                        <li><a class="dropdown-item py-2 rounded-3" href="<?php echo $path; ?>post_room.php"><i class="fa-solid fa-circle-plus me-2 text-muted"></i> Đăng tin mới</a></li>
                        <?php endif; ?>

                <?php if((isset($_SESSION['user']['role']) && $_SESSION['user']['role'] == 2) || (isset($_SESSION['user']['Role']) && $_SESSION['user']['Role'] == 2)): ?>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-primary fw-bold rounded-3" href="<?php echo $path; ?>admin/dashboard.php"><i class="fa-solid fa-gauge-high me-2"></i> Quản trị hệ thống</a></li>
                 <?php endif; ?>

        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item text-danger py-2 rounded-3" href="<?php echo $path; ?>logout.php"><i class="fa-solid fa-power-off me-2"></i> Đăng xuất</a></li>
    </ul>
                </li>
<?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
