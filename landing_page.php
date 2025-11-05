<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DentLink</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="design.css">
</head>

<body class="light-mode">

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
                    <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-primary px-4" href="registration.php">Register</a>
                    </li>
                    <li class="nav-item ms-3">
                        <button id="themeToggle" class="btn btn-outline-secondary rounded-circle">
                            <i class="bi bi-moon-fill" id="themeIcon"></i>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <section class="hero d-flex align-items-center text-center">
        <div class="container">
            <h1 class="fw-bold display-5 mb-3 animate__animated animate__fadeInDown">Your Smile, Our Digital Care</h1>
            <p class="lead mb-4 animate__animated animate__fadeInUp animate__delay-1s">
                DentLink makes dental appointments and record management effortless, smart, and reliable.
            </p>
            <a href="registration.php" class="btn btn-primary btn-lg me-2 animate__animated animate__fadeInUp animate__delay-2s">Book an Appointment</a>
            <a href="#about" class="btn btn-outline-primary btn-lg animate__animated animate__fadeInUp animate__delay-2s">Learn More</a>
        </div>
    </section>

    <section id="about" class="py-5 bg-light transition-all">
        <div class="container text-center">
            <h2 class="fw-bold mb-4">About DentLink</h2>
            <p class="text-muted mx-auto" style="max-width: 800px;">
                DentLink is an innovative web-based system designed to make dental management easier and faster.
                It helps dental clinics handle appointments, automate reminders, and organize patient records with ease.
                With accurate visit tracking and returning patient management, DentLink ensures a seamless and smart experience
                for both clinics and clients — bringing digital care to every smile.
            </p>
        </div>
    </section>

    <section id="services" class="py-5 transition-all">
        <div class="container text-center">
            <h2 class="fw-bold mb-5">Our Dental Services</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card service-card h-100 animate__animated animate__fadeInUp animate__delay-1s">
                        <div class="card-body">
                            <div class="icon mb-3">
                                <i class="bi bi-brightness-high display-5 text-primary"></i>
                            </div>
                            <h5 class="card-title fw-bold">Teeth Whitening</h5>
                            <p class="card-text text-muted">Brighten your smile with our professional whitening treatment for a confident, glowing look.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card service-card h-100 animate__animated animate__fadeInUp animate__delay-2s">
                        <div class="card-body">
                            <div class="icon mb-3">
                                <i class="bi bi-heart-pulse display-5 text-primary"></i>
                            </div>
                            <h5 class="card-title fw-bold">Oral Checkups</h5>
                            <p class="card-text text-muted">Comprehensive oral health checkups for early detection and long-term dental wellness.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card service-card h-100 animate__animated animate__fadeInUp animate__delay-3s">
                        <div class="card-body">
                            <div class="icon mb-3">
                                <i class="bi bi-shield-check display-5 text-primary"></i>
                            </div>
                            <h5 class="card-title fw-bold">Preventive Care</h5>
                            <p class="card-text text-muted">Protect your teeth from cavities and gum diseases with regular preventive care services.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="contact" class="py-5 bg-light transition-all">
        <div class="container text-center">
            <h2 class="fw-bold mb-4">Contact Us</h2>
            <p class="text-muted mb-4">Have questions or need to schedule an appointment? Get in touch with us!</p>
            <div class="d-flex justify-content-center">
                <div class="contact-info text-start">
                    <p><i class="bi bi-geo-alt-fill text-muted"></i> CL Building, E. Mayo St., Lipa City, Philippines, 4217</p>
                    <p><i class="bi bi-envelope-fill text-muted"></i> dentlinkclinic@gmail.com</p>
                    <p><i class="bi bi-telephone-fill text-muted"></i> +63 917 888 1058</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-primary text-white text-center py-3 mt-5 transition-all">
        <p class="mb-0">© 2025 DentLink | All Rights Reserved</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Animate sections on scroll
        document.addEventListener("DOMContentLoaded", () => {
            const sections = document.querySelectorAll("section");
            const options = {
                threshold: 0.15
            };
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add("fade-in");
                    }
                });
            }, options);

            sections.forEach(section => observer.observe(section));
        });

        // Dark/Light Mode Toggle
        const toggleButton = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');
        const body = document.body;

        toggleButton.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            if (body.classList.contains('dark-mode')) {
                themeIcon.classList.remove('bi-moon-fill');
                themeIcon.classList.add('bi-sun-fill');
            } else {
                themeIcon.classList.remove('bi-sun-fill');
                themeIcon.classList.add('bi-moon-fill');
            }
        });
    </script>


</body>

</html>