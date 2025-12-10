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

// Fetch admin's first name from database
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

// Set display name (fallback to 'Admin' if first_name not available)
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
    <link rel="stylesheet" href ="admin_dashboard.css">
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <img src="dentlink-logo.png" alt="DentLink Logo" style="background-color: #f0f0f0;">
            <h4>DentLink</h4>
        </div>

        <nav class="nav flex-column sidebar-nav">
            <a class="nav-link active" href="admin_dashboard.php">
                <i class="bi bi-house-door-fill"></i>
                <span>Dashboard</span>
            </a>
            
            <!-- Appointments Dropdown -->
            <div class="appointments-dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-calendar-check"></i>
                    <span>Appointments</span>
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="pending_appointments.php">
                        <i class="bi bi-clock-history me-2"></i>Pending
                    </a></li>
                    <li><a class="dropdown-item" href="approved_appointments.php">
                        <i class="bi bi-check-circle me-2"></i>Approved
                    </a></li>
                </ul>
            </div>

            <a class="nav-link" href="patient_records.php">
                <i class="bi bi-folder2-open"></i>
                <span>Patient Records</span>
            </a>
            <a class="nav-link" href="admin_chats.php">
                <i class="bi bi-chat-dots-fill"></i>
                <span>Messages</span>
            </a>
            <a class="nav-link" href="#reviews">
                <i class="bi bi-star-fill"></i>
                <span>Reviews</span>
            </a>
        </nav>

        <!-- Profile Section -->
        <div class="profile-section">
            <button class="profile-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="upload/<?= htmlspecialchars($_SESSION['profile_pic'] ?? 'default-avatar.png'); ?>" alt="Profile">
                <div class="profile-info">
                    <div class="profile-name"><?php echo htmlspecialchars($_SESSION['first_name']); ?></div>
                </div>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Header -->
        <div class="top-header">
            <div class="user-greeting">
                Welcome, <?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?>! 👋
            </div>
            <div class="top-actions">
                <a href="scan_qr.php" class="btn btn-scan">
                    <i class="bi bi-qr-code-scan"></i>
                    <span>Scan QR</span>
                </a>
                <a href="activity_logs.php" class="btn btn-logs">
                    <i class="bi bi-clock-history"></i>
                    <span>Activity Logs</span>
                </a>
            </div>
        </div>

        <!-- Welcome Section -->
        <section class="welcome-section">
            <div class="welcome-content">
                <div class="welcome-header">
                    <h1>Admin Dashboard</h1>
                    <p>Manage your clinic operations efficiently from this administrative dashboard. Monitor appointments, patient records, and communications all in one place.</p>
                </div>
            </div>
        </section>

        <!-- Analytics Widget -->
        <section class="analytics-section">
            <?php include 'analytics_widget.php'; ?>
        </section>

        <!-- Reviews Section -->
        <section id="reviews" class="reviews-section">
            <div class="reviews-content">
                <div class="section-title">
                    <h2>Patient Reviews</h2>
                    <p>What our patients say about us</p>
                </div>

                <!-- Reviews Display -->
                <div class="row g-4" id="reviewsContainer">
                    <?php
                    // Fetch all reviews from database
                    $reviews_sql = "SELECT r.rating, r.review_text, r.created_at, u.first_name 
                                   FROM reviews r 
                                   JOIN users u ON r.user_id = u.user_id 
                                   ORDER BY r.created_at DESC";
                    $reviews_result = $conn->query($reviews_sql);

                    if ($reviews_result && $reviews_result->num_rows > 0):
                        while ($review = $reviews_result->fetch_assoc()):
                            // Get first letter of first name
                            $avatar_letter = strtoupper(substr($review['first_name'], 0, 1));

                            // Generate random color for avatar
                            $colors = ['#80A1BA', '#91C4C3', '#B4DEBD'];
                            $avatar_color = $colors[array_rand($colors)];

                            // Format date
                            $review_date = date('F j, Y', strtotime($review['created_at']));
                    ?>
                            <div class="col-lg-4 col-md-6">
                                <div class="card border-0 shadow-sm h-100 review-card">
                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-start mb-3">
                                            <div class="avatar-circle text-white me-3" style="background-color: <?= $avatar_color ?>;">
                                                <?= $avatar_letter ?>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1 fw-bold">Anonymous Patient</h6>
                                                <div class="review-stars mb-1">
                                                    <?php
                                                    // Display stars based on rating
                                                    for ($i = 1; $i <= 5; $i++) {
                                                        if ($i <= $review['rating']) {
                                                            echo '<i class="bi bi-star-fill" style="color: #FFD700;"></i>';
                                                        } else {
                                                            echo '<i class="bi bi-star" style="color: #e0e0e0;"></i>';
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                                <small class="text-muted"><?= $review_date ?></small>
                                            </div>
                                        </div>
                                        <p class="text-muted mb-0 ms-0">"<?= htmlspecialchars($review['review_text']) ?>"</p>
                                    </div>
                                </div>
                            </div>
                        <?php
                        endwhile;
                    else:
                        ?>
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body text-center empty-reviews">
                                    <i class="bi bi-chat-quote display-1 text-muted mb-3 d-block"></i>
                                    <h5 class="text-muted mb-2">No reviews yet</h5>
                                    <p class="text-muted mb-0">Waiting for patient feedback</p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>

    <script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Active nav link 
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop();
            const navLinks = document.querySelectorAll('.sidebar .nav-link');

            navLinks.forEach(link => {
                const linkPage = link.getAttribute('href');

                // Check if current page matches link
                if (linkPage === currentPage ||
                    (currentPage === '' && linkPage === 'admin_dashboard.php') ||
                    (currentPage === 'admin_dashboard.php' && linkPage === 'admin_dashboard.php')) {
                    link.classList.add('active');
                }

                // Add click listener
                link.addEventListener('click', function() {
                    if (!this.classList.contains('dropdown-toggle')) {
                        navLinks.forEach(l => l.classList.remove('active'));
                        this.classList.add('active');
                    }
                });
            });
        });
    </script>
</body>

</html>