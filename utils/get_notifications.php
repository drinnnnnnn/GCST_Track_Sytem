<?php
session_start();
include 'db_connect.php';

$student_id = $_SESSION['student_id'];
$sql = "SELECT product_name, status, notified_at FROM notifications WHERE student_id = ? AND is_read = 0 ORDER BY notified_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $student_id);
$stmt->execute();
echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
?>