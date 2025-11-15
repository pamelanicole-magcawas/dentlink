<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['role'] !== 'Admin') {   // ⭐ FIX
    header("Location: dashboard.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="dashboard.css">
    <title>DentLink - Clinic System</title>
</head>

<body style="font-family: Cambria, serif; color: #333;">
    <nav class="navbar navbar-expand-lg bg-white shadow-sm py-3">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary d-flex align-items-center" href="#">
                <img src="dentlink-logo.png" alt="DentLink" width="35" class="me-2">
                Admin Dashboard
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item"><a class="nav-link" href="admin_dashboard.php"><i class="bi bi-house-door-fill"></i> Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_chats.php"><i class="bi bi-envelope-fill"></i>Messages</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>


    <section id="home" class="min-vh-100 py-5 d-flex align-items-center justify-content-center text-center">
        <div class="container">
            <h2 class="fw-bold mb-5">Welcome to DentLink Clinic System</h2>
            <p class="text-muted mb-5">Manage dental appointments and records easily.</p>

            <div class="row g-4 justify-content-center">
                <!-- View Pending Appointments -->
                <div class="col-md-4">
                    <div class="card home-card text-center shadow-sm border-0 h-100">
                        <div class="card-body">
                            <i class="bi bi-calendar-check display-3 text-primary mb-3"></i>
                            <h5 class="card-title fw-bold">View Pending Appointments</h5>
                            <p class="card-text">Manage patients' pending appointments.</p>
                            <a href="pending_appointments.php" class="btn btn-primary">View Pending Appointments</a>
                        </div>
                    </div>
                </div>

                <!-- View Approved Appointments -->
                <div class="col-md-4">
                    <div class="card home-card text-center shadow-sm border-0 h-100">
                        <div class="card-body">
                            <i class="bi bi-list-check display-3 text-warning mb-3"></i>
                            <h5 class="card-title fw-bold">View Approved Appointments</h5>
                            <p class="card-text">Check approved appointments schedules.</p>
                            <a href="approved_appointments.php" class="btn btn-warning text-white">View Approved Appointments</a>
                        </div>
                    </div>
                </div>

                 <!-- View Patient Records -->
                <div class="col-md-4">
                    <div class="card home-card text-center shadow-sm border-0 h-100">
                        <div class="card-body">
                            <i class="bi bi-folder2-open display-3 text-success mb-3"></i>
                            <h5 class="card-title fw-bold">View Patient Records</h5>
                            <p class="card-text">Manage patient records.</p>
                            <a href="patient_records.php" class="btn btn-warning text-white">View Patient Records</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section>
    </div>
    </div>
</body>

</html>