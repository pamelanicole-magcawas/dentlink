<?php
include 'db_config.php';

if (($current_user_type ?? null) !== 'Admin' || !isset($_GET['patient_id'])) {
    die("Access Denied or Missing Patient ID.");
}

$patient_id = (int)$_GET['patient_id'];

$stmt = $conn->prepare("SELECT first_name, last_name FROM users WHERE user_id = ? LIMIT 1");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$patient_info = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$patient_info) die("Patient not found.");
$patientName = htmlspecialchars($patient_info['first_name'] . ' ' . $patient_info['last_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chat with <?= htmlspecialchars($patient_info['first_name']) ?> - DentLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
</head>
<body class="admin-page">

    <div class="admin-chat-wrapper">
        <div class="admin-chat-container">

            <!-- Chat Header -->
            <div class="admin-chat-header">
                <a href="admin_chats.php" class="admin-chat-header-back">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <div class="admin-chat-header-icon">
                    <i class="bi bi-person-fill"></i>
                </div>
                <div class="admin-chat-header-info">
                    <p class="admin-chat-header-title"><?= $patientName ?></p>
                    <p class="admin-chat-header-subtitle">Patient Chat</p>
                </div>
                <div class="admin-chat-header-status">
                    <span class="status-dot"></span>
                    Online
                </div>
            </div>

            <!-- Chat Messages Window -->
            <div id="chat-window" class="admin-chat-window"></div>

            <!-- Chat Input Bar -->
            <div class="admin-chat-input-bar">
                <input type="text" id="message-input" class="admin-chat-input" placeholder="Reply as Admin...">
                <button id="send-button" class="admin-chat-send-btn">
                    <i class="bi bi-send-fill"></i>
                </button>
            </div>

        </div>
    </div>

    <script>
        let last_message_id = 0;
        const patientId = <?= $patient_id ?>;
        const chatWindow = document.getElementById('chat-window');

        function renderMessage(msg) {
            const el = document.createElement('div');
            el.className = 'admin-chat-message';

            if (msg.sender_type === "Patient") {
                el.classList.add('patient');
            } else if (msg.sender_type === "Admin") {
                el.classList.add('admin');
            } else {
                el.classList.add('system');
            }

            el.innerHTML = (msg.message_text || '')
                .replace(/\*\*(.*?)\*\*/g, "<strong>$1</strong>")
                .replace(/\n/g, "<br>");

            chatWindow.appendChild(el);
        }

        function fetchMessages() {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'fetch_messages.php?last_id=' + last_message_id + '&patient_id=' + patientId, true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const msgs = JSON.parse(xhr.responseText);
                        msgs.forEach(msg => {
                            renderMessage(msg);
                            last_message_id = Math.max(last_message_id, msg.id);
                        });
                        chatWindow.scrollTop = chatWindow.scrollHeight;
                    } catch {}
                }
            };
            xhr.send();
        }

        fetchMessages();
        setInterval(fetchMessages, 1500);

        const inputField = document.getElementById('message-input');
        const sendBtn = document.getElementById('send-button');

        function sendMessage() {
            const text = inputField.value.trim();
            if (!text) return;

            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'send_message.php', true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                try {
                    const r = JSON.parse(xhr.responseText);
                    if (r.success) {
                        inputField.value = '';
                        fetchMessages();
                    }
                } catch {}
            };
            xhr.send("message_text=" + encodeURIComponent(text) + "&target_id=" + patientId);
        }

        sendBtn.addEventListener('click', sendMessage);
        inputField.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                sendMessage();
            }
        });
    </script>

</body>
</html>