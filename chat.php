<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
$user_role = $_SESSION['role'];

// Get admin user_id for patients to chat with
$admin_sql = "SELECT user_id, first_name, last_name FROM users WHERE role = 'Admin' LIMIT 1";
$admin_result = $conn->query($admin_sql);
$admin = $admin_result->fetch_assoc();
$admin_id = $admin['user_id'];
$admin_name = $admin['first_name'] . ' ' . $admin['last_name'];

// Get quick actions
$quick_actions_sql = "SELECT * FROM chat_quick_actions WHERE is_active = 1";
$quick_actions_result = $conn->query($quick_actions_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - DentLink</title>
    <link rel="stylesheet" href="bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #80A1BA;
            --secondary-color: #91C4C3;
            --accent-color: #B4DEBD;
            --light-color: #FFF7DD;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, var(--accent-color) 0%, var(--light-color) 100%);
            min-height: 100vh;
        }

        .chat-container {
            max-width: 1000px;
            margin: 20px auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 24px rgba(128, 161, 186, 0.15);
            overflow: hidden;
            height: calc(100vh - 40px);
            display: flex;
            flex-direction: column;
        }

        .chat-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .chat-header h5 {
            margin: 0;
            font-weight: 600;
        }

        .back-btn {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            padding: 8px 15px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: #f8f9fa;
        }

        .message {
            margin-bottom: 15px;
            display: flex;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message.sent {
            justify-content: flex-end;
        }

        .message.received {
            justify-content: flex-start;
        }

        .message-content {
            max-width: 70%;
            padding: 12px 16px;
            border-radius: 15px;
            word-wrap: break-word;
        }

        .message.sent .message-content {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            border-bottom-right-radius: 5px;
        }

        .message.received .message-content {
            background: white;
            color: #333;
            border: 1px solid #e0e0e0;
            border-bottom-left-radius: 5px;
        }

        .message.system .message-content {
            background: linear-gradient(135deg, rgba(180, 222, 189, 0.2) 0%, rgba(255, 247, 221, 0.2) 100%);
            color: #333;
            border: 1px solid var(--accent-color);
            max-width: 90%;
            margin: 0 auto;
        }

        .message-time {
            font-size: 0.75rem;
            color: #999;
            margin-top: 5px;
        }

        .quick-actions {
            padding: 15px 20px;
            background: white;
            border-top: 1px solid #e0e0e0;
            overflow-x: auto;
            white-space: nowrap;
        }

        .quick-action-btn {
            display: inline-block;
            background: linear-gradient(135deg, rgba(128, 161, 186, 0.1) 0%, rgba(145, 196, 195, 0.1) 100%);
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
            padding: 8px 15px;
            border-radius: 20px;
            margin-right: 10px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .quick-action-btn:hover {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            transform: translateY(-2px);
        }

        .chat-input-area {
            padding: 20px;
            background: white;
            border-top: 1px solid #e0e0e0;
        }

        .input-group input {
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            padding: 12px 20px;
            font-size: 0.95rem;
        }

        .input-group input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(128, 161, 186, 0.1);
        }

        .send-btn {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            color: white;
            padding: 12px 25px;
            border-radius: 25px;
            margin-left: 10px;
            transition: all 0.3s ease;
        }

        .send-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(128, 161, 186, 0.3);
        }

        .loading {
            text-align: center;
            padding: 20px;
            color: #999;
        }

        /* Scrollbar */
        .chat-messages::-webkit-scrollbar {
            width: 8px;
        }

        .chat-messages::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .chat-messages::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 4px;
        }
    </style>
