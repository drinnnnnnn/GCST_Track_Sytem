<?php // not used
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['request_id'], $data['duration'], $data['unit'])) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}
require_once __DIR__ . '/../config/db_connect.php';
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'DB connection failed']);
    exit;
}

$request_id = intval($data['request_id']);
$duration = intval($data['duration']);
$unit = $conn->real_escape_string($data['unit']);

// 1. Get request, user, and book info
$sql = "SELECT r.student_id, r.book_id, u.name, u.email
        FROM request r
        JOIN users u ON r.student_id = u.student_id
        WHERE r.request_id = $request_id";
$result = $conn->query($sql);
if (!$result || $result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Request not found']);
    exit;
}
$row = $result->fetch_assoc();
$student_id = $row['student_id'];
$book_id = $row['book_id'];

// 2. Calculate dates
$date_received = date('Y-m-d');
switch (strtolower($unit)) {
    case 'days':
        $return_date = date('Y-m-d', strtotime("+$duration days"));
        break;
    case 'weeks':
        $return_date = date('Y-m-d', strtotime("+$duration weeks"));
        break;
    case 'hours':
        $return_date = date('Y-m-d', strtotime("+$duration hours"));
        break;
    default:
        $return_date = date('Y-m-d', strtotime("+$duration days"));
}
$borrow_duration_days = (strtotime($return_date) - strtotime($date_received)) / (60 * 60 * 24);

// 3. Insert into non_returned_books
$stmt = $conn->prepare("INSERT INTO non_returned_books (book_id, student_id, date_student_received_book, return_date, borrow_duration_days, overdued_book) VALUES (?, ?, ?, ?, ?, 0)");
$stmt->bind_param("isssi", $book_id, $student_id, $date_received, $return_date, $borrow_duration_days);
$success = $stmt->execute();

if ($success) {
    updateNonReturnedBooksCount($conn);

    // Insert notification for "received"
    $notif = $conn->prepare("INSERT INTO student_notification (student_id, book_id, status) VALUES (?, ?, 'received')");
    $notif->bind_param("si", $student_id, $book_id);
    $notif->execute();
}
$conn->query("UPDATE books SET quantity = quantity - 1 WHERE book_id = $book_id");
// 4. Update request status to 'approved'
$conn->query("UPDATE request SET status='approved' WHERE request_id=$request_id");

echo json_encode(['success' => $success]);
$conn->close();

function updateNonReturnedBooksCount($conn) {
    $result = $conn->query("SELECT COUNT(*) AS cnt FROM non_returned_books");
    $row = $result->fetch_assoc();
    $total = (int)$row['cnt'];
    $conn->query("INSERT INTO count_items (total_non_returned_books) SELECT $total WHERE NOT EXISTS (SELECT 1 FROM count_items)");
    $conn->query("UPDATE count_items SET total_non_returned_books = $total");
}
?>
