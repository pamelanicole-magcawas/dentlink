<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DentLink - Your Smile, Our Priority</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="landing.css">
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
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link px-3" href="#home">Home</a></li>
                    <li class="nav-item"><a class="nav-link px-3" href="#about">About</a></li>
                    <li class="nav-item"><a class="nav-link px-3" href="#services">Services</a></li>
                    <li class="nav-item"><a class="nav-link px-3" href="#reviews">Reviews</a></li>
                    <li class="nav-item"><a class="nav-link px-3" href="#contact">Contact</a></li>
                </ul>
                <div class="nav-buttons">
                    <a href="login.php" class="btn btn-outline-nav me-2">Login</a>
                    <a href="registration.php" class="btn btn-nav">Register</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-section">
        <div class="hero-overlay"></div>
        <div class="container position-relative" style="z-index: 3;">
            <div class="row align-items-center min-vh-100">
                <div class="col-lg-6">
                    <h1 class="hero-title">Your Smile,<br>Our <span class="text-gradient">Priority</span></h1>
                    <p class="hero-subtitle">Experience modern dental care with DentLink. Book appointments, track your dental health, and connect with our expert dentists - all in one place.</p>
                    <div class="hero-buttons">
                        <a href="registration.php" class="btn btn-hero-primary">
                            <i class="bi bi-calendar-check me-2"></i>Book Appointment
                        </a>
                        <a href="#services" class="btn btn-hero-outline">
                            <i class="bi bi-arrow-down-circle me-2"></i>Learn More
                        </a>
                    </div>
                    <div class="hero-stats">
                        <div class="stat-item">
                            <h3>4.2K</h3>
                            <p>Happy Patients</p>
                        </div>
                        <div class="stat-item">
                            <h3>5+</h3>
                            <p>Expert Dentists</p>
                        </div>
                        <div class="stat-item">
                            <h3>5+</h3>
                            <p>Years Experience</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 d-none d-lg-block">
                    <div class="hero-image">
                        <div class="floating-card card-1">
                            <i class="bi bi-shield-check"></i>
                            <span>Safe & Hygienic</span>
                        </div>
                        <div class="floating-card card-2">
                            <i class="bi bi-clock"></i>
                            <span>Business-hours support</span>
                        </div>
                        <div class="floating-card card-3">
                            <i class="bi bi-star-fill"></i>
                            <span>5-Star Rated</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="smile-curve">
            <svg viewBox="0 0 1440 120" preserveAspectRatio="none">
                <path fill="#ffffff" d="M0,64L80,69.3C160,75,320,85,480,80C640,75,800,53,960,48C1120,43,1280,53,1360,58.7L1440,64L1440,120L1360,120C1280,120,1120,120,960,120C800,120,640,120,480,120C320,120,160,120,80,120L0,120Z"></path>
            </svg>
        </div>
    </section>
    
    <!-- About Section -->
    <section id="about" class="about-section py-5">
        <div class="container">
            <div class="row align-items-center">
                <!-- Text Content -->
                <div class="col-lg-6 order-lg-2">
                    <div class="about-content">
                        <span class="section-badge">About DentLink</span>
                        <h2>Your Trusted Dental Partner</h2>
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
                </div>

                <!-- Image Content -->
                <div class="col-lg-6 order-lg-1">
                    <div class="about-image" style="height: 300px; width: 100%; overflow: hidden;">
                        <img src="dental.jpg" alt="Dental Clinic" class="img-fluid rounded shadow" style="object-fit: cover; width: 100%; height: 100%;">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="services-section">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">Our Services</span>
                <h2>Comprehensive Dental Care</h2>
                <p>We offer a wide range of dental services to keep your smile healthy and beautiful</p>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="service-card">
                        <div class="service-icon"><i class="bi bi-clipboard-check"></i></div>
                        <h4>General Dental Services</h4>
                        <p>Essential dental care including consultations, cleanings, x-rays, fillings, and extractions for everyday oral health.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="service-card">
                        <div class="service-icon"><i class="bi bi-capsule"></i></div>
                        <h4>Endodontics & Surgery</h4>
                        <p>Specialized treatments including root canals, surgical extractions, and gum procedures for complex dental issues.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="service-card">
                        <div class="service-icon"><i class="bi bi-scissors"></i></div>
                        <h4>Orthodontics</h4>
                        <p>Braces and retainers to straighten teeth and correct bite issues for a perfect smile.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="service-card">
                        <div class="service-icon"><i class="bi bi-person-badge"></i></div>
                        <h4>Prosthodontics & Aesthetic Dentistry</h4>
                        <p>Crowns, dentures, veneers, and cosmetic solutions to restore and enhance your smile.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="service-card">
                        <div class="service-icon"><i class="bi bi-stars"></i></div>
                        <h4>Other Dental Services</h4>
                        <p>Additional treatments including teeth whitening, bracket repairs, and crown re-cementation.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <div class="service-card">
                        <div class="service-icon"><i class="bi bi-shield-plus"></i></div>
                        <h4>Emergency Care</h4>
                        <p>Immediate attention for dental emergencies and urgent oral health issues.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Reviews Section -->
    <section id="reviews" class="py-5 bg-light">
        <div class="container">
            <div class="section-header">
                <span class="section-badge">Reviews</span>
                <h2>What Our Patients Say</h2>
                <p>Real experiences from our valued patients</p>
            </div>

            <!-- Reviews Display Only -->
            <div class="row g-4" id="reviewsContainer">
                <?php
                // Include database connection
                include 'db_connect.php';

                // Fetch all reviews from database (limit to 6 for landing page)
                $reviews_sql = "SELECT r.rating, r.review_text, r.created_at, u.first_name 
                           FROM reviews r 
                           JOIN users u ON r.user_id = u.user_id 
                           ORDER BY r.created_at DESC 
                           LIMIT 6";
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
                                    <p class="text-muted mb-0">"<?= htmlspecialchars($review['review_text']) ?>"</p>
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
                                <p class="text-muted mb-0">Be the first to experience our exceptional dental care!</p>
                            </div>
                        </div>
                    </div>
                <?php
                endif;
                $conn->close();
                ?>
            </div>

            <!-- Call to Action -->
            <?php if ($reviews_result && $reviews_result->num_rows > 0): ?>
                <div class="text-center mt-5">
                    <a href="login.php" class="btn btn-hero-primary">
                        <i class="bi bi-calendar-check me-2"></i>Book Your Appointment
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold mb-3" style="color: #80A1BA;">Contact Us</h2>
                <p class="lead text-muted">Get in touch with us today</p>
            </div>

            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100 text-center">
                        <div class="card-body p-4">
                            <div class="icon-circle mx-auto mb-3" style="background-color: rgba(128, 161, 186, 0.15);">
                                <i class="bi bi-geo-alt-fill fs-1" style="color: #80A1BA;"></i>
                            </div>
                            <h5 class="fw-bold mb-3">Lipa City Clinic</h5>
                            <p class="text-muted">2nd Floor, CL Building, E Mayo St,<br>Brgy. 4, Lipa City,<br>4217 Batangas</p>
                            <a href="https://maps.google.com/?q=2nd+Floor+CL+Building+E+Mayo+St+Brgy+4+Lipa+City+Batangas" target="_blank" class="btn btn-outline-custom btn-sm rounded-pill">
                                <i class="bi bi-map me-2"></i>Tap to Navigate
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100 text-center">
                        <div class="card-body p-4">
                            <div class="icon-circle mx-auto mb-3" style="background-color: rgba(128, 161, 186, 0.15);">
                                <i class="bi bi-geo-alt-fill fs-1" style="color: #80A1BA;"></i>
                            </div>
                            <h5 class="fw-bold mb-3">Santa Rosa Clinic</h5>
                            <p class="text-muted">Sta. Rosa Commercial Complex,<br>468 Garnet Rd, Balibago,<br>City of Santa Rosa, 4026 Laguna</p>
                            <a href="https://maps.google.com/?q=Sta.+Rosa+Commercial+Complex+468+Garnet+Rd+Balibago+Santa+Rosa+Laguna" target="_blank" class="btn btn-outline-custom btn-sm rounded-pill">
                                <i class="bi bi-map me-2"></i>Tap to Navigate
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100 text-center">
                        <div class="card-body p-4">
                            <div class="icon-circle mx-auto mb-3" style="background-color: rgba(145, 196, 195, 0.15);">
                                <i class="bi bi-telephone-fill fs-1" style="color: #91C4C3;"></i>
                            </div>
                            <h5 class="fw-bold mb-3">Call Us</h5>
                            <p class="text-muted mb-3">We're here to help you</p>
                            <a href="tel:+639178881058" class="btn btn-outline-custom btn-sm rounded-pill">
                                <i class="bi bi-telephone me-2"></i>Call Now
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100 text-center">
                        <div class="card-body p-4">
                            <div class="icon-circle mx-auto mb-3" style="background-color: rgba(180, 222, 189, 0.15);">
                                <i class="bi bi-share-fill fs-1" style="color: #B4DEBD;"></i>
                            </div>
                            <h5 class="fw-bold mb-3">Follow Us</h5>
                            <p class="text-muted mb-3">Stay connected with us</p>
                            <div class="d-flex justify-content-center gap-2">
                                <a href="https://www.facebook.com/share/17pAPyNDWG/?mibextid=wwXIfr" target="_blank" class="btn btn-sm rounded-circle social-btn" style="width: 40px; height: 40px; padding: 8px; background-color: #80A1BA; color: white;" title="SG Dental Clinic - Lipa City Branch">
                                    <i class="bi bi-facebook"></i>
                                </a>
                                <a href="https://www.facebook.com/share/17pXfpRbSF/?mibextid=wwXIfr" target="_blank" class="btn btn-sm rounded-circle social-btn" style="width: 40px; height: 40px; padding: 8px; background-color: #6FA8A7; color: white;" title="SG Dental Clinic - Sta. Rosa Branch">
                                    <i class="bi bi-facebook"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Ready for a Brighter Smile?</h2>
                <p>Join thousands of happy patients who trust DentLink for their dental care needs.</p>
                <div class="cta-buttons">
                    <a href="registration.php" class="btn btn-cta-primary">
                        <i class="bi bi-person-plus me-2"></i>Create Account
                    </a>
                    <a href="login.php" class="btn btn-cta-outline">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="footer-brand">
                        <img src="dentlink-logo.png" alt="DentLink" width="50">
                        <h4>DentLink</h4>
                    </div>
                    <p>Your trusted partner in dental health. Modern care for modern smiles.</p>
                </div>
                <div class="col-lg-3 col-md-4 mb-4">
                    <h5>Services</h5>
                    <ul>
                        <li><a href="#services">General Dental Services</a></li>
                        <li><a href="#services">Endodontics & Surgery</a></li>
                        <li><a href="#services">Orthodontics</a></li>
                        <li><a href="#services">Prosthodontics & Aesthetic Dentistry</a></li>
                        <li><a href="#services">Other Dental Services</a></li>
                        <li><a href="#services">Emergency Care</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-4 mb-4">
                    <h5>Clinic Hours</h5>
                    <ul class="hours-list">
                        <li><span>Monday - Sunday</span><span>9:00 AM - 5:00 PM</span></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 DentLink: Dental Clinic Digital Appointment and Patient Records Management System</p>
                <p>All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Active nav link on scroll
        window.addEventListener('scroll', () => {
            let current = '';
            const sections = document.querySelectorAll('section');
            const navLinks = document.querySelectorAll('.navbar .nav-link');

            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                if (window.pageYOffset >= sectionTop - 200) {
                    current = section.getAttribute('id');
                }
            });

            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === `#${current}`) {
                    link.classList.add('active');
                }
            });
        });

        // Active nav link on click
        document.querySelectorAll('.navbar .nav-link').forEach(link => {
            link.addEventListener('click', function() {
                document.querySelectorAll('.navbar .nav-link').forEach(l => l.classList.remove('active'));
                this.classList.add('active');
            });
        });
    </script>
</body>

</html>