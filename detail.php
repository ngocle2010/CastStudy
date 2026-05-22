<?php
session_start();
require_once 'includes/db_config.php';
require_once 'includes/header.php';

// Tọa độ Đại học Vinh mới
$vinhLat = 18.667238;
$vinhLng = 105.693334;

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $currentRole = (int)($_SESSION['user']['role'] ?? $_SESSION['user']['Role'] ?? 0);
    $currentUserId = (int)($_SESSION['user_id'] ?? $_SESSION['user']['ID'] ?? 0);
    $ownerRoomFilter = ($currentRole === 1 && $currentUserId > 0) ? " AND motel.user_id = $currentUserId" : "";

    $sql = "SELECT motel.*, user.Name as owner_name, user.Avatar as owner_avatar
            FROM motel
            LEFT JOIN user ON motel.user_id = user.ID
            WHERE motel.ID = '$id' $ownerRoomFilter";

    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $room = mysqli_fetch_assoc($result);
        mysqli_query($conn, "UPDATE motel SET count_view = count_view + 1 WHERE ID = '$id'");

        $raw_images = explode(',', $room['images']);
        $all_images = array_filter(array_map('trim', $raw_images));
        $first_img = !empty($all_images) ? reset($all_images) : 'default-room.jpg';

        $is_fav = false;
        $fav_user_id = 0;
        if (isset($_SESSION['user_id'])) {
            $fav_user_id = intval($_SESSION['user_id']);
        } elseif (isset($_SESSION['user']['ID'])) {
            $fav_user_id = intval($_SESSION['user']['ID']);
        }
        if ($fav_user_id > 0) {
            $favCheck = mysqli_query($conn, "SELECT * FROM favorites WHERE user_id = $fav_user_id AND motel_id = " . intval($room['ID']));
            if ($favCheck && mysqli_num_rows($favCheck) > 0) {
                $is_fav = true;
            }
        }
    } else {
        echo "<div class='container my-5'><h3>không tìm thấy phòng trọ này m ơi!</h3></div>";
        include 'includes/footer.php';
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}

