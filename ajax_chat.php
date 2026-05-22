<?php
session_start();
require_once "includes/db_config.php";

header("Content-Type: application/json; charset=utf-8");

if (!isset($_SESSION['user'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Bạn chưa đăng nhập"
    ]);
    exit();
}

$currentUserID = (int)$_SESSION['user']['ID'];
$currentRole = (int)$_SESSION['user']['Role'];
$action = $_POST['action'] ?? '';

function checkCanChat($conn, $currentUserID, $currentRole, $receiver_id, $motel_id = 0) {
    $stmt = $conn->prepare("SELECT ID, Role FROM user WHERE ID = ? LIMIT 1");
    $stmt->bind_param("i", $receiver_id);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        return false;
    }

    $receiver = $result->fetch_assoc();
    $receiverRole = (int)$receiver['Role'];

    if ($motel_id > 0) {
        $roomStmt = $conn->prepare("SELECT user_id FROM motel WHERE ID = ? AND approve = 1 LIMIT 1");
        $roomStmt->bind_param("i", $motel_id);
        $roomStmt->execute();
        $roomResult = $roomStmt->get_result();

        if ($roomResult->num_rows === 0) {
            return false;
        }

        $room = $roomResult->fetch_assoc();
        $ownerID = (int)$room['user_id'];

        if ($receiver_id !== $ownerID && $currentUserID !== $ownerID && $currentRole !== 2) {
            return false;
        }
    }

    // Admin được chat với tất cả
    if ($currentRole == 2) {
        return true;
    }

    // Khách thuê chỉ chat với chủ trọ
    if ($currentRole == 0 && $receiverRole == 1) {
        return true;
    }

    // Chủ trọ chỉ chat với khách thuê
    if ($currentRole == 1 && $receiverRole == 0) {
        return true;
    }

    return false;
}

