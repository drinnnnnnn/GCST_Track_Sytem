<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'librarian') {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

header('Content-Type: application/json');
$host = "localhost";
$username = "root";
$password = "";
$dbname = "gcst_tracking_db";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT r.request_id, r.student_id, u.name, b.book_name, r.status
        FROM request r
        JOIN users u ON r.student_id = u.student_id
        JOIN books b ON r.book_id = b.book_id
        WHERE r.status IN ('pending', 'ready to pick up')
        ORDER BY r.request_date DESC";
$result = $conn->query($sql);
$rows = [];
while ($row = $result->fetch_assoc()) $rows[] = $row;
echo json_encode($rows);
?>
