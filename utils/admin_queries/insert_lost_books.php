<?php
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

// Step 1: Insert into lost_books kung wala pa roon at overdue
$insert_sql = "
    INSERT INTO lost_books (book_id, student_id, fine_amount, days_overdue, status)
    SELECT 
        nrb.book_id,
        nrb.student_id,
        (nrb.overdue_days * 5.00) AS fine_amount,  -- example: ₱5 per day
        nrb.overdue_days,
        'pending fine'
    FROM non_returned_books nrb
    LEFT JOIN lost_books lb ON nrb.book_id = lb.book_id AND nrb.student_id = lb.student_id
    WHERE nrb.student_id = ?
      AND nrb.overdued_book = 1
      AND lb.lost_id IS NULL
";
$insert_stmt = $conn->prepare($insert_sql);
$insert_stmt->bind_param("s", $student_id);
$insert_stmt->execute();
$insert_stmt->close();

// Step 2: Select updated list from non_returned_books to lost_books
// This will fetch all lost books for the logged-in student
$sql = "
SELECT 
    lb.book_id,
    lb.student_id,
    user.name AS student_name,
    b.book_name,
    lb.days_overdue,
    lb.fine_amount,
    lb.status
FROM lost_books lb
JOIN users u ON lb.student_id = user.student_id
JOIN books b ON lb.book_id = b.book_id
ORDER BY lb.fined_date DESC
";

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
