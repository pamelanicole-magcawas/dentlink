<?php
session_start();
include 'db_connect.php';
header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Please log in to submit a review.';
    echo json_encode($response);
    exit();
}

$user_id = $_SESSION['user_id'];
$rating = $_POST['rating'] ?? 0;
$review = trim($_POST['review'] ?? '');

// Validate rating and review
if ($rating < 1 || $rating > 5) {
    $response['message'] = 'Invalid rating.';
    echo json_encode($response);
    exit();
}

if (strlen($review) < 5 || strlen($review) > 200) {
    $response['message'] = 'Review must be between 5 and 200 characters.';
    echo json_encode($response);
    exit();
}

// Check if user already submitted today
$check_sql = "SELECT COUNT(*) AS count FROM reviews WHERE user_id = ? AND DATE(created_at) = CURDATE()";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("i", $user_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$check_data = $check_result->fetch_assoc();
$check_stmt->close();

if ($check_data['count'] > 0) {
    $response['message'] = 'You have already submitted a review today.';
    echo json_encode($response);
    exit();
}

// Insert review
$insert_sql = "INSERT INTO reviews (user_id, rating, review_text) VALUES (?, ?, ?)";
$insert_stmt = $conn->prepare($insert_sql);
$insert_stmt->bind_param("iis", $user_id, $rating, $review);

if ($insert_stmt->execute()) {
    $response['success'] = true;
    $response['message'] = 'Thank you for your feedback!';
} else {
    $response['message'] = 'Failed to save review. Please try again.';
}

$insert_stmt->close();
$conn->close();

echo json_encode($response);
?>
