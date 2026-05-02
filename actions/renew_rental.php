<?php
require_once __DIR__ . '/security.php';
secureSessionStart();
requireAuth(['student']);
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db_connect.php';

$rental_id = isset($_POST['rental_id']) ? intval($_POST['rental_id']) : 0;
if (!$rental_id) {
    echo json_encode(['success' => false, 'error' => 'Missing rental_id']);
    exit;
}

$student_id = $_SESSION['student_id'];

$sql = "SELECT return_date FROM active_rentals WHERE rental_id = ? AND student_id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param('is', $rental_id, $student_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row) {
    echo json_encode(['success' => false, 'error' => 'Rental not found']);
    exit;
}

$currentDate = $row['return_date'] ?: date('Y-m-d');
$newDueDate = date('Y-m-d', strtotime($currentDate . ' + 7 days'));

$update = $conn->prepare("UPDATE active_rentals SET return_date = ?, status = 'active' WHERE rental_id = ? AND student_id = ? LIMIT 1");
$update->bind_param('sis', $newDueDate, $rental_id, $student_id);
$success = $update->execute();
$update->close();
$conn->close();

if ($success) {
    echo json_encode(['success' => true, 'new_due_date' => $newDueDate]);
} else {
    echo json_encode(['success' => false, 'error' => 'Unable to renew rental']);
}