</head>
<body>

    <div class="chat-container">
        <!-- Chat Header -->
        <div class="chat-header">
            <div class="d-flex align-items-center">
                <a href="dashboard.php" class="back-btn me-3">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <div>
                    <h5>
                        <i class="bi bi-chat-dots-fill me-2"></i>
                        <?php echo $user_role === 'Admin' ? 'Patient Support Chat' : 'Chat with Admin'; ?>
                    </h5>
                    <small><?php echo $user_role === 'Admin' ? 'Help patients with their concerns' : 'We\'re here to help!'; ?></small>
                </div>
            </div>
            <div>
                <i class="bi bi-circle-fill text-success" style="font-size: 0.5rem;"></i>
                <small>Online</small>
            </div>
        </div>

        <!-- Chat Messages -->
        <div class="chat-messages" id="chatMessages">
            <div class="loading">
                <i class="bi bi-hourglass-split"></i> Loading messages...
            </div>
        </div>

        <!-- Quick Actions (Patient Only) -->
        <?php if ($user_role === 'Patient'): ?>
        <div class="quick-actions">
            <strong class="d-block mb-2" style="color: var(--primary-color);">Quick Actions:</strong>
            <?php while ($action = $quick_actions_result->fetch_assoc()): ?>
                <button class="quick-action-btn" data-action="<?= $action['action_key'] ?>">
                    <?= htmlspecialchars($action['action_text']) ?>
                </button>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>

        <!-- Chat Input -->
        <div class="chat-input-area">
            <form id="chatForm" class="d-flex">
                <input type="text" 
                       class="form-control" 
                       id="messageInput" 
                       placeholder="Type your message..." 
                       required 
                       autocomplete="off">
                <button type="submit" class="send-btn">
                    <i class="bi bi-send-fill"></i> Send
                </button>
            </form>
        </div>
    </div>

    <script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const userId = <?= $user_id ?>;
        const userRole = '<?= $user_role ?>';
        const adminId = <?= $admin_id ?>;
        let lastMessageId = 0;

        // Load messages
        function loadMessages() {
            fetch('chat_load_messages.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const chatMessages = document.getElementById('chatMessages');
                        
                        if (data.messages.length === 0 && chatMessages.querySelector('.loading')) {
                            chatMessages.innerHTML = '<div class="text-center text-muted py-5"><i class="bi bi-chat-text display-1"></i><p class="mt-3">No messages yet. Start the conversation!</p></div>';
                            return;
                        }

                        if (data.messages.length > 0) {
                            lastMessageId = data.messages[data.messages.length - 1].message_id;
                            
                            chatMessages.innerHTML = '';
                            data.messages.forEach(msg => {
                                appendMessage(msg);
                            });
                            
                            chatMessages.scrollTop = chatMessages.scrollHeight;
                        }
                    }
                })
                .catch(error => console.error('Error loading messages:', error));
        }

        // Append message to chat
        function appendMessage(msg) {
            const chatMessages = document.getElementById('chatMessages');
            const messageDiv = document.createElement('div');
            
            if (msg.is_system_message == 1) {
                messageDiv.className = 'message system';
            } else {
                messageDiv.className = msg.sender_id == userId ? 'message sent' : 'message received';
            }
            
            const time = new Date(msg.created_at).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
            
            messageDiv.innerHTML = `
                <div class="message-content">
                    ${msg.message}
                    <div class="message-time">${time}</div>
                </div>
            `;
            
            chatMessages.appendChild(messageDiv);
        }

        // Send message
        document.getElementById('chatForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const messageInput = document.getElementById('messageInput');
            const message = messageInput.value.trim();
            
            if (!message) return;
            
            const formData = new FormData();
            formData.append('message', message);
            formData.append('message_type', 'text');
            
            fetch('chat_send_message.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    messageInput.value = '';
                    loadMessages();
                }
            })
            .catch(error => console.error('Error sending message:', error));
        });

        // Quick action buttons
        document.querySelectorAll('.quick-action-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const action = this.getAttribute('data-action');
                
                const formData = new FormData();
                formData.append('action_key', action);
                
                fetch('chat_quick_action.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadMessages();
                    }
                })
                .catch(error => console.error('Error:', error));
            });
        });

        // Poll for new messages every 2 seconds
        setInterval(() => {
            fetch(`chat_check_new.php?last_id=${lastMessageId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.has_new) {
                        loadMessages();
                    }
                });
        }, 2000);

        // Initial load
        loadMessages();
    </script>

</body>
</html>