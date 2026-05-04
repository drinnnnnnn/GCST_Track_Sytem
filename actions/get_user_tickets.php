<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db_connect.php';

if (!isset($_SESSION['student_id'])) {
    echo json_encode(['tickets' => []]);
    exit;
}

$student_id = $_SESSION['student_id'];

// Resolve internal user ID from alphanumeric student_id
$userId = null;
$lookup = $conn->prepare('SELECT id FROM users WHERE student_id = ? LIMIT 1');
$lookup->bind_param('s', $student_id);
$lookup->execute();
$lookup->bind_result($userId);
$userFound = $lookup->fetch();
$lookup->close();

if (!$userFound) {
    echo json_encode(['tickets' => []]);
    exit;
}

// Safety check: Ensure columns exist
$conn->query("ALTER TABLE `queue` ADD COLUMN IF NOT EXISTS `student_name` VARCHAR(255) DEFAULT NULL, ADD COLUMN IF NOT EXISTS `purpose` VARCHAR(255) DEFAULT NULL");

$sql = "SELECT id, queue_number, status, created_at, served_at, student_name, purpose FROM queue WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
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
            'served_at' => $row['served_at'],
            'student_name' => $row['student_name'],
            'purpose' => $row['purpose']
        ];
    }
}

echo json_encode(['tickets' => $tickets]);
$stmt->close();
$conn->close();
?>
