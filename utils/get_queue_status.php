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

// Get current time
$current_time = date('H:i:s');

// Get now serving (latest serving)
$sql_serving = "SELECT queue_number FROM queue WHERE status = 'serving' ORDER BY id DESC LIMIT 1";
$result_serving = $conn->query($sql_serving);
$now_serving = $result_serving->num_rows > 0 ? $result_serving->fetch_assoc()['queue_number'] : null;

// Get next queue (first waiting)
$sql_next = "SELECT queue_number FROM queue WHERE status = 'waiting' ORDER BY created_at ASC LIMIT 1";
$result_next = $conn->query($sql_next);
$next_queue = $result_next->num_rows > 0 ? $result_next->fetch_assoc()['queue_number'] : null;

echo json_encode([
    'current_time' => $current_time,
    'now_serving' => $now_serving,
    'next_queue' => $next_queue
]);

$conn->close();
?>