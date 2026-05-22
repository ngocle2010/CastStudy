<?php
session_start();
require_once "includes/db_config.php";

if (!isset($_SESSION['user']) || !isset($_SESSION['user']['ID'])) {
    header("Location: login.php");
    exit();
}

$currentUserID = (int)$_SESSION['user']['ID'];
require_once "includes/header.php";
?>

<style>
:root {
    --chat-ink: #172033;
    --chat-muted: #667085;
    --chat-line: #e6ebf2;
    --chat-soft: #f5f8fc;
    --chat-panel: #ffffff;
    --chat-primary: #2563eb;
    --chat-primary-dark: #1e40af;
    --chat-accent: #10b981;
    --chat-warn-bg: #fff7ed;
    --chat-warn: #c2410c;
}

body {
    background:
        linear-gradient(180deg, rgba(37, 99, 235, 0.07), rgba(16, 185, 129, 0.04) 38%, #f7fafc 100%);
}

.inbox-shell {
    max-width: 1080px;
    width: calc(100% - 28px);
    height: min(720px, calc(100vh - 145px));
    min-height: 560px;
    margin: 28px auto;
    display: grid;
    grid-template-columns: 330px minmax(0, 1fr);
    background: var(--chat-panel);
    border: 1px solid rgba(148, 163, 184, 0.25);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 24px 70px rgba(15, 23, 42, 0.14);
}

.inbox-sidebar {
    border-right: 1px solid var(--chat-line);
    background: #f8fbff;
    overflow-y: auto;
    min-height: 0;
}

.inbox-sidebar-header {
    padding: 16px 18px;
    background:
        linear-gradient(135deg, rgba(37, 99, 235, 0.96), rgba(16, 185, 129, 0.94));
    color: white;
    position: sticky;
    top: 0;
    z-index: 2;
}

.inbox-sidebar-header h4 {
    margin: 0;
    font-size: 16px;
    font-weight: 900;
    letter-spacing: 0;
}

.conversation-item {
    margin: 8px 10px;
    padding: 11px 12px;
    cursor: pointer;
    border: 1px solid transparent;
    border-radius: 12px;
    display: grid;
    grid-template-columns: 38px minmax(0, 1fr) auto;
    gap: 10px;
    align-items: center;
    background: transparent;
    transition: background 0.16s ease, border-color 0.16s ease, transform 0.16s ease, box-shadow 0.16s ease;
}

.conversation-item:hover {
    background: #ffffff;
    border-color: #dbeafe;
    transform: translateY(-1px);
    box-shadow: 0 10px 22px rgba(37, 99, 235, 0.08);
}

.conversation-item.active {
    background: #ffffff;
    border-color: #93c5fd;
    box-shadow: 0 12px 28px rgba(37, 99, 235, 0.12);
}

.conversation-item.has-unread {
    background: var(--chat-warn-bg);
    border-color: #fed7aa;
    font-weight: 900;
}

.conversation-avatar {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: linear-gradient(135deg, #dbeafe, #d1fae5);
    color: var(--chat-primary-dark);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: inset 0 0 0 1px rgba(37, 99, 235, 0.12);
}

.conversation-title {
    color: var(--chat-ink);
    font-size: 13px;
    font-weight: 900;
    line-height: 1.3;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.conversation-room {
    color: var(--chat-muted);
    font-size: 12px;
    margin-top: 4px;
    line-height: 1.35;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.conversation-badge {
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

.inbox-main {
    display: flex;
    flex-direction: column;
    min-width: 0;
    min-height: 0;
    background: var(--chat-panel);
}

.inbox-chat-header {
    min-height: 64px;
    padding: 12px 18px;
    border-bottom: 1px solid var(--chat-line);
    display: flex;
    flex-direction: column;
    justify-content: center;
    background: rgba(255, 255, 255, 0.92);
}

.inbox-chat-header h4 {
    margin: 0;
    font-size: 16px;
    font-weight: 900;
    color: var(--chat-ink);
}

.inbox-chat-header div {
    margin-top: 4px;
    font-size: 13px;
    color: var(--chat-muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.inbox-messages {
    flex: 1;
    min-height: 0;
    overflow-y: auto;
    padding: 18px;
    background:
        radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 30%),
        radial-gradient(circle at bottom right, rgba(16, 185, 129, 0.08), transparent 28%),
        var(--chat-soft);
}

.inbox-empty {
    text-align: center;
    color: var(--chat-muted);
    padding-top: 170px;
    font-size: 14px;
}

.inbox-row {
    display: flex;
    margin-bottom: 12px;
}

.inbox-row.me {
    justify-content: flex-end;
}

.inbox-row.other {
    justify-content: flex-start;
}

.inbox-bubble {
    max-width: min(76%, 480px);
    padding: 9px 12px;
    border-radius: 15px;
    font-size: 13px;
    line-height: 1.5;
    word-wrap: break-word;
    box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
}

.inbox-row.me .inbox-bubble {
    background: linear-gradient(135deg, var(--chat-primary), var(--chat-primary-dark));
    color: white;
    border-bottom-right-radius: 6px;
}

.inbox-row.other .inbox-bubble {
    background: white;
    color: #1f2937;
    border: 1px solid var(--chat-line);
    border-bottom-left-radius: 6px;
}

.inbox-row.unread-incoming .inbox-bubble {
    border-color: #fdba74;
    background: var(--chat-warn-bg);
    color: var(--chat-warn);
    font-weight: 800;
}

.inbox-time {
    font-size: 10px;
    margin-top: 5px;
    opacity: 0.68;
    text-align: right;
}

.inbox-input {
    min-height: 64px;
    padding: 10px 14px;
    border-top: 1px solid var(--chat-line);
    display: flex;
    gap: 10px;
    align-items: center;
    background: white;
}

.inbox-input input {
    flex: 1;
    height: 42px;
    border: 1px solid #d8e1ee;
    border-radius: 999px;
    padding: 0 18px;
    outline: none;
    background: #f8fbff;
    color: var(--chat-ink);
    transition: border-color 0.16s ease, box-shadow 0.16s ease, background 0.16s ease;
}

.inbox-input input:focus {
    background: white;
    border-color: var(--chat-primary);
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
}

.inbox-input input:disabled {
    color: #94a3b8;
    background: #f1f5f9;
}

.inbox-input button {
    height: 42px;
    border: none;
    border-radius: 999px;
    padding: 0 16px;
    background: var(--chat-accent);
    color: white;
    font-weight: 900;
    box-shadow: 0 10px 20px rgba(16, 185, 129, 0.18);
    transition: background 0.16s ease, transform 0.16s ease;
}

.inbox-input button:hover {
    background: #059669;
    transform: translateY(-1px);
}

@media (max-width: 800px) {
    .inbox-shell {
        height: auto;
        min-height: 0;
        margin: 18px 12px;
        grid-template-columns: 1fr;
        border-radius: 16px;
    }

    .inbox-sidebar {
        max-height: 300px;
        border-right: 0;
        border-bottom: 1px solid var(--chat-line);
    }

    .inbox-main {
        min-height: 560px;
    }

    .inbox-messages {
        padding: 16px;
    }

    .inbox-bubble {
        max-width: 84%;
    }

    .inbox-input {
        padding: 12px;
    }
}
</style>

<div class="inbox-shell">
    <aside class="inbox-sidebar">
        <div class="inbox-sidebar-header">
            <h4><i class="fa-solid fa-comments me-2"></i>Tất cả tin nhắn</h4>
        </div>
        <div id="conversationList">
            <div class="inbox-empty">Đang tải cuộc trò chuyện...</div>
        </div>
    </aside>

    <main class="inbox-main">
        <div class="inbox-chat-header">
            <h4 id="chatTitle">Chọn cuộc trò chuyện</h4>
            <div id="chatRoomTitle">Tất cả đoạn chat đã từng trao đổi sẽ nằm ở danh sách bên trái.</div>
        </div>

        <div class="inbox-messages" id="chatMessages">
            <div class="inbox-empty">Chọn một khách hoặc chủ trọ để xem lịch sử chat</div>
        </div>

        <div class="inbox-input">
            <input type="text" id="chatInput" placeholder="Nhập tin nhắn..." disabled>
            <button type="button" onclick="sendInboxMessage()">
                <i class="fa-solid fa-paper-plane me-1"></i>Gửi
            </button>
        </div>
    </main>
</div>

<script>
const currentUserID = <?php echo $currentUserID; ?>;
let currentReceiverID = 0;
let currentMotelID = 0;
let inboxInterval = null;

function loadConversations(autoOpen = false) {
    const formData = new FormData();
    formData.append("action", "conversations");

    fetch("ajax_chat.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        const list = document.getElementById("conversationList");
        list.innerHTML = "";

        if (data.status !== "success" || data.conversations.length === 0) {
            list.innerHTML = `<div class="inbox-empty">Chưa có cuộc trò chuyện nào</div>`;
            return;
        }

        data.conversations.forEach((conversation, index) => {
            const unreadCount = parseInt(conversation.unread_count || 0);
            const item = document.createElement("div");
            item.className = "conversation-item";

            if (unreadCount > 0) {
                item.classList.add("has-unread");
            }

            if (parseInt(conversation.other_user_id) === currentReceiverID && parseInt(conversation.motel_id) === currentMotelID) {
                item.classList.add("active");
            }

            item.innerHTML = `
                <span class="conversation-avatar"><i class="fa-solid fa-user"></i></span>
                <span>
                    <div class="conversation-title">${escapeHtml(conversation.other_user_name)}</div>
                    <div class="conversation-room">${escapeHtml(conversation.room_title)}</div>
                </span>
                ${unreadCount > 0 ? `<span class="conversation-badge">${unreadCount}</span>` : ""}
            `;

            item.onclick = function () {
                document.querySelectorAll(".conversation-item").forEach(el => el.classList.remove("active"));
                item.classList.add("active");
                openConversation(conversation);
            };

            list.appendChild(item);

            if (autoOpen && index === 0) {
                item.click();
            }
        });
    });
}

function openConversation(conversation) {
    currentReceiverID = parseInt(conversation.other_user_id);
    currentMotelID = parseInt(conversation.motel_id);
    document.getElementById("chatTitle").textContent = "Đang chat với: " + conversation.other_user_name;
    document.getElementById("chatRoomTitle").textContent = "Phòng: " + conversation.room_title;
    document.getElementById("chatInput").disabled = false;
    loadInboxMessages();

    if (inboxInterval !== null) {
        clearInterval(inboxInterval);
    }

    inboxInterval = setInterval(function () {
        loadConversations(false);
        loadInboxMessages();
    }, 2500);
}

function loadInboxMessages() {
    if (currentReceiverID === 0 || currentMotelID === 0) return;

    const formData = new FormData();
    formData.append("action", "load");
    formData.append("receiver_id", currentReceiverID);
    formData.append("motel_id", currentMotelID);

    fetch("ajax_chat.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        const box = document.getElementById("chatMessages");
        box.innerHTML = "";

        if (data.status !== "success") {
            box.innerHTML = `<div class="inbox-empty">Không tải được tin nhắn</div>`;
            return;
        }

        if (data.messages.length === 0) {
            box.innerHTML = `<div class="inbox-empty">Chưa có tin nhắn nào</div>`;
            return;
        }

        data.messages.forEach(msg => {
            const row = document.createElement("div");
            row.className = parseInt(msg.sender_id) === currentUserID ? "inbox-row me" : "inbox-row other";

            if (parseInt(msg.receiver_id) === currentUserID && parseInt(msg.is_read || 0) === 0) {
                row.classList.add("unread-incoming");
            }

            row.innerHTML = `
                <div class="inbox-bubble">
                    <div>${escapeHtml(msg.message)}</div>
                    <div class="inbox-time">${msg.time_send}</div>
                </div>
            `;

            box.appendChild(row);
        });

        box.scrollTop = box.scrollHeight;
    });
}

function sendInboxMessage() {
    const input = document.getElementById("chatInput");
    const message = input.value.trim();

    if (currentReceiverID === 0 || currentMotelID === 0 || message === "") return;

    const formData = new FormData();
    formData.append("action", "send");
    formData.append("receiver_id", currentReceiverID);
    formData.append("motel_id", currentMotelID);
    formData.append("message", message);

    fetch("ajax_chat.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === "success") {
            input.value = "";
            loadConversations(false);
            loadInboxMessages();
        } else {
            alert(data.message);
        }
    });
}

function escapeHtml(text) {
    const div = document.createElement("div");
    div.innerText = text || "";
    return div.innerHTML;
}

document.addEventListener("DOMContentLoaded", function () {
    loadConversations(true);

    document.getElementById("chatInput").addEventListener("keyup", function (e) {
        if (e.key === "Enter") {
            sendInboxMessage();
        }
    });
});
</script>

<?php include "includes/footer.php"; ?>
