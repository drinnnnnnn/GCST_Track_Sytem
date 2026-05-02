<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../config/db_connect.php';

if (!isset($_SESSION['student_id'])) {
    echo json_encode([]);
    exit;
}

$student_id = $_SESSION['student_id'];
$rentals = [];

function buildImagePath($path) {
    if (empty($path)) {
        return '/assets/img_bg/product_details.png';
    }
    return $path;
}

$sql = "SELECT ar.rental_id, ar.product_id, p.product_name, p.product_image, p.rent_price, ar.date_student_received_book, ar.return_date, ar.status
        FROM active_rentals ar
        LEFT JOIN products p ON ar.product_id = p.product_id
        WHERE ar.student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $status = strtolower(trim($row['status'] ?? 'borrowed'));
        if ($status === 'borrowed') {
            $status = 'active';
        }

        if ($status !== 'returned' && !empty($row['return_date']) && $row['return_date'] < date('Y-m-d')) {
            $status = 'overdue';
        }

        $rentals[] = [
            'rental_id' => intval($row['rental_id']),
            'product_id' => intval($row['product_id']),
            'product_name' => $row['product_name'],
            'product_image' => buildImagePath($row['product_image']),
            'rental_date' => $row['date_student_received_book'],
            'due_date' => $row['return_date'],
            'status' => $status,
            'rental_fee' => isset($row['rent_price']) ? floatval($row['rent_price']) : 0
        ];
    }
}
$stmt->close();

$sql = "SELECT ib.id AS rental_id, ib.product_id, p.product_name, p.product_image, p.rent_price, ib.date_student_received_book, ib.book_returned_date
        FROM issued_books ib
        LEFT JOIN products p ON ib.product_id = p.product_id
        WHERE ib.student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $rentals[] = [
            'rental_id' => intval($row['rental_id']),
            'product_id' => intval($row['product_id']),
            'product_name' => $row['product_name'],
            'product_image' => buildImagePath($row['product_image']),
            'rental_date' => $row['date_student_received_book'],
            'due_date' => $row['book_returned_date'],
            'status' => 'returned',
            'rental_fee' => isset($row['rent_price']) ? floatval($row['rent_price']) : 0
        ];
    }
}
$stmt->close();
$conn->close();

echo json_encode(['rentals' => $rentals]);
?>
