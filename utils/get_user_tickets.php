<?php
session_start();
header('Content-Type: application/json');
$host = "localhost";
$username = "root";
$password = "";
$dbname = "gcst_tracking_system";

$student_id = $_SESSION['student_id'];
$sql = "SELECT p.product_name, p.product_author, p.product_category, r.date_student_received_book, r.return_date 
        FROM active_rentals r 
        JOIN products p ON r.product_id = p.product_id 
        WHERE r.student_id = ? AND r.status != 'returned'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $student_id);
$stmt->execute();
echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
?>