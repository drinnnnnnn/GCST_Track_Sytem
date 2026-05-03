<?php
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/../config/db_connect.php';

secureSessionStart();
requireAuth(['student', 'admincashier', 'superadmin']);
header('Content-Type: application/json');

$student_id = $_SESSION['student_id'] ?? null;
$admin_id = $_SESSION['admin_id'] ?? null;

if ($admin_id) {
    echo json_encode([]);
    $conn->close();
    exit;
}

if (!$student_id) {
    http_response_code(401);
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("SELECT id, product_name, status, notified_at, is_read FROM notifications WHERE student_id = ? ORDER BY notified_at DESC LIMIT 50");
$stmt->bind_param('s', $student_id);
$stmt->execute();
$result = $stmt->get_result();
$notifications = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $notifications[] = [
            'id' => $row['id'],
            'message' => trim(($row['product_name'] ? $row['product_name'] . ' - ' : '') . ($row['status'] ?? 'Notification')),
            'time' => $row['notified_at'] ?? '',
            'read' => (bool)($row['is_read'] ?? false)
        ];
    }
}

echo json_encode($notifications);
$stmt->close();
$conn->close();
?>