// Nếu phòng chưa có tọa độ thì lấy tạm tọa độ Đại học Vinh
$roomLat = !empty($room['latitude']) ? (float)$room['latitude'] : $vinhLat;
$roomLng = !empty($room['longitude']) ? (float)$room['longitude'] : $vinhLng;
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($room['title']); ?></li>
                </ol>
            </nav>

            <div class="gallery-container mb-4">
                <div class="main-img-box rounded-4 overflow-hidden shadow-sm mb-2" style="height: 450px;">
                    <img id="main-view" src="uploads/rooms/<?php echo htmlspecialchars($first_img); ?>" class="w-100 h-100 object-fit-cover" alt="Phòng trọ">
                </div>

                <div class="row g-2">
                    <?php foreach($all_images as $img_item) { ?>
                    <div class="col-3 col-md-2">
                        <div class="thumb-box rounded-3 overflow-hidden border" style="height: 70px; cursor: pointer;">
                            <img src="uploads/rooms/<?php echo htmlspecialchars($img_item); ?>"
                                 class="w-100 h-100 object-fit-cover"
                                 onclick="document.getElementById('main-view').src=this.src">
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>

            <h2 class="fw-bold mb-3"><?php echo htmlspecialchars($room['title']); ?></h2>
            <p class="text-muted">
                <i class="fa-solid fa-location-dot me-2"></i>
                <?php echo htmlspecialchars($room['address']); ?>
            </p>

            <hr class="my-4 opacity-50">

            <h5 class="fw-bold mb-3">Mô tả chi tiết</h5>
            <p class="text-secondary leading-relaxed">
                <?php echo nl2br(htmlspecialchars($room['description'])); ?>
            </p>

            <div class="row g-3 my-4 text-center">
                <?php
                $utils = explode(',', $room['utilities']);
                foreach($utils as $u) {
                    if(!empty(trim($u))) {
                ?>
                <div class="col-md-3 col-6">
                    <div class="p-3 bg-white rounded-4 shadow-sm border border-light h-100">
                        <i class="fa-solid fa-check-circle text-success mb-2 fs-4"></i>
                        <div class="small fw-bold"><?php echo htmlspecialchars(trim($u)); ?></div>
                    </div>
                </div>
                <?php } } ?>
            </div>

            <div class="mt-5">
                <h5 class="fw-bold mb-3">Vị trí & Chỉ đường</h5>

                <div id="detailMap" class="rounded-4 shadow-sm border" style="height: 350px;"></div>

                <div class="mt-3 d-flex flex-wrap gap-2">
                    <a
                        href="https://www.openstreetmap.org/directions?from=<?php echo $vinhLat; ?>,<?php echo $vinhLng; ?>&to=<?php echo $roomLat; ?>,<?php echo $roomLng; ?>"
                        target="_blank"
                        class="btn btn-outline-primary rounded-pill px-4"
                    >
                        <i class="fa-solid fa-route me-2"></i> Chỉ đường từ Đại học Vinh
                    </a>

                    <a
                        href="https://www.openstreetmap.org/?mlat=<?php echo $roomLat; ?>&mlon=<?php echo $roomLng; ?>#map=17/<?php echo $roomLat; ?>/<?php echo $roomLng; ?>"
                        target="_blank"
                        class="btn btn-outline-dark rounded-pill px-4"
                    >
                        <i class="fa-solid fa-map-location-dot me-2"></i> Mở bản đồ lớn
                    </a>
                </div>
            </div>

            <!-- COMMENT -->
            <div class="mt-5">
                <h4 class="fw-bold mb-3">Đánh giá</h4>

                <?php if (isset($_SESSION['user']) && isset($_SESSION['user']['ID'])): ?>
                <form action="add_comment.php" method="POST" class="mb-4">
                    <input type="hidden" name="motel_id" value="<?php echo $room['ID']; ?>">

                    <textarea name="content" class="form-control mb-2" placeholder="Viết bình luận..." required></textarea>

                    <div class="d-flex justify-content-between">
                        <select name="rating" class="form-select w-auto">
                            <option value="5">⭐⭐⭐⭐⭐</option>
                            <option value="4">⭐⭐⭐⭐</option>
                            <option value="3">⭐⭐⭐</option>
                            <option value="2">⭐⭐</option>
                            <option value="1">⭐</option>
                        </select>

                        <button class="btn btn-primary">Gửi</button>
                    </div>
                </form>
                <?php else: ?>
                    <p>👉 Vui lòng đăng nhập để bình luận</p>
                <?php endif; ?>

                <?php
                $roomID = (int)$room['ID'];
                $sql_cmt = "SELECT comments.*, user.Name, user.Avatar
                            FROM comments
                            JOIN user ON comments.user_id = user.ID
                            WHERE motel_id = $roomID
                            ORDER BY created_at DESC";

                $res_cmt = mysqli_query($conn, $sql_cmt);

                while($c = mysqli_fetch_assoc($res_cmt)){
                ?>
                <div class="card mb-2 p-3">
                    <div class="d-flex align-items-center mb-2">
                        <img src="uploads/avatars/<?php echo htmlspecialchars($c['Avatar']); ?>"
                             width="40" height="40"
                             class="rounded-circle me-2"
                             style="object-fit:cover;">

                        <div>
                            <b><?php echo htmlspecialchars($c['Name']); ?></b>
                            <div style="font-size:12px;color:gray;">
                                <?php echo htmlspecialchars($c['created_at']); ?>
                            </div>
                        </div>
                    </div>

                    <div>
                        <?php echo str_repeat("⭐", (int)$c['rating']); ?>
                    </div>

                    <p class="mt-2 mb-1"><?php echo htmlspecialchars($c['content']); ?></p>

                    <div class="small text-muted position-relative mt-1">
                        <span style="cursor:pointer;" onmouseover="showReact(<?php echo $c['ID']; ?>)">
                            ❤️ Yêu thích
                        </span>

                        · <?php echo htmlspecialchars($c['created_at']); ?>

                        <div id="react-box-<?php echo $c['ID']; ?>" class="react-box">
                            <span onclick="react(<?php echo $c['ID']; ?>,'love')">❤️</span>
                        </div>

                        <div class="mt-1">
                            ❤️ <?php echo $c['react_love'] ?? 0; ?>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="sticky-top" style="top: 100px; z-index: 10;">
                <div class="card border-0 shadow-lg p-4 rounded-4 mb-4">
                    <div class="text-muted small mb-2">Giá phòng tháng này:</div>
                    <h3 class="text-danger fw-extrabold mb-4">
                        <?php echo number_format($room['price'], 0, ',', '.'); ?>đ
                        <small class="text-muted fs-6">/ tháng</small>
                    </h3>

                    <?php if ((int)($room['is_rented'] ?? 0) === 1): ?>
                        <div class="alert alert-secondary py-2 mb-4 fw-bold">Đã thuê</div>
                    <?php else: ?>
                        <div class="alert alert-success py-2 mb-4 fw-bold">Còn trống</div>
                    <?php endif; ?>

                    <div class="d-grid gap-2">
                        <a href="tel:<?php echo htmlspecialchars($room['phone']); ?>" class="btn btn-primary py-3 fw-bold rounded-pill shadow">
                            <i class="fa-solid fa-phone me-2"></i> Gọi ngay: <?php echo htmlspecialchars($room['phone']); ?>
                        </a>
                        <button type="button" class="btn btn-outline-dark py-3 fw-bold rounded-pill" onclick="toggleWishlist(<?php echo $room['ID']; ?>, this)">
                            <i class="<?php echo $is_fav ? 'fa-solid' : 'fa-regular'; ?> fa-heart me-2"></i> Lưu tin này
                        </button>
                    </div>

                    <div class="mt-4 pt-4 border-top">
                        <div class="d-flex align-items-center mb-3">
                            <img src="uploads/<?php echo htmlspecialchars($room['owner_avatar']); ?>" class="rounded-circle me-3" width="50" height="50" style="object-fit: cover;">
                            <div>
                                <div class="fw-bold"><?php echo htmlspecialchars($room['owner_name']); ?></div>
                                <div class="text-muted small">Chủ trọ tin cậy</div>
                            </div>
                        </div>

                        <?php if (isset($_SESSION['user']) && isset($_SESSION['user']['ID'])): ?>
                            <?php $isRoomOwner = (int)$_SESSION['user']['ID'] === (int)$room['user_id']; ?>
                            <?php if ($isRoomOwner): ?>
                            <button type="button" id="roomMessagesButton" class="btn btn-success w-100 py-2 fw-bold rounded-pill" onclick='toggleDetailChatBox(0, "", <?php echo (int)$room["ID"]; ?>, <?php echo json_encode($room["title"], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>, true)'>
                                <i class="fa-solid fa-comments me-2"></i> Tin nhắn phòng này
                            </button>
                            <?php else: ?>
                            <button type="button" class="btn btn-success w-100 py-2 fw-bold rounded-pill" onclick='toggleDetailChatBox(<?php echo (int)$room["user_id"]; ?>, <?php echo json_encode($room["owner_name"], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>, <?php echo (int)$room["ID"]; ?>, <?php echo json_encode($room["title"], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>)'>
                                <i class="fa-solid fa-comments me-2"></i> Chat với chủ trọ
                            </button>
                            <?php endif; ?>
                        <?php else: ?>
                        <a href="login.php" class="btn btn-success w-100 py-2 fw-bold rounded-pill">
                            <i class="fa-solid fa-comments me-2"></i> Chat với chủ trọ
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card border-0 shadow-sm p-4 rounded-4">
                    <h6 class="fw-bold mb-3">
                        <i class="fa-solid fa-school text-primary me-2"></i> Khoảng cách
                    </h6>
                    <div id="distanceText" class="text-muted small">Đang tính khoảng cách tới Đại học Vinh...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.react-box{
    display:none;
    position:absolute;
    background:#fff;
    padding:5px 10px;
    border-radius:30px;
    box-shadow:0 2px 10px rgba(0,0,0,0.2);
    top:-40px;
}

.react-box span{
    font-size:20px;
    margin:0 5px;
    cursor:pointer;
    transition:0.2s;
}

.react-box span:hover{
    transform:scale(1.3);
}
</style>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="assets/js/search.js"></script>

<script>
const roomLat = <?php echo $roomLat; ?>;
const roomLng = <?php echo $roomLng; ?>;

const vinhLat = <?php echo $vinhLat; ?>;
const vinhLng = <?php echo $vinhLng; ?>;

const vinhUniversity = [vinhLat, vinhLng];
const roomPosition = [roomLat, roomLng];

const detailMap = L.map('detailMap').setView(roomPosition, 15);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap'
}).addTo(detailMap);

