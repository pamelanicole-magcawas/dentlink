<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
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

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg bg-body-tertiary sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold text-primary" href="#">
                <img src="dentlink-logo.png" alt="Logo" width="70" height="60" class="me-2">
                DentLink
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarScroll" aria-controls="navbarScroll" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarScroll">
                <ul class="navbar-nav me-auto my-2 my-lg-0 navbar-nav-scroll">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="#home">
                            <i class="bi bi-house-door-fill"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#services">
                            <i class="bi bi-clipboard2-pulse-fill"></i> Services
                        </a>
                    </li>
                </ul>

                <ul class="navbar-nav me-3">
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">
                            <i class="bi bi-person-fill"></i>
                            <?php echo htmlspecialchars($_SESSION['first_name']); ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                </ul>

                <form class="d-flex" role="search">
                    <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
                    <button class="btn btn-outline-primary" type="submit">Search</button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Home Section -->
    <section id="home" class="vh-100 d-flex align-items-center justify-content-center text-center">
        <div class="container" style="margin-top: -50px;">
            <h2 class="fw-bold mb-4">Welcome to DentLink Clinic System</h2>
            <p class="text-muted mb-5">Manage your dental appointments and records easily.</p>

            <div class="row g-4 justify-content-center">
                <!-- Book Appointment -->
                <div class="col-md-4">
                    <div class="card home-card text-center shadow-sm border-0 h-100">
                        <div class="card-body">
                            <i class="bi bi-calendar-check display-3 text-primary mb-3"></i>
                            <h5 class="card-title fw-bold">Book Appointment</h5>
                            <p class="card-text">Schedule your next dental visit with ease.</p>
                            <a href="book_appointment.php" class="btn btn-primary">Book Now</a>
                        </div>
                    </div>
                </div>

                <!-- View Appointments -->
                <div class="col-md-4">
                    <div class="card home-card text-center shadow-sm border-0 h-100">
                        <div class="card-body">
                            <i class="bi bi-list-check display-3 text-warning mb-3"></i>
                            <h5 class="card-title fw-bold">View Appointments</h5>
                            <p class="card-text">Check your upcoming and past appointment schedules.</p>
                            <a href="view_appointment.php" class="btn btn-warning text-white">View Appointments</a>
                        </div>
                    </div>
                </div>

                <!-- View Records -->
                <div class="col-md-4">
                    <div class="card home-card text-center shadow-sm border-0 h-100">
                        <div class="card-body">
                            <i class="bi bi-folder2-open display-3 text-success mb-3"></i>
                            <h5 class="card-title fw-bold">View Records</h5>
                            <p class="card-text">Access your dental treatment and history records.</p>
                            <a href="view_records.php" class="btn btn-success">View Records</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="min-vh-100 d-flex flex-column align-items-center justify-content-center text-center py-5">
        <div class="container">
            <i class="bi bi-heart-pulse display-3 text-danger d-block mb-3"></i>
            <h3 class="section-title mb-3">Our Services</h3>
            <p class="text-muted mb-5">Explore our available medical and dental services with their rates.</p>

            <div class="row g-4">
                <!-- General Procedures -->
                <div class="col-md-6">
                    <div class="card text-start shadow-lg border-0 h-100">
                        <div class="card-body" style="border-left: 6px solid #0d6efd; border-radius: 6px;">
                            <h5 class="card-title text-primary"><i class="bi bi-clipboard-check-fill text-danger"></i> General Procedures</h5>
                            <ul class="service-list">
                                <li>• Oral Prophylaxis — <strong>₱1,000</strong></li>
                                <li>• Tooth Extraction — <strong>₱1,000</strong></li>
                                <li>• Fluoride Treatment — <strong>₱600</strong></li>
                                <li>• Periapical X-ray — <strong>₱500</strong></li>
                                <li>• Panoramic X-ray — <strong>₱1,000</strong></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Prosthodontics -->
                <div class="col-md-6">
                    <div class="card text-start shadow-lg border-0 h-100">
                        <div class="card-body" style="border-left: 6px solid #0d6efd; border-radius: 6px;">
                            <h5 class="card-title text-primary"><i class="bi bi-person-hearts text-danger"></i> Prosthodontics & Aesthetic Dentistry</h5>
                            <ul class="service-list">
                                <li>• Complete Denture — <strong>₱15,000 / arch</strong></li>
                                <li>• Removable Partial Dentures — <strong>₱10,000</strong></li>
                                <li>• Flexible Dentures — <strong>₱12,000</strong></li>
                                <li>• Fixed Bridge — <strong>₱8,000 / unit</strong></li>
                                <li>• Dental Crowns — <strong>₱9,000 / tooth</strong></li>
                                <li>• Veneers — <strong>₱12,000 / tooth</strong></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Endodontics -->
                <div class="col-md-6">
                    <div class="card text-start shadow-lg border-0 h-100">
                        <div class="card-body" style="border-left: 6px solid #0d6efd; border-radius: 6px;">
                            <h5 class="card-title text-primary"><i class="bi bi-bandaid-fill text-danger"></i> Endodontics & Surgery</h5>
                            <ul class="service-list">
                                <li>• Root Canal Treatment — <strong>₱6,000 / tooth</strong></li>
                                <li>• Odontectomy — <strong>₱5,000</strong></li>
                                <li>• Wisdom Tooth Extraction — <strong>₱4,500</strong></li>
                                <li>• Gingivectomy — <strong>₱3,000 / area</strong></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Orthodontics -->
                <div class="col-md-6">
                    <div class="card text-start shadow-lg border-0 h-100">
                        <div class="card-body" style="border-left: 6px solid #0d6efd; border-radius: 6px;">
                            <h5 class="card-title text-primary"><i class="bi bi-emoji-heart-eyes-fill text-danger"></i> Orthodontics</h5>
                            <ul class="service-list">
                                <li>• Metal Braces — <strong>₱45,000–₱60,000</strong></li>
                                <li>• Ceramic Braces — <strong>₱70,000–₱90,000</strong></li>
                                <li>• Self-ligating Braces — <strong>₱80,000+</strong></li>
                                <li>• Retainers — <strong>₱5,000+</strong></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="text-center mt-auto">
        <div class="container">
            <p class="mb-2">© 2025 SG Dental Clinic. All Rights Reserved.</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="#"><i class="bi bi-facebook fs-4"></i></a>
                <a href="#"><i class="bi bi-instagram fs-4"></i></a>
                <a href="#"><i class="bi bi-envelope fs-4"></i></a>
            </div>
            <p class="mt-2">Developed by <strong>Clinic System Team</strong></p>
        </div>
    </footer>

    <script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>