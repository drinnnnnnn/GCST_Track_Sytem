<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['favorite_id'])) {
    echo json_encode(['success' => false]);
    exit;
}

$conn = new mysqli("localhost", "root", "", "library_db");
if ($conn->connect_error) {
    echo json_encode(['success' => false]);
    exit;
}

$stmt = $conn->prepare("DELETE FROM favorites WHERE favorite_id = ?");
$stmt->bind_param("i", $data['favorite_id']);
$success = $stmt->execute();

echo json_encode(['success' => $success]);

$stmt->close();
$conn->close();
?>
