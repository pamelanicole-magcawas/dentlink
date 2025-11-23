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
    <title>User Profile - DentLink</title>
    <link rel="stylesheet" href="bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="credentials.css">
    <link rel="stylesheet" href="dashboard.css">
</head>

<body class="profile-page">
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
                        <a class="nav-link px-3" href="dashboard.php">Home</a>
                    </li>
                    <li class="nav-item dropdown ms-3">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                            <img src="upload/<?= htmlspecialchars($user['profile_pic']); ?>" 
                                 alt="Profile" 
                                 style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover; margin-right: 8px;">
                            <?php echo htmlspecialchars($user['first_name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person"></i> Profile</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="profile-container">
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-image-container">
                    <img src="upload/<?= htmlspecialchars($user['profile_pic']); ?>"
                        alt="Profile Picture"
                        class="profile-image">
                </div>
                <h2 class="profile-name">
                    <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                </h2>
            </div>

            <div class="profile-body">
                <div class="profile-info-item">
                    <div class="profile-info-icon">
                        <i class="bi bi-hash"></i>
                    </div>
                    <div class="profile-info-content">
                        <div class="profile-info-label">User ID</div>
                        <p class="profile-info-value"><?= htmlspecialchars($user['user_id']); ?></p>
                    </div>
                </div>

                <div class="profile-info-item">
                    <div class="profile-info-icon">
                        <i class="bi bi-envelope-fill"></i>
                    </div>
                    <div class="profile-info-content">
                        <div class="profile-info-label">Email Address</div>
                        <p class="profile-info-value"><?= htmlspecialchars($user['email']); ?></p>
                    </div>
                </div>

                <div class="profile-info-item">
                    <div class="profile-info-icon">
                        <i class="bi bi-telephone-fill"></i>
                    </div>
                    <div class="profile-info-content">
                        <div class="profile-info-label">Phone Number</div>
                        <p class="profile-info-value"><?= htmlspecialchars($user['phone']); ?></p>
                    </div>
                </div>

                <div class="profile-info-item">
                    <div class="profile-info-icon">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>
                    <div class="profile-info-content">
                        <div class="profile-info-label">Address</div>
                        <p class="profile-info-value"><?= htmlspecialchars($user['address']); ?></p>
                    </div>
                </div>

                <div class="profile-info-item">
                    <div class="profile-info-icon">
                        <i class="bi bi-calendar-check-fill"></i>
                    </div>
                    <div class="profile-info-content">
                        <div class="profile-info-label">Member Since</div>
                        <p class="profile-info-value"><?= date('F d, Y', strtotime($user['created_at'])); ?></p>
                    </div>
                </div>

                <div class="profile-actions">
                    <a href="dashboard.php" class="btn-profile btn-profile-primary">
                        <i class="bi bi-arrow-left"></i>
                        Back to Dashboard
                    </a>
                    <a href="edit_profile.php" class="btn-profile btn-profile-outline">
                        <i class="bi bi-pencil-square"></i>
                        Edit Profile
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>