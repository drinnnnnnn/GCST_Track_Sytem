<?php
require_once __DIR__ . '/security.php';
secureSessionStart();
requireAuth(['student']);
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db_connect.php';

$payload = json_decode(file_get_contents('php://input'), true) ?: [];
$rental_id = isset($payload['rental_id']) ? intval($payload['rental_id']) : 0;
$duration = isset($payload['duration']) ? intval($payload['duration']) : 0;
$unit = isset($payload['unit']) ? trim(strtolower($payload['unit'])) : 'days';

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
$interval = ($unit === 'hours') ? " + $duration hours" : " + $duration days";
$newDueDate = date('Y-m-d H:i:s', strtotime($currentDate . $interval));

// Set status to pending_renewal so admin can approve it
$update = $conn->prepare("UPDATE active_rentals SET return_date = ?, status = 'pending_renewal' WHERE rental_id = ? AND student_id = ? LIMIT 1");
$update->bind_param('sis', $newDueDate, $rental_id, $student_id);
$success = $update->execute();
$update->close();
$conn->close();

if ($success) {
    echo json_encode(['success' => true, 'new_due_date' => $newDueDate]);
} else {
    echo json_encode(['success' => false, 'error' => 'Unable to renew rental']);
}
