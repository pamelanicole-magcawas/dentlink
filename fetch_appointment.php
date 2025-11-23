<?php
include 'db_connect.php';

if (!isset($_GET['id'])) {
    echo json_encode(['status'=>'error','message'=>'No ID provided']);
    exit;
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM appointments WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status'=>'error','message'=>'Appointment not found']);
    exit;
}

$appointment = $result->fetch_assoc();
echo json_encode(['status'=>'success','data'=>$appointment]);
?>
