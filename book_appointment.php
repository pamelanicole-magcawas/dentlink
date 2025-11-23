<?php
session_start();
include 'db_connect.php';
require "log_activity.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$user_stmt = $conn->prepare("SELECT first_name, last_name, email FROM users WHERE user_id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
$full_name = $user['first_name'] . ' ' . $user['last_name'];
$email = $user['email'];

$message = '';
$alertScript = '';

/* =================== HANDLE BOOKING =================== */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'book') {
    $date = $_POST['date'];
    $location = $_POST['location'];
    $time = $_POST['time'];
    $service = $_POST['service'];

    $limit_sql = "SELECT COUNT(*) AS total FROM appointments 
                  WHERE user_id = ? 
                  AND DATE(created_at) = CURDATE() 
                  AND status IN ('pending', 'approved')";
    $limit_stmt = $conn->prepare($limit_sql);
    $limit_stmt->bind_param("i", $user_id);
    $limit_stmt->execute();
    $limit_result = $limit_stmt->get_result();
    $limit_row = $limit_result->fetch_assoc();

    if ($limit_row['total'] >= 2) {
        $alertScript = "Swal.fire({
          title: 'Booking Limit Reached!',
          text: 'You can only make 2 booking requests per day.',
          icon: 'warning',
          customClass: { confirmButton: 'swal-btn' }
        });";
    } else {
        $conflict_sql = "SELECT * FROM appointments 
                         WHERE status = 'approved' 
                         AND date = ? 
                         AND start_time = ? 
                         AND location = ?";
        $stmt = $conn->prepare($conflict_sql);
        $stmt->bind_param("sss", $date, $time, $location);
        $stmt->execute();
        $conflict_result = $stmt->get_result();

        if ($conflict_result->num_rows > 0) {
            $alertScript = "Swal.fire({
              title: 'Time Slot Unavailable!',
              text: 'This time slot is already booked at $location. Please choose another time.',
              icon: 'error',
              customClass: { confirmButton: 'swal-btn' }
            });";
        } else {
            $insert_sql = "INSERT INTO appointments 
                           (user_id, name, email, date, location, start_time, description, status, created_at, qr_code_url, calendar_link) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW(), NULL, NULL)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("issssss", $user_id, $full_name, $email, $date, $location, $time, $service);

            if ($insert_stmt->execute()) {
                $alertScript = "Swal.fire({
                  title: 'Appointment Booked!',
                  text: 'Your request for $location has been submitted successfully!',
                  icon: 'success',
                  customClass: { confirmButton: 'swal-btn' }
                });";
            } else {
                $alertScript = "Swal.fire({
                  title: 'Booking Failed!',
                  text: 'Something went wrong. Please try again.',
                  icon: 'error',
                  customClass: { confirmButton: 'swal-btn' }
                });";
            }
        }
    }
}

/* =================== HANDLE DELETE =================== */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $id = intval($_POST['id']);
    $verify_sql = "SELECT * FROM appointments WHERE id = ? AND user_id = ? AND status != 'approved'";
    $stmt = $conn->prepare($verify_sql);
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $delete_sql = "DELETE FROM appointments WHERE id = ?";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $alertScript = "Swal.fire({
              title: 'Deleted!',
              text: 'Your appointment was successfully deleted.',
              icon: 'success',
              customClass: { confirmButton: 'swal-btn' }
            });";
        } else {
            $alertScript = "Swal.fire({
              title: 'Delete Failed!',
              text: 'Unable to delete appointment. Please try again.',
              icon: 'error',
              customClass: { confirmButton: 'swal-btn' }
            });";
        }
    }
}

