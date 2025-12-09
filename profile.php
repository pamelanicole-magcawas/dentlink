<?php
session_start();
include("db_connect.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user details from the database
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT user_id, first_name, last_name, email, phone, address, profile_pic, gender, created_at 
    FROM users WHERE user_id = ? AND role='Patient' LIMIT 1
");
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

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - DentLink</title>
    <link rel="stylesheet" href="bootstrap-5.3.3-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
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

        .form-control-static {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 16px;
            color: #212529;
            font-size: 0.95rem;
            min-height: 45px;
            display: flex;
            align-items: center;
        }

        .form-control-static.readonly {
            background-color: #e9ecef;
            color: #6c757d;
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

        .row.form-row>.col-md-6 {
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
                <h2>Profile Information</h2>
                <p>View your personal details and account information</p>
            </div>

            <!-- Profile Picture Section -->
            <div class="profile-pic-section">
                <div class="profile-pic-wrapper">
                    <img src="upload/<?= htmlspecialchars($user['profile_pic']); ?>"
                        alt="Profile Picture"
                        class="profile-pic-display">
                </div>
            </div>

            <!-- Form Body -->
            <div class="profile-form-body">
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
                                <div class="form-control-static">
                                    <?= htmlspecialchars($user['first_name']); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="bi bi-person"></i>
                                    Last Name
                                </label>
                                <div class="form-control-static">
                                    <?= htmlspecialchars($user['last_name']); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row form-row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="bi bi-gender-ambiguous"></i>
                                    Gender
                                </label>
                                <div class="form-control-static">
                                    <?= htmlspecialchars($user['gender']); ?>
                                </div>
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
                            <i class="bi bi-envelope"></i>
                            Email Address
                        </label>
                        <div class="form-control-static">
                            <?= htmlspecialchars($user['email']); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-phone"></i>
                            Phone Number
                        </label>
                        <div class="form-control-static">
                            <?= htmlspecialchars($user['phone']); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-geo-alt"></i>
                            Address
                        </label>
                        <div class="form-control-static">
                            <?= htmlspecialchars($user['address']); ?>
                        </div>
                    </div>
                </div>

                <!-- Account Information Section -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="bi bi-info-circle"></i>
                        <span>Account Information</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-calendar-check"></i>
                            Member Since
                        </label>
                        <div class="form-control-static readonly">
                            <?= date('F d, Y', strtotime($user['created_at'])); ?>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="form-actions">
                    <a href="dashboard.php" class="btn-form btn-form-secondary">
                        <i class="bi bi-arrow-left"></i>
                        Back
                    </a>
                    <a href="edit_profile.php" class="btn-form btn-form-primary">
                        <i class="bi bi-pencil-square"></i>
                        Edit Profile
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>