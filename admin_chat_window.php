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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Chat with <?php echo htmlspecialchars($patient_info['first_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">

    <style>
        body {
            background: #eef0f3;
            margin: 0;
            font-family: Arial, sans-serif;
        }

        .admin-chat-container {
            width: 600px;
            height: 85vh;
            margin: 30px auto;
            background: white;
            border-radius: 15px;
            display: flex;
            flex-direction: column;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        .admin-chat-header {
            padding: 15px 20px;
            background: #1877f2;
            color: white;
            font-size: 1.2rem;
            font-weight: bold;
        }

        #chat-window {
            flex: 1;
            overflow-y: auto;
            padding: 18px;
            background: #f0f2f5;
        }

        /* Chat bubble design */
        .message {
            padding: 10px 14px;
            margin: 6px 0;
            border-radius: 18px;
            max-width: 70%;
            font-size: 0.95rem;
            line-height: 1.4;
            display: block;
            clear: both;
        }

        /* Patient → LEFT, Gray */
        .message.patient {
            background: #e4e6eb;
            color: black;
            float: left;
            border-bottom-left-radius: 4px;
        }

        /* Admin → RIGHT, Blue */
        .message.admin {
            background: #1877f2;
            color: white;
            float: right;
            border-bottom-right-radius: 4px;
        }

        /* System → RIGHT, Light Blue */
        .message.system {
            background: #dce9ff;
            color: #073a7c;
            float: right;
            border-left: 4px solid #1877f2;
            border-radius: 16px;
        }

        .admin-input-bar {
            display: flex;
            padding: 12px;
            background: white;
            border-top: 1px solid #ccc;
        }

        #message-input {
            flex: 1;
            padding: 12px;
            border-radius: 20px;
            border: 1px solid #bbb;
            outline: none;
        }

        #send-button {
            width: 48px;
            height: 48px;
            margin-left: 10px;
            border: none;
            background: #1877f2;
            color: white;
            border-radius: 50%;
            font-size: 1.4rem;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="container">
        <a href="admin_chats.php" class="btn btn-outline-primary mb-3">
            <i class="bi bi-arrow-left"></i> Back to Chat Window
        </a>
    </div>
    <div class="admin-chat-container">

        <div class="admin-chat-header">
            Chat with <?php echo htmlspecialchars($patient_info['first_name'] . ' ' . $patient_info['last_name']); ?>
        </div>

        <div id="chat-window"></div>

        <div class="admin-input-bar">
            <input type="text" id="message-input" placeholder="Reply as Admin">
            <button id="send-button">➤</button>
        </div>

    </div>

    <script>
        let last_message_id = 0;
        const patientId = <?php echo $patient_id; ?>;
        const chatWindow = document.getElementById('chat-window');

        /* Render bubble */
        function renderMessage(msg) {
            const el = document.createElement('div');

            if (msg.sender_type === "Patient") {
                el.className = "message patient";
            } else if (msg.sender_type === "Admin") {
                el.className = "message admin";
            } else {
                el.className = "message system";
            }

            const formatted = (msg.message_text || '')
                .replace(/\*\*(.*?)\*\*/g, "<strong>$1</strong>")
                .replace(/\n/g, "<br>");

            el.innerHTML = formatted;
            chatWindow.appendChild(el);
        }

        /* Fetch new messages */
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

        /* Click send */
        sendBtn.addEventListener('click', sendMessage);

        /* ENTER to send */
        inputField.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                sendMessage();
            }
        });
    </script>

</body>

</html>