$pending = $conn->query("SELECT * FROM appointments WHERE user_id = $user_id AND status = 'pending' ORDER BY date DESC");
$denied = $conn->query("SELECT * FROM appointments WHERE user_id = $user_id AND status = 'denied' ORDER BY date DESC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Your Dental Appointment - DentLink</title>
    <link rel="stylesheet" href="bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="patient.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="container-fluid">
        <a href="dashboard.php" class="btn btn-custom mb-3 d-inline-flex align-items-center gap-2">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>

        <div class="page-header">
            <h1><i class="bi bi-calendar-check"></i> Book Your Dental Appointment</h1>
            <p class="text-muted mb-0">Welcome, <strong><?= htmlspecialchars($full_name) ?></strong></p>
        </div>

        <?= $message ?>

        <div class="calendar-container">
            <h4 class="mb-3"><i class="bi bi-calendar3"></i> Check Available Time Slots</h4>
            <p class="text-muted small">Calendar shows available time slots. Booked times will appear as busy blocks.</p>
            <iframe src="https://calendar.google.com/calendar/embed?height=500&wkst=1&bgcolor=%23ffffff&ctz=Asia%2FManila&showTitle=0&showPrint=0&showCalendars=0&showTz=0&mode=WEEK&src=c2dkZW50YWxjbGluaWNjY0BnbWFpbC5jb20&color=%234CAF50"></iframe>
        </div>

        <div class="booking-section">
            <div class="booking-form">
                <h4><i class="bi bi-clipboard-check"></i> Appointment Details</h4>

                <form method="POST">
                    <input type="hidden" name="action" value="book">

                    <div class="mb-3">
                        <label class="form-label">Date:</label>
                        <input type="date" name="date" class="form-control" required min="<?= date('Y-m-d') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Location:</label>
                        <select name="location" class="form-select" required>
                            <option value="">--Select Location--</option>
                            <option value="Dental Clinic, Lipa City">Lipa City</option>
                            <option value="Dental Clinic, San Pablo City">San Pablo City</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Time:</label>
                        <select name="time" id="timeSlot" class="form-select" required>
                            <option value="">-- Select Time --</option>
                            <?php
                            for ($hour = 8; $hour <= 16; $hour++) {
                                $timeValue = sprintf("%02d:00:00", $hour);
                                $displayTime = date("h:i A", strtotime($timeValue));
                                echo "<option value='$timeValue' data-label='$displayTime'>$displayTime</option>";
                            }
                            ?>
                        </select>
                        <small class="text-muted">Clinic hours: 8:00 AM - 5:00 PM</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Service:</label>
                        <select name="service" id="serviceSelect" class="form-select" required>
                            <option value="">--Select Service--</option>
                            <option value="Consultation Fee">Consultation Fee</option>
                            <option value="Fluoride Treatment">Fluoride Treatment</option>
                            <option value="Oral Prophylaxis (Cleaning)">Oral Prophylaxis (Cleaning)</option>
                            <option value="Panoramic X-ray">Panoramic X-ray</option>
                            <option value="Periapical X-ray">Periapical X-ray</option>
                            <option value="Tooth Extraction">Tooth Extraction</option>
                            <option value="Tooth Restoration (Filling)">Tooth Restoration (Filling)</option>
                            <option value="Gingivectomy">Gingivectomy</option>
                            <option value="Odontectomy (Surgical Extraction)">Odontectomy (Surgical Extraction)</option>
                            <option value="Post and Core">Post and Core</option>
                            <option value="Root Canal Therapy">Root Canal Therapy</option>
                            <option value="Wisdom Tooth Extraction">Wisdom Tooth Extraction</option>
                            <option value="Ceramic Braces">Ceramic Braces</option>
                            <option value="Metal Braces">Metal Braces</option>
                            <option value="Orthodontic Treatment">Orthodontic Treatment</option>
                            <option value="Retainers / Arch (Hawley's)">Retainers / Arch (Hawley's)</option>
                            <option value="Self-Ligating Braces">Self-Ligating Braces</option>
                            <option value="All-Porcelain / Emax Crown">All-Porcelain / Emax Crown</option>
                            <option value="Complete Denture">Complete Denture</option>
                            <option value="Flexible Dentures">Flexible Dentures</option>
                            <option value="One-Piece Metal Casting">One-Piece Metal Casting</option>
                            <option value="Partial Denture 1 Pontic">Partial Denture 1 Pontic</option>
                            <option value="Partial Denture Anterior / Posterior">Partial Denture Anterior / Posterior</option>
                            <option value="Porcelain Fused to Metal (PFM) Crown">Porcelain Fused to Metal (PFM) Crown</option>
                            <option value="Plastic Crown">Plastic Crown</option>
                            <option value="Removable Partial Dentures">Removable Partial Dentures</option>
                            <option value="Veneer (Porcelain)">Veneer (Porcelain)</option>
                            <option value="Zirconia Crown">Zirconia Crown</option>
                            <option value="Bracket Recement">Bracket Recement</option>
                            <option value="Laser Teeth Bleaching">Laser Teeth Bleaching</option>
                            <option value="Re-cementation of Crown">Re-cementation of Crown</option>
                            <option value="Teeth Bleaching">Teeth Bleaching</option>
                            <option value="Temporary Crown">Temporary Crown</option>
                        </select>
                    </div>

                    <div id="serviceDescription" class="service-info-box" style="display: none;">
                        <div class="service-info-header">
                            <i class="bi bi-info-circle-fill"></i>
                            <strong>Service Details</strong>
                        </div>
                        <p id="descriptionText"></p>
                        <div class="service-price-tag">
                            <i class="bi bi-tag-fill"></i>
                            <span id="priceText"></span>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-custom w-100 mt-3">
                        <i class="bi bi-send"></i> Submit Booking Request
                    </button>
                </form>
            </div>

            <div class="sidebar">
                <h5><i class="bi bi-hourglass-split text-warning"></i> Pending Requests</h5>
                <?php if ($pending && $pending->num_rows > 0): ?>
                    <?php while ($p = $pending->fetch_assoc()): ?>
                        <div class="appointment-item">
                            <h6><?= htmlspecialchars($p['description']) ?></h6>
                            <p class="mb-1 small">
                                <?= htmlspecialchars($p['date']) ?> @ <?= date("h:i A", strtotime($p['start_time'])) ?>
                            </p>
                            <p class="mb-2 small"><?= htmlspecialchars($p['location']) ?></p>
                            <button type="button" class="delete-btn swal-delete-btn" data-id="<?= $p['id'] ?>">
                                <i class="bi bi-trash"></i> Delete Request
                            </button>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-muted text-center">No pending requests.</p>
                <?php endif; ?>

                <h5 class="mt-4"><i class="bi bi-x-circle text-danger"></i> Denied Requests</h5>
                <?php if ($denied && $denied->num_rows > 0): ?>
                    <?php while ($d = $denied->fetch_assoc()): ?>
                        <div class="appointment-item">
                            <h6 class="text-danger"><?= htmlspecialchars($d['description']) ?></h6>
                            <p class="mb-1 small">
                                <?= htmlspecialchars($d['date']) ?> @ <?= date("h:i A", strtotime($d['start_time'])) ?>
                            </p>
                            <p class="mb-2 small"><?= htmlspecialchars($d['location']) ?></p>
                            <button type="button" class="delete-btn swal-delete-btn" data-id="<?= $d['id'] ?>">
                                <i class="bi bi-trash"></i> Delete Request
                            </button>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-muted text-center">No denied requests.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dateInput = document.querySelector('input[name="date"]');
            const locationSelect = document.querySelector('select[name="location"]');
            const timeSelect = document.getElementById('timeSlot');

            function fetchBookedSlots() {
                const date = dateInput.value;
                const location = locationSelect.value;
                if (!date || !location) return;

                fetch(`get_booked_times.php?date=${encodeURIComponent(date)}&location=${encodeURIComponent(location)}`)
                    .then(response => response.json())
                    .then(bookedTimes => {
                        for (let option of timeSelect.options) {
                            if (option.value === "") continue;
                            option.disabled = bookedTimes.includes(option.value);
                            option.textContent = option.disabled ? option.dataset.label + " (Booked)" : option.dataset.label;
                        }
                    });
            }

            dateInput.addEventListener('change', fetchBookedSlots);
            locationSelect.addEventListener('change', fetchBookedSlots);
        });

        const serviceData = {
            "Consultation Fee": {
                desc: "Initial assessment of dental health and treatment planning with a professional dentist.",
                price: "₱700"
            },
            "Fluoride Treatment": {
                desc: "Application of fluoride to strengthen tooth enamel and prevent cavities.",
                price: "₱600"
            },
            "Oral Prophylaxis (Cleaning)": {
                desc: "Professional cleaning to remove plaque, tartar, and stains for healthier gums and teeth.",
                price: "₱1,200"
            },
            "Panoramic X-ray": {
                desc: "Wide-view X-ray image of the entire mouth including teeth, jaws, and surrounding structures.",
                price: "₱1,000"
            },
            "Periapical X-ray": {
                desc: "Detailed X-ray of one or two teeth showing the entire tooth from crown to root.",
                price: "₱500"
            },
            "Tooth Extraction": {
                desc: "Removal of a damaged or problematic tooth. Additional teeth cost ₱700 each.",
                price: "₱1,200"
            },
            "Tooth Restoration (Filling)": {
                desc: "Repair of cavities or damaged teeth using composite or amalgam filling materials.",
                price: "₱1,200"
            },
            "Gingivectomy": {
                desc: "Surgical removal of gum tissue to treat gum disease or improve tooth appearance.",
                price: "₱3,000 / area"
            },
            "Odontectomy (Surgical Extraction)": {
                desc: "Surgical removal of impacted or difficult-to-extract teeth requiring incision.",
                price: "₱10,000"
            },
            "Post and Core": {
                desc: "Foundation structure placed inside a tooth to support a dental crown after root canal treatment.",
                price: "₱4,000"
            },
            "Root Canal Therapy": {
                desc: "Treatment to remove infected pulp from inside a tooth and seal it to prevent further infection.",
                price: "₱8,000 per canal"
            },
            "Wisdom Tooth Extraction": {
                desc: "Removal of third molars (wisdom teeth) that may be impacted or causing problems.",
                price: "₱4,500"
            },
            "Ceramic Braces": {
                desc: "Tooth-colored orthodontic braces that are less visible than traditional metal braces.",
                price: "₱70,000 - ₱90,000"
            },
            "Metal Braces": {
                desc: "Traditional stainless steel braces for straightening teeth and correcting bite problems.",
                price: "₱45,000 - ₱60,000"
            },
            "Orthodontic Treatment": {
                desc: "Comprehensive treatment to align teeth and correct jaw position. Minimum down payment: ₱15,000.",
                price: "₱50,000"
            },
            "Retainers / Arch (Hawley's)": {
                desc: "Removable appliance to maintain teeth position after braces treatment.",
                price: "₱6,000 per arch"
            },
            "Self-Ligating Braces": {
                desc: "Advanced braces system that uses clips instead of elastic bands for faster treatment.",
                price: "₱80,000+"
            },
            "All-Porcelain / Emax Crown": {
                desc: "High-quality ceramic crown that provides superior aesthetics and strength.",
                price: "₱20,000 per unit"
            },
            "Complete Denture": {
                desc: "Full set of removable false teeth to replace all missing teeth in upper or lower jaw.",
                price: "₱16,000 per set"
            },
            "Flexible Dentures": {
                desc: "Comfortable, lightweight partial dentures made from flexible thermoplastic material.",
                price: "₱20,000 per arch"
            },
            "One-Piece Metal Casting": {
                desc: "Custom-made metal framework denture for superior fit and durability.",
                price: "₱18,000 - ₱25,000 per arch"
            },
            "Partial Denture 1 Pontic": {
                desc: "Removable denture to replace a single missing tooth.",
                price: "₱4,500"
            },
            "Partial Denture Anterior / Posterior": {
                desc: "Removable denture to replace missing front or back teeth.",
                price: "₱6,500"
            },
            "Porcelain Fused to Metal (PFM) Crown": {
                desc: "Durable crown with metal base and porcelain coating for natural appearance.",
                price: "₱8,000 per unit"
            },
            "Plastic Crown": {
                desc: "Temporary or budget-friendly crown option made from acrylic material.",
                price: "₱5,000 per unit"
            },
            "Removable Partial Dentures": {
                desc: "Removable appliance to replace several missing teeth with clasps for stability.",
                price: "₱10,000"
            },
            "Veneer (Porcelain)": {
                desc: "Thin shell of porcelain bonded to front of tooth to improve appearance.",
                price: "₱15,000 per unit"
            },
            "Zirconia Crown": {
                desc: "Premium quality crown made from zirconia for maximum strength and natural aesthetics.",
                price: "₱25,000 per unit"
            },
            "Bracket Recement": {
                desc: "Reattachment of a loose or detached orthodontic bracket to the tooth.",
                price: "₱500"
            },
            "Laser Teeth Bleaching": {
                desc: "Professional whitening treatment using laser technology for fastest, most dramatic results.",
                price: "₱25,000"
            },
            "Re-cementation of Crown": {
                desc: "Reattachment of a loose or fallen crown back onto the tooth.",
                price: "₱1,200"
            },
            "Teeth Bleaching": {
                desc: "Professional whitening treatment to brighten and remove stains from teeth.",
                price: "₱15,000"
            },
            "Temporary Crown": {
                desc: "Short-term crown to protect a tooth while permanent restoration is being made.",
                price: "₱2,500"
            }
        };

        const serviceSelect = document.getElementById('serviceSelect');
        const descriptionBox = document.getElementById('serviceDescription');
        const descriptionText = document.getElementById('descriptionText');
        const priceText = document.getElementById('priceText');

        if (serviceSelect) {
            serviceSelect.addEventListener('change', function() {
                const selectedService = this.value;
                if (serviceData[selectedService]) {
                    descriptionText.textContent = serviceData[selectedService].desc;
                    priceText.textContent = serviceData[selectedService].price;
                    descriptionBox.style.display = 'block';
                } else {
                    descriptionBox.style.display = 'none';
                }
            });
        }

        document.querySelectorAll('.swal-delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const appointmentId = this.dataset.id;
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You cannot undo this action.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    customClass: {
                        confirmButton: 'swal-btn',
                        cancelButton: 'swal-cancel-btn'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.innerHTML = `
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="${appointmentId}">
                        `;
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });
        });
    </script>

    <?php if (!empty($alertScript)): ?>
        <script>
            <?= $alertScript ?>
        </script>
    <?php endif; ?>
</body>

</html>