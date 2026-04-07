<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['student_id'])) {
    $stmt = $conn->prepare("DELETE FROM notifications WHERE student_id = ?");
    $stmt->bind_param("s", $data['student_id']);
    
    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false]);
    }
    $stmt->close();
}
$conn->close();
?>