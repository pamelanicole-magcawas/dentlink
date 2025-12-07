<?php
session_start();
include("db_connect.php");

$first_name = $last_name = $email = $phone = $address = $password = $confirm = $gender = "";
$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $email      = trim($_POST['email']);
    $phone      = trim($_POST['phone']);
    $address    = trim($_POST['address']);
    $password   = trim($_POST['password']);
    $confirm    = trim($_POST['confirm_password']);
    $gender     = $_POST['gender'] ?? '';
    $role       = "Patient";

    // --- Validation ---
    if (empty($first_name)) $errors['first_name'] = "First name is required";
    elseif (!preg_match("/^[a-zA-Z-' ]*$/", $first_name)) $errors['first_name'] = "Only letters and spaces allowed";

    if (empty($last_name)) $errors['last_name'] = "Last name is required";
    elseif (!preg_match("/^[a-zA-Z-' ]*$/", $last_name)) $errors['last_name'] = "Only letters and spaces allowed";

    if (empty($email)) $errors['email'] = "Email is required";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "Invalid email format";

    if (empty($phone)) $errors['phone'] = "Phone number is required";
    elseif (!preg_match("/^[0-9]{11}$/", $phone)) $errors['phone'] = "Phone number must be exactly 11 digits";

    if (empty($address)) $errors['address'] = "Address is required";

    if (empty($gender)) $errors['gender'] = "Please select your gender";
    elseif (!in_array($gender, ['Male', 'Female', 'Prefer not to say']))
        $errors['gender'] = "Invalid selection";

    if (empty($password)) $errors['password'] = "Password is required";
    elseif (!preg_match("/^.{5,15}$/", $password)) $errors['password'] = "Password must be 5–15 characters";

    if (empty($confirm)) $errors['confirm_password'] = "Please confirm your password";
    elseif ($password !== $confirm) $errors['confirm_password'] = "Passwords do not match";

    // --- Profile picture validation ---
    if (empty($_FILES["profile_pic"]["name"])) {
        $errors['profile_pic'] = "Please upload a profile picture";
    } else {
        $target_dir = "upload/";
        $file_name = basename($_FILES["profile_pic"]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $check = getimagesize($_FILES["profile_pic"]["tmp_name"]);
        if ($check === false) $errors['profile_pic'] = "File is not a valid image";
        if (file_exists($target_file)) $errors['profile_pic'] = "File already exists";
        if ($_FILES["profile_pic"]["size"] > 2 * 1024 * 1024) $errors['profile_pic'] = "File too large. Max 2MB";
        if (!in_array($imageFileType, ['jpg', 'jpeg', 'png'])) $errors['profile_pic'] = "Only JPG, JPEG, PNG allowed";
    }

    // --- Only proceed if no validation errors ---
    if (empty($errors)) {
        // --- Check email & phone duplicates first ---
        $checkEmail = $conn->prepare("SELECT * FROM users WHERE email=?");
        $checkEmail->bind_param("s", $email);
        $checkEmail->execute();
        $resultEmail = $checkEmail->get_result();

        $checkPhone = $conn->prepare("SELECT * FROM users WHERE phone=?");
        $checkPhone->bind_param("s", $phone);
        $checkPhone->execute();
        $resultPhone = $checkPhone->get_result();

        if ($resultEmail->num_rows > 0) $errors['email'] = "Email already registered";
        if ($resultPhone->num_rows > 0) $errors['phone'] = "Phone number already registered";

        $checkEmail->close();
        $checkPhone->close();

        // --- Only move file if no duplicate errors ---
        if (empty($errors)) {
            if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Save user temporarily in session before OTP verification
                $_SESSION['pending_user'] = [
                    'first_name' => $first_name,
                    'last_name'  => $last_name,
                    'email'      => $email,
                    'phone'      => $phone,
                    'address'    => $address,
                    'gender'     => $gender,
                    'password'   => $hashed_password,
                    'role'       => $role,
                    'profile_pic' => $file_name
                ];
                $_SESSION['otp_resend_count'] = 0; // reset resend attempts
                header("Location: send_otp.php");
                exit();
            } else {
                $errors['profile_pic'] = "Error uploading file";
            }
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DentLink Registration</title>
    <link href="bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="credentials.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>

<body>
    <div class="container-fluid login-container d-flex flex-column flex-lg-row min-vh-100 p-0">
        <div class="left-side d-flex flex-column justify-content-center align-items-center text-center p-5">
            <img src="dentlink-logo.png" alt="DentLink Logo">
            <p class="mt-3"><strong>DentLink: Dental Clinic Digital Appointment and Patient Records Management System</strong></p>
            <p class="mt-3"><strong>DentLink: Dental Clinic Digital Appointment and Patient Records Management System</strong></p>
                        <div class="info-box mt-3">
                <p class="mb-3">DentLink makes dental care simple and convenient with our modern booking system.</p>
                
                <div class="mb-2">
                    <strong><i class="bi bi-mouse"></i> Easy Online Booking</strong>
                    <p class="mb-0">Book appointments anytime, anywhere with just a few clicks.</p>
                </div>
                
                <div class="mb-2">
                    <strong><i class="bi bi-bell"></i> Appointment Reminders</strong>
                    <p class="mb-0">Never miss an appointment with automated email notifications.</p>
                </div>
                
                <div class="mb-2">
                    <strong><i class="bi bi-chat-dots"></i> Live Chat Support</strong>
                    <p class="mb-0">Connect with our staff instantly through our chat system.</p>
                </div>
                
                <div class="mb-2">
                    <strong><i class="bi bi-qr-code"></i> QR Code Check-in</strong>
                    <p class="mb-0">Fast and contactless check-in with your unique QR code.</p>
                </div>
            </div>
        </div>

        <div class="right-side d-flex justify-content-center align-items-center p-5">
            <div class="form-box w-100">
                <h2><i class="bi bi-person-plus-fill"></i> Registration Form</h2>

                <form method="POST" action="" enctype="multipart/form-data" novalidate>
                    <div class="row mb-3">
                        <div class="col">
                            <input type="text" name="first_name" class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>" placeholder="First Name" value="<?= htmlspecialchars($first_name); ?>">
                            <?php if (isset($errors['first_name'])): ?><div class="invalid-feedback"><?= $errors['first_name']; ?></div><?php endif; ?>
                        </div>
                        <div class="col">
                            <input type="text" name="last_name" class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>" placeholder="Last Name" value="<?= htmlspecialchars($last_name); ?>">
                            <?php if (isset($errors['last_name'])): ?><div class="invalid-feedback"><?= $errors['last_name']; ?></div><?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <input type="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" placeholder="Email Address" value="<?= htmlspecialchars($email); ?>">
                        <?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?= $errors['email']; ?></div><?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <input type="text" name="phone" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" placeholder="Phone Number (09XXXXXXXXX)" value="<?= htmlspecialchars($phone); ?>">
                        <?php if (isset($errors['phone'])): ?><div class="invalid-feedback"><?= $errors['phone']; ?></div><?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <input type="text" name="address" class="form-control <?= isset($errors['address']) ? 'is-invalid' : '' ?>" placeholder="Complete Address" value="<?= htmlspecialchars($address); ?>">
                        <?php if (isset($errors['address'])): ?><div class="invalid-feedback"><?= $errors['address']; ?></div><?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <select name="gender" class="form-select <?= isset($errors['gender']) ? 'is-invalid' : '' ?>">
                            <option value="">Select Gender</option>
                            <option value="Male" <?= ($gender === 'Male') ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= ($gender === 'Female') ? 'selected' : '' ?>>Female</option>
                            <option value="Prefer not to say" <?= ($gender === 'Prefer not to say') ? 'selected' : '' ?>>Prefer not to say</option>
                        </select>
                        <?php if (isset($errors['gender'])): ?>
                            <div class="invalid-feedback"><?= $errors['gender']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="file-label">
                            Choose Profile Picture
                            <input type="file" id="profile_pic" name="profile_pic" accept="image/*" hidden class="<?= isset($errors['profile_pic']) ? 'is-invalid' : '' ?>">
                        </label>
                        <span id="file-chosen">No file chosen</span>
                        <?php if (isset($errors['profile_pic'])): ?><div class="invalid-feedback d-block"><?= $errors['profile_pic']; ?></div><?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <input type="password" name="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" placeholder="Password (5-15 characters)">
                        <?php if (isset($errors['password'])): ?><div class="invalid-feedback"><?= $errors['password']; ?></div><?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <input type="password" name="confirm_password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" placeholder="Confirm Password">
                        <?php if (isset($errors['confirm_password'])): ?><div class="invalid-feedback"><?= $errors['confirm_password']; ?></div><?php endif; ?>
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary">Register</button>
                    </div>
                </form>

                <div class="text-center">
                    Already have an account? <a href="login.php">Login here</a>
                </div>
            </div>
        </div>
    </div>

    <script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        const fileInput = document.getElementById('profile_pic');
        const fileChosen = document.getElementById('file-chosen');

        fileInput.addEventListener('change', function() {
            fileChosen.textContent = this.files[0] ? this.files[0].name : "No file chosen";
        });

        $(document).ready(function() {

            // Show chosen file name
            $("#profile_pic").on("change", function() {
                $("#file-chosen").text(this.files[0] ? this.files[0].name : "No file chosen");
            });

            // Main validation function
            function validateField(field) {
                let value = field.val().trim();
                let name = field.attr("name");
                let errorBox = field.siblings(".invalid-feedback");

                // Create error box if missing (Bootstrap requires it)
                if (errorBox.length === 0) {
                    field.after('<div class="invalid-feedback"></div>');
                    errorBox = field.siblings(".invalid-feedback");
                }

                // Reset error
                field.removeClass("is-invalid");
                errorBox.text("");

                // FIRST NAME & LAST NAME
                if (name === "first_name" || name === "last_name") {
                    if (value === "") return setError("This field is required");
                    if (!/^[a-zA-Z-' ]+$/.test(value)) return setError("Only letters and spaces allowed");
                }

                // EMAIL
                if (name === "email") {
                    if (value === "") return setError("Email is required");
                    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) return setError("Invalid email format");
                }

                // PHONE
                if (name === "phone") {
                    if (!/^[0-9]{11}$/.test(value))
                        return setError("Phone number must be exactly 11 digits");
                }

                // ADDRESS
                if (name === "address") {
                    if (value === "") return setError("Address is required");
                }

                // GENDER (select)
                if (name === "gender") {
                    if (value === "") return setError("Please select your gender");
                }

                // PASSWORD
                if (name === "password") {
                    if (value.length < 5 || value.length > 15)
                        return setError("Password must be 5–15 characters");
                }

                // CONFIRM PASSWORD
                if (name === "confirm_password") {
                    let password = $("input[name='password']").val();
                    if (value !== password) return setError("Passwords do not match");
                }

                // PROFILE PIC (live front-end check)
                if (name === "profile_pic") {
                    let file = field[0].files[0];
                    if (!file) return setError("Please upload a profile picture");

                    let fileType = file.type;
                    let fileSize = file.size;

                    if (!["image/jpeg", "image/png", "image/jpg"].includes(fileType))
                        return setError("Only JPG, JPEG, PNG allowed");

                    if (fileSize > 2 * 1024 * 1024)
                        return setError("File too large. Max 2MB");
                }

                // No error
                function setError(msg) {
                    field.addClass("is-invalid");
                    errorBox.text(msg);
                    return false;
                }

                return true;
            }

            // Live validation (every input & select)
            $("input, select").on("input change", function() {
                validateField($(this));
            });

            // Validate all fields before submit
            $("form").on("submit", function(e) {
                let hasError = false;

                $("input, select").each(function() {
                    if (!validateField($(this))) hasError = true;
                });

                if (hasError) e.preventDefault();
            });

        });
    </script>
</body>

</html>