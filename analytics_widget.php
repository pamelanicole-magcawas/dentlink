<?php
// analytics_widget.php - Include this in admin_dashboard.php
// This provides a quick overview with link to full analytics

// Make sure $conn is available
if (!isset($conn)) {
    require_once 'db_connect.php';
}

// Fetch quick stats
$today = date('Y-m-d');

// New users (last 30 days)
$new_users_stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'Patient' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$new_users_stmt->execute();
$new_users = $new_users_stmt->get_result()->fetch_assoc()['count'];
$new_users_stmt->close();

// Pending appointments today
$query = "
    SELECT COUNT(*) AS pending_today
    FROM appointments
    WHERE status = 'pending'
    AND DATE(created_at) = CURDATE()
";
$result = $conn->query($query);
$row = $result->fetch_assoc();
$pendingToday = $row['pending_today'];

// Approved appointments today
$approved_today_stmt = $conn->prepare("
    SELECT COUNT(*) as count 
    FROM appointments 
    WHERE status = 'approved' 
    AND DATE(updated_at) = ?
");
$approved_today_stmt->bind_param("s", $today);
$approved_today_stmt->execute();
$approved_today = $approved_today_stmt->get_result()->fetch_assoc()['count'];
$approved_today_stmt->close();

// Unread messages
$unread_msgs_stmt = $conn->prepare("SELECT COUNT(*) as count FROM chat_messages WHERE sender_type = 'Patient' AND is_read = 0");
$unread_msgs_stmt->execute();
$unread_msgs = $unread_msgs_stmt->get_result()->fetch_assoc()['count'];
$unread_msgs_stmt->close();
?>

<style>
    .analytics-widget {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 50%, var(--accent-color) 100%);
        border-radius: var(--card-radius);
        padding: 30px;
        color: white;
        box-shadow: 0 10px 30px rgba(128, 161, 186, 0.3);
        margin: 30px 0;
        position: relative;
        overflow: hidden;
    }

    .analytics-widget::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255, 247, 221, 0.1) 0%, transparent 70%);
        animation: pulseGlow 8s ease-in-out infinite;
    }

    @keyframes pulseGlow {
        0%, 100% { 
            transform: scale(1); 
            opacity: 0.5; 
        }
        50% { 
            transform: scale(1.1); 
            opacity: 0.8; 
        }
    }

    .analytics-widget h3 {
        color: white;
        margin-bottom: 25px;
        font-weight: 600;
        position: relative;
        z-index: 2;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .quick-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
        position: relative;
        z-index: 2;
    }

    .quick-stat {
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 15px;
        padding: 25px 20px;
        text-align: center;
        transition: all var(--transition);
        position: relative;
        overflow: hidden;
        cursor: pointer;
        text-decoration: none;
        color: white;
        display: block;
    }

    .quick-stat::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(255, 247, 221, 0.2) 0%, transparent 100%);
        opacity: 0;
        transition: opacity var(--transition);
    }

    .quick-stat:hover {
        transform: translateY(-8px);
        background: rgba(255, 255, 255, 0.3);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        color: white;
    }

    .quick-stat:hover::before {
        opacity: 1;
    }

    .quick-stat-value {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 8px;
        color: var(--light-color);
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        position: relative;
        z-index: 1;
    }

    .quick-stat-label {
        font-size: 0.85rem;
        opacity: 0.95;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 500;
        position: relative;
        z-index: 1;
    }

    .view-full-analytics {
        display: inline-block;
        background: white;
        color: var(--primary-color);
        padding: 12px 35px;
        border-radius: 25px;
        text-decoration: none;
        font-weight: 600;
        transition: all var(--transition);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        position: relative;
        z-index: 2;
    }

    .view-full-analytics:hover {
        transform: scale(1.05);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        color: var(--secondary-color);
        background: var(--light-color);
    }

    .view-full-analytics i {
        transition: transform var(--transition);
    }

    .view-full-analytics:hover i {
        transform: translateX(5px);
    }
</style>

<div class="analytics-widget">
    <h3><i class="bi bi-graph-up me-2"></i>Today's Overview</h3>
    
    <div class="quick-stats-grid">
        <a href="patient_records.php" class="quick-stat">
            <div class="quick-stat-value"><?= $new_users ?></div>
            <div class="quick-stat-label">New Users (30d)</div>
        </a>
        
        <a href="pending_appointments.php" class="quick-stat">
            <div class="quick-stat-value"><?= $pendingToday ?></div>
            <div class="quick-stat-label">Pending Today</div>
        </a>
        
        <a href="approved_appointments.php" class="quick-stat">
            <div class="quick-stat-value"><?= $approved_today ?></div>
            <div class="quick-stat-label">Approved Today</div>
        </a>
        
        <a href="admin_chats.php" class="quick-stat">
            <div class="quick-stat-value"><?= $unread_msgs ?></div>
            <div class="quick-stat-label">Unread Messages</div>
        </a>
    </div>
    
    <div class="text-center">
        <a href="analytics.php" class="view-full-analytics">
            <i class="bi bi-bar-chart-line me-2"></i>View Full Analytics
        </a>
    </div>
</div>
