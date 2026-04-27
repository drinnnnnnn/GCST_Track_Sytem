<?php
session_start();
$host = "localhost";
$username = "root";
$password = "";
$dbname = "gcst_tracking_db";

$student_id = $_SESSION['student_id'];
$stmt = $conn->prepare("SELECT student_id, name, email, course, contact_no FROM students WHERE student_id = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
echo json_encode($result->fetch_assoc());
?>