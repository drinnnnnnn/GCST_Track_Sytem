<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['product_id']) && isset($data['student_id'])) {
    $student_id = $data['student_id'];
    $product_id = $data['product_id'];

    $stmt = $conn->prepare("INSERT INTO requests (student_id, product_id, status) VALUES (?, ?, 'Pending')");
    $stmt->bind_param("si", $student_id, $product_id);
    
    if ($stmt->execute()) {
        // Automatically generate a notification for the student
        $notif_stmt = $conn->prepare("INSERT INTO notifications (student_id, product_id, status) VALUES (?, ?, 'Request Pending Approval')");
        $notif_stmt->bind_param("si", $student_id, $product_id);
        $notif_stmt->execute();
        $notif_stmt->close();

        echo json_encode(["success" => true, "message" => "Request sent successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to send request"]);
    }
    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid input"]);
}
$conn->close();
?>