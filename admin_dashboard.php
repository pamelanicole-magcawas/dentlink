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

$admin_name = $_SESSION['first_name'] ?? 'Admin';
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
    <link rel="stylesheet" href="admin-dashboard.css">
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

        <!-- Footer -->
        <footer class="text-white" style="background: linear-gradient(135deg, #80A1BA 0%, #91C4C3 100%); padding: 20px 0;">
            <div class="container text-center">
                <p style="margin: 0;">&copy; 2025 DentLink: Dental Clinic Management System</p>
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

        document.addEventListener('DOMContentLoaded', loadCharts);
    </script>

</body>

</html>