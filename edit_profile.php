<?php
session_start();
include("db_connect.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user details
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
} else {
    session_destroy();
    header("Location: login.php");
    exit();
}

$errors = [];
$success = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $current_profile_pic = $user['profile_pic'];

    // Validate phone
    if (empty($phone)) {
        $errors['phone'] = "Phone number is required";
    } elseif (!preg_match("/^[0-9]{11}$/", $phone)) {
        $errors['phone'] = "Phone number must be exactly 11 digits";
    }

    // Validate address
    if (empty($address)) {
        $errors['address'] = "Address is required";
    }

    // Handle profile picture upload
    if (!empty($_FILES["profile_pic"]["name"])) {
        $target_dir = "upload/";
        $file_name = basename($_FILES["profile_pic"]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $check = getimagesize($_FILES["profile_pic"]["tmp_name"]);
        if ($check === false) {
            $errors['profile_pic'] = "File is not a valid image";
        } elseif ($_FILES["profile_pic"]["size"] > 2000000) {
            $errors['profile_pic'] = "File too large. Max 2MB";
        } elseif (!in_array($imageFileType, ['jpg', 'jpeg', 'png'])) {
            $errors['profile_pic'] = "Only JPG, JPEG, PNG allowed";
        } else {
            // Delete old profile picture if different
            if ($current_profile_pic && file_exists($target_dir . $current_profile_pic)) {
                unlink($target_dir . $current_profile_pic);
            }
            
            if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
                $current_profile_pic = $file_name;
            } else {
                $errors['profile_pic'] = "Error uploading file";
            }
        }
    }

    // Update if no errors
    if (empty($errors)) {
        $update_stmt = $conn->prepare("UPDATE users SET phone = ?, address = ?, profile_pic = ? WHERE user_id = ?");
        $update_stmt->bind_param("sssi", $phone, $address, $current_profile_pic, $user_id);
        
        if ($update_stmt->execute()) {
            $_SESSION['profile_pic'] = $current_profile_pic;
            $success = "Profile updated successfully!";
            // Refresh user data
            $user['phone'] = $phone;
            $user['address'] = $address;
            $user['profile_pic'] = $current_profile_pic;
        } else {
            $errors['general'] = "Database error: " . $update_stmt->error;
        }
        $update_stmt->close();
    }
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - DentLink</title>
    <link rel="stylesheet" href="bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="credentials.css">
    <link rel="stylesheet" href="dashboard.css">
</head>

<body class="profile-page">
    <!-- Navbar matching dashboard.php -->
    <nav class="navbar navbar-expand-lg bg-white sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center" href="dashboard.php" style="color: #80A1BA;">
                <img src="dentlink-logo.png" alt="Logo" width="50" height="45" class="me-2">
                <span style="font-size: 1.5rem;">DentLink</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link px-3" href="dashboard.php">Home</a>
                    </li>
                    <li class="nav-item dropdown ms-3">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                            <img src="upload/<?= htmlspecialchars($user['profile_pic']); ?>" 
                                 alt="Profile" 
                                 style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover; margin-right: 8px;">
                            <?php echo htmlspecialchars($user['first_name']); ?>
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

    <div class="profile-container">
        <div class="profile-card">
            
            <div class="profile-header">
                <div class="profile-image-container">
                    <img src="upload/<?= htmlspecialchars($user['profile_pic']); ?>"
                        alt="Profile Picture"
                        class="profile-image"
                        id="preview-image">
                </div>
                <h2 class="profile-name">
                    Edit Your Profile
                </h2>
            </div>

            <div class="profile-body">
                <?php if (!empty($success)): ?>
                    <div class="success"><?= htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <?php if (!empty($errors['general'])): ?>
                    <div class="message"><?= htmlspecialchars($errors['general']); ?></div>
                <?php endif; ?>

                <form method="POST" action="" enctype="multipart/form-data">
                    <!-- Profile Picture -->
                    <div class="mb-3">
                        <label class="profile-info-label d-block mb-2">
                            <i class="bi bi-camera-fill me-2"></i>Profile Picture
                        </label>
                        <label class="file-label">
                            Choose New Picture
                            <input type="file" id="profile_pic" name="profile_pic" accept="image/*" hidden>
                        </label>
                        <span id="file-chosen" class="ms-2">No file chosen (Max: 900KB)</span>
                        <?php if (isset($errors['profile_pic'])): ?>
                            <div class="invalid-feedback d-block"><?= $errors['profile_pic']; ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Phone Number -->
                    <div class="mb-3">
                        <label class="profile-info-label d-block mb-2">
                            <i class="bi bi-telephone-fill me-2"></i>Phone Number
                        </label>
                        <input type="text" 
                               name="phone" 
                               class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" 
                               placeholder="09XXXXXXXXX" 
                               value="<?= htmlspecialchars($user['phone']); ?>">
                        <?php if (isset($errors['phone'])): ?>
                            <div class="invalid-feedback"><?= $errors['phone']; ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Address -->
                    <div class="mb-3">
                        <label class="profile-info-label d-block mb-2">
                            <i class="bi bi-geo-alt-fill me-2"></i>Address
                        </label>
                        <textarea name="address" 
                                  rows="3"
                                  class="form-control <?= isset($errors['address']) ? 'is-invalid' : '' ?>" 
                                  placeholder="Complete Address"><?= htmlspecialchars($user['address']); ?></textarea>
                        <?php if (isset($errors['address'])): ?>
                            <div class="invalid-feedback"><?= $errors['address']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="profile-actions">
                        <a href="profile.php" class="btn-profile btn-profile-outline">
                            <i class="bi bi-x-circle"></i>
                            Back to Profile
                        </a>
                        <button type="submit" class="btn-profile btn-profile-primary">
                            <i class="bi bi-check-circle"></i>
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // File input preview
        const fileInput = document.getElementById('profile_pic');
        const fileChosen = document.getElementById('file-chosen');
        const previewImage = document.getElementById('preview-image');

        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                const fileSize = (file.size / 1024).toFixed(2);
                
                if (file.size > 900000) {
                    fileChosen.textContent = `⚠️ ${file.name} (${fileSize}KB - Too large!)`;
                    fileChosen.style.color = '#dc3545';
                } else {
                    fileChosen.textContent = `${file.name} (${fileSize}KB)`;
                    fileChosen.style.color = '#28a745';
                    
                    // Preview the image
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImage.src = e.target.result;
                    }
                    reader.readAsDataURL(file);
                }
            } else {
                fileChosen.textContent = "No file chosen (Max: 900KB)";
                fileChosen.style.color = '#666';
            }
        });
    </script>
</body>

</html>