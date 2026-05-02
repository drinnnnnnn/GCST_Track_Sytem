<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db_connect.php';
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$data = json_decode(file_get_contents('php://input'), true);
$request_id = $data['request_id'];
$status = $data['status'];

$stmt = $conn->prepare("UPDATE request SET status=? WHERE request_id=?");
$stmt->bind_param("si", $status, $request_id);
$stmt->execute();
updatePendingRequestsCount($conn);

if ($status === 'ready to pick up') {
    // Get student_id and book_id for the request
    $stmt = $conn->prepare("SELECT student_id, book_id FROM request WHERE request_id = ?");
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $student_id = $row['student_id'];
        $book_id = $row['book_id'];
        // Insert notification
        $notif = $conn->prepare("INSERT INTO student_notification (student_id, book_id, status) VALUES (?, ?, 'ready to pick up')");
        $notif->bind_param("si", $student_id, $book_id);
        $notif->execute();
    }
}

echo json_encode(['success' => true]);
$conn->close();

function updatePendingRequestsCount($conn) {
    $result = $conn->query("SELECT COUNT(*) AS cnt FROM request WHERE status = 'pending'");
    $row = $result->fetch_assoc();
    $total = (int)$row['cnt'];
    // If count_items table is empty, insert a row
    $conn->query("INSERT INTO count_items (pending_requests) SELECT $total WHERE NOT EXISTS (SELECT 1 FROM count_items)");
    // Otherwise, update the row
    $conn->query("UPDATE count_items SET pending_requests = $total");
}

?>
