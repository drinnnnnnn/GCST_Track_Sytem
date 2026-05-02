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
$sql = "SELECT product_id, date_student_received_book FROM active_rentals WHERE rental_id = ? AND student_id = ? LIMIT 1";
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

$product_id = intval($row['product_id']);
$rental_date = $row['date_student_received_book'] ?: date('Y-m-d');
$return_date = date('Y-m-d');

$insert = $conn->prepare("INSERT INTO issued_books (student_id, product_id, date_student_received_book, book_returned_date) VALUES (?, ?, ?, ?)");
$insert->bind_param('siss', $student_id, $product_id, $rental_date, $return_date);
$insertSuccess = $insert->execute();
$insert->close();

$delete = $conn->prepare("DELETE FROM active_rentals WHERE rental_id = ? AND student_id = ? LIMIT 1");
$delete->bind_param('is', $rental_id, $student_id);
$deleteSuccess = $delete->execute();
$delete->close();

$conn->close();

if ($insertSuccess && $deleteSuccess) {
    echo json_encode(['success' => true, 'returned_date' => $return_date]);
} else {
    echo json_encode(['success' => false, 'error' => 'Unable to mark rental as returned']);
}
