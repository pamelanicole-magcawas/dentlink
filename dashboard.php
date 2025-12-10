<?php
session_start();
include 'db_connect.php';

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

$user_first_name = $_SESSION['first_name'] ?? 'User';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>DentLink - Dashboard</title>
    <style>
        :root {
            --primary-color: #80A1BA;
            --secondary-color: #91C4C3;
            --accent-color: #B4DEBD;
            --light-accent: #FFF7DD;
            --sidebar-width: 280px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            overflow-x: hidden;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            padding: 20px 0;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }

        .sidebar .logo {
            text-align: center;
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 30px;
        }

        .sidebar .logo img {
            width: 70px;
            height: 70px;
            margin-bottom: 15px;
            background: white;
            padding: 10px;
            border-radius: 50%;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .sidebar .logo h4 {
            color: white;
            font-weight: bold;
            margin: 0;
            font-size: 1.6rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .sidebar-nav {
            padding: 0 15px;
            padding-bottom: 80px;
        }

        .sidebar .nav-link {
            color: white;
            padding: 12px 20px;
            margin-bottom: 5px;
            border-radius: 10px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            font-weight: 500;
        }

        .sidebar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.15);
            transform: translateX(5px);
        }

        .sidebar .nav-link.active {
            background-color: white;
            color: var(--primary-color);
            font-weight: 600;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar .nav-link i {
            font-size: 1.1rem;
            width: 20px;
        }

        /* Services Dropdown */
        .services-dropdown {
            position: relative;
        }

        .services-dropdown .dropdown-menu {
            background-color: rgba(255, 255, 255, 0.98);
            border: none;
            border-radius: 10px;
            margin-top: 5px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            padding: 10px;
            width: 100%;
        }

        .services-dropdown .dropdown-item {
            color: var(--primary-color);
            padding: 10px 15px;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .services-dropdown .dropdown-item:hover {
            background-color: var(--accent-color);
            color: white;
            transform: translateX(5px);
        }

        /* Profile Section */
        .profile-section {
            position: fixed;
            bottom: 0;
            left: 0;
            width: var(--sidebar-width);
            padding: 15px;
            background: linear-gradient(180deg, transparent 0%, rgba(0, 0, 0, 0.1) 100%);
        }

        .profile-btn {
            width: 100%;
            background-color: rgba(255, 255, 255, 0.15);
            color: white;
            padding: 12px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .profile-btn:hover {
            background-color: rgba(255, 255, 255, 0.25);
            transform: translateY(-2px);
        }

        .profile-btn img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
        }

        .profile-btn .profile-info {
            flex-grow: 1;
            text-align: left;
        }

        .profile-btn .profile-name {
            font-weight: 600;
            font-size: 0.95rem;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

        /* Top Header */
        .top-header {
            background: white;
            padding: 20px 40px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .user-greeting {
            font-size: 1.8rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .top-actions {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .top-actions .btn {
            border-radius: 50px;
            padding: 10px 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-feedback {
            background-color: var(--secondary-color);
            color: white;
        }

        .btn-feedback:hover {
            background-color: #7AB3B2;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(145, 196, 195, 0.4);
        }

        .btn-book {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-book:hover {
            background-color: #6A8CA3;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(128, 161, 186, 0.4);
        }

        /* Welcome Section */
        .welcome-section {
            padding: 60px 40px;
        }

        .welcome-content {
            max-width: 1200px;
            margin: 0 auto;
        }

        .welcome-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .welcome-header h1 {
            font-size: 3.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 20px;
        }

        .welcome-header p {
            font-size: 1.2rem;
            color: #6c757d;
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.8;
        }

        .welcome-image-container {
            text-align: center;
            margin-bottom: 50px;
        }

        .welcome-image {
            max-width: 700px;
            width: 100%;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease;
        }

        .welcome-image:hover {
            transform: scale(1.02);
        }

        /* Services Section */
        .services-section {
            background: linear-gradient(135deg, rgba(128, 161, 186, 0.05), rgba(180, 222, 189, 0.05));
            padding: 60px 40px;
        }

        .services-content {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-title {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-title h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .section-title p {
            font-size: 1.1rem;
            color: #6c757d;
        }

        .service-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            height: 100%;
        }

        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
        }

        .service-card h5 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 20px;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .service-card h5 i {
            font-size: 1.5rem;
            color: var(--secondary-color);
        }

        .service-list {
            list-style: none;
            padding: 0;
        }

        .service-list li {
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #495057;
        }

        .service-list li:last-child {
            border-bottom: none;
        }

        .service-list li i {
            color: var(--accent-color);
            font-size: 1rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .top-header {
                padding: 15px 20px;
                flex-direction: column;
                gap: 15px;
            }

            .user-greeting {
                font-size: 1.3rem;
            }

            .top-actions {
                width: 100%;
                justify-content: center;
            }

            .welcome-header h1 {
                font-size: 2.2rem;
            }

            .welcome-section,
            .services-section {
                padding: 40px 20px;
            }
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <img src="dentlink-logo.png" alt="DentLink Logo" style="background-color: #f0f0f0;">
            <h4>DentLink</h4>
        </div>

        <nav class="nav flex-column sidebar-nav">
            <a class="nav-link active" href="dashboard.php">
                <i class="bi bi-house-door-fill"></i>
                <span>Home</span>
            </a>
            <a class="nav-link" href="view_appointment.php">
                <i class="bi bi-calendar-check"></i>
                <span>View Appointments</span>
            </a>
            <a class="nav-link" href="view_records.php">
                <i class="bi bi-folder2-open"></i>
                <span>View Records</span>
            </a>
            <a class="nav-link" href="#services">
                <i class="bi bi-grid-3x3-gap"></i>
                <span>Services</span>
            </a>
            <a class="nav-link" href="chat.php">
                <i class="bi bi-chat-dots-fill"></i>
                <span>Chat Support</span>
            </a>
        </nav>

        <!-- Profile Section -->
        <div class="profile-section">
            <button class="profile-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="upload/<?= htmlspecialchars($_SESSION['profile_pic']); ?>" alt="Profile">
                <div class="profile-info">
                    <div class="profile-name"><?php echo htmlspecialchars($_SESSION['first_name']); ?></div>
                </div>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Header -->
        <div class="top-header">
            <div class="user-greeting">
                Hi, <?php echo htmlspecialchars($user_first_name); ?>! 👋
            </div>
            <div class="top-actions">
                <button class="btn btn-feedback" data-bs-toggle="modal" data-bs-target="#feedbackModal">
                    <i class="bi bi-star-fill"></i>
                    <span>Feedback</span>
                </button>
                <a href="book_appointment.php" class="btn btn-book">
                    <i class="bi bi-calendar-plus"></i>
                    <span>Book Now</span>
                </a>
            </div>
        </div>

        <!-- Welcome Section -->
        <section class="welcome-section">
            <div class="welcome-content">
                <div class="welcome-header">
                    <h1>Welcome to DentLink</h1>
                    <p>Your Journey to a Confident Smile Starts Here. Experience exceptional dental care with advanced technology and compassionate service.</p>
                </div>
                <div class="welcome-image-container">
                    <img src="https://images.unsplash.com/photo-1606811841689-23dfddce3e95?q=80&w=1200" alt="Dental Clinic" class="welcome-image">
                </div>
            </div>
        </section>

        <!-- Services Section -->
        <section id="services" class="services-section">
            <div class="services-content">
                <div class="section-title">
                    <h2>Our Services</h2>
                    <p>Comprehensive dental care tailored to your needs</p>
                </div>

                <div class="row g-4">
                    <!-- General Dental Services -->
                    <div class="col-lg-6" id="general-services">
                        <div class="service-card">
                            <h5>
                                <i class="bi bi-clipboard-check"></i>
                                General Dental Services
                            </h5>
                            <ul class="service-list">
                                <li><i class="bi bi-check-circle-fill"></i>Consultation – ₱700</li>
                                <li><i class="bi bi-check-circle-fill"></i>Fluoride Treatment – ₱600</li>
                                <li><i class="bi bi-check-circle-fill"></i>Oral Prophylaxis (Cleaning) – ₱1,200</li>
                                <li><i class="bi bi-check-circle-fill"></i>Panoramic X-ray – ₱1,000</li>
                                <li><i class="bi bi-check-circle-fill"></i>Periapical X-ray – ₱500</li>
                                <li><i class="bi bi-check-circle-fill"></i>Tooth Extraction – ₱1,200 (Additional ₱700)</li>
                                <li><i class="bi bi-check-circle-fill"></i>Tooth Restoration (Filling) – ₱1,200</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Endodontics & Surgery -->
                    <div class="col-lg-6" id="endodontics">
                        <div class="service-card">
                            <h5>
                                <i class="bi bi-capsule"></i>
                                Endodontics & Surgery
                            </h5>
                            <ul class="service-list">
                                <li><i class="bi bi-check-circle-fill"></i>Gingivectomy – ₱3,000 / area</li>
                                <li><i class="bi bi-check-circle-fill"></i>Odontectomy (Surgical Extraction) – ₱10,000</li>
                                <li><i class="bi bi-check-circle-fill"></i>Post and Core – ₱4,000</li>
                                <li><i class="bi bi-check-circle-fill"></i>Root Canal Therapy – ₱8,000 per canal</li>
                                <li><i class="bi bi-check-circle-fill"></i>Wisdom Tooth Extraction – ₱4,500</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Orthodontics -->
                    <div class="col-lg-6" id="orthodontics">
                        <div class="service-card">
                            <h5>
                                <i class="bi bi-scissors"></i>
                                Orthodontics
                            </h5>
                            <ul class="service-list">
                                <li><i class="bi bi-check-circle-fill"></i>Ceramic Braces – ₱70,000 - ₱90,000</li>
                                <li><i class="bi bi-check-circle-fill"></i>Metal Braces – ₱45,000 - ₱60,000</li>
                                <li><i class="bi bi-check-circle-fill"></i>Orthodontic Treatment – ₱50,000 (Min. DP: ₱15,000)</li>
                                <li><i class="bi bi-check-circle-fill"></i>Retainers (Hawley's) – ₱6,000 per arch</li>
                                <li><i class="bi bi-check-circle-fill"></i>Self-Ligating Braces – ₱80,000+</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Prosthodontics & Aesthetic -->
                    <div class="col-lg-6" id="prosthodontics">
                        <div class="service-card">
                            <h5>
                                <i class="bi bi-person-badge"></i>
                                Prosthodontics & Aesthetic
                            </h5>
                            <ul class="service-list">
                                <li><i class="bi bi-check-circle-fill"></i>All-Porcelain / Emax Crown – ₱20,000</li>
                                <li><i class="bi bi-check-circle-fill"></i>Complete Denture Set – ₱16,000</li>
                                <li><i class="bi bi-check-circle-fill"></i>Flexible Dentures – ₱20,000 per arch</li>
                                <li><i class="bi bi-check-circle-fill"></i>One-Piece Metal Casting – ₱18,000 - ₱25,000</li>
                                <li><i class="bi bi-check-circle-fill"></i>Partial Denture 1 Pontic – ₱4,500</li>
                                <li><i class="bi bi-check-circle-fill"></i>PFM Crown – ₱8,000 per unit</li>
                                <li><i class="bi bi-check-circle-fill"></i>Veneer (Porcelain) – ₱15,000 per unit</li>
                                <li><i class="bi bi-check-circle-fill"></i>Zirconia Crown – ₱25,000 per unit</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Other Services -->
                    <div class="col-lg-12" id="other-services">
                        <div class="service-card">
                            <h5>
                                <i class="bi bi-stars"></i>
                                Other Dental Services
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="service-list">
                                        <li><i class="bi bi-check-circle-fill"></i>Bracket Recement – ₱500</li>
                                        <li><i class="bi bi-check-circle-fill"></i>Laser Teeth Bleaching – ₱25,000</li>
                                        <li><i class="bi bi-check-circle-fill"></i>Re-cementation of Crown – ₱1,200</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="service-list">
                                        <li><i class="bi bi-check-circle-fill"></i>Teeth Bleaching – ₱15,000</li>
                                        <li><i class="bi bi-check-circle-fill"></i>Temporary Crown – ₱2,500</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Feedback Modal -->
    <div class="modal fade" id="feedbackModal" tabindex="-1" aria-labelledby="feedbackModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="feedbackModalLabel">
                        <i class="bi bi-star-fill me-2" style="color: #FFD700;"></i>Share Your Feedback
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="feedbackForm">
                        <div class="mb-3">
                            <label class="form-label">Rating <span class="text-danger">*</span></label>
                            <div class="star-rating d-flex justify-content-center mb-2" style="font-size: 2rem; gap: 5px;">
                                <i class="bi bi-star" data-rating="1" style="color: #FFD700; cursor: pointer;"></i>
                                <i class="bi bi-star" data-rating="2" style="color: #FFD700; cursor: pointer;"></i>
                                <i class="bi bi-star" data-rating="3" style="color: #FFD700; cursor: pointer;"></i>
                                <i class="bi bi-star" data-rating="4" style="color: #FFD700; cursor: pointer;"></i>
                                <i class="bi bi-star" data-rating="5" style="color: #FFD700; cursor: pointer;"></i>
                            </div>
                            <input type="hidden" name="rating" id="rating" value="0" required>
                        </div>
                        <div class="mb-3">
                            <label for="feedback" class="form-label">Your Feedback <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="feedback" name="feedback" rows="4" minlength="5" maxlength="200" required placeholder="Share your experience (5-200 characters)"></textarea>
                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-muted"><span id="charCount">0</span>/200</small>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="submitFeedback" style="background-color: var(--primary-color); border: none;">Submit Feedback</button>
                </div>
            </div>
        </div>
    </div>

    <script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
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

        // Character counter
        const feedbackTextarea = document.getElementById('feedback');
        const charCount = document.getElementById('charCount');

        if (feedbackTextarea && charCount) {
            feedbackTextarea.addEventListener('input', function() {
                const count = this.value.length;
                charCount.textContent = count;

                if (count < 5) {
                    charCount.style.color = '#dc3545';
                } else if (count >= 200) {
                    charCount.style.color = '#ffc107';
                } else {
                    charCount.style.color = '#28a745';
                }
            });
        }

        // Submit feedback
        document.getElementById('submitFeedback').addEventListener('click', function() {
            const rating = ratingInput.value;
            const feedback = feedbackTextarea.value;

            if (rating === '0') {
                Swal.fire({
                    title: 'Rating Required',
                    text: 'Please select a star rating.',
                    icon: 'warning',
                    confirmButtonColor: '#80A1BA'
                });
                return;
            }

            if (feedback.length < 5) {
                Swal.fire({
                    title: 'Feedback Too Short',
                    text: 'Please write at least 5 characters.',
                    icon: 'warning',
                    confirmButtonColor: '#80A1BA'
                });
                return;
            }

            // Submit to server (implement your submission logic)
            Swal.fire({
                title: 'Success!',
                text: 'Thank you for your feedback!',
                icon: 'success',
                confirmButtonColor: '#B4DEBD'
            }).then(() => {
                bootstrap.Modal.getInstance(document.getElementById('feedbackModal')).hide();
                feedbackTextarea.value = '';
                ratingInput.value = '0';
                stars.forEach(s => {
                    s.classList.remove('bi-star-fill');
                    s.classList.add('bi-star');
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop();
            const navLinks = document.querySelectorAll('.sidebar .nav-link');

            navLinks.forEach(link => {
                const linkPage = link.getAttribute('href');

                // Check if current page matches link
                if (linkPage === currentPage ||
                    (currentPage === '' && linkPage === 'dashboard.php') ||
                    (currentPage === 'dashboard.php' && linkPage === 'dashboard.php')) {
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