<?php
// index.php — PATIENT CHAT UI
include 'db_config.php';

if ($current_user_id === 0) {
    die("Error: User not authenticated.");
}

// Load chat option buttons
$options = [];
$q = $conn->query("SELECT query_id, button_label FROM chat_options ORDER BY query_id ASC");
if ($q) $options = $q->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Patient Support Chat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">

    <style>
        body {
            background: #eef0f3;
            margin: 0;
            font-family: Arial, sans-serif;
        }

        .patient-chat-container {
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

        .patient-chat-header {
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

        /* CHAT BUBBLES */
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

        /* PATIENT → RIGHT (BLUE) */
        .message.patient {
            background: #1877f2;
            color: white;
            float: right;
            border-bottom-right-radius: 4px;
        }

        /* ADMIN + SYSTEM → LEFT (GRAY) */
        .message.admin {
            background: #e4e6eb;
            color: black;
            float: left;
            border-bottom-left-radius: 4px;
        }

        .message.system {
            background: #d9dce2;
            color: #333;
            float: left;
            border-left: 4px solid #6a6e74;
        }

        /* OPTION BUTTONS */
        #message-options {
            padding: 10px;
            background: white;
            border-top: 1px solid #ddd;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .option-button {
            padding: 8px 14px;
            border-radius: 20px;
            border: 1px solid #1877f2;
            color: #1877f2;
            background: white;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .option-button:hover {
            background: #e7f0ff;
        }

        .patient-input-bar {
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
        <a href="admin_dashboard.php" class="btn btn-outline-primary mb-3">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
    </div>
    <div class="patient-chat-container">

        <div class="patient-chat-header">
            DentLink Chat Support
        </div>

        <div id="chat-window"></div>

        <div id="message-options">
            <?php foreach ($options as $opt): ?>
                <button class="option-button" data-query-id="<?= $opt['query_id'] ?>">
                    <?= htmlspecialchars($opt['button_label']) ?>
                </button>
            <?php endforeach; ?>
        </div>

        <div class="patient-input-bar">
            <input type="text" id="message-input" placeholder="Type your message...">
            <button id="send-button">➤</button>
        </div>

    </div>

    <script>
        let last_message_id = 0;
        const chatWindow = document.getElementById("chat-window");

        // RENDER MESSAGE
        function renderMessage(msg) {
            const el = document.createElement("div");

            if (msg.sender_type === "Patient") {
                el.className = "message patient";
            } else if (msg.sender_type === "Admin") {
                el.className = "message admin";
            } else {
                el.className = "message system";
            }

            el.innerHTML = msg.message_text
                .replace(/\*\*(.*?)\*\*/g, "<strong>$1</strong>")
                .replace(/\n/g, "<br>");

            chatWindow.appendChild(el);
        }

        // FETCH MESSAGES
        function fetchMessages() {
            const xhr = new XMLHttpRequest();
            xhr.open("GET", "fetch_messages.php?last_id=" + last_message_id, true);

            xhr.onload = () => {
                try {
                    const messages = JSON.parse(xhr.responseText);
                    messages.forEach(msg => {
                        renderMessage(msg);
                        last_message_id = Math.max(last_message_id, msg.id);
                    });
                    chatWindow.scrollTop = chatWindow.scrollHeight;
                } catch {}
            };

            xhr.send();
        }

        fetchMessages();
        setInterval(fetchMessages, 1500);

        // SEND MESSAGE (ENTER + BUTTON)
        const inputField = document.getElementById("message-input");
        const sendBtn = document.getElementById("send-button");

        function sendMessage() {
            const text = inputField.value.trim();
            if (!text) return;

            const xhr = new XMLHttpRequest();
            xhr.open("POST", "send_message.php", true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

            xhr.onload = () => {
                try {
                    const r = JSON.parse(xhr.responseText);
                    if (r.success) {
                        inputField.value = "";
                        fetchMessages();
                    }
                } catch {}
            };

            xhr.send("message_text=" + encodeURIComponent(text));
        }

        sendBtn.addEventListener("click", sendMessage);

        inputField.addEventListener("keydown", e => {
            if (e.key === "Enter") {
                e.preventDefault();
                sendMessage();
            }
        });

        // OPTION BUTTON CLICK
        document.getElementById("message-options").addEventListener("click", e => {
            const btn = e.target.closest(".option-button");
            if (!btn) return;

            const queryId = btn.getAttribute("data-query-id");

            const xhr = new XMLHttpRequest();
            xhr.open("POST", "handle_option.php", true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

            xhr.onload = () => {
                try {
                    const r = JSON.parse(xhr.responseText);
                    if (r.success) fetchMessages();
                } catch {}
            };

            xhr.send("query_id=" + encodeURIComponent(queryId));
        });
    </script>

</body>

</html>