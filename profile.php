<?php
session_start();
include("db_connect.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user details from the database
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
} else {
    session_destroy();
    header("Location: login.php");
    exit();
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body style="font-family: Cambria, serif; color: #333;">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg bg-white shadow-sm py-3 fixed-top">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary d-flex align-items-center" href="#">
            <img src="dentlink-logo.png" alt="DentLink" width="35" class="me-2">
            DentLink
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav align-items-center">
                <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="bi bi-house-door-fill"></i> Home</a></li>
                <li class="nav-item"><a class="nav-link" href="dashboard.php#services"><i class="bi bi-clipboard2-pulse-fill"></i> Services</a></li>
                <li class="nav-item d-flex align-items-center">
                    <a href="profile.php" class="d-flex align-items-center text-decoration-none text-dark">
                        <img src="upload/<?= htmlspecialchars($user['profile_pic']); ?>"
                            alt="Profile Picture"
                            style="width:40px; height:40px; border-radius:50%; object-fit:cover;">
                        <span class="ms-2"><?= htmlspecialchars($user['first_name']); ?></span>
                    </a>
                </li>
                <li class="nav-item"><a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container py-5" style="margin-top:80px;">
    <div class="card mx-auto" style="max-width:600px;">
        <div class="card-body text-center">
            <img src="upload/<?= htmlspecialchars($user['profile_pic']); ?>"
                alt="Profile Picture"
                class="rounded-circle mb-3"
                style="width:200px; height:200px; object-fit:cover;">
            <h3 class="card-title"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h3>
            <p class="text-muted"><strong>ID:</strong> <?= htmlspecialchars($user['user_id']); ?></p>
            <p class="text-muted mb-1"><strong>Email:</strong> <?= htmlspecialchars($user['email']); ?></p>
            <p class="text-muted mb-1"><strong>Phone:</strong> <?= htmlspecialchars($user['phone']); ?></p>
            <p class="text-muted mb-1"><strong>Address:</strong> <?= htmlspecialchars($user['address']); ?></p>
            <p class="text-muted"><strong>Joined:</strong> <?= htmlspecialchars($user['created_at']); ?></p>
            <a href="dashboard.php" class="btn btn-primary mt-3">Back to Dashboard</a>
        </div>
    </div>
</div>

<script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