L.marker(vinhUniversity)
    .addTo(detailMap)
    .bindPopup("Đại học Vinh");

L.marker(roomPosition)
    .addTo(detailMap)
    .bindPopup("<?php echo addslashes($room['title']); ?>")
    .openPopup();

L.polyline([vinhUniversity, roomPosition], {
    weight: 4
}).addTo(detailMap);

function getDistanceKm(lat1, lon1, lat2, lon2) {
    const R = 6371;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;

    const a =
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(lat1 * Math.PI / 180) *
        Math.cos(lat2 * Math.PI / 180) *
        Math.sin(dLon / 2) *
        Math.sin(dLon / 2);

    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
}

const distance = getDistanceKm(vinhLat, vinhLng, roomLat, roomLng);

document.getElementById("distanceText").innerHTML =
    "Cách Đại học Vinh khoảng <strong>" + distance.toFixed(2) + " km</strong> Theo đường chim bay.";

function showReact(id){
    document.getElementById("react-box-" + id).style.display = "block";
}

function react(id, type){
    fetch("react.php", {
        method:"POST",
        headers:{"Content-Type":"application/x-www-form-urlencoded"},
        body:"id=" + id + "&type=" + type
    }).then(() => location.reload());
}
</script>

<!-- CHAT POPUP MODAL -->
<style>
.detail-chat-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(15, 23, 42, 0.42);
    backdrop-filter: blur(2px);
    z-index: 999;
}

.detail-chat-box {
    display: none;
    position: fixed;
    bottom: 24px;
    right: 24px;
    width: min(430px, calc(100vw - 32px));
    height: min(640px, calc(100vh - 48px));
    background: white;
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 18px;
    box-shadow: 0 24px 70px rgba(15, 23, 42, 0.28);
    overflow: hidden;
    z-index: 1000;
    flex-direction: column;
}

.detail-chat-box.active {
    display: flex;
}

.detail-chat-header {
    min-height: 66px;
    background: linear-gradient(135deg, #0f172a, #2563eb);
    color: white;
    padding: 14px 18px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
}

.detail-chat-header h5 {
    margin: 0;
    font-size: 15px;
    font-weight: 800;
    line-height: 1.35;
    max-width: 330px;
}

.detail-chat-close {
    width: 34px;
    height: 34px;
    background: rgba(255, 255, 255, 0.14);
    border: none;
    border-radius: 50%;
    color: white;
    cursor: pointer;
    font-size: 22px;
    line-height: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.detail-chat-close:hover {
    background: rgba(255, 255, 255, 0.24);
}

.detail-chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 18px 16px;
    background:
        radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 34%),
        #f8fafc;
}

.detail-chat-room-users {
    display: none;
    max-height: 154px;
    overflow-y: auto;
    border-bottom: 1px solid #e5e7eb;
    background: #ffffff;
    padding: 8px;
}

.detail-chat-user-item {
    padding: 11px 12px;
    cursor: pointer;
    border: 1px solid transparent;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 700;
    color: #334155;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.detail-chat-user-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #dbeafe;
    color: #1d4ed8;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 32px;
    font-size: 13px;
}

.detail-chat-user-item:hover,
.detail-chat-user-item.active {
    background: #eff6ff;
    border-color: #bfdbfe;
    color: #1d4ed8;
}

.detail-chat-user-item.has-unread {
    background: #fff7ed;
    border-color: #fdba74;
    color: #9a3412;
    font-weight: 900;
}

.detail-chat-user-name {
    flex: 1;
}

.detail-chat-unread-badge {
    min-width: 24px;
    height: 24px;
    padding: 0 8px;
    border-radius: 999px;
    background: #ef4444;
    color: white;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 900;
}

.detail-chat-empty {
    text-align: center;
    color: #64748b;
    padding: 68px 22px 0;
    font-size: 14px;
}

.detail-chat-row {
    display: flex;
    margin-bottom: 12px;
}

.detail-chat-row.me {
    justify-content: flex-end;
}

.detail-chat-row.other {
    justify-content: flex-start;
}

.detail-chat-row.unread-incoming .detail-chat-bubble {
    border-color: #fdba74;
    background: #fff7ed;
    color: #9a3412;
    font-weight: 800;
}

.detail-chat-bubble {
    max-width: 78%;
    padding: 10px 13px;
    border-radius: 16px;
    font-size: 14px;
    line-height: 1.45;
    word-wrap: break-word;
    box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
}

.detail-chat-row.me .detail-chat-bubble {
    background: #2563eb;
    color: white;
    border-bottom-right-radius: 6px;
}

.detail-chat-row.other .detail-chat-bubble {
    background: #ffffff;
    color: #1f2937;
    border: 1px solid #e5e7eb;
    border-bottom-left-radius: 6px;
}

.detail-chat-time {
    font-size: 10px;
    margin-top: 5px;
    opacity: 0.68;
    text-align: right;
}

