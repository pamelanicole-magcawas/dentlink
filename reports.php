<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #80A1BA;
            --secondary: #91C4C3;
            --accent: #B4DEBD;
            --light: #FFF7DD;
            --white: #ffffff;
            --text-dark: #2c3e50;
            --text-light: #6c757d;
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.08);
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.1);
            --radius: 12px;
            --radius-sm: 8px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--accent) 0%, var(--light) 100%);
            min-height: 100vh;
            padding: 40px 20px;
            color: var(--text-dark);
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .page-header {
            background: var(--white);
            padding: 30px 40px;
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
            margin-bottom: 30px;
        }

        .page-header h2 {
            color: var(--primary);
            font-size: 32px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .filter-section {
            background: var(--white);
            padding: 30px 40px;
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
            margin-bottom: 30px;
        }

        .filter-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        label {
            font-weight: 500;
            font-size: 14px;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        label i {
            color: var(--primary);
            font-size: 16px;
        }

        input,
        select {
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: var(--radius-sm);
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            background: var(--white);
            transition: all 0.3s ease;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(128, 161, 186, 0.1);
        }

        .btn-back-dashboard {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 12px;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            margin-bottom: 20px;
        }

        .btn-back-dashboard:hover {
            background: var(--primary);
            color: white;
            transform: translateX(-3px);
            box-shadow: 0 4px 12px rgba(128, 161, 186, 0.25);
        }

        .btn {
            padding: 12px 32px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            border: none;
            cursor: pointer;
            border-radius: var(--radius-sm);
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .report-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 25px 40px;
            border-radius: var(--radius);
            margin-bottom: 30px;
            box-shadow: var(--shadow-md);
        }

        .report-header h3 {
            font-size: 24px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .summary-box {
            background: var(--white);
            padding: 25px;
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            border-left: 4px solid var(--primary);
        }

        .summary-box-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--primary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .summary-box p {
            margin: 12px 0;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .summary-box p:last-child {
            border-bottom: none;
        }

        .summary-box p i {
            font-size: 20px;
            color: var(--secondary);
            width: 24px;
            text-align: center;
        }

        .stat-value {
            margin-left: auto;
            font-weight: 700;
            color: var(--primary);
            font-size: 18px;
        }

        .table-container {
            background: var(--white);
            padding: 30px;
            border-radius: var(--radius);
            margin-bottom: 30px;
            box-shadow: var(--shadow-sm);
            overflow-x: auto;
        }

        .table-container h4 {
            font-size: 20px;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
        }

        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 12px 15px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }

        tbody tr:hover {
            background: #f8f9fa;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-approved {
            background: rgba(74, 222, 128, 0.2);
            color: #059669;
        }

        .status-pending {
            background: rgba(251, 191, 36, 0.2);
            color: #d97706;
        }

        .status-denied {
            background: rgba(248, 113, 113, 0.2);
            color: #dc2626;
        }

        .status-completed {
            background: rgba(74, 222, 128, 0.2);
            color: #059669;
        }

        .status-checked-in {
            background: rgba(96, 165, 250, 0.2);
            color: #2563eb;
        }

        @media (max-width: 768px) {
            body {
                padding: 20px 15px;
            }

            .page-header,
            .filter-section,
            .table-container {
                padding: 20px;
            }

            .page-header h2 {
                font-size: 24px;
            }

            form {
                grid-template-columns: 1fr;
            }

            .summary-grid {
                grid-template-columns: 1fr;
            }

            .table-container {
                overflow-x: auto;
            }

            table {
                font-size: 12px;
            }

            td, th {
                padding: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <a href="admin_dashboard.php" class="btn-back-dashboard">
            <i class="bi bi-arrow-left"></i> Back 
        </a>

        <div class="page-header">
            <h2><i class="bi bi-graph-up-arrow"></i> Reports</h2>
        </div>

        <div class="filter-section">
            <div class="filter-title"><i class="bi bi-funnel-fill"></i> Generate Report</div>
            <form method="GET" action="">
                <div class="form-group">
                    <label><i class="bi bi-calendar-event"></i> From Date</label>
                    <input type="date" name="from_date" required>
                </div>
                <div class="form-group">
                    <label><i class="bi bi-calendar-check"></i> To Date</label>
                    <input type="date" name="to_date" required>
                </div>
                <div class="form-group">
                    <label><i class="bi bi-file-earmark-text"></i> Report Type</label>
                    <select name="report_type" required>
                        <option value="overview">Overview (All)</option>
                        <option value="users">Users</option>
                        <option value="services">Services</option>
                        <option value="appointments">Appointments</option>
                        <option value="reviews">Reviews</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn"><i class="bi bi-table"></i> Generate Report</button>
                </div>
            </form>
        </div>

        <?php
        if (isset($_GET['from_date'], $_GET['to_date'], $_GET['report_type'])) {
            $from = $_GET['from_date'];
            $to = $_GET['to_date'];
            $type = $_GET['report_type'];

            echo "<div class='report-header'>
                <h3><i class='bi bi-table'></i> " . strtoupper($type) . " REPORT: $from to $to</h3>
            </div>";

            // USERS TABLE REPORT
            if ($type == "users" || $type == "overview") {
                $sql_users = $conn->prepare("
                    SELECT user_id, first_name, last_name, email, gender, phone, created_at 
                    FROM users 
                    WHERE role = 'Patient' 
                    AND DATE(created_at) BETWEEN ? AND ?
                    ORDER BY created_at DESC
                ");
                $sql_users->bind_param("ss", $from, $to);
                $sql_users->execute();
                $users_result = $sql_users->get_result();

                $total_users = $male_users = $female_users = 0;
                $users_data = [];
                while ($u = $users_result->fetch_assoc()) {
                    $total_users++;
                    $users_data[] = $u;
                    if ($u['gender'] == 'Male') $male_users++;
                    if ($u['gender'] == 'Female') $female_users++;
                }

                echo "<div class='summary-grid'>
                    <div class='summary-box'>
                        <div class='summary-box-title'><i class='bi bi-people-fill'></i> User Statistics</div>
                        <p><i class='bi bi-people-fill'></i> Total Users <span class='stat-value'>$total_users</span></p>
                        <p><i class='bi bi-gender-male'></i> Male Users <span class='stat-value'>$male_users</span></p>
                        <p><i class='bi bi-gender-female'></i> Female Users <span class='stat-value'>$female_users</span></p>
                    </div>
                </div>";

                echo "<div class='table-container'>
                    <h4><i class='bi bi-table'></i> Users List</h4>
                    <table>
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Gender</th>
                                <th>Registered</th>
                            </tr>
                        </thead>
                        <tbody>";
                        
                foreach ($users_data as $user) {
                    $registered_date = date('M d, Y', strtotime($user['created_at']));
                    echo "<tr>
                        <td>{$user['user_id']}</td>
                        <td>{$user['first_name']} {$user['last_name']}</td>
                        <td>{$user['email']}</td>
                        <td>{$user['phone']}</td>
                        <td>{$user['gender']}</td>
                        <td>$registered_date</td>
                    </tr>";
                }
                
                echo "</tbody>
                    </table>
                </div>";
            }

            // SERVICES TABLE REPORT
            if ($type == "services" || $type == "overview") {
                $sql_services = $conn->prepare("
                    SELECT description AS service, location, COUNT(*) AS total, status
                    FROM appointments
                    WHERE `date` BETWEEN ? AND ?
                    GROUP BY description, location, status
                    ORDER BY total DESC
                ");
                $sql_services->bind_param("ss", $from, $to);
                $sql_services->execute();
                $services_result = $sql_services->get_result();

                $service_labels = [];
                $service_data = [];
                $services_table = [];
                while ($s = $services_result->fetch_assoc()) {
                    $service_labels[] = $s['service'];
                    $service_data[] = $s['total'];
                    $services_table[] = $s;
                }

                $total_services = array_sum($service_data);
                $top_service = count($services_table) > 0 ? $services_table[0]['service'] : 'N/A';

                echo "<div class='summary-grid'>
                    <div class='summary-box'>
                        <div class='summary-box-title'><i class='bi bi-clipboard-pulse'></i> Service Statistics</div>
                        <p><i class='bi bi-list-task'></i> Total Procedures <span class='stat-value'>$total_services</span></p>
                        <p><i class='bi bi-award-fill'></i> Top Service <span class='stat-value'>$top_service</span></p>
                    </div>
                </div>";

                echo "<div class='table-container'>
                    <h4><i class='bi bi-table'></i> Services Report</h4>
                    <table>
                        <thead>
                            <tr>
                                <th>Service</th>
                                <th>Location</th>
                                <th>Total Appointments</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>";

                foreach ($services_table as $service) {
                    $status_class = 'status-' . strtolower($service['status']);
                    echo "<tr>
                        <td>{$service['service']}</td>
                        <td>{$service['location']}</td>
                        <td>{$service['total']}</td>
                        <td><span class='status-badge $status_class'>{$service['status']}</span></td>
                    </tr>";
                }

                echo "</tbody>
                    </table>
                </div>";
            }

            // APPOINTMENTS TABLE REPORT
            if ($type == "appointments" || $type == "overview") {
                $stmt = $conn->prepare("
                    SELECT a.id, a.date, a.description, a.status, u.first_name, u.last_name
                    FROM appointments a
                    LEFT JOIN users u ON a.user_id = u.user_id
                    WHERE a.`date` BETWEEN ? AND ?
                    ORDER BY a.date DESC
                ");

                $stmt->bind_param("ss", $from, $to);
                $stmt->execute();
                $appts_result = $stmt->get_result();

                $total = $approved = $pending = $denied = $checked_in = $completed = 0;
                $appts_data = [];

                while ($a = $appts_result->fetch_assoc()) {
                    $total++;
                    $appts_data[] = $a;
                    switch ($a['status']) {
                        case "approved":
                            $approved++;
                            break;
                        case "pending":
                            $pending++;
                            break;
                        case "denied":
                            $denied++;
                            break;
                        case "checked-in":
                            $checked_in++;
                            break;
                        case "completed":
                            $completed++;
                            break;
                    }
                }

                echo "<div class='summary-grid'>
                    <div class='summary-box'>
                        <div class='summary-box-title'><i class='bi bi-calendar-check'></i> Appointment Statistics</div>
                        <p><i class='bi bi-calendar-check-fill'></i> Total <span class='stat-value'>$total</span></p>
                        <p><i class='bi bi-check-circle-fill'></i> Approved <span class='stat-value'>$approved</span></p>
                        <p><i class='bi bi-clock-fill'></i> Pending <span class='stat-value'>$pending</span></p>
                        <p><i class='bi bi-x-circle-fill'></i> Denied <span class='stat-value'>$denied</span></p>
                    </div>
                </div>";

                echo "<div class='table-container'>
                    <h4><i class='bi bi-table'></i> Appointments List</h4>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Patient Name</th>
                                <th>Service</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>";

                foreach ($appts_data as $apt) {
                    $status_class = 'status-' . str_replace(' ', '-', strtolower($apt['status']));
                    $apt_date = date('M d, Y', strtotime($apt['date']));
                    $patient_name = $apt['first_name'] . ' ' . $apt['last_name'];
                    echo "<tr>
                        <td>{$apt['id']}</td>
                        <td>$patient_name</td>
                        <td>{$apt['description']}</td>
                        <td>$apt_date</td>
                        <td><span class='status-badge $status_class'>{$apt['status']}</span></td>
                    </tr>";
                }

                echo "</tbody>
                    </table>
                </div>";
            }

            // REVIEWS TABLE REPORT
            if ($type == "reviews" || $type == "overview") {
                $stmt = $conn->prepare("
                    SELECT r.review_id, r.rating, r.review_text, r.created_at, u.first_name
                    FROM reviews r
                    JOIN users u ON r.user_id = u.user_id
                    WHERE DATE(r.created_at) BETWEEN ? AND ?
                    ORDER BY r.created_at DESC
                ");

                $stmt->bind_param("ss", $from, $to);
                $stmt->execute();
                $reviews_result = $stmt->get_result();

                $total_reviews = 0;
                $reviews_data = [];
                $avg_rating = 0;

                while ($r = $reviews_result->fetch_assoc()) {
                    $total_reviews++;
                    $reviews_data[] = $r;
                    $avg_rating += $r['rating'];
                }

                $avg_rating = $total_reviews > 0 ? round($avg_rating / $total_reviews, 1) : 0;

                echo "<div class='summary-grid'>
                    <div class='summary-box'>
                        <div class='summary-box-title'><i class='bi bi-star-fill'></i> Reviews Statistics</div>
                        <p><i class='bi bi-chat-quote'></i> Total Reviews <span class='stat-value'>$total_reviews</span></p>
                        <p><i class='bi bi-star-fill'></i> Average Rating <span class='stat-value'>$avg_rating / 5</span></p>
                    </div>
                </div>";

                echo "<div class='table-container'>
                    <h4><i class='bi bi-table'></i> Reviews List</h4>
                    <table>
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Rating</th>
                                <th>Review</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>";

                foreach ($reviews_data as $review) {
                    $review_date = date('M d, Y', strtotime($review['created_at']));
                    $stars = str_repeat('⭐', $review['rating']);
                    echo "<tr>
                        <td>{$review['first_name']}</td>
                        <td>$stars ({$review['rating']}/5)</td>
                        <td>" . substr($review['review_text'], 0, 100) . "...</td>
                        <td>$review_date</td>
                    </tr>";
                }

                echo "</tbody>
                    </table>
                </div>";
            }
        }
        ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
