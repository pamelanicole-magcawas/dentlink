<?php
session_start();
require "db_connect.php";
require "log_activity.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

logActivity($_SESSION['user_id'], "Visited page: Activity Logs");

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Logs - DentLink</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body class="admin-page">

    <a href="admin_dashboard.php" class="btn-back-dashboard">
        <i class="bi bi-arrow-left"></i> Back to Dashboard
    </a>

    <div class="admin-page-header">
        <h2><i class="bi bi-clock-history"></i> Activity Logs</h2>
        <p>Monitor all system activities and user actions in real-time</p>
    </div>

    <!-- Summary Cards -->
    <div class="activity-summary-cards">
        <div class="summary-card primary">
            <div class="card-icon">
                <i class="bi bi-people-fill"></i>
            </div>
            <h5>Total Active Users</h5>
            <p class="card-value" id="total-active-users">0</p>
        </div>
        <div class="summary-card success">
            <div class="card-icon" style="background: linear-gradient(135deg, #4ade80, #22c55e);">
                <i class="bi bi-activity"></i>
            </div>
            <h5>Today's Activities</h5>
            <p class="card-value" id="today-activities">0</p>
        </div>
        <div class="summary-card warning">
            <div class="card-icon" style="background: linear-gradient(135deg, #fbbf24, #f59e0b);">
                <i class="bi bi-box-arrow-in-right"></i>
            </div>
            <h5>Recent Logins</h5>
            <p class="card-value" id="recent-logins">0</p>
        </div>
    </div>

    <!-- Tabs -->
    <div class="activity-tabs">
        <button class="tab-btn active" data-tab="all-logs">
            <i class="bi bi-list-ul me-1"></i> All Logs
        </button>
        <button class="tab-btn" data-tab="logins">
            <i class="bi bi-box-arrow-in-right me-1"></i> Logins
        </button>
        <button class="tab-btn" data-tab="actions">
            <i class="bi bi-cursor me-1"></i> Log out
        </button>
    </div>

    <!-- Activity Table -->
    <div class="activity-table-wrapper">
        <table class="activity-table" id="logs-table">
            <thead>
                <tr>
                    <th><i class="bi bi-hash me-1"></i>User ID</th>
                    <th><i class="bi bi-person me-1"></i>User Name</th>
                    <th><i class="bi bi-lightning me-1"></i>Activity</th>
                    <th><i class="bi bi-clock me-1"></i>Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="4" class="text-center py-4 text-muted">
                        <i class="bi bi-hourglass-split fs-4 d-block mb-2"></i>
                        Loading activity logs...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function fetchActivityData(filter = '') {
            $.getJSON('fetch_activity_logs.php?filter=' + filter, function(data) {
                $('#total-active-users').text(data.totalActiveUsers || 0);
                $('#today-activities').text(data.todayActivities || 0);
                $('#recent-logins').text(data.recentLogins || 0);

                if (data.allLogs && data.allLogs.trim() !== '') {
                    $('#logs-table tbody').html(data.allLogs);
                } else {
                    $('#logs-table tbody').html(`
                <tr>
                    <td colspan="4" class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        No activity logs found
                    </td>
                </tr>
            `);
                }
            }).fail(function() {
                $('#logs-table tbody').html(`
            <tr>
                <td colspan="4" class="text-center py-4 text-danger">
                    <i class="bi bi-exclamation-triangle fs-4 d-block mb-2"></i>
                    Failed to load activity logs
                </td>
            </tr>
        `);
            });
        }

        // Tab functionality
        $('.tab-btn').on('click', function() {
            $('.tab-btn').removeClass('active');
            $(this).addClass('active');

            const tab = $(this).data('tab');
            if (tab === 'all-logs') fetchActivityData(''); // all login/logout
            else if (tab === 'logins') fetchActivityData('login'); // only logins
            else if (tab === 'actions') fetchActivityData('logout'); // only logouts
        });

        // Initial load
        fetchActivityData();
        setInterval(() => fetchActivityData($('.tab-btn.active').data('tab') === 'logins' ? 'login' : ($('.tab-btn.active').data('tab') === 'actions' ? 'logout' : '')), 5000);
    </script>
</body>

</html>