.detail-chat-input-box {
    min-height: 70px;
    border-top: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    padding: 12px;
    gap: 10px;
    background: #ffffff;
}

.detail-chat-input-box input {
    flex: 1;
    height: 44px;
    border: 1px solid #dbe3ef;
    border-radius: 999px;
    padding: 0 16px;
    outline: none;
    font-size: 14px;
    background: #f8fafc;
}

.detail-chat-input-box input:focus {
    border-color: #2563eb;
    background: #ffffff;
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
}

.detail-chat-input-box button {
    height: 44px;
    min-width: 64px;
    border: none;
    background: #111827;
    color: white;
    border-radius: 999px;
    cursor: pointer;
    font-weight: 800;
    font-size: 13px;
    padding: 0 16px;
}

.detail-chat-input-box button:hover {
    background: #2563eb;
}

@media (max-width: 600px) {
    .detail-chat-box {
        width: 100%;
        height: 100%;
        bottom: 0;
        right: 0;
        border-radius: 0;
    }

    .detail-chat-header {
        border-radius: 0;
    }
}
</style>

<div class="detail-chat-overlay" id="detailChatOverlay"></div>

<div class="detail-chat-box" id="detailChatBox">
    <div class="detail-chat-header">
        <h5 id="detailChatTitle">Chat với chủ trọ</h5>
        <button class="detail-chat-close" onclick="toggleDetailChatBox(0, '')">&times;</button>
    </div>

    <div class="detail-chat-room-users" id="detailChatRoomUsers"></div>

    <div class="detail-chat-messages" id="detailChatMessages">
        <div class="detail-chat-empty">Chưa có tin nhắn nào</div>
    </div>

    <div class="detail-chat-input-box">
        <input type="text" id="detailChatInput" placeholder="Nhập tin nhắn...">
        <button onclick="sendDetailMessage()"><i class="fa-solid fa-paper-plane me-1"></i>Gửi</button>
    </div>
</div>

<script>
let detailChatReceiverID = 0;
let detailChatMotelID = 0;
let detailChatInterval = null;
let detailChatLoaded = false;
let detailChatOwnerMode = false;
const currentUserID = <?php echo isset($_SESSION['user']['ID']) ? (int)$_SESSION['user']['ID'] : 0; ?>;
const shouldOpenChatFromNotice = <?php echo (isset($_GET['open_chat']) && isset($_SESSION['user']['ID'])) ? 'true' : 'false'; ?>;
const detailRoomOwnerID = <?php echo (int)$room['user_id']; ?>;
const detailRoomOwnerName = <?php echo json_encode($room["owner_name"], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>;
const detailRoomID = <?php echo (int)$room["ID"]; ?>;
const detailRoomTitle = <?php echo json_encode($room["title"], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>;

function toggleDetailChatBox(ownerID, ownerName, motelID = 0, roomTitle = "", ownerMode = false) {
    const box = document.getElementById("detailChatBox");
    const overlay = document.getElementById("detailChatOverlay");
    const roomUsers = document.getElementById("detailChatRoomUsers");

    if (box.classList.contains("active")) {
        box.classList.remove("active");
        overlay.style.display = "none";
        detailChatReceiverID = 0;
        detailChatMotelID = 0;
        detailChatOwnerMode = false;
        roomUsers.style.display = "none";
        roomUsers.innerHTML = "";

        if (detailChatInterval !== null) {
            clearInterval(detailChatInterval);
        }
    } else {
        if (ownerID === 0 && !ownerMode) {
            alert("Vui lòng chọn phòng để chat");
            return;
        }

        detailChatReceiverID = ownerID;
        detailChatMotelID = motelID;
        detailChatOwnerMode = ownerMode;
        document.getElementById("detailChatTitle").textContent = ownerMode ? "Tin nhắn phòng: " + roomTitle : (roomTitle ? "Chat về phòng: " + roomTitle : "Chat với " + ownerName);
        box.classList.add("active");
        overlay.style.display = "block";

        if (ownerMode) {
            roomUsers.style.display = "block";
            loadDetailRoomUsers();
            document.getElementById("detailChatMessages").innerHTML = `<div class="detail-chat-empty">Chọn khách để xem tin nhắn</div>`;
        } else {
            roomUsers.style.display = "none";
            loadDetailMessages();
        }

        if (detailChatInterval !== null) {
            clearInterval(detailChatInterval);
        }

        detailChatInterval = setInterval(function () {
            if (detailChatOwnerMode) {
                loadDetailRoomUsers();
            }

            loadDetailMessages();
        }, 2000);
    }
}

function loadDetailRoomUsers() {
    if (detailChatMotelID === 0) return;

    let formData = new FormData();
    formData.append("action", "room_users");
    formData.append("motel_id", detailChatMotelID);

    fetch("ajax_chat.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        const list = document.getElementById("detailChatRoomUsers");
        list.innerHTML = "";

        if (data.status !== "success" || data.users.length === 0) {
            list.innerHTML = `<div class="detail-chat-user-item">Chưa có khách nhắn về phòng này</div>`;
            return;
        }

        data.users.forEach(user => {
            let item = document.createElement("div");
            item.className = "detail-chat-user-item";
            const unreadCount = parseInt(user.unread_count || 0);

            if (parseInt(user.ID) === parseInt(detailChatReceiverID)) {
                item.classList.add("active");
            }

            if (unreadCount > 0) {
                item.classList.add("has-unread");
            }

            item.innerHTML = `
                <span class="detail-chat-user-avatar"><i class="fa-solid fa-user"></i></span>
                <span class="detail-chat-user-name">${escapeHtml(user.Name)}</span>
                ${unreadCount > 0 ? `<span class="detail-chat-unread-badge">${unreadCount}</span>` : ""}
            `;
            item.onclick = function () {
                detailChatReceiverID = parseInt(user.ID);
                document.querySelectorAll(".detail-chat-user-item").forEach(el => el.classList.remove("active"));
                item.classList.add("active");
                loadDetailMessages();
            };

            list.appendChild(item);
        });
    });
}

function sendDetailMessage() {
    const input = document.getElementById("detailChatInput");
    const message = input.value.trim();

    if (detailChatReceiverID === 0 || detailChatMotelID === 0) {
        alert("Vui lòng chọn phòng để chat");
        return;
    }

    if (message === "") return;

    let formData = new FormData();
    formData.append("action", "send");
    formData.append("receiver_id", detailChatReceiverID);
    formData.append("motel_id", detailChatMotelID);
    formData.append("message", message);

    fetch("ajax_chat.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === "success") {
            input.value = "";
            loadDetailMessages();
        } else {
            alert(data.message);
        }
    });
}

