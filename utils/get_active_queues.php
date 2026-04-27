<?php
header('Content-Type: application/json');
$host = "localhost";
$username = "root";
$password = "";
$dbname = "gcst_tracking_db";
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed']));
}

$sql = "SELECT id, queue_number, status, created_at, served_at FROM queue WHERE status IN ('waiting', 'serving') ORDER BY created_at ASC";
$result = $conn->query($sql);

$queues = [];
while($row = $result->fetch_assoc()) {
    $queues[] = $row;
}

echo json_encode($queues);
$conn->close();
?>