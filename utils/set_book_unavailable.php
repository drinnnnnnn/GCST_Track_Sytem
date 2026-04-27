<?php // not used
$host = "localhost";
$username = "root";
$password = "";     
$dbname = "gcst_tracking_db";

// Connect to MySQL server
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['book_id'])) {
    echo json_encode(['success' => false, 'message' => 'Book ID missing.']);
    exit;
}

$book_id = intval($data['book_id']);
$stmt = $conn->prepare("UPDATE books SET book_status='unavailable' WHERE book_id=?");
$stmt->bind_param("i", $book_id);

if ($stmt->execute()) {
    // Update counts
    $conn->query("UPDATE count_items SET 
        total_books = (SELECT COUNT(*) FROM books WHERE book_status='available'),
        total_unavailable_books = (SELECT COUNT(*) FROM books WHERE book_status='unavailable')
        LIMIT 1
    ");
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update book status.']);
}

$stmt->close();
$conn->close();
?>