function loadDetailMessages() {
    if (detailChatReceiverID === 0 || detailChatMotelID === 0) return;

    let formData = new FormData();
    formData.append("action", "load");
    formData.append("receiver_id", detailChatReceiverID);
    formData.append("motel_id", detailChatMotelID);

    fetch("ajax_chat.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        const box = document.getElementById("detailChatMessages");
        box.innerHTML = "";

        if (data.status !== "success") {
            box.innerHTML = `<div class="detail-chat-empty">Không tải được tin nhắn</div>`;
            return;
        }

        if (data.messages.length === 0) {
            box.innerHTML = `<div class="detail-chat-empty">Chưa có tin nhắn nào</div>`;
            return;
        }

        data.messages.forEach(msg => {
            let row = document.createElement("div");

            if (parseInt(msg.sender_id) === parseInt(currentUserID)) {
                row.className = "detail-chat-row me";
            } else {
                row.className = "detail-chat-row other";
            }

            if (parseInt(msg.receiver_id) === parseInt(currentUserID) && parseInt(msg.is_read || 0) === 0) {
                row.classList.add("unread-incoming");
            }

            row.innerHTML = `
                <div class="detail-chat-bubble">
                    <div>${escapeHtml(msg.message)}</div>
                    <div class="detail-chat-time">${msg.time_send}</div>
                </div>
            `;

            box.appendChild(row);
        });

        box.scrollTop = box.scrollHeight;
    });
}

function escapeHtml(text) {
    let div = document.createElement("div");
    div.innerText = text;
    return div.innerHTML;
}

document.addEventListener("DOMContentLoaded", function () {
    const input = document.getElementById("detailChatInput");

    if (input) {
        input.addEventListener("keyup", function (e) {
            if (e.key === "Enter") {
                sendDetailMessage();
            }
        });
    }

    if (shouldOpenChatFromNotice) {
        const roomMessagesButton = document.getElementById("roomMessagesButton");
        if (roomMessagesButton) {
            roomMessagesButton.click();
        } else if (detailRoomOwnerID > 0) {
            toggleDetailChatBox(detailRoomOwnerID, detailRoomOwnerName, detailRoomID, detailRoomTitle);
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>
