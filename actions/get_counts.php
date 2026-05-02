<?php // Not used
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db_connect.php';
if ($conn->connect_error) {
    echo json_encode([
        'total_books' => 0,
        'total_categories' => 0,
        'total_members' => 0,
        'pending_requests' => 0,
        'total_non_returned_books' => 0,
        'total_issued_books' => 0,
        'total_suspended' => 0,
        'total_unavailable_books' => 0

    ]);
    exit;
}
$res = $conn->query("SELECT total_books, total_categories, total_members, pending_requests, total_non_returned_books, total_issued_books, total_suspended FROM count_items LIMIT 1");
$row = $res->fetch_assoc();

// Get overdue books count
$today = date('Y-m-d');
$overdue_books_query = "SELECT COUNT(*) as overdue_books FROM non_returned_books WHERE return_date <= '$today'";
$overdue_books_result = $conn->query($overdue_books_query);
$overdue_books_row = $overdue_books_result->fetch_assoc();
$overdue_books = (int)($overdue_books_row['overdue_books'] ?? 0);

// Get unavailable books count
$unavailable_books_query = "SELECT COUNT(*) as unavailable_books FROM books WHERE book_status = 'unavailable'";
$unavailable_books_result = $conn->query($unavailable_books_query);
$unavailable_books_row = $unavailable_books_result->fetch_assoc();
$unavailable_books = (int)($unavailable_books_row['unavailable_books'] ?? 0);

echo json_encode([
    'total_books' => (int)($row['total_books'] ?? 0),
    'total_categories' => (int)($row['total_categories'] ?? 0),
    'total_members' => (int)($row['total_members'] ?? 0),
    'pending_requests' => (int)($row['pending_requests'] ?? 0),
    'total_non_returned_books' => (int)($row['total_non_returned_books'] ?? 0),
    'total_issued_books' => (int)($row['total_issued_books'] ?? 0),
    'total_suspended' => (int)($row['total_suspended'] ?? 0),
    'overdue_books' => $overdue_books,
    'total_unavailable_books' => $unavailable_books
]);
$conn->close();
?>
