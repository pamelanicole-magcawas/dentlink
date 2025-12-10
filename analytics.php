<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_connect.php';

$user_id = $_SESSION['user_id'];
$user_sql = "SELECT first_name FROM users WHERE user_id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();

if ($user_result->num_rows > 0) {
    $user_data = $user_result->fetch_assoc();
    $_SESSION['first_name'] = $user_data['first_name'];
}
$user_stmt->close();

$admin_name = $_SESSION['first_name'] ?? 'Admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Analytics Dashboard - DentLink</title>
    <link rel="stylesheet" href="bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="admin.css">
    <style>
        .analytics-page {
            padding: 20px;
            background: #f8f9fa;
            min-height: 100vh;
        }

        .analytics-header {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }

        .header-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            border-left: 4px solid;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, rgba(128,161,186,0.1), rgba(180,222,189,0.1));
            border-radius: 0 0 0 100%;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 15px;
            position: relative;
            z-index: 1;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin: 10px 0;
            position: relative;
            z-index: 1;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
            z-index: 1;
        }

        .stat-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 8px;
            position: relative;
            z-index: 1;
        }

        .stat-primary { border-left-color: #80A1BA; }
        .stat-primary .stat-icon { background: rgba(128,161,186,0.15); color: #80A1BA; }

        .stat-success { border-left-color: #4ade80; }
        .stat-success .stat-icon { background: rgba(74,222,128,0.15); color: #4ade80; }

        .stat-warning { border-left-color: #fbbf24; }
        .stat-warning .stat-icon { background: rgba(251,191,36,0.15); color: #fbbf24; }

        .stat-danger { border-left-color: #f87171; }
        .stat-danger .stat-icon { background: rgba(248,113,113,0.15); color: #f87171; }

        .stat-info { border-left-color: #60a5fa; }
        .stat-info .stat-icon { background: rgba(96,165,250,0.15); color: #60a5fa; }

        .stat-purple { border-left-color: #a78bfa; }
        .stat-purple .stat-icon { background: rgba(167,139,250,0.15); color: #a78bfa; }

        .loading-skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
            border-radius: 8px;
            height: 30px;
        }

        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        .refresh-btn {
            background: linear-gradient(135deg, #80A1BA, #91C4C3);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            transition: transform 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .refresh-btn:hover {
            transform: scale(1.05);
            color: white;
        }

        .reports-btn {
            background: linear-gradient(135deg, #91C4C3, #B4DEBD);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            transition: transform 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .reports-btn:hover {
            transform: scale(1.05);
            color: white;
        }

        .large-stat-card {
            grid-column: span 2;
        }

        @media (max-width: 768px) {
            .large-stat-card {
                grid-column: span 1;
            }
            
            .header-actions {
                width: 100%;
                margin-top: 15px;
            }
            
            .refresh-btn,
            .reports-btn {
                flex: 1;
            }
        }
    </style>
</head>
<body class="analytics-page">

    <a href="admin_dashboard.php" class="btn-back-dashboard">
        <i class="bi bi-arrow-left"></i> Back
    </a>

    <div class="analytics-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h2><i class="bi bi-graph-up me-2"></i>Analytics Dashboard</h2>
                <p class="text-muted mb-0">Real-time system metrics and insights</p>
            </div>
        </div>
        <p class="text-muted small mt-2 mb-0">Last updated: <span id="lastUpdated">Loading...</span></p>
    </div>

    <div class="analytics-grid" id="analyticsGrid">
        <!-- Loading skeletons -->
        <div class="stat-card">
            <div class="loading-skeleton"></div>
        </div>
        <div class="stat-card">
            <div class="loading-skeleton"></div>
        </div>
        <div class="stat-card">
            <div class="loading-skeleton"></div>
        </div>
        <div class="stat-card">
            <div class="loading-skeleton"></div>
        </div>
    </div>

    <script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function formatNumber(num) {
            return new Intl.NumberFormat().format(num);
        }

        function loadAnalytics() {
            const grid = document.getElementById('analyticsGrid');
            
            // Show loading state
            grid.innerHTML = `
                <div class="stat-card"><div class="loading-skeleton"></div></div>
                <div class="stat-card"><div class="loading-skeleton"></div></div>
                <div class="stat-card"><div class="loading-skeleton"></div></div>
                <div class="stat-card"><div class="loading-skeleton"></div></div>
            `;

            fetch('fetch_analytics.php')
                .then(response => response.json())
                .then(result => {
                    if (!result.success) {
                        throw new Error(result.error || 'Failed to load analytics');
                    }

                    const data = result.data;
                    document.getElementById('lastUpdated').textContent = result.timestamp;

                    grid.innerHTML = `
                        <!-- New Users -->
                        <div class="stat-card stat-primary">
                            <div class="stat-icon"><i class="bi bi-person-plus-fill"></i></div>
                            <div class="stat-value">${formatNumber(data.new_users)}</div>
                            <div class="stat-label">New Users (30 Days)</div>
                            <span class="stat-badge" style="background: rgba(128,161,186,0.15); color: #80A1BA;">
                                Total: ${formatNumber(data.total_patients)}
                            </span>
                        </div>

                        <!-- Pending Appointments Total -->
                        <div class="stat-card stat-warning">
                            <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
                            <div class="stat-value">${formatNumber(data.pending_appointments_total)}</div>
                            <div class="stat-label">Pending Appointments</div>
                            <span class="stat-badge" style="background: rgba(251,191,36,0.15); color: #d97706;">
                                Today: ${formatNumber(data.pending_appointments_today)}
                            </span>
                        </div>

                        <!-- Approved Appointments Total -->
                        <div class="stat-card stat-info">
                            <div class="stat-icon"><i class="bi bi-calendar-check"></i></div>
                            <div class="stat-value">${formatNumber(data.approved_appointments_total)}</div>
                            <div class="stat-label">Approved Appointments</div>
                            <span class="stat-badge" style="background: rgba(96,165,250,0.15); color: #2563eb;">
                                Today: ${formatNumber(data.approved_appointments_today)}
                            </span>
                        </div>

                        <!-- Completed Appointments Total -->
                        <div class="stat-card stat-success">
                            <div class="stat-icon"><i class="bi bi-check-circle-fill"></i></div>
                            <div class="stat-value">${formatNumber(data.completed_appointments_total)}</div>
                            <div class="stat-label">Completed Appointments</div>
                            <span class="stat-badge" style="background: rgba(74,222,128,0.15); color: #059669;">
                                Today: ${formatNumber(data.completed_appointments_today)}
                            </span>
                        </div>

                        <!-- Unread Messages Total -->
                        <div class="stat-card stat-danger">
                            <div class="stat-icon"><i class="bi bi-chat-dots-fill"></i></div>
                            <div class="stat-value">${formatNumber(data.unread_messages_total)}</div>
                            <div class="stat-label">Unread Messages</div>
                            <span class="stat-badge" style="background: rgba(248,113,113,0.15); color: #dc2626;">
                                Today: ${formatNumber(data.unread_messages_today)}
                            </span>
                        </div>

                        <!-- Checked-in Today -->
                        <div class="stat-card stat-purple">
                            <div class="stat-icon"><i class="bi bi-box-arrow-in-right"></i></div>
                            <div class="stat-value">${formatNumber(data.checked_in_today)}</div>
                            <div class="stat-label">Checked-in Today</div>
                            <span class="stat-badge" style="background: rgba(167,139,250,0.15); color: #7c3aed;">
                                Active Patients
                            </span>
                        </div>

                        <!-- Appointments This Week -->
                        <div class="stat-card stat-info">
                            <div class="stat-icon"><i class="bi bi-calendar-week"></i></div>
                            <div class="stat-value">${formatNumber(data.appointments_this_week)}</div>
                            <div class="stat-label">Appointments This Week</div>
                            <span class="stat-badge" style="background: rgba(96,165,250,0.15); color: #2563eb;">
                                Month: ${formatNumber(data.appointments_this_month)}
                            </span>
                        </div>

                        <!-- Upcoming Appointments -->
                        <div class="stat-card stat-info">
                            <div class="stat-icon"><i class="bi bi-calendar-event"></i></div>
                            <div class="stat-value">${formatNumber(data.upcoming_appointments_week)}</div>
                            <div class="stat-label">Upcoming (7 Days)</div>
                            <span class="stat-badge" style="background: rgba(96,165,250,0.15); color: #2563eb;">
                                Approved
                            </span>
                        </div>

                        <!-- Active Patients -->
                        <div class="stat-card stat-primary">
                            <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
                            <div class="stat-value">${formatNumber(data.active_patients_week)}</div>
                            <div class="stat-label">Active Patients (7 Days)</div>
                            <span class="stat-badge" style="background: rgba(128,161,186,0.15); color: #80A1BA;">
                                Logged In
                            </span>
                        </div>

                        <!-- Denied Appointments -->
                        <div class="stat-card stat-danger">
                            <div class="stat-icon"><i class="bi bi-x-circle-fill"></i></div>
                            <div class="stat-value">${formatNumber(data.denied_appointments_total)}</div>
                            <div class="stat-label">Denied Appointments</div>
                            <span class="stat-badge" style="background: rgba(248,113,113,0.15); color: #dc2626;">
                                All Time
                            </span>
                        </div>

                        <!-- Average Rating -->
                        <div class="stat-card stat-warning">
                            <div class="stat-icon"><i class="bi bi-star-fill"></i></div>
                            <div class="stat-value">${data.average_rating} <small style="font-size: 1rem; color: #666;">/5</small></div>
                            <div class="stat-label">Average Rating</div>
                            <span class="stat-badge" style="background: rgba(251,191,36,0.15); color: #d97706;">
                                ${formatNumber(data.total_reviews)} Reviews
                            </span>
                        </div>

                        <!-- Completion Rate -->
                        <div class="stat-card stat-success">
                            <div class="stat-icon"><i class="bi bi-graph-up-arrow"></i></div>
                            <div class="stat-value">${data.completion_rate}%</div>
                            <div class="stat-label">Completion Rate</div>
                            <span class="stat-badge" style="background: rgba(74,222,128,0.15); color: #059669;">
                                Performance
                            </span>
                        </div>

                        <!-- Most Popular Service -->
                        <div class="stat-card large-stat-card stat-primary">
                            <div class="stat-icon"><i class="bi bi-star-fill"></i></div>
                            <div class="stat-value">${formatNumber(data.most_popular_service_count)}</div>
                            <div class="stat-label">Most Popular Service</div>
                            <span class="stat-badge" style="background: rgba(128,161,186,0.15); color: #80A1BA;">
                                ${data.most_popular_service}
                            </span>
                        </div>
                    `;
                })
                .catch(error => {
                    console.error('Error loading analytics:', error);
                    grid.innerHTML = `
                        <div class="col-12">
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                Failed to load analytics. Please try again.
                            </div>
                        </div>
                    `;
                });
        }

        // Load analytics on page load
        loadAnalytics();

        // Auto-refresh every 30 seconds
        setInterval(loadAnalytics, 30000);
    </script>
</body>
</html>