<?php
require_once __DIR__ . '/security.php';
secureSessionStart();
requireAuth(['student']);
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

$product_name = null;
$lookup_stmt = $conn->prepare("SELECT product_name FROM products WHERE product_id = ?");
$lookup_stmt->bind_param("i", $product_id);
$lookup_stmt->execute();
$lookup_stmt->bind_result($product_name);
if (!$lookup_stmt->fetch() || !$product_name) {
    $lookup_stmt->close();
    echo json_encode(["success" => false, "message" => "Product not found"]);
    exit;
}
$lookup_stmt->close();

$stmt = $conn->prepare("INSERT INTO requests (student_id, product_id, status) VALUES (?, ?, 'Pending')");
$stmt->bind_param("si", $student_id, $product_id);

if ($stmt->execute()) {
    // Automatically generate a notification for the student
    $notif_stmt = $conn->prepare("INSERT INTO notifications (student_id, product_name, status) VALUES (?, ?, 'Request Pending Approval')");
    $notif_stmt->bind_param("ss", $student_id, $product_name);
    $notif_stmt->execute();
    $notif_stmt->close();

    echo json_encode(["success" => true, "message" => "Request sent successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to send request"]);
}
$stmt->close();
$conn->close();
?>
