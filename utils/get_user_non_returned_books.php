<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'librarian') {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['student_id'])) {
    echo json_encode([]);
    exit;
}

$student_id = $_SESSION['student_id'];
$host = "localhost";
$username = "root";
$password = "";
$dbname = "library_db";
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT nrb.nrb_id, nrb.book_id, nrb.date_student_received_book, nrb.return_date, nrb.days_left, nrb.overdued_book,
               b.book_name, b.book_category, b.book_author
        FROM non_returned_books nrb
        JOIN books b ON nrb.book_id = b.book_id
        WHERE nrb.student_id = ?
        ORDER BY nrb.return_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}
echo json_encode($rows);
$conn->close();
?>
