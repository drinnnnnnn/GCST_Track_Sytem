<?php
header('Content-Type: application/json');
$host = "localhost";
$username = "root";
$password = "";
$dbname = "gcst_tracking_system";
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed']));
}

$data = json_decode(file_get_contents('php://input'), true);
$queue_id = $data['queue_id'];
$status = $data['status'];

if ($status === 'serving') {
    $sql = "UPDATE queue SET status = 'serving', served_at = NOW() WHERE id = ?";
} elseif ($status === 'completed') {
    $sql = "UPDATE queue SET status = 'completed' WHERE id = ?";
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid status']);
    exit;
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $queue_id);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to update status']);
}

$stmt->close();
$conn->close();
?>