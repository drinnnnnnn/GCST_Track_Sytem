<?php
// Enable error output
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Output JSON headers
header('Content-Type: application/json');


// DB connection
require_once __DIR__ . '/../config/db_connect.php';
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}
// Determine action
$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'add':
        $book_id = $_GET['book_id'] ?? null;
        $student_id = $_GET['student_id'] ?? null;
        $fine = $_GET['fine'] ?? 0;

        if ($book_id && $student_id) {
            $stmt = $conn->prepare("INSERT INTO lost_books (book_id, student_id, fine_amount) VALUES (?, ?, ?)");
            $stmt->bind_param("isd", $book_id, $student_id, $fine);
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Lost book added.']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => $stmt->error]);
            }
            $stmt->close();
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Missing book_id or student_id']);
        }
        break;

    case 'update':
        $lost_id = $_GET['lost_id'] ?? null;
        $status = $_GET['status'] ?? null;

        if ($lost_id && in_array($status, ['pending fine', 'fined', 'paid'])) {
            if ($status === 'paid') {
                $stmt = $conn->prepare("UPDATE lost_books SET status = ?, paid_at = NOW() WHERE lost_id = ?");
            } else {
                $stmt = $conn->prepare("UPDATE lost_books SET status = ?, paid_at = NULL WHERE lost_id = ?");
            }
            $stmt->bind_param("si", $status, $lost_id);
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Status updated']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => $stmt->error]);
            }
            $stmt->close();
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid status or missing lost_id']);
        }
        break;

    case 'list':
    default:
        $result = $conn->query("SELECT * FROM lost_books ORDER BY report_date DESC");
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode($data);
        break;
         $result = $conn->query("
            SELECT lb.*, s.name AS student_name, s.email 
            FROM lost_books lb 
            JOIN students s ON lb.student_id = s.student_id 
            ORDER BY lb.report_date DESC
        ");
}

$conn->close();
