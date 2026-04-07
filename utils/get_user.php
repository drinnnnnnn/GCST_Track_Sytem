<?php

session_start();
$host = "localhost";
$username = "root";
$password = "";
$dbname = "gcst_tracking_db";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
header('Content-Type: application/json');
echo json_encode([
  'student_id' => $_SESSION['student_id'] ?? null,
  'name' => $_SESSION['user_name'] ?? null
]);
?>
