<?php // not used
$host = "localhost";
$username = "root";
$password = "";
$dbname = "gcst_tracking_db";
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'DB connection failed']);
    exit;
}
// Set response header
header('Content-Type: application/json');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw POST data
    $data = json_decode(file_get_contents('php://input'), true);

    // Validate the input
    if (isset($data['book_id']) && isset($data['new_status'])) {
        $book_id = $data['book_id'];
        $new_status = $data['new_status'];

        // Check the current quantity of the book
        $quantityQuery = "SELECT quantity FROM books WHERE book_id = ?";
        $quantityStmt = $conn->prepare($quantityQuery);
        $quantityStmt->bind_param('i', $book_id);
        $quantityStmt->execute();
        $quantityResult = $quantityStmt->get_result();
        $quantityRow = $quantityResult->fetch_assoc();
        $quantityStmt->close();

        if ($quantityRow && $quantityRow['quantity'] == 0) {
            $new_status = 'unavailable'; // Automatically set to unavailable if quantity is 0
        }

        // Update the book status in the database
        $query = "UPDATE books SET book_status = ? WHERE book_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('si', $new_status, $book_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Book status updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update book status.']);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

$conn->close();
?>
