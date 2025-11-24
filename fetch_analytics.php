<?php
// fetch_analytics.php - API endpoint for dashboard analytics
header('Content-Type: application/json');
require_once 'db_connect.php';

date_default_timezone_set('Asia/Manila');
$today = date('Y-m-d');

// Initialize response array
$analytics = [];

try {
    // 1. Total number of NEW USERS (registered in last 30 days)
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM users 
        WHERE role = 'Patient' 
        AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    $stmt->execute();
    $analytics['new_users'] = $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();

    // 2. Total number of PENDING APPOINTMENTS (any date)
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM appointments 
        WHERE status = 'pending'
    ");
    $stmt->execute();
    $analytics['pending_appointments_total'] = $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();

    // 3. Total number of PENDING APPOINTMENTS (for today)
     $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM appointments 
        WHERE status = 'pending'
        AND DATE(created_at) = ?
    ");
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $analytics['pending_appointments_today'] = $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();

    // 4. Total number of APPROVED APPOINTMENTS (any date)
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM appointments 
        WHERE status = 'approved'
    ");
    $stmt->execute();
    $analytics['approved_appointments_total'] = $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();

    // 5. Total number of APPROVED APPOINTMENTS (for today)
    $approved_appointments_today_stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM appointments 
        WHERE status = 'approved' 
        AND DATE(updated_at) = ?
    ");
    $approved_appointments_today_stmt->bind_param("s", $today);
    $approved_appointments_today_stmt->execute();
    $analytics['approved_appointments_today'] = $approved_appointments_today_stmt->get_result()->fetch_assoc()['count'];
    $approved_appointments_today_stmt->close();

    // 6. Total number of COMPLETED APPOINTMENTS (any date)
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM appointments 
        WHERE status = 'completed'
    ");
    $stmt->execute();
    $analytics['completed_appointments_total'] = $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();

    // 7. Total number of COMPLETED APPOINTMENTS (for today)
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM appointments 
        WHERE status = 'completed'
        AND DATE(updated_at) = ?
    ");
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $analytics['completed_appointments_today'] = $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();

    // 8. Total number of UNSEEN CHAT MESSAGES (any date)
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM chat_messages 
        WHERE sender_type = 'Patient' 
        AND is_read = 0
    ");
    $stmt->execute();
    $analytics['unread_messages_total'] = $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();

    // 9. Total number of UNSEEN CHAT MESSAGES (for today)
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM chat_messages 
        WHERE sender_type = 'Patient' 
        AND is_read = 0
        AND DATE(timestamp) = ?
    ");
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $analytics['unread_messages_today'] = $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();

    // ADDITIONAL ANALYTICS

    // 10. Total Patients
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM users 
        WHERE role = 'Patient'
    ");
    $stmt->execute();
    $analytics['total_patients'] = $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();

    // 11. Appointments This Week
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM appointments 
        WHERE YEARWEEK(date, 1) = YEARWEEK(CURDATE(), 1)
        AND status IN ('approved', 'completed', 'checked-in', 'in-treatment')
    ");
    $stmt->execute();
    $analytics['appointments_this_week'] = $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();

    // 12. Appointments This Month
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM appointments 
        WHERE MONTH(date) = MONTH(CURDATE()) 
        AND YEAR(date) = YEAR(CURDATE())
        AND status IN ('approved', 'completed', 'checked-in', 'in-treatment')
    ");
    $stmt->execute();
    $analytics['appointments_this_month'] = $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();

    // 13. Checked-in Today
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM appointments 
        WHERE status = 'checked-in' 
        AND date = ?
    ");
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $analytics['checked_in_today'] = $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();

    // 14. In Treatment Today
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM appointments 
        WHERE status = 'in-treatment' 
        AND date = ?
    ");
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $analytics['in_treatment_today'] = $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();

    // 15. Denied Appointments (All Time)
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM appointments 
        WHERE status = 'denied'
    ");
    $stmt->execute();
    $analytics['denied_appointments_total'] = $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();

    // 16. Most Popular Service
    $stmt = $conn->prepare("
        SELECT description, COUNT(*) as count 
        FROM appointments 
        WHERE status IN ('approved', 'completed', 'checked-in', 'in-treatment')
        GROUP BY description 
        ORDER BY count DESC 
        LIMIT 1
    ");
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $analytics['most_popular_service'] = $result ? $result['description'] : 'N/A';
    $analytics['most_popular_service_count'] = $result ? $result['count'] : 0;
    $stmt->close();

    // 17. Average Reviews Rating
    $stmt = $conn->prepare("
        SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews 
        FROM reviews
    ");
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $analytics['average_rating'] = $result['avg_rating'] ? round($result['avg_rating'], 1) : 0;
    $analytics['total_reviews'] = $result['total_reviews'];
    $stmt->close();

    // 18. Active Patients (logged in last 7 days)
    $stmt = $conn->prepare("
        SELECT COUNT(DISTINCT user_id) as count 
        FROM activity_logs 
        WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        AND user_id IN (SELECT user_id FROM users WHERE role = 'Patient')
    ");
    $stmt->execute();
    $analytics['active_patients_week'] = $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();

    // 19. Upcoming Appointments (Next 7 Days)
    $next_week = date('Y-m-d', strtotime('+7 days'));
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM appointments 
        WHERE status = 'approved' 
        AND date BETWEEN ? AND ?
    ");
    $stmt->bind_param("ss", $today, $next_week);
    $stmt->execute();
    $analytics['upcoming_appointments_week'] = $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();

    // 20. Completion Rate (%)
    $stmt = $conn->prepare("
        SELECT 
            (SELECT COUNT(*) FROM appointments WHERE status = 'completed') as completed,
            (SELECT COUNT(*) FROM appointments WHERE status IN ('approved', 'completed', 'checked-in', 'in-treatment', 'denied')) as total
    ");
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $analytics['completion_rate'] = $result['total'] > 0 ? round(($result['completed'] / $result['total']) * 100, 1) : 0;
    $stmt->close();

    echo json_encode([
        'success' => true,
        'data' => $analytics,
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn->close();
?>
