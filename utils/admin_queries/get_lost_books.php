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

// Get the status parameter from the request
$status = isset($_GET['status']) ? $_GET['status'] : 'pending fine';

// Step 1: Insert new overdue books into lost_books
$insert_sql = "
    INSERT INTO lost_books (book_id, student_id, fine_amount, days_overdue, status)
    SELECT 
        nrb.book_id,
        nrb.student_id,
        (nrb.overdue_days * 50.00 + b.fine_value) AS fine_amount,
        nrb.overdue_days,
        'pending fine'
    FROM non_returned_books nrb
    JOIN books b ON nrb.book_id = b.book_id
    LEFT JOIN lost_books lb ON nrb.book_id = lb.book_id AND nrb.student_id = lb.student_id
    WHERE nrb.overdued_book = 1 AND lb.lost_id IS NULL
";
$conn->query($insert_sql); 

// Step 2: Select updated list from lost_books based on the status
$sql = "
SELECT 
    lb.book_id,
    lb.student_id,
    u.name AS student_name, 
    b.book_name,
    lb.days_overdue,
    (lb.days_overdue * 20.00 + b.fine_value) AS fine_amount,
    lb.status
FROM lost_books lb
JOIN users u ON lb.student_id = u.student_id
JOIN books b ON lb.book_id = b.book_id
WHERE lb.status = ?
ORDER BY lb.days_overdue DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $status);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}
echo json_encode($data);
$conn->close();
?>
