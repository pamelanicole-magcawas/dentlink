<?php
include 'db_connect.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Reports</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
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
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            text-decoration: none;
            border-radius: var(--radius-sm);
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-sm);
            margin-bottom: 30px;
        }

        .btn-back-dashboard:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-back-dashboard:active {
            transform: translateY(0);
        }

        .btn-back-dashboard i {
            font-size: 18px;
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

        .btn:active {
            transform: translateY(0);
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

        .chart-container {
            background: var(--white);
            padding: 30px;
            border-radius: var(--radius);
            margin-bottom: 30px;
            box-shadow: var(--shadow-sm);
        }

        .chart-container h4 {
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

        .chart-wrapper {
            position: relative;
            height: 400px;
        }

        @media (max-width: 768px) {
            body {
                padding: 20px 15px;
            }

            .page-header,
            .filter-section,
            .chart-container {
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

            .chart-wrapper {
                height: 300px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <a href="analytics.php" class="btn-back-dashboard">
            <i class="bi bi-arrow-left"></i> Back
        </a>

        <div class="page-header">
            <h2><i class="bi bi-graph-up-arrow"></i> Reports Dashboard</h2>
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
                    <button type="submit" class="btn"><i class="bi bi-bar-chart-fill"></i> Generate Report</button>
                </div>
            </form>
        </div>

        <?php
        if (isset($_GET['from_date'], $_GET['to_date'], $_GET['report_type'])) {
            $from = $_GET['from_date'];
            $to = $_GET['to_date'];
            $type = $_GET['report_type'];

            echo "<div class='report-header'>
            <h3><i class='bi bi-clipboard-data'></i> " . strtoupper($type) . " REPORT: $from to $to</h3>
          </div>";

            // USERS REPORT
            if ($type == "users" || $type == "overview") {
                $sql_users = $conn->prepare("SELECT user_id, gender FROM users WHERE DATE(created_at) BETWEEN ? AND ?");
                $sql_users->bind_param("ss", $from, $to);
                $sql_users->execute();
                $users = $sql_users->get_result();

                $total_users = $male_users = $female_users = 0;
                while ($u = $users->fetch_assoc()) {
                    $total_users++;
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

                echo "<div class='chart-container'>
        <h4><i class='bi bi-pie-chart-fill'></i> User Gender Distribution</h4>
        <div class='chart-wrapper'>
            <canvas id='genderChart'></canvas>
        </div>
    </div>
    <script>
    const genderCtx = document.getElementById('genderChart').getContext('2d');
    new Chart(genderCtx, {
        type: 'pie',
        data: {
            labels: ['Male', 'Female'],
            datasets: [{ data: [$male_users, $female_users], backgroundColor: ['#80A1BA', '#91C4C3'], borderWidth: 3, borderColor: '#fff' }]
        },
        options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ position:'bottom', labels:{ font:{ size:14, family:'Poppins' } } } } }
    });
    </script>";
            }

            // SERVICES REPORT
            if ($type == "services" || $type == "overview") {
                $sql_services = $conn->prepare("
            SELECT description AS service, COUNT(*) AS total, location
            FROM appointments
            WHERE `date` BETWEEN ? AND ?
            GROUP BY description, location
            ORDER BY total DESC
        ");
                $sql_services->bind_param("ss", $from, $to);
                $sql_services->execute();
                $services_result = $sql_services->get_result();

                $service_labels = [];
                $service_data = [];
                $locations = [];
                $top_service = '';

                while ($s = $services_result->fetch_assoc()) {
                    $service_labels[] = $s['service'];
                    $service_data[] = $s['total'];
                    $locations[$s['service']] = $s['location'];
                    if (!$top_service) $top_service = $s['service'];
                }

                if (empty($top_service)) {
                    $top_service = 'N/A';
                }
                $top_location = isset($locations[$top_service]) ? $locations[$top_service] : 'N/A';

                $service_labels_json = json_encode($service_labels);
                $service_data_json = json_encode($service_data);

                echo "<div class='summary-grid'>
                <div class='summary-box'>
                    <div class='summary-box-title'><i class='bi bi-clipboard-pulse'></i> Service Statistics</div>
                    <p><i class='bi bi-list-task'></i> Total Procedures <span class='stat-value'>" . array_sum($service_data) . "</span></p>
                    <p><i class='bi bi-award-fill'></i> Top Service <span class='stat-value'>$top_service</span></p>
                    <p><i class='bi bi-geo-alt-fill'></i> Top Location <span class='stat-value'>$top_location</span></p>
                </div>
              </div>";

                echo "<div class='chart-container'>
                <h4><i class='bi bi-bar-chart-fill'></i> Top Services / Most Chosen Procedures</h4>
                <div class='chart-wrapper'><canvas id='servicesChart'></canvas></div>
              </div>
              <script>
              const servicesCtx = document.getElementById('servicesChart').getContext('2d');
              new Chart(servicesCtx, {
                  type: 'bar',
                  data: { labels: $service_labels_json, datasets: [{ label:'Number of Appointments', data: $service_data_json, backgroundColor:'#80A1BA', borderColor:'#91C4C3', borderWidth:2, borderRadius:8 }] },
                  options:{ responsive:true, maintainAspectRatio:false, scales:{ y:{ beginAtZero:true, ticks:{ font:{ family:'Poppins' } } }, x:{ ticks:{ font:{ family:'Poppins' } } } } }
              });
              </script>";
            }

            // APPOINTMENTS REPORT
            if ($type == "appointments" || $type == "overview") {
                $stmt = $conn->prepare("
                    SELECT a.*, u.gender, d.name AS dentist
                    FROM appointments a
                    LEFT JOIN users u ON a.user_id = u.user_id
                    LEFT JOIN dentists d ON d.id = a.dentist_id
                    WHERE a.`date` BETWEEN ? AND ?
                    ");

                if (!$stmt) {
                    die("Prepare failed: " . $conn->error);
                }
                
                $stmt->bind_param("ss", $from, $to);
                $stmt->execute();
                $appts = $stmt->get_result();

                $total = $approved = $pending = $denied = 0;
                while ($a = $appts->fetch_assoc()) {
                    $total++;
                    if ($a['status'] == "approved") $approved++;
                    if ($a['status'] == "pending") $pending++;
                    if ($a['status'] == "denied") $denied++;
                }

                echo "<div class='summary-grid'>
                <div class='summary-box'>
                    <div class='summary-box-title'><i class='bi bi-calendar-check'></i> Appointment Statistics</div>
                    <p><i class='bi bi-calendar-check-fill'></i> Total Appointments <span class='stat-value'>$total</span></p>
                    <p><i class='bi bi-check-circle-fill'></i> Approved <span class='stat-value'>$approved</span></p>
                    <p><i class='bi bi-clock-fill'></i> Pending <span class='stat-value'>$pending</span></p>
                    <p><i class='bi bi-x-circle-fill'></i> Denied <span class='stat-value'>$denied</span></p>
                </div>
              </div>";

                echo "<div class='chart-container'>
                <h4><i class='bi bi-pie-chart-fill'></i> Appointment Status Breakdown</h4>
                <div class='chart-wrapper'><canvas id='appointmentsChart'></canvas></div>
              </div>
              <script>
              const appointmentsCtx = document.getElementById('appointmentsChart').getContext('2d');
              new Chart(appointmentsCtx, {
                  type:'doughnut',
                  data:{ labels:['Approved','Pending','Denied'], datasets:[{ data:[$approved,$pending,$denied], backgroundColor:['#80A1BA','#91C4C3','#B4DEBD'], borderWidth:3, borderColor:'#fff' }]},
                  options:{ responsive:true, maintainAspectRatio:false, plugins:{ legend:{ position:'bottom', labels:{ font:{ size:14, family:'Poppins' } } } } }
              });
              </script>";
            }

            // REVIEWS REPORT
            if ($type == "reviews" || $type == "overview") {
                $stmt = $conn->prepare("
            SELECT rating, COUNT(*) AS count
            FROM reviews
            WHERE DATE(created_at) BETWEEN ? AND ?
            GROUP BY rating
            ORDER BY rating ASC
        ");
                $stmt->bind_param("ss", $from, $to);
                $stmt->execute();
                $reviews_result = $stmt->get_result();

                $total_reviews = 0;
                $rating_counts = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
                while ($r = $reviews_result->fetch_assoc()) {
                    $rating_counts[$r['rating']] = $r['count'];
                    $total_reviews += $r['count'];
                }

                $average_rating = $total_reviews ?
                    round(array_sum(array_map(fn($rate, $count) => $rate * $count, array_keys($rating_counts), $rating_counts)) / $total_reviews, 2)
                    : 0;

                $ratings_json = json_encode(array_values($rating_counts));

                echo "<div class='summary-grid'>
                <div class='summary-box'>
                    <div class='summary-box-title'><i class='bi bi-star-fill'></i> Reviews Statistics</div>
                    <p><i class='bi bi-star-half'></i> Average Rating <span class='stat-value'>$average_rating</span></p>
                    <p><i class='bi bi-chat-text'></i> Total Reviews <span class='stat-value'>$total_reviews</span></p>
                </div>
              </div>";

                echo "<div class='chart-container'>
                <h4><i class='bi bi-bar-chart-fill'></i> Rating Distribution</h4>
                <div class='chart-wrapper'><canvas id='reviewsChart'></canvas></div>
              </div>
              <script>
              const reviewsCtx = document.getElementById('reviewsChart').getContext('2d');
              new Chart(reviewsCtx,{
                  type:'bar',
                  data:{
                      labels:['1 Star','2 Star','3 Star','4 Star','5 Star'],
                      datasets:[{ label:'Number of Reviews', data:$ratings_json, backgroundColor:'#91C4C3', borderColor:'#80A1BA', borderWidth:2, borderRadius:8 }]
                  },
                  options:{ responsive:true, maintainAspectRatio:false, scales:{ y:{ beginAtZero:true, ticks:{ font:{ family:'Poppins' } } }, x:{ ticks:{ font:{ family:'Poppins' } } } } }
              });
              </script>";
            }
        }
        ?>
    </div>
</body>

</html>