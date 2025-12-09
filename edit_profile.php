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
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $current_profile_pic = $user['profile_pic'];

    // Validate first name
    if (empty($first_name)) {
        $errors['first_name'] = "First name is required";
    }

    // Validate last name
    if (empty($last_name)) {
        $errors['last_name'] = "Last name is required";
    }

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
        $update_stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, phone = ?, address = ?, profile_pic = ? WHERE user_id = ?");
        $update_stmt->bind_param("sssssi", $first_name, $last_name, $phone, $address, $current_profile_pic, $user_id);
        
        if ($update_stmt->execute()) {
            $_SESSION['profile_pic'] = $current_profile_pic;
            $success = "Profile updated successfully!";
            // Refresh user data
            $user['first_name'] = $first_name;
            $user['last_name'] = $last_name;
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
    <style>
        .profile-page {
            background: linear-gradient(135deg, #B4DEBD 0%, #FFF7DD 100%);
            min-height: 100vh;
            padding: 40px 0;
        }

        .profile-form-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .profile-form-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        .profile-form-header {
            background: linear-gradient(135deg, #80A1BA 0%, #6b8fa8 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .profile-form-header h2 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 600;
        }

        .profile-form-header p {
            margin: 8px 0 0 0;
            opacity: 0.9;
            font-size: 0.95rem;
        }

        .profile-pic-section {
            text-align: center;
            padding: 30px;
            background: #f8f9fa;
            border-bottom: 2px solid #e9ecef;
        }

        .profile-pic-wrapper {
            display: inline-block;
            position: relative;
        }

        .profile-pic-display {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .profile-form-body {
            padding: 40px;
        }

        .form-section {
            margin-bottom: 35px;
        }

        .form-section:last-of-type {
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #80A1BA;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            font-size: 1.3rem;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-label i {
            color: #80A1BA;
            font-size: 1.1rem;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #80A1BA;
            box-shadow: 0 0 0 0.2rem rgba(128, 161, 186, 0.25);
        }

        .form-control.is-invalid {
            border-color: #dc3545;
        }

        .invalid-feedback {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 5px;
        }

        .file-upload-section {
            background: #f8f9fa;
            border: 2px dashed #80A1BA;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .file-upload-section:hover {
            background: #e8f1f5;
            border-color: #6b8fa8;
        }

        .file-label {
            display: inline-block;
            background: linear-gradient(135deg, #80A1BA 0%, #6b8fa8 100%);
            color: white;
            padding: 10px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .file-label:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(128, 161, 186, 0.3);
        }

        #file-chosen {
            display: block;
            margin-top: 10px;
            color: #6c757d;
            font-size: 0.9rem;
        }

        .alert-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-danger {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            padding-top: 30px;
            border-top: 2px solid #e9ecef;
        }

        .btn-form {
            flex: 1;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s ease;
            border: none;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
        }

        .btn-form-primary {
            background: linear-gradient(135deg, #80A1BA 0%, #6b8fa8 100%);
            color: white;
        }

        .btn-form-primary:hover {
            background: linear-gradient(135deg, #6b8fa8 0%, #5a7a91 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(128, 161, 186, 0.3);
            color: white;
        }

        .btn-form-secondary {
            background: white;
            color: #80A1BA;
            border: 2px solid #80A1BA;
        }

        .btn-form-secondary:hover {
            background: #80A1BA;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(128, 161, 186, 0.3);
        }

        .row.form-row {
            margin: 0 -10px;
        }

        .row.form-row > .col-md-6 {
            padding: 0 10px;
        }

        @media (max-width: 768px) {
            .profile-form-body {
                padding: 30px 20px;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn-form {
                width: 100%;
            }
        }
    </style>
</head>

<body class="profile-page">
    <div class="profile-form-container">
        <div class="profile-form-card">
            <!-- Header -->
            <div class="profile-form-header">
                <h2>Edit Your Profile</h2>
                <p>Update your personal information and settings</p>
            </div>

            <!-- Profile Picture Section -->
            <div class="profile-pic-section">
                <div class="profile-pic-wrapper">
                    <img src="upload/<?= htmlspecialchars($user['profile_pic']); ?>"
                        alt="Profile Picture"
                        class="profile-pic-display"
                        id="preview-image">
                </div>
            </div>

            <!-- Form Body -->
            <div class="profile-form-body">
                <?php if (!empty($success)): ?>
                    <div class="alert-success">
                        <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors['general'])): ?>
                    <div class="alert-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($errors['general']); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" enctype="multipart/form-data">
                    <!-- Profile Picture Upload Section -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="bi bi-camera"></i>
                            <span>Profile Picture</span>
                        </div>
                        
                        <div class="file-upload-section">
                            <label class="file-label">
                                <i class="bi bi-cloud-upload me-2"></i>Choose New Picture
                                <input type="file" id="profile_pic" name="profile_pic" accept="image/*" hidden>
                            </label>
                            <span id="file-chosen">No file chosen (Max: 2MB)</span>
                        </div>
                        <?php if (isset($errors['profile_pic'])): ?>
                            <div class="invalid-feedback d-block mt-2"><?= $errors['profile_pic']; ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Personal Information Section -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="bi bi-person-circle"></i>
                            <span>Personal Information</span>
                        </div>
                        
                        <div class="row form-row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="bi bi-person"></i>
                                        First Name
                                    </label>
                                    <input type="text" 
                                           name="first_name" 
                                           class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>" 
                                           placeholder="Enter first name" 
                                           value="<?= htmlspecialchars($user['first_name']); ?>">
                                    <?php if (isset($errors['first_name'])): ?>
                                        <div class="invalid-feedback"><?= $errors['first_name']; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="bi bi-person"></i>
                                        Last Name
                                    </label>
                                    <input type="text" 
                                           name="last_name" 
                                           class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>" 
                                           placeholder="Enter last name" 
                                           value="<?= htmlspecialchars($user['last_name']); ?>">
                                    <?php if (isset($errors['last_name'])): ?>
                                        <div class="invalid-feedback"><?= $errors['last_name']; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information Section -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="bi bi-telephone"></i>
                            <span>Contact Information</span>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                <i class="bi bi-phone"></i>
                                Phone Number
                            </label>
                            <input type="text" 
                                   name="phone" 
                                   class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" 
                                   placeholder="09XXXXXXXXX" 
                                   value="<?= htmlspecialchars($user['phone']); ?>"
                                   maxlength="11">
                            <?php if (isset($errors['phone'])): ?>
                                <div class="invalid-feedback"><?= $errors['phone']; ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="bi bi-geo-alt"></i>
                                Address
                            </label>
                            <textarea name="address" 
                                      rows="3"
                                      class="form-control <?= isset($errors['address']) ? 'is-invalid' : '' ?>" 
                                      placeholder="Enter your complete address"><?= htmlspecialchars($user['address']); ?></textarea>
                            <?php if (isset($errors['address'])): ?>
                                <div class="invalid-feedback"><?= $errors['address']; ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="form-actions">
                        <a href="profile.php" class="btn-form btn-form-secondary">
                            <i class="bi bi-x-circle"></i>
                            Cancel
                        </a>
                        <button type="submit" class="btn-form btn-form-primary">
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
                
                if (file.size > 2000000) {
                    fileChosen.textContent = `⚠️ ${file.name} (${fileSize}KB - Too large!)`;
                    fileChosen.style.color = '#dc3545';
                } else {
                    fileChosen.textContent = `✓ ${file.name} (${fileSize}KB)`;
                    fileChosen.style.color = '#28a745';
                    
                    // Preview the image
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImage.src = e.target.result;
                    }
                    reader.readAsDataURL(file);
                }
            } else {
                fileChosen.textContent = "No file chosen (Max: 2MB)";
                fileChosen.style.color = '#6c757d';
            }
        });
    </script>
</body>

</html>