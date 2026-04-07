<?php
header('Content-Type: application/json');
session_start();

$host = "localhost";
$username = "root";
$password = "";
$dbname = "gcst_tracking_system";
$conn = new mysqli($host, $username, $password, $dbname);

$student_id = $_SESSION['student_id'];
$sql = "SELECT p.product_name, p.product_category, r.date_student_received_book, r.book_returned_date 
        FROM issued_books r 
        JOIN products p ON r.product_id = p.product_id 
        WHERE r.student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $student_id);
$stmt->execute();
echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
?>