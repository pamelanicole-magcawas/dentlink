<?php
// chat.php – PATIENT CHAT UI
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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chat - DentLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="patient.css">
</head>

<body class="chat-page">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg bg-white sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center" href="dashboard.php" style="color: #80A1BA;">
                <img src="dentlink-logo.png" alt="Logo" width="50" height="45" class="me-2">
                <span style="font-size: 1.5rem;">DentLink</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link px-3" href="dashboard.php#home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3" href="dashboard.php#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3" href="dashboard.php#services">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3" href="dashboard.php#reviews">Reviews</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3" href="dashboard.php#contact">Contact Us</a>
                    </li>
                    <!-- Chat Button (Active) -->
                    <li class="nav-item ms-2">
                        <a class="nav-link chat-btn px-3 active" href="chat.php">
                            <i class="bi bi-chat-dots-fill chat-icon"></i>
                            <span class="d-none d-lg-inline">Chat</span>
                        </a>
                    </li>
                    <li class="nav-item dropdown ms-3">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                            <img src="upload/<?= htmlspecialchars($_SESSION['profile_pic'] ?? 'default-avatar.png'); ?>"
                                alt="Profile"
                                style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover; margin-right: 8px;">
                            <?= htmlspecialchars($_SESSION['first_name'] ?? 'User'); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="chat-wrapper">
        <div class="chat-container">

            <!-- Chat Header -->
            <div class="chat-header">
                <a href="dashboard.php" class="chat-header-back">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <div class="chat-header-icon">
                    <i class="bi bi-chat-dots-fill"></i>
                </div>
                <div class="chat-header-info">
                    <p class="chat-header-title">Chat with Admin</p>
                    <p class="chat-header-subtitle">We're here to help!</p>
                </div>
            </div>

            <!-- Chat Messages Window -->
            <div id="chat-window" class="chat-window"></div>

            <!-- Quick Action Buttons -->
            <div class="chat-quick-actions">
                <div class="chat-quick-actions-label">Quick Actions:</div>
                <div id="message-options" class="chat-options-container">
                    <?php foreach ($options as $opt): ?>
                        <button class="chat-option-btn" data-query-id="<?= $opt['query_id'] ?>">
                            <?= htmlspecialchars($opt['button_label']) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Chat Input Bar -->
            <div class="chat-input-bar">
                <input type="text" id="message-input" class="chat-input" placeholder="Type your message...">
                <button id="send-button" class="chat-send-btn">
                    <i class="bi bi-send-fill"></i>
                </button>
            </div>

        </div>
    </div>

    <script>
        let last_message_id = 0;
        const chatWindow = document.getElementById("chat-window");

        // RENDER MESSAGE
        function renderMessage(msg) {
            const el = document.createElement("div");
            el.className = "chat-message";

            if (msg.sender_type === "Patient") {
                el.classList.add("patient");
            } else if (msg.sender_type === "Admin") {
                el.classList.add("admin");
            } else {
                el.classList.add("system");
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
            const btn = e.target.closest(".chat-option-btn");
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
