<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$response = ['success' => false, 'message' => ''];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $rating = intval($_POST['rating']);
    $review_text = trim($_POST['review']);
    
    // Validate rating (1-5)
    if ($rating < 1 || $rating > 5) {
        $response['message'] = 'Invalid rating value.';
        echo json_encode($response);
        exit();
    }
    
    // Validate review text
    if (empty($review_text)) {
        $response['message'] = 'Review text cannot be empty.';
        echo json_encode($response);
        exit();
    }
    
    // Check if user has already submitted a review today (optional limit)
    $check_sql = "SELECT COUNT(*) as count FROM reviews 
                  WHERE user_id = ? AND DATE(created_at) = CURDATE()";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $check_row = $check_result->fetch_assoc();
    
    if ($check_row['count'] >= 3) {
        $response['message'] = 'You can only submit 3 reviews per day.';
        echo json_encode($response);
        exit();
    }
    
    // Insert review into database
    $insert_sql = "INSERT INTO reviews (user_id, rating, review_text, created_at) 
                   VALUES (?, ?, ?, NOW())";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("iis", $user_id, $rating, $review_text);
    
    if ($insert_stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Thank you for your review!';
    } else {
        $response['message'] = 'Failed to submit review. Please try again.';
    }
    
    $insert_stmt->close();
    $check_stmt->close();
} else {
    $response['message'] = 'Invalid request method.';
}

$conn->close();

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>