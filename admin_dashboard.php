<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role'] !== 'Admin') {
    header("Location: dashboard.php");
    exit();
}

require_once 'db_connect.php';

$user_id = $_SESSION['user_id'];
$user_sql = "SELECT first_name, last_name FROM users WHERE user_id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();

if ($user_result->num_rows > 0) {
    $user_data = $user_result->fetch_assoc();
    $_SESSION['first_name'] = $user_data['first_name'];
    $_SESSION['last_name'] = $user_data['last_name'];
}
$user_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>DentLink - Admin Dashboard</title>
    <link rel="stylesheet" href="bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="admin_dashboard.css">
    <style>
        .activity-logs-section {
            padding: 40px 0;
            background: #f8f9fa;
        }
        
        .activity-mini-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .activity-mini-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            display: flex;
            align-items: center;
            gap: 15px;
            transition: transform 0.3s ease;
        }
        
        .activity-mini-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
        }
        
        .activity-mini-card .icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }
        
        .activity-mini-card .info h6 {
            margin: 0;
            font-size: 0.85rem;
            color: #6c757d;
            font-weight: 500;
        }
        
        .activity-mini-card .info .value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #212529;
            margin: 0;
        }
        
        .recent-logs-table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .recent-logs-table .table-header {
            background: linear-gradient(135deg, #80A1BA 0%, #91C4C3 100%);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .recent-logs-table .table-header h5 {
            margin: 0;
            font-weight: 600;
        }
        
        .recent-logs-table table {
            width: 100%;
            margin: 0;
        }
        
        .recent-logs-table table thead th {
            background: #f8f9fa;
            padding: 15px;
            font-weight: 600;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
            font-size: 0.9rem;
        }
        
        .recent-logs-table table tbody td {
            padding: 12px 15px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 0.9rem;
        }
        
        .recent-logs-table table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .view-all-btn {
            background: white;
            color: #80A1BA;
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .view-all-btn:hover {
            background: rgba(255,255,255,0.9);
            transform: translateX(5px);
        }
        
        .activity-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .activity-badge.login {
            background: #d1fae5;
            color: #22c55e;
        }
        
        .activity-badge.logout {
            background: #fee2e2;
            color: #000000;
        }
        
        .activity-badge.action {
            background: #dbeafe;
            color: #1e40af;
        }
    </style>
</head>

<body>

    <aside class="sidebar">
        <div class="logo">
            <img src="dentlink-logo.png" alt="DentLink Logo">
            <h4>DentLink</h4>
        </div>

        <nav class="sidebar-nav">
            <a href="admin_dashboard.php" class="nav-link active">
                <i class="bi bi-house-door"></i> <span>Dashboard</span>
            </a>
            <a href="pending_appointments.php" class="nav-link">
                <i class="bi bi-calendar-check"></i> <span>Pending Appointments</span>
            </a>
            <a href="patient_records.php" class="nav-link">
                <i class="bi bi-folder2-open"></i> <span>Patient Records</span>
            </a>
            <a href="scan_qr.php" class="nav-link">
                <i class="bi bi-qr-code-scan"></i> <span>QR Scanner</span>
            </a>
            <a href="admin_chats.php" class="nav-link">
                <i class="bi bi-chat-dots"></i> <span>Messages</span>
            </a>
        </nav>

        <div class="profile-section">
            <button class="profile-btn" onclick="document.getElementById('logoutForm').submit();">
                <i class="bi bi-person-circle" style="font-size: 1.5rem;"></i>
                <div class="profile-info">
                    <div class="profile-name"><?php echo htmlspecialchars($_SESSION['first_name']); ?></div>
                    <small style="opacity: 0.8;">Admin</small>
                </div>
                <i class="bi bi-box-arrow-right"></i>
            </button>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">

        <!-- HERO SECTION -->
        <section id="home" class="hero-section position-relative">
            <div class="hero-overlay"></div>
            <div class="container">
                <div class="row align-items-center py-5" style="min-height: 60vh;">
                    <div class="col-lg-10 mx-auto text-center position-relative" style="z-index: 2;">
                        <h1 class="display-3 fw-bold mb-4 hero-title text-dark">
                            DentLink Management System
                        </h1>
                        <p class="lead mb-5 hero-subtitle text-dark">
                            Monitor real-time analytics, manage appointments, and oversee patient records efficiently.
                        </p>
                    </div>
                </div>
            </div>
            <div class="smile-curve">
                <svg viewBox="0 0 1440 120" width="100%" height="120" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 0C240 80 480 120 720 120C960 120 1200 80 1440 0V120H0V0Z" fill="#f8f9fa" />
                </svg>
            </div>
        </section>

        <!-- CHARTS SECTION -->
        <section class="charts-section">
            <div class="container">
                <div class="text-center mb-5">
                    <h2 class="display-5 fw-bold mb-3" style="color: #80A1BA;">Dashboard Analytics</h2>
                    <p class="lead text-muted">Real-time metrics and visualizations</p>
                </div>

                <div class="row g-4">
                    <!-- Appointments Status Chart -->
                    <div class="col-lg-6">
                        <div class="chart-card">
                            <h5><i class="bi bi-graph-up me-2"></i>Appointments Overview</h5>
                            <div class="chart-wrapper">
                                <canvas id="appointmentsChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- User Statistics Chart -->
                    <div class="col-lg-6">
                        <div class="chart-card">
                            <h5><i class="bi bi-bar-chart me-2"></i>Services Distribution</h5>
                            <div class="chart-wrapper">
                                <canvas id="servicesChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Completion Rate Chart -->
                    <div class="col-lg-6">
                        <div class="chart-card">
                            <h5><i class="bi bi-pie-chart me-2"></i>Completion Rate</h5>
                            <div class="chart-wrapper">
                                <canvas id="completionChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Monthly Appointments Trend -->
                    <div class="col-lg-6">
                        <div class="chart-card">
                            <h5><i class="bi bi-graph-up me-2"></i>Monthly Trend</h5>
                            <div class="chart-wrapper">
                                <canvas id="trendChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Bar -->
                <div class="action-bar">
                    <a href="analytics.php" class="action-btn">
                        <i class="bi bi-graph-up"></i> Full Analytics
                    </a>
                    <a href="reports.php" class="action-btn">
                        <i class="bi bi-file-earmark-bar-graph"></i> Generate Reports
                    </a>
                </div>
            </div>
        </section>

        <!-- ACTIVITY LOGS SECTION -->
        <section class="activity-logs-section">
            <div class="container">
                <div class="text-center mb-4">
                    <h2 class="display-6 fw-bold mb-2" style="color: #80A1BA;">Recent Activity</h2>
                    <p class="text-muted">Quick overview of system activities</p>
                </div>

                <!-- Mini Summary Cards -->
                <div class="activity-mini-cards">
                    <div class="activity-mini-card">
                        <div class="icon" style="background: linear-gradient(135deg, #80A1BA, #91C4C3);">
                            <i class="bi bi-people-fill"></i>
                        </div>
                        <div class="info">
                            <h6>Active Users</h6>
                            <p class="value" id="mini-active-users">0</p>
                        </div>
                    </div>
                    <div class="activity-mini-card">
                        <div class="icon" style="background: linear-gradient(135deg, #4ade80, #22c55e);">
                            <i class="bi bi-activity"></i>
                        </div>
                        <div class="info">
                            <h6>Today's Activities</h6>
                            <p class="value" id="mini-today-activities">0</p>
                        </div>
                    </div>
                    <div class="activity-mini-card">
                        <div class="icon" style="background: linear-gradient(135deg, #fbbf24, #f59e0b);">
                            <i class="bi bi-box-arrow-in-right"></i>
                        </div>
                        <div class="info">
                            <h6>Recent Logins</h6>
                            <p class="value" id="mini-recent-logins">0</p>
                        </div>
                    </div>
                </div>

                <!-- Recent Logs Table -->
                <div class="recent-logs-table">
                    <div class="table-header">
                        <h5><i class="bi bi-clock-history me-2"></i>Latest Activity Logs</h5>
                        <a href="activity_logs.php" class="view-all-btn">
                            View All <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Activity</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody id="recent-logs-body">
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted">
                                    <i class="bi bi-hourglass-split"></i> Loading recent activities...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="text-white" style="background: linear-gradient(135deg, #80A1BA 0%, #91C4C3 100%); padding: 20px 0;">
            <div class="container text-center">
                <p style="margin: 0;">&copy; 2025 DentLink: Dental Clinic Digital Appointment and Patient Records Management System</p>
                <p style="margin: 0;">All rights reserved.</p>
            </div>
        </footer>
    </main>


    <!-- Logout form for profile button -->
    <form id="logoutForm" action="logout.php" method="POST" style="display: none;"></form>

    <script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let appointmentsChartInstance, servicesChartInstance, completionChartInstance, trendChartInstance;

        function loadCharts() {
            fetch('fetch_analytics.php')
                .then(response => response.json())
                .then(result => {
                    if (!result.success) {
                        throw new Error(result.error || 'Failed to load analytics');
                    }

                    const data = result.data;

                    // Appointments Status Chart
                    const appointmentsCtx = document.getElementById('appointmentsChart').getContext('2d');
                    if (appointmentsChartInstance) {
                        appointmentsChartInstance.destroy();
                    }
                    appointmentsChartInstance = new Chart(appointmentsCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Pending', 'Approved', 'Completed', 'Denied'],
                            datasets: [{
                                data: [
                                    data.pending_appointments_total,
                                    data.approved_appointments_total,
                                    data.completed_appointments_total,
                                    data.denied_appointments_total
                                ],
                                backgroundColor: ['#fbbf24', '#60a5fa', '#4ade80', '#f87171'],
                                borderColor: '#ffffff',
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        font: {
                                            size: 14,
                                            family: 'Poppins'
                                        }
                                    }
                                }
                            }
                        }
                    });

                    // Services Distribution Chart
                    const servicesCtx = document.getElementById('servicesChart').getContext('2d');
                    if (servicesChartInstance) {
                        servicesChartInstance.destroy();
                    }
                    servicesChartInstance = new Chart(servicesCtx, {
                        type: 'bar',
                        data: {
                            labels: ['Week', 'Month', 'Total'],
                            datasets: [{
                                label: 'Appointments',
                                data: [
                                    data.appointments_this_week,
                                    data.appointments_this_month,
                                    data.approved_appointments_total
                                ],
                                backgroundColor: ['#80A1BA', '#91C4C3', '#B4DEBD'],
                                borderRadius: 8,
                                borderWidth: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        font: {
                                            family: 'Poppins'
                                        }
                                    }
                                },
                                x: {
                                    ticks: {
                                        font: {
                                            family: 'Poppins'
                                        }
                                    }
                                }
                            }
                        }
                    });

                    // Completion Rate Chart
                    const completionCtx = document.getElementById('completionChart').getContext('2d');
                    if (completionChartInstance) {
                        completionChartInstance.destroy();
                    }
                    completionChartInstance = new Chart(completionCtx, {
                        type: 'pie',
                        data: {
                            labels: ['Completed', 'In Progress'],
                            datasets: [{
                                data: [
                                    data.completion_rate,
                                    (100 - data.completion_rate)
                                ],
                                backgroundColor: ['#4ade80', '#e5e7eb'],
                                borderColor: '#ffffff',
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        font: {
                                            size: 14,
                                            family: 'Poppins'
                                        }
                                    }
                                }
                            }
                        }
                    });

                    // Trend Chart
                    const trendCtx = document.getElementById('trendChart').getContext('2d');
                    if (trendChartInstance) {
                        trendChartInstance.destroy();
                    }
                    trendChartInstance = new Chart(trendCtx, {
                        type: 'line',
                        data: {
                            labels: ['Active Patients', 'New Users', 'Checked In'],
                            datasets: [{
                                label: 'Count',
                                data: [
                                    data.active_patients_week,
                                    data.new_users,
                                    data.checked_in_today
                                ],
                                borderColor: '#80A1BA',
                                backgroundColor: 'rgba(128, 161, 186, 0.1)',
                                borderWidth: 3,
                                fill: true,
                                tension: 0.4,
                                pointBackgroundColor: '#80A1BA',
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 2,
                                pointRadius: 6
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        font: {
                                            family: 'Poppins'
                                        }
                                    }
                                },
                                x: {
                                    ticks: {
                                        font: {
                                            family: 'Poppins'
                                        }
                                    }
                                }
                            }
                        }
                    });
                })
                .catch(error => console.error('Error loading charts:', error));
        }

        // Load Activity Logs
        function loadRecentActivityLogs() {
            $.ajax({
                url: 'fetch_activity_logs.php',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    // Update summary cards
                    $('#mini-active-users').text(data.totalActiveUsers || 0);
                    $('#mini-today-activities').text(data.todayActivities || 0);
                    $('#mini-recent-logins').text(data.recentLogins || 0);

                    // Parse the HTML from allLogs and limit to 5 rows
                    if (data.allLogs && data.allLogs.trim() !== '') {
                        const tempDiv = $('<div>').html(data.allLogs);
                        const rows = tempDiv.find('tr');
                        
                        if (rows.length > 0) {
                            let html = '';
                            rows.slice(0, 5).each(function(index) {
                                const tds = $(this).find('td');
                                if (tds.length >= 4) {
                                    const userName = tds.eq(1).text().trim();
                                    const activity = tds.eq(2).text().trim();
                                    const timestamp = tds.eq(3).text().trim();
                                    
                                    const activityType = activity.toLowerCase().includes('login') ? 'login' : 
                                                       activity.toLowerCase().includes('logout') ? 'logout' : 'action';
                                    
                                    html += `
                                        <tr>
                                            <td><strong>${userName}</strong></td>
                                            <td><span class="activity-badge ${activityType}">${activity}</span></td>
                                            <td><small class="text-muted">${timestamp}</small></td>
                                        </tr>
                                    `;
                                }
                            });
                            
                            if (html !== '') {
                                $('#recent-logs-body').html(html);
                            } else {
                                $('#recent-logs-body').html(`
                                    <tr>
                                        <td colspan="3" class="text-center py-3 text-muted">
                                            <i class="bi bi-inbox"></i> No recent activities
                                        </td>
                                    </tr>
                                `);
                            }
                        } else {
                            $('#recent-logs-body').html(`
                                <tr>
                                    <td colspan="3" class="text-center py-3 text-muted">
                                        <i class="bi bi-inbox"></i> No recent activities
                                    </td>
                                </tr>
                            `);
                        }
                    } else {
                        $('#recent-logs-body').html(`
                            <tr>
                                <td colspan="3" class="text-center py-3 text-muted">
                                    <i class="bi bi-inbox"></i> No recent activities
                                </td>
                            </tr>
                        `);
                    }
                },
                error: function() {
                    $('#recent-logs-body').html(`
                        <tr>
                            <td colspan="3" class="text-center py-3 text-danger">
                                <i class="bi bi-exclamation-triangle"></i> Failed to load activities
                            </td>
                        </tr>
                    `);
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            loadCharts();
            loadRecentActivityLogs();
            
            // Refresh activity logs every 30 seconds
            setInterval(loadRecentActivityLogs, 30000);
        });
    </script>

</body>

</html>
