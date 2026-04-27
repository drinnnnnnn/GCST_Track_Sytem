<?php // not use
header('Content-Type: application/json');
$host = "localhost";
$username = "root";
$password = "";
$dbname = "gcst_tracking_db";
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode([]);
    exit;
}

// STEP 1: Update overdue values
$update_sql = "
UPDATE non_returned_books
SET 
    remaining_days_before_due = GREATEST(DATEDIFF(return_date, CURDATE()), 0),
    overdue_days = GREATEST(DATEDIFF(CURDATE(), return_date), 0),
    overdued_book = IF(CURDATE() >= return_date, 1, 0)
";
$conn->query($update_sql);

// STEP 2: Fetch updated data
$select_sql = "
SELECT nrb.nrb_id, nrb.book_id, nrb.student_id, nrb.date_student_received_book, 
       nrb.return_date, nrb.remaining_days_before_due, nrb.overdued_book,
       u.name AS student_name, b.book_name
FROM non_returned_books nrb
JOIN users u ON nrb.student_id = u.student_id
JOIN books b ON nrb.book_id = b.book_id
ORDER BY nrb.return_date DESC
";
$result = $conn->query($select_sql);

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}
echo json_encode($rows);
$conn->close();
?>
