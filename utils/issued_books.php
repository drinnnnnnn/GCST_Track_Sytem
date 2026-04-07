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
$dbname = "library_db";
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT ib.issued_id, b.book_name, b.book_category, u.name AS student_name, ib.date_student_received_book, ib.book_returned_date
        FROM issued_books ib
        JOIN books b ON ib.book_id = b.book_id
        JOIN users u ON ib.student_id = u.student_id
        ORDER BY ib.issued_id DESC";
$result = $conn->query($sql);

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}
echo json_encode($rows);
$conn->close();
?>
