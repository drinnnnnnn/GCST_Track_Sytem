<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db_connect.php';

$student_id = $_SESSION['student_id'] ?? null;
$admin_id = $_SESSION['admin_id'] ?? null;

if (!$student_id && !$admin_id) {
    http_response_code(401);
    echo json_encode([]);
    exit;
}

if ($admin_id) {
    echo json_encode([]);
    $conn->close();
    exit;
}

$stmt = $conn->prepare("SELECT product_name, status, notified_at FROM notifications WHERE student_id = ? AND is_read = 0 ORDER BY notified_at DESC");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$notifications = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $row['product_image'] = '';
        $notifications[] = $row;
    }
}

echo json_encode($notifications);
$stmt->close();
$conn->close();
?>