if ($action === "users") {

    if ($currentRole == 0) {
        // Khách thuê chỉ thấy chủ trọ
        $stmt = $conn->prepare("
            SELECT ID, Name, Role
            FROM user
            WHERE ID != ? AND Role = 1
            ORDER BY Name ASC
        ");
    } elseif ($currentRole == 1) {
        // Chủ trọ chỉ thấy khách thuê
        $stmt = $conn->prepare("
            SELECT ID, Name, Role
            FROM user
            WHERE ID != ? AND Role = 0
            ORDER BY Name ASC
        ");
    } else {
        // Admin thấy tất cả
        $stmt = $conn->prepare("
            SELECT ID, Name, Role
            FROM user
            WHERE ID != ?
            ORDER BY Role DESC, Name ASC
        ");
    }

    $stmt->bind_param("i", $currentUserID);
    $stmt->execute();

    $result = $stmt->get_result();
    $users = [];

    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }

    echo json_encode([
        "status" => "success",
        "users" => $users
    ]);
    exit();
}

if ($action === "conversations") {
    $stmt = $conn->prepare("
        SELECT
            u.ID AS other_user_id,
            u.Name AS other_user_name,
            u.Role AS other_user_role,
            m.motel_id,
            COALESCE(motel.title, 'Phòng đã xoá') AS room_title,
            SUM(CASE WHEN m.receiver_id = ? AND m.is_read = 0 THEN 1 ELSE 0 END) AS unread_count,
            MAX(m.created_at) AS latest_message_at
        FROM messages m
        JOIN user u ON u.ID = CASE
            WHEN m.sender_id = ? THEN m.receiver_id
            ELSE m.sender_id
        END
        LEFT JOIN motel ON motel.ID = m.motel_id
        WHERE (m.sender_id = ? OR m.receiver_id = ?)
          AND m.motel_id IS NOT NULL
        GROUP BY u.ID, u.Name, u.Role, m.motel_id, motel.title
        ORDER BY unread_count DESC, latest_message_at DESC, u.Name ASC
    ");
    $stmt->bind_param("iiii", $currentUserID, $currentUserID, $currentUserID, $currentUserID);
    $stmt->execute();

    $result = $stmt->get_result();
    $conversations = [];

    while ($row = $result->fetch_assoc()) {
        $conversations[] = $row;
    }

    echo json_encode([
        "status" => "success",
        "conversations" => $conversations
    ]);
    exit();
}

if ($action === "room_users") {
    $motel_id = (int)($_POST['motel_id'] ?? 0);

    if ($motel_id <= 0) {
        echo json_encode([
            "status" => "error",
            "message" => "Phòng không hợp lệ"
        ]);
        exit();
    }

    $roomStmt = $conn->prepare("SELECT user_id FROM motel WHERE ID = ? LIMIT 1");
    $roomStmt->bind_param("i", $motel_id);
    $roomStmt->execute();
    $roomResult = $roomStmt->get_result();

    if ($roomResult->num_rows === 0) {
        echo json_encode([
            "status" => "error",
            "message" => "Không tìm thấy phòng"
        ]);
        exit();
    }

    $room = $roomResult->fetch_assoc();
    $ownerID = (int)$room['user_id'];

    if ($currentRole !== 2 && $currentUserID !== $ownerID) {
        echo json_encode([
            "status" => "error",
            "message" => "Bạn không có quyền xem tin nhắn phòng này"
        ]);
        exit();
    }

    $stmt = $conn->prepare("
        SELECT
            u.ID,
            u.Name,
            u.Role,
            SUM(CASE WHEN m.sender_id = u.ID AND m.receiver_id = ? AND m.is_read = 0 THEN 1 ELSE 0 END) AS unread_count,
            MAX(m.created_at) AS latest_message_at
        FROM messages m
        JOIN user u ON u.ID = CASE
            WHEN m.sender_id = ? THEN m.receiver_id
            ELSE m.sender_id
        END
        WHERE m.motel_id = ?
          AND (m.sender_id = ? OR m.receiver_id = ?)
          AND u.ID != ?
        GROUP BY u.ID, u.Name, u.Role
        ORDER BY unread_count DESC, latest_message_at DESC, u.Name ASC
    ");
    $stmt->bind_param("iiiiii", $currentUserID, $currentUserID, $motel_id, $currentUserID, $currentUserID, $currentUserID);
    $stmt->execute();

    $result = $stmt->get_result();
    $users = [];

    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }

    echo json_encode([
        "status" => "success",
        "users" => $users
    ]);
    exit();
}

if ($action === "send") {

    $receiver_id = (int)($_POST['receiver_id'] ?? 0);
    $motel_id = (int)($_POST['motel_id'] ?? 0);
    $message = trim($_POST['message'] ?? '');

    if ($receiver_id <= 0 || $message === '') {
        echo json_encode([
            "status" => "error",
            "message" => "Dữ liệu không hợp lệ"
        ]);
        exit();
    }

    if ($motel_id <= 0) {
        echo json_encode([
            "status" => "error",
            "message" => "Vui lòng chọn phòng để chat"
        ]);
        exit();
    }

    if (!checkCanChat($conn, $currentUserID, $currentRole, $receiver_id, $motel_id)) {
        echo json_encode([
            "status" => "error",
            "message" => "Bạn chỉ được chat với chủ trọ/khách thuê phù hợp"
        ]);
        exit();
    }

    $stmt = $conn->prepare("
        INSERT INTO messages(sender_id, receiver_id, motel_id, message)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("iiis", $currentUserID, $receiver_id, $motel_id, $message);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Không gửi được tin nhắn"
        ]);
    }

    exit();
}

if ($action === "load") {

    $receiver_id = (int)($_POST['receiver_id'] ?? 0);
    $motel_id = (int)($_POST['motel_id'] ?? 0);

    if ($receiver_id <= 0) {
        echo json_encode([
            "status" => "error",
            "message" => "Người nhận không hợp lệ"
        ]);
        exit();
    }

    if ($motel_id <= 0) {
        echo json_encode([
            "status" => "error",
            "message" => "Vui lòng chọn phòng để chat"
        ]);
        exit();
    }

    if (!checkCanChat($conn, $currentUserID, $currentRole, $receiver_id, $motel_id)) {
        echo json_encode([
            "status" => "error",
            "message" => "Bạn không có quyền xem cuộc trò chuyện này"
        ]);
        exit();
    }

    $stmt = $conn->prepare("
        SELECT
            ID,
            sender_id,
            receiver_id,
            is_read,
            message,
            DATE_FORMAT(created_at, '%H:%i') AS time_send
        FROM messages
        WHERE
            motel_id = ?
            AND (
                (sender_id = ? AND receiver_id = ?)
                OR
                (sender_id = ? AND receiver_id = ?)
            )
        ORDER BY created_at ASC
    ");

    $stmt->bind_param("iiiii", $motel_id, $currentUserID, $receiver_id, $receiver_id, $currentUserID);
    $stmt->execute();

    $result = $stmt->get_result();
    $messages = [];

    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }

    $readStmt = $conn->prepare("
        UPDATE messages
        SET is_read = 1
        WHERE motel_id = ?
          AND sender_id = ?
          AND receiver_id = ?
          AND is_read = 0
    ");
    $readStmt->bind_param("iii", $motel_id, $receiver_id, $currentUserID);
    $readStmt->execute();

    echo json_encode([
        "status" => "success",
        "messages" => $messages
    ]);
    exit();
}

echo json_encode([
    "status" => "error",
    "message" => "Action không hợp lệ"
]);
exit();
