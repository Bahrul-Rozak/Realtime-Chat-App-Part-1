<?php
session_start();
include 'config.php';

// cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Update last_seen user setiap buka halaman chat
$conn->query("UPDATE users SET last_seen = NOW() WHERE id = $user_id");

// Ambil semua user lain sebagai daftar teman kecuali diri sendiri
$sql = "SELECT id, username, last_seen FROM users WHERE id != $user_id ORDER BY username ASC";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Chat - WhatsApp Clone</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        /* Custom CSS buat layout mirip WhatsApp */
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #ECE5DD;
        }
        .app {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }
        /* Sidebar (list teman) */
        .sidebar {
            width: 320px;
            background: #FFFFFF;
            border-right: 1px solid #ddd;
            display: flex;
            flex-direction: column;
        }
        .sidebar-header {
            padding: 15px 20px;
            background: #075E54;
            color: white;
            font-weight: bold;
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .user-list {
            flex-grow: 1;
            overflow-y: auto;
        }
        .user-item {
            padding: 10px 20px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            display: flex;
            align-items: center;
        }
        .user-item:hover, .user-item.active {
            background: #DCF8C6;
        }
        .user-item .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #bbb;
            display: inline-block;
            margin-right: 15px;
            text-align: center;
            line-height: 40px;
            color: white;
            font-weight: bold;
            font-size: 1.1rem;
            user-select: none;
        }
        .user-item .username {
            flex-grow: 1;
        }
        .user-item .last-seen {
            font-size: 0.75rem;
            color: gray;
        }
        /* Chat window */
        .chat-window {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            background: #ECE5DD;
        }
        .chat-header {
            padding: 15px 20px;
            background: #075E54;
            color: white;
            font-weight: bold;
            display: flex;
            align-items: center;
        }
        .chat-messages {
            flex-grow: 1;
            padding: 20px;
            overflow-y: auto;
        }
        .chat-input {
            padding: 10px 20px;
            background: #f0f0f0;
            border-top: 1px solid #ddd;
            display: flex;
            align-items: center;
        }
        .chat-input textarea {
            resize: none;
            width: 100%;
            height: 50px;
            padding: 10px;
            border-radius: 20px;
            border: 1px solid #ccc;
            outline: none;
            font-size: 1rem;
        }
        .chat-input button {
            margin-left: 10px;
            background: #128C7E;
            border: none;
            color: white;
            padding: 10px 15px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        /* Message bubbles */
        .message {
            max-width: 60%;
            margin-bottom: 10px;
            padding: 10px 15px;
            border-radius: 7.5px;
            position: relative;
            clear: both;
            font-size: 0.9rem;
            line-height: 1.2rem;
        }
        .message.sent {
            background: #DCF8C6;
            float: right;
            border-bottom-right-radius: 0;
        }
        .message.received {
            background: white;
            float: left;
            border-bottom-left-radius: 0;
        }
        .message .timestamp {
            display: block;
            font-size: 0.7rem;
            color: gray;
            margin-top: 5px;
            text-align: right;
        }
    </style>
</head>
<body>
<div class="app">
    <div class="sidebar">
        <div class="sidebar-header">
            Hi, <?= htmlspecialchars($username) ?>
            <a href="logout.php" class="btn btn-sm btn-danger">Logout</a>
        </div>
        <div class="user-list" id="userList">
            <?php while($user = $result->fetch_assoc()): 
                $last_seen = strtotime($user['last_seen']);
                $status = (time() - $last_seen) < 60 ? 'Online' : 'Offline';
            ?>
                <div class="user-item" data-userid="<?= $user['id'] ?>">
                    <div class="avatar"><?= strtoupper($user['username'][0]) ?></div>
                    <div class="username"><?= htmlspecialchars($user['username']) ?></div>
                    <div class="last-seen"><?= $status ?></div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <div class="chat-window">
        <div class="chat-header" id="chatHeader">Pilih teman untuk mulai chat</div>
        <div class="chat-messages" id="chatMessages"></div>
        <div class="chat-input" id="chatInput" style="display:none;">
            <textarea id="messageInput" placeholder="Ketik pesan..."></textarea>
            <button id="sendBtn">&#9658;</button>
        </div>
    </div>
</div>

<script>
    let selectedUserId = null;
    let userList = document.querySelectorAll('.user-item');
    const chatHeader = document.getElementById('chatHeader');
    const chatMessages = document.getElementById('chatMessages');
    const chatInput = document.getElementById('chatInput');
    const messageInput = document.getElementById('messageInput');
    const sendBtn = document.getElementById('sendBtn');

    // Highlight selected user and load chat
    userList.forEach(userItem => {
        userItem.addEventListener('click', () => {
            userList.forEach(ui => ui.classList.remove('active'));
            userItem.classList.add('active');
            selectedUserId = userItem.getAttribute('data-userid');
            chatHeader.textContent = 'Chat dengan ' + userItem.querySelector('.username').textContent;
            chatInput.style.display = 'flex';
            chatMessages.innerHTML = '';

            loadMessages();

            // Set interval polling untuk ambil pesan baru tiap 2 detik
            if (window.pollingInterval) clearInterval(window.pollingInterval);
            window.pollingInterval = setInterval(loadMessages, 2000);
        });
    });

    // Fungsi load pesan dari server via AJAX
    function loadMessages() {
        if (!selectedUserId) return;
        fetch('load_message.php?user_id=' + selectedUserId)
        .then(response => response.json())
        .then(data => {
            chatMessages.innerHTML = '';
            data.forEach(msg => {
                const div = document.createElement('div');
                div.classList.add('message');
                div.classList.add(msg.sender_id == <?= $user_id ?> ? 'sent' : 'received');
                div.innerHTML = `${msg.message}<span class="timestamp">${msg.timestamp}</span>`;
                chatMessages.appendChild(div);
            });
            chatMessages.scrollTop = chatMessages.scrollHeight;
        });
    }

    // Kirim pesan ke server
    sendBtn.addEventListener('click', () => {
        sendMessage();
    });

    // Kirim pesan pakai Enter
    messageInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    function sendMessage() {
        const message = messageInput.value.trim();
        if (!message || !selectedUserId) return;
        fetch('send_message.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `receiver_id=${selectedUserId}&message=${encodeURIComponent(message)}`
        })
        .then(response => response.text())
        .then(data => {
            if (data === 'success') {
                messageInput.value = '';
                loadMessages();
            } else {
                alert('Gagal kirim pesan!');
            }
        });
    }
</script>
</body>
</html>