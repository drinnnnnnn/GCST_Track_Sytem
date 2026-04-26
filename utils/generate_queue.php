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

// Generate unique queue number (e.g., Q001, Q002, etc.)
$sql = "SELECT MAX(CAST(SUBSTRING(queue_number, 2) AS UNSIGNED)) as max_num FROM queue";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$next_num = ($row['max_num'] ?? 0) + 1;
$queue_number = 'Q' . str_pad($next_num, 3, '0', STR_PAD_LEFT);

// Insert new queue
$sql_insert = "INSERT INTO queue (queue_number, status, created_at) VALUES (?, 'waiting', NOW())";
$stmt = $conn->prepare($sql_insert);
$stmt->bind_param("s", $queue_number);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'queue_number' => $queue_number]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to generate queue']);
}

$stmt->close();
$conn->close();
?>