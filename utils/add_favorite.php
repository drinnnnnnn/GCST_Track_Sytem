<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['product_id']) && isset($data['student_id'])) {
    $stmt = $conn->prepare("INSERT IGNORE INTO favorites (student_id, product_id) VALUES (?, ?)");
    $stmt->bind_param("si", $data['student_id'], $data['product_id']);
    
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Added to favorites"]);
    } else {
        echo json_encode(["success" => false, "message" => "Database error"]);
    }
    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid input"]);
}
$conn->close();
?>