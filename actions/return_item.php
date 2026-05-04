<?php
require_once __DIR__ . '/security.php';
secureSessionStart();
requireAuth(['admincashier', 'superadmin']);
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db_connect.php';

$payload = json_decode(file_get_contents('php://input'), true) ?: [];
$rental_id = isset($payload['rental_id']) ? intval($payload['rental_id']) : 0;

if (!$rental_id) {
    echo json_encode(['success' => false, 'message' => 'Missing rental_id']);
    exit;
}

$conn->begin_transaction();
try {
    // 1. Get rental details
    $stmt = $conn->prepare("SELECT product_id, quantity FROM active_rentals WHERE rental_id = ? FOR UPDATE");
    $stmt->bind_param('i', $rental_id);
    $stmt->execute();
    $rental = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$rental) {
        throw new Exception('Rental record not found.');
    }

    $productId = $rental['product_id'];
    $quantity = $rental['quantity'];

    // 2. Identify the correct stock column
    $stockColumn = 'stock_count';
    $stockCheck = $conn->query("SHOW COLUMNS FROM `products` LIKE 'stock_count'");
    if (!$stockCheck || $stockCheck->num_rows === 0) {
        $stockColumn = 'stock';
    }

    // 3. Update rental status
    $updateRental = $conn->prepare("UPDATE active_rentals SET status = 'returned' WHERE rental_id = ?");
    $updateRental->bind_param('i', $rental_id);
    if (!$updateRental->execute()) {
        throw new Exception('Failed to update rental status.');
    }
    $updateRental->close();

    // 4. Increment product stock
    $updateStock = $conn->prepare("UPDATE products SET `$stockColumn` = `$stockColumn` + ? WHERE product_id = ?");
    $updateStock->bind_param('ii', $quantity, $productId);
    if (!$updateStock->execute()) {
        throw new Exception('Failed to restock product.');
    }
    $updateStock->close();

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Item successfully returned and stock updated.']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
$conn->close();
?>