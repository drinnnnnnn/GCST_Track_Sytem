<?php
require_once __DIR__ . '/security.php';
secureSessionStart();
requireAuth(['student', 'admin', 'admincashier', 'superadmin']);
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db_connect.php';

$student_id = $_SESSION['student_id'] ?? null;
$admin_id = $_SESSION['admin_id'] ?? null;

if ($student_id) {
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE student_id = ? AND is_read = 0");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    echo json_encode(["success" => true]);
    $stmt->close();
} elseif ($admin_id) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false]);
}
$conn->close();
?>
