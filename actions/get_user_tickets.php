<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db_connect.php';

if (!isset($_SESSION['student_id'])) {
    echo json_encode(['tickets' => []]);
    exit;
}

$student_id = $_SESSION['student_id'];
$sql = "SELECT id, queue_number, status, created_at, served_at FROM queue WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$tickets = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $status = strtolower($row['status'] ?? 'waiting');
        $displayStatus = $status;
        if ($status === 'cancelled') {
            $displayStatus = 'expired';
        }

        $tickets[] = [
            'id' => $row['id'],
            'queue_number' => $row['queue_number'],
            'status' => $status,
            'display_status' => ucfirst($displayStatus),
            'created_at' => $row['created_at'],
            'served_at' => $row['served_at']
        ];
    }
}

echo json_encode(['tickets' => $tickets]);
$stmt->close();
$conn->close();
?>

