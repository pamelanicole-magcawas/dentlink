<?php
session_start();
include("db_connect.php");

$first_name = $last_name = $email = $phone = $address = $password = $confirm = "";
$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $email      = trim($_POST['email']);
    $phone      = trim($_POST['phone']);
    $address    = trim($_POST['address']);
    $password   = trim($_POST['password']);
    $confirm    = trim($_POST['confirm_password']);
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

    if (empty($password)) $errors['password'] = "Password is required";
    elseif (!preg_match("/^.{5,12}$/", $password)) $errors['password'] = "Password must be 5–12 characters";

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
        if ($_FILES["profile_pic"]["size"] > 900000) $errors['profile_pic'] = "File too large. Max 900KB";
        if (!in_array($imageFileType, ['jpg', 'jpeg', 'png'])) $errors['profile_pic'] = "Only JPG, JPEG, PNG allowed";
    }

    if (empty($errors)) {
        if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $check = $conn->prepare("SELECT * FROM users WHERE email=?");
            $check->bind_param("s", $email);
            $check->execute();
            $result = $check->get_result();

            if ($result->num_rows > 0) {
                $errors['email'] = "Email already registered";
                unlink($target_file);
            } else {
                $stmt = $conn->prepare("INSERT INTO users (first_name,last_name,email,phone,address,password,role,profile_pic) VALUES (?,?,?,?,?,?,?,?)");
                $stmt->bind_param("ssssssss", $first_name, $last_name, $email, $phone, $address, $hashed_password, $role, $file_name);

                if ($stmt->execute()) {
                    $success = "Registration successful! You can now log in.";
                } else {
                    $errors['general'] = "Database error: " . $stmt->error;
                    unlink($target_file);
                }
                $stmt->close();
            }
            $check->close();
        } else {
            $errors['profile_pic'] = "Error uploading file";
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="credentials.css">
</head>
<body>
    <div class="container-fluid login-container d-flex flex-column flex-lg-row min-vh-100 p-0">
        <div class="left-side d-flex flex-column justify-content-center align-items-center text-center p-5">
            <img src="dentlink-logo.png" alt="DentLink Logo">
            <p class="mt-3"><strong>DentLink: Dental Clinic Digital Appointment and Patient Records Management System</strong></p>
            <div class="info-box mt-3">
                <p><strong>DentLink</strong> simplifies dental appointment scheduling and patient record management. Patients can book online, check time slots, and get email notifications.</p>
                <p>The system ensures accurate record-keeping by tracking treatment histories and identifying new or returning patients for reliable dental services.</p>
            </div>
        </div>

        <div class="right-side d-flex justify-content-center align-items-center p-5">
            <div class="form-box w-100" style="max-width:400px;">
                <h2 class="mb-4 text-center">Registration Form</h2>
                <?php if (!empty($errors['general'])): ?>
                    <p class="message text-center"><?= htmlspecialchars($errors['general']); ?></p>
                <?php elseif (!empty($success)): ?>
                    <p class="success"><?= htmlspecialchars($success); ?></p>
                <?php endif; ?>

                <form method="POST" action="" enctype="multipart/form-data">
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
                        <input type="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" placeholder="Email" value="<?= htmlspecialchars($email); ?>">
                        <?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?= $errors['email']; ?></div><?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <input type="text" name="phone" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" placeholder="Phone Number" value="<?= htmlspecialchars($phone); ?>">
                        <?php if (isset($errors['phone'])): ?><div class="invalid-feedback"><?= $errors['phone']; ?></div><?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <input type="text" name="address" class="form-control <?= isset($errors['address']) ? 'is-invalid' : '' ?>" placeholder="Address" value="<?= htmlspecialchars($address); ?>">
                        <?php if (isset($errors['address'])): ?><div class="invalid-feedback"><?= $errors['address']; ?></div><?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label file-label btn btn-primary">
                            Choose Profile Picture
                            <input type="file" id="profile_pic" name="profile_pic" accept="image/*" hidden class="<?= isset($errors['profile_pic']) ? 'is-invalid' : '' ?>">
                        </label>
                        <span id="file-chosen" class="ms-2">No file chosen</span>
                        <?php if (isset($errors['profile_pic'])): ?><div class="invalid-feedback d-block"><?= $errors['profile_pic']; ?></div><?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <input type="password" name="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" placeholder="Password">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const fileInput = document.getElementById('profile_pic');
        const fileChosen = document.getElementById('file-chosen');

        fileInput.addEventListener('change', function() {
            fileChosen.textContent = this.files[0] ? this.files[0].name : "No file chosen";
        });
    </script>

</body>
</html>
