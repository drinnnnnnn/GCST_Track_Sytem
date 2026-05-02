<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);
$student_id = $_SESSION['student_id'] ?? null;

if (!$student_id || !isset($data['product_id'])) {
    echo json_encode(["success" => false, "message" => "Invalid input"]);
    exit;
}

$product_id = filter_var($data['product_id'], FILTER_VALIDATE_INT);
if ($product_id === false || $product_id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid product ID"]);
    exit;
}

$stmt = $conn->prepare("INSERT IGNORE INTO favorites (student_id, product_id) VALUES (?, ?)");
$stmt->bind_param("si", $student_id, $product_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Added to favorites"]);
} else {
    echo json_encode(["success" => false, "message" => "Database error"]);
}
$stmt->close();
$conn->close();
?>
