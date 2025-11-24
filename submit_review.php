<?php
session_start();
include 'db_connect.php';

// Set response header
header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Please log in to submit a review.';
    echo json_encode($response);
    exit();
}

// Validate POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit();
}

$user_id = $_SESSION['user_id'];
$rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
$review_text = isset($_POST['review']) ? trim($_POST['review']) : '';

// Validate rating (1-5)
if ($rating < 1 || $rating > 5) {
    $response['message'] = 'Please select a valid rating (1-5 stars).';
    echo json_encode($response);
    exit();
}

// Validate review text is not empty
if (empty($review_text)) {
    $response['message'] = 'Review text cannot be empty.';
    echo json_encode($response);
    exit();
}

// Validate review text length (5-200 characters)
$review_length = strlen($review_text);
if ($review_length < 5) {
    $response['message'] = 'Review must be at least 5 characters long.';
    echo json_encode($response);
    exit();
}

if ($review_length > 200) {
    $response['message'] = 'Review must not exceed 200 characters.';
    echo json_encode($response);
    exit();
}

// Check if user has already submitted a review today
$check_sql = "SELECT COUNT(*) as count FROM reviews 
              WHERE user_id = ? AND DATE(created_at) = CURDATE()";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("i", $user_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$check_row = $check_result->fetch_assoc();
$check_stmt->close();

if ($check_row['count'] > 0) {
    $response['message'] = 'You have already submitted a review today. Please try again tomorrow.';
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
    $response['message'] = 'Thank you for your review! Your feedback helps us improve our services.';
} else {
    $response['message'] = 'Failed to submit review. Please try again later.';
}

$insert_stmt->close();
$conn->close();

echo json_encode($response);
?>