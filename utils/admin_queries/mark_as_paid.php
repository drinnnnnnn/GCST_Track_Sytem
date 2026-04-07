<?php
header('Content-Type: application/json');
$host = "localhost";
$username = "root";
$password = "";
$dbname = "library_db";
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["error" => "Connection failed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$student_id = $data['student_id'];
$book_id = $data['book_id'];

$sql = "UPDATE lost_books SET status = 'paid' WHERE student_id = ? AND book_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $student_id, $book_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => $conn->error]);
}

$conn->close();
?>
