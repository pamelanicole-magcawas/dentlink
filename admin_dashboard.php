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
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
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
                    <a class="nav-link px-3" href="admin_chats.php">
                        <i class="bi me-1"></i> Chat
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

    <!-- QUICK ACTIONS SECTION -->
<section class="py-5 bg-light" style="margin-top: -1px;">
    <div class="container">
        <div class="row g-4 justify-content-center">

            <div class="col-md-6 col-lg-4">
                <div class="card hover-card text-center shadow-sm border-0 h-100">
                    <div class="card-body py-4">
                        <div class="icon-circle bg-primary-custom mb-3 mx-auto">
                            <i class="bi bi-hourglass-split text-white display-6"></i>
                        </div>
                        <h5 class="card-title fw-bold">Pending Appointments</h5>
                        <p class="card-text text-muted">View and manage all pending appointment requests.</p>
                        <a href="pending_appointments.php" class="btn btn-outline-custom mt-2 rounded-pill">View Pending</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="card hover-card text-center shadow-sm border-0 h-100">
                    <div class="card-body py-4">
                        <div class="icon-circle bg-secondary-custom mb-3 mx-auto">
                            <i class="bi bi-calendar-check text-white display-6"></i>
                        </div>
                        <h5 class="card-title fw-bold">Approved Appointments</h5>
                        <p class="card-text text-muted">See schedules that were already approved.</p>
                        <a href="approved_appointments.php" class="btn btn-outline-custom mt-2 rounded-pill">View Approved</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="card hover-card text-center shadow-sm border-0 h-100">
                    <div class="card-body py-4">
                        <div class="icon-circle bg-secondary-custom mb-3 mx-auto">
                            <i class="bi bi-qr-code-scan text-white display-6"></i>
                        </div>
                        <h5 class="card-title fw-bold">Scan QR</h5>
                        <p class="card-text text-muted">Scan patients' QR codes for appointment verification.</p>
                        <a href="scan_qr.php" class="btn btn-outline-custom mt-2 rounded-pill">Scan Now</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="card hover-card text-center shadow-sm border-0 h-100">
                    <div class="card-body py-4">
                        <div class="icon-circle bg-accent-custom mb-3 mx-auto">
                            <i class="bi bi-folder2-open text-white display-6"></i>
                        </div>
                        <h5 class="card-title fw-bold">Patient Records</h5>
                        <p class="card-text text-muted">Access and maintain patient treatment history and files.</p>
                        <a href="patient_records.php" class="btn btn-outline-custom mt-2 rounded-pill">View Records</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="card hover-card text-center shadow-sm border-0 h-100">
                    <div class="card-body py-4">
                        <div class="icon-circle bg-primary-custom mb-3 mx-auto">
                            <i class="bi bi-chat-square-text text-white display-6"></i>
                        </div>
                        <h5 class="card-title fw-bold">Chat System</h5>
                        <p class="card-text text-muted">Open the admin chat console to communicate with patients.</p>
                        <a href="admin_chats.php" class="btn btn-outline-custom mt-2 rounded-pill">Admin Chats</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

    <!-- REVIEWS SECTION -->
    <section id="reviews" class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold mb-3 text-primary-custom">Latest Patient Reviews</h2>
                <p class="lead text-muted">See what patients are saying about your clinic</p>
            </div>

            <!-- Reviews Display -->
            <div class="row g-4" id="reviewsContainer">
                <?php
                // Fetch all reviews from database
                $reviews_sql = "SELECT rating, review_text, created_at FROM reviews ORDER BY created_at DESC LIMIT 6";
                $reviews_result = $conn->query($reviews_sql);

                if ($reviews_result && $reviews_result->num_rows > 0):
                    while ($review = $reviews_result->fetch_assoc()):
                        // Generate random avatar letter
                        $letters = range('A', 'Z');
                        $avatar_letter = $letters[array_rand($letters)];

                        // Generate random color for avatar
                        $colors = ['#80A1BA', '#91C4C3', '#B4DEBD'];
                        $avatar_color = $colors[array_rand($colors)];

                        // Format date
                        $review_date = date('F j, Y', strtotime($review['created_at'])); ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="review-card">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar-circle text-white me-3" style="background-color: <?= $avatar_color ?>;">
                                        <?= $avatar_letter ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-bold">Anonymous Patient</h6>
                                        <div class="mb-1">
                                            <?php
                                            // Display stars based on rating
                                            for ($i = 1; $i <= 5; $i++) {
                                                if ($i <= $review['rating']) {
                                                    echo '<i class="bi bi-star-fill text-accent-custom"></i>';
                                                } else {
                                                    echo '<i class="bi bi-star text-secondary-custom"></i>';
                                                }
                                            }
                                            ?>
                                        </div>
                                        <small class="text-muted"><?= $review_date ?></small>
                                    </div>
                                </div>
                                <p class="text-muted mb-0">"<?= htmlspecialchars($review['review_text']) ?>"</p>
                            </div>
                        </div>
                <?php
                    endwhile;
                else:
                ?>
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-5 text-center">
                                <i class="bi bi-chat-quote display-1 text-muted mb-3"></i>
                                <h5 class="text-muted">No reviews yet</h5>
                                <p class="text-muted">Patient reviews will appear here once submitted.</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

        <!-- Footer -->
        <footer class="text-white py-2" style="background: linear-gradient(135deg, #80A1BA 0%, #91C4C3 100%);">
        <div class="container text-center">
                <p style="margin-bottom: 0;">&copy; 2025 DentLink: Dental Clinic Digital Appointment and Patient Records Management System</p>
                <p style="margin-top: 0;">All rights reserved.</p>
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

        // Active nav link on scroll
        window.addEventListener('scroll', () => {
            let current = '';
            const sections = document.querySelectorAll('section');

            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                if (pageYOffset >= sectionTop - 200) {
                    current = section.getAttribute('id');
                }
            });

            document.querySelectorAll('.navbar-nav .nav-link').forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === `#${current}`) {
                    link.classList.add('active');
                }
            });
        });
    </script>

</body>
</html>