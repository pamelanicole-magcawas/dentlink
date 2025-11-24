<?php
session_start();
include 'db_connect.php'; // <-- this defines $conn

// Optionally: check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user's first name from database
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

// Set display name (fallback to 'User' if first_name not available)
$user_first_name = $_SESSION['first_name'] ?? 'User';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>DentLink - Clinic System</title>
    <style>
        /* Chat notification badge */
        .chat-badge {
            position: absolute;
            top: -5px;
            right: -10px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }
        }

        .chat-btn {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .chat-icon {
            font-size: 1.2rem;
        }
    </style>
</head>

<body>
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
                        <a class="nav-link px-3" href="#home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3" href="#services">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3" href="#reviews">Reviews</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link px-3" href="#contact">Contact Us</a>
                    </li>
                    <!-- Chat Button -->
                    <li class="nav-item ms-2">
                        <a class="nav-link chat-btn px-3" href="chat.php">
                            <i class="bi bi-chat-dots-fill chat-icon" style="color: #80A1BA;"></i>
                            <span class="d-none d-lg-inline">Chat</span>
                            <span class="chat-badge d-none" id="chatBadge">0</span>
                        </a>
                    </li>
                    <li class="nav-item dropdown ms-3">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                            <img src="upload/<?= htmlspecialchars($_SESSION['profile_pic']); ?>"
                                alt="Profile"
                                style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover; margin-right: 8px;">
                            <?php echo htmlspecialchars($_SESSION['first_name']); ?>
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

    <!-- Hero Section - UPDATED -->
    <section id="home" class="hero-section position-relative">
        <div class="hero-overlay"></div>
        <div class="container">
            <div class="row align-items-center min-vh-100 py-5">
                <div class="col-lg-8 mx-auto text-center position-relative" style="z-index: 2;">
                    <h1 class="display-3 fw-bold mb-4 hero-title text-dark">
                        Welcome, <?php echo htmlspecialchars($user_first_name); ?>!
                    </h1>
                    <h3 class="h2 fw-semibold mb-4 text-dark" style="opacity: 0.9;">
                        Your Journey to a Confident Smile Starts Here
                    </h3>
                    <p class="lead mb-5 hero-subtitle text-dark">
                        Experience exceptional dental care with DentLink - where advanced technology meets compassionate service for your perfect smile.
                    </p>
                    <a href="book_appointment.php" class="btn btn-custom btn-lg px-5 py-3 rounded-pill shadow-lg">
                        <i class="bi bi-calendar-check me-2"></i>Book Appointment
                    </a>
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

    <!-- Quick Actions -->
    <section class="py-5 bg-light" style="margin-top: -1px;">
        <div class="container">
            <div class="row g-4 justify-content-center">
                <div class="col-md-5">
                    <div class="card border-0 shadow-sm h-100 text-center hover-card">
                        <div class="card-body p-4">
                            <div class="icon-circle mx-auto mb-3" style="background-color: rgba(255, 247, 221, 0.5);">
                                <i class="bi bi-list-check display-4" style="color: #80A1BA;"></i>
                            </div>
                            <h5 class="card-title fw-bold">View Appointments</h5>
                            <p class="card-text text-muted">Check your upcoming and past schedules</p>
                            <a href="view_appointment.php" class="btn btn-outline-custom rounded-pill">View All</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="card border-0 shadow-sm h-100 text-center hover-card">
                        <div class="card-body p-4">
                            <div class="icon-circle mx-auto mb-3" style="background-color: rgba(180, 222, 189, 0.5);">
                                <i class="bi bi-folder2-open display-4" style="color: #91C4C3;"></i>
                            </div>
                            <h5 class="card-title fw-bold">View Records</h5>
                            <p class="card-text text-muted">Access your dental treatment and history records.</p>
                            <a href="view_records.php" class="btn btn-outline-custom rounded-pill">View Records</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <h2 class="display-5 fw-bold mb-4" style="color: #80A1BA;">Welcome to DentLink</h2>
                    <p class="lead text-muted mb-4">Your trusted partner in comprehensive dental care and oral health excellence.</p>
                    <p class="mb-4">At DentLink, we combine state-of-the-art dental technology with a patient-centered approach to deliver exceptional care. Our experienced team of dental professionals is dedicated to helping you achieve and maintain optimal oral health in a comfortable, welcoming environment.</p>
                    <p class="mb-4">We believe that everyone deserves a healthy, beautiful smile. Whether you need routine preventive care, cosmetic enhancements, or complex restorative treatments, we're here to guide you every step of the way with personalized treatment plans tailored to your unique needs.</p>
                    <div class="row g-3 mt-4">
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-check-circle-fill fs-4 me-3" style="color: #B4DEBD;"></i>
                                <span>Experienced Dentists</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-check-circle-fill fs-4 me-3" style="color: #B4DEBD;"></i>
                                <span>Modern Equipment</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-check-circle-fill fs-4 me-3" style="color: #B4DEBD;"></i>
                                <span>Comfortable Environment</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-check-circle-fill fs-4 me-3" style="color: #B4DEBD;"></i>
                                <span>Affordable Rates</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="position-relative text-center">
                        <img src="https://images.unsplash.com/photo-1606811856475-5e6fcdc6e509?ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&q=80&w=736"
                            alt="Dental Clinic"
                            class="img-fluid rounded-4 shadow-lg w-75 mx-auto">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold mb-3" style="color: #80A1BA;">Our Services</h2>
                <p class="lead text-muted">Comprehensive dental care tailored to your needs</p>
            </div>

            <div class="row g-4">
                <!-- General Dental Services -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <h5 class="card-title mb-4" style="color: #80A1BA;">
                                <i class="bi bi-clipboard-check me-2" style="color: #91C4C3;"></i>General Dental Services
                            </h5>
                            <ul class="list-unstyled service-list">
                                <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color: #B4DEBD;"></i>Consultation – ₱700</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color: #B4DEBD;"></i>Fluoride Treatment – ₱600</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color: #B4DEBD;"></i>Oral Prophylaxis (Cleaning) – ₱1,200</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color: #B4DEBD;"></i>Panoramic X-ray – ₱1,000</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color: #B4DEBD;"></i>Periapical X-ray – ₱500</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color: #B4DEBD;"></i>Tooth Extraction – ₱1,200 (Additional Tooth – ₱700)</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color: #B4DEBD;"></i>Tooth Restoration (Filling) – ₱1,200</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Endodontics & Surgery -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <h5 class="card-title mb-4" style="color: #80A1BA;">
                                <i class="bi bi-capsule me-2" style="color: #91C4C3;"></i>Endodontics & Surgery
                            </h5>
                            <ul class="list-unstyled service-list">
                                <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color: #B4DEBD;"></i>Gingivectomy – ₱3,000 / area</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color: #B4DEBD;"></i>Odontectomy (Surgical Extraction) – ₱10,000</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color: #B4DEBD;"></i>Post and Core – ₱4,000</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color: #B4DEBD;"></i>Root Canal Therapy – ₱8,000 per canal</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color: #B4DEBD;"></i>Wisdom Tooth Extraction – ₱4,500</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Orthodontics -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <h5 class="card-title mb-4" style="color: #80A1BA;">
                                <i class="bi bi-scissors me-2" style="color: #91C4C3;"></i>Orthodontics
                            </h5>
                            <ul class="list-unstyled service-list">
                                <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color: #B4DEBD;"></i>Ceramic Braces – ₱70,000 - ₱90,000</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color: #B4DEBD;"></i>Metal Braces – ₱45,000 - ₱60,000</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color: #B4DEBD;"></i>Orthodontic Treatment (Upper / Lower) – ₱50,000 (Minimum Down Payment: ₱15,000)</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color: #B4DEBD;"></i>Retainers / Arch (Hawley's) – ₱6,000 per arch</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color: #B4DEBD;"></i>Self-Ligating Braces – ₱80,000+</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Prosthodontics & Aesthetic Dentistry -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <h5 class="card-title mb-4" style="color: #80A1BA;">
                                <i class="bi bi-person-badge me-2" style="color: #91C4C3;"></i>Prosthodontics & Aesthetic Dentistry
                            </h5>
                            <ul class="list-unstyled service-list">
                                <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color: #B4DEBD;"></i>All-Porcelain / Emax Crown – ₱20,000 per unit</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color: #B4DEBD;"></i>Complete Denture Upper / Lower Set – ₱16,000</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color: #B4DEBD;"></i>Flexible Dentures – ₱20,000 per arch</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color: #B4DEBD;"></i>One-Piece Metal Casting – ₱18,000 – ₱25,000 per arch</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color: #B4DEBD;"></i>Partial Denture 1 Pontic – ₱4,500</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color: #B4DEBD;"></i>Partial Denture Anterior / Posterior – ₱6,500</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color: #B4DEBD;"></i>Porcelain Fused to Metal (PFM) Crown – ₱8,000 per unit</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color: #B4DEBD;"></i>Plastic Crown – ₱5,000 per unit</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color: #B4DEBD;"></i>Removable Partial Dentures – ₱10,000</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color: #B4DEBD;"></i>Veneer (Porcelain) – ₱15,000 per unit</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color: #B4DEBD;"></i>Zirconia Crown – ₱25,000 per unit</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Other Dental Services -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <h5 class="card-title mb-4" style="color: #80A1BA;">
                                <i class="bi bi-stars me-2" style="color: #91C4C3;"></i>Other Dental Services
                            </h5>
                            <ul class="list-unstyled service-list">
                                <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color: #B4DEBD;"></i>Bracket Recement – ₱500</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color: #B4DEBD;"></i>Laser Teeth Bleaching – ₱25,000</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color: #B4DEBD;"></i>Re-cementation of Crown – ₱1,200</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color: #B4DEBD;"></i>Teeth Bleaching – ₱15,000</li>
                                <li class="mb-2"><i class="bi bi-check-circle-fill me-2" style="color: #B4DEBD;"></i>Temporary Crown – ₱2,500</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Reviews Section -->
    <section id="reviews" class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold mb-3" style="color: #80A1BA;">Patient Reviews</h2>
                <p class="lead text-muted">What our patients say about us</p>
            </div>

            <!-- Add Review Form -->
            <div class="row justify-content-center mb-5">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <h5 class="card-title mb-4">Share Your Experience</h5>

                            <?php
                            // Check if user has already submitted a review today
                            $user_id = $_SESSION['user_id'];

                            $check_sql = "SELECT COUNT(*) as count FROM reviews 
                                     WHERE user_id = ? AND DATE(created_at) = CURDATE()";
                            $check_stmt = $conn->prepare($check_sql);
                            $check_stmt->bind_param("i", $user_id);
                            $check_stmt->execute();
                            $check_result = $check_stmt->get_result();
                            $check_data = $check_result->fetch_assoc();
                            $can_review = $check_data['count'] == 0;
                            $check_stmt->close();

                            if ($can_review): ?>
                                <form id="reviewForm" action="submit_review.php" method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Rating <span class="text-danger">*</span></label>
                                        <div class="star-rating d-flex">
                                            <i class="bi bi-star" data-rating="1" style="color: #FFD700;"></i>
                                            <i class="bi bi-star" data-rating="2" style="color: #FFD700;"></i>
                                            <i class="bi bi-star" data-rating="3" style="color: #FFD700;"></i>
                                            <i class="bi bi-star" data-rating="4" style="color: #FFD700;"></i>
                                            <i class="bi bi-star" data-rating="5" style="color: #FFD700;"></i>
                                        </div>
                                        <input type="hidden" name="rating" id="rating" value="0" required>
                                        <small class="text-muted">Click on the stars to rate</small>
                                    </div>
                                    <div class="mb-3">
                                        <label for="review" class="form-label">Your Review <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="review" name="review" rows="4"
                                            minlength="5" maxlength="200" required
                                            placeholder="Share your experience (5-200 characters)"></textarea>
                                        <div class="d-flex justify-content-between mt-1">
                                            <small class="text-muted">Minimum 5 characters</small>
                                            <small class="text-muted"><span id="charCount">0</span>/200</small>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-custom">
                                        <i class="bi bi-send me-2"></i>Submit Review
                                    </button>
                                </form>
                            <?php else: ?>
                                <div class="alert alert-info mb-0">
                                    <i class="bi bi-info-circle me-2"></i>
                                    You've already submitted a review today. You can submit another review tomorrow.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
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
                                <p class="text-muted mb-0">Be the first to share your experience!</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <script>
        // Character counter for review textarea
        const reviewTextarea = document.getElementById('review');
        const charCount = document.getElementById('charCount');

        if (reviewTextarea && charCount) {
            reviewTextarea.addEventListener('input', function() {
                const count = this.value.length;
                charCount.textContent = count;

                // Change color based on character count
                if (count < 5) {
                    charCount.style.color = '#dc3545'; // Red
                } else if (count >= 200) {
                    charCount.style.color = '#ffc107'; // Warning
                } else {
                    charCount.style.color = '#28a745'; // Green
                }
            });
        }

        (() => {
            // Star Rating System (Isolated Scope)
            const stars = document.querySelectorAll('.star-rating i');
            const ratingInput = document.getElementById('rating');

            if (stars.length > 0 && ratingInput) {
                stars.forEach((star, idx) => {

                    // Click event
                    star.addEventListener('click', () => {
                        const selectedRating = idx + 1;
                        ratingInput.value = selectedRating;

                        stars.forEach((s, i) => {
                            if (i < selectedRating) {
                                s.classList.add('bi-star-fill');
                                s.classList.remove('bi-star');
                            } else {
                                s.classList.add('bi-star');
                                s.classList.remove('bi-star-fill');
                            }
                        });
                    });

                    // Hover event
                    star.addEventListener('mouseenter', () => {
                        const hoverRating = idx + 1;
                        stars.forEach((s, i) => {
                            if (i < hoverRating) {
                                s.style.transform = 'scale(1.2)';
                            }
                        });
                    });

                    // Remove hover
                    star.addEventListener('mouseleave', () => {
                        stars.forEach(s => s.style.transform = 'scale(1)');
                    });
                });
            }
        })();


        // Handle review form submission
        const reviewForm = document.getElementById('reviewForm');
        if (reviewForm) {
            reviewForm.addEventListener('submit', function(e) {
                e.preventDefault();

                // Validate rating
                const rating = document.getElementById('rating').value;
                if (rating === '0') {
                    Swal.fire({
                        title: 'Rating Required',
                        text: 'Please select a star rating before submitting.',
                        icon: 'warning',
                        confirmButtonColor: '#80A1BA'
                    });
                    return;
                }

                // Validate review length
                const reviewText = document.getElementById('review').value;
                if (reviewText.length < 5) {
                    Swal.fire({
                        title: 'Review Too Short',
                        text: 'Please write at least 5 characters.',
                        icon: 'warning',
                        confirmButtonColor: '#80A1BA'
                    });
                    return;
                }

                const formData = new FormData(this);

                fetch('submit_review.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Success!',
                                text: data.message,
                                icon: 'success',
                                confirmButtonColor: '#B4DEBD'
                            }).then(() => {
                                // Reload page to show new review
                                window.location.href = '#reviews';
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: data.message,
                                icon: 'error',
                                confirmButtonColor: '#80A1BA'
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            title: 'Error!',
                            text: 'An error occurred. Please try again.',
                            icon: 'error',
                            confirmButtonColor: '#80A1BA'
                        });
                        console.error('Error:', error);
                    });
            });
        }
    </script>

    <!-- Contact Section -->
    <section id="contact" class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold mb-3" style="color: #80A1BA;">Contact Us</h2>
                <p class="lead text-muted">Get in touch with us today</p>
            </div>

            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm h-100 text-center">
                        <div class="card-body p-4">
                            <div class="icon-circle mx-auto mb-3" style="background-color: rgba(128, 161, 186, 0.15);">
                                <i class="bi bi-geo-alt-fill fs-1" style="color: #80A1BA;"></i>
                            </div>
                            <h5 class="fw-bold mb-3">Visit Us</h5>
                            <p class="text-muted">2nd Floor, CL Building, E Mayo St,<br>Brgy. 4, Lipa City,<br>4217 Batangas</p>
                            <a href="https://maps.google.com/?q=2nd+Floor+CL+Building+E+Mayo+St+Brgy+4+Lipa+City+Batangas" target="_blank" class="btn btn-outline-custom btn-sm rounded-pill">
                                <i class="bi bi-map me-2"></i>Tap to Navigate
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm h-100 text-center">
                        <div class="card-body p-4">
                            <div class="icon-circle mx-auto mb-3" style="background-color: rgba(145, 196, 195, 0.15);">
                                <i class="bi bi-telephone-fill fs-1" style="color: #91C4C3;"></i>
                            </div>
                            <h5 class="fw-bold mb-3">Call Us</h5>
                            <p class="text-muted mb-3">We're here to help you</p>
                            <a href="tel:+639123456789" class="btn btn-outline-custom btn-sm rounded-pill">
                                <i class="bi bi-telephone me-2"></i>Call Now
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm h-100 text-center">
                        <div class="card-body p-4">
                            <div class="icon-circle mx-auto mb-3" style="background-color: rgba(180, 222, 189, 0.15);">
                                <i class="bi bi-share-fill fs-1" style="color: #B4DEBD;"></i>
                            </div>
                            <h5 class="fw-bold mb-3">Follow Us</h5>
                            <p class="text-muted mb-3">Stay connected with us</p>
                            <div class="d-flex justify-content-center gap-2">
                                <a href="#" class="btn btn-sm rounded-circle social-btn" style="width: 40px; height: 40px; padding: 8px; background-color: #80A1BA; color: white;">
                                    <i class="bi bi-facebook"></i>
                                </a>
                                <a href="#" class="btn btn-sm rounded-circle social-btn" style="width: 40px; height: 40px; padding: 8px; background-color: #91C4C3; color: white;">
                                    <i class="bi bi-instagram"></i>
                                </a>
                                <a href="#" class="btn btn-sm rounded-circle social-btn" style="width: 40px; height: 40px; padding: 8px; background-color: #B4DEBD; color: white;">
                                    <i class="bi bi-envelope"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Smooth scrolling
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

        // Star rating functionality
        const stars = document.querySelectorAll('.star-rating i');
        const ratingInput = document.getElementById('rating');

        stars.forEach(star => {
            star.addEventListener('click', function() {
                const rating = this.getAttribute('data-rating');
                ratingInput.value = rating;

                stars.forEach((s, index) => {
                    if (index < rating) {
                        s.classList.remove('bi-star');
                        s.classList.add('bi-star-fill');
                    } else {
                        s.classList.remove('bi-star-fill');
                        s.classList.add('bi-star');
                    }
                });
            });

            // Hover effect
            star.addEventListener('mouseenter', function() {
                const rating = this.getAttribute('data-rating');
                stars.forEach((s, index) => {
                    if (index < rating) {
                        s.style.transform = 'scale(1.2)';
                    }
                });
            });

            star.addEventListener('mouseleave', function() {
                stars.forEach(s => {
                    s.style.transform = 'scale(1)';
                });
            });
        });

        // Handle review form submission
        document.getElementById('reviewForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('submit_review.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: data.message,
                            icon: 'success',
                            confirmButtonColor: '#B4DEBD'
                        }).then(() => {
                            // Reload page to show new review
                            window.location.href = '#reviews';
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: data.message,
                            icon: 'error',
                            confirmButtonColor: '#80A1BA'
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        title: 'Error!',
                        text: 'An error occurred. Please try again.',
                        icon: 'error',
                        confirmButtonColor: '#80A1BA'
                    });
                    console.error('Error:', error);
                });
        });

        // Active nav link on scroll
        window.addEventListener('scroll', () => {
            let current = '';
            const sections = document.querySelectorAll('section');

            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
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
