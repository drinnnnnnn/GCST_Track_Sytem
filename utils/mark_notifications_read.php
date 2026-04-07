<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['student_id'])) {
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE student_id = ? AND is_read = 0");
    $stmt->bind_param("s", $data['student_id']);
    $stmt->execute();
    
    echo json_encode(["success" => true]);
    $stmt->close();
}
$conn->close();
?>