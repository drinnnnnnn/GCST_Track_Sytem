<?php // not used
header('Content-Type: application/json');
$host = "localhost";
$username = "root";
$password = "";
$dbname = "gcst_tracking_db";
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'DB connection failed']);
    exit;
}

$nrb_id = isset($_POST['nrb_id']) ? intval($_POST['nrb_id']) : 0;
if (!$nrb_id) {
    echo json_encode(['success' => false, 'error' => 'Missing nrb_id']);
    exit;
}

// Fetch the non-returned book record
$sql = "SELECT * FROM non_returned_books WHERE nrb_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $nrb_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    echo json_encode(['success' => false, 'error' => 'Record not found']);
    exit;
}

// Insert into issued_books
$book_id = $row['book_id'];
$student_id = $row['student_id'];
$date_student_received_book = $row['date_student_received_book'];
$book_returned_date = date('Y-m-d'); // today

$insert = $conn->prepare("INSERT INTO issued_books (book_id, student_id, date_student_received_book, book_returned_date) VALUES (?, ?, ?, ?)");
$insert->bind_param("isss", $book_id, $student_id, $date_student_received_book, $book_returned_date);
$insert->execute();
updateIssuedBooksCount($conn);

// Delete from non_returned_books
$delete = $conn->prepare("DELETE FROM non_returned_books WHERE nrb_id = ?");
$delete->bind_param("i", $nrb_id);
$delete->execute();
updateNonReturnedBooksCount($conn);
echo json_encode(['success' => true]);
$conn->close();


function updateNonReturnedBooksCount($conn) {
    $result = $conn->query("SELECT COUNT(*) AS cnt FROM non_returned_books");
    $row = $result->fetch_assoc();
    $total = (int)$row['cnt'];
    // If count_items table is empty, insert a row
    $conn->query("INSERT INTO count_items (total_non_returned_books) SELECT $total WHERE NOT EXISTS (SELECT 1 FROM count_items)");
    // Otherwise, update the row
    $conn->query("UPDATE count_items SET total_non_returned_books = $total");
}

function updateIssuedBooksCount($conn) {
    $result = $conn->query("SELECT COUNT(*) AS cnt FROM issued_books");
    $row = $result->fetch_assoc();
    $total = (int)$row['cnt'];
    // If count_items table is empty, insert a row
    $conn->query("INSERT INTO count_items (total_issued_books) SELECT $total WHERE NOT EXISTS (SELECT 1 FROM count_items)");
    // Otherwise, update the row
    $conn->query("UPDATE count_items SET total_issued_books = $total");
}
?>
