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

// Include DB connection if available
require_once 'db_connect.php'; // ensure $conn is available

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
    <link rel="stylesheet" href="admin-dashboard.css">
</head>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg bg-white sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="#home" style="color: #80A1BA;">
            <img src="dentlink-logo.png" alt="Logo" width="50" height="45" class="me-2">
            <span style="font-size: 1.5rem;">DentLink</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item">
                    <a class="nav-link px-3" href="admin_dashboard.php">
                        <i class="bi me-1"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3" href="scan_qr.php">
                        <i class="bi me-1"></i> Scan QR
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3" href="#reviews">
                        <i class="bi me-1"></i> Reviews
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3" href="activity_logs.php">
                        <i class="bi me-1"></i> Activity Logs
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3 text-danger" href="logout.php">
                        <i class="bi bi-box-arrow-right me-1"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- HERO SECTION -->
<section id="home" class="hero-section position-relative">
    <div class="hero-overlay"></div>
    <div class="container">
        <div class="row align-items-center py-5" style="min-height: 70vh;">
            <div class="col-lg-10 mx-auto text-center position-relative" style="z-index: 2;">
                <h1 class="display-3 fw-bold mb-4 hero-title text-dark">
                    Welcome, <?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?>!
                </h1>
                <p class="lead mb-5 hero-subtitle text-dark">
                    Manage your clinic operations efficiently from this administrative dashboard.
                    Monitor appointments, patient records, and communications all in one place.
                </p>
            </div>
        </div>
    </div>
    <!-- Smile Curve Effect -->
    <div class="smile-curve">
        <svg viewBox="0 0 1440 120" width="100%" height="120" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0 0C240 80 480 120 720 120C960 120 1200 80 1440 0V120H0V0Z" fill="#f8f9fa" />
        </svg>
    </div>
</section>

<?php include 'analytics_widget.php'; ?>

<!-- Reviews Section - ADMIN DASHBOARD (View Only) -->
<section id="reviews" class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold mb-3" style="color: #80A1BA;">Patient Reviews</h2>
            <p class="lead text-muted">What our patients say about us</p>
        </div>

        <!-- Reviews Display Only -->
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

<!-- Footer -->
<footer class="text-white" style="background: linear-gradient(135deg, #80A1BA 0%, #91C4C3 100%); padding: 20px 0;">
    <div class="container text-center">
        <p style="margin: 0;">&copy; 2025 DentLink: Dental Clinic Digital Appointment and Patient Records Management System</p>
        <p style="margin: 0;">All rights reserved.</p>
    </div>
</footer>

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
        const navLinks = document.querySelectorAll('.navbar .nav-link');

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
                navLinks.forEach(l => l.classList.remove('active'));
                this.classList.add('active');
            });
        });
    });
</script>
</body>

</html>
