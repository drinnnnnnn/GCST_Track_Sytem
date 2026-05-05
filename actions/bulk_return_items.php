<?php
require_once __DIR__ . '/security.php';
secureSessionStart();
requireAuth(['admincashier', 'superadmin']);
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db_connect.php';

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

$payload = json_decode(file_get_contents('php://input'), true);
$rentalIds = $payload['rental_ids'] ?? [];

if (!is_array($rentalIds) || empty($rentalIds)) {
    echo json_encode(['success' => false, 'message' => 'No items selected.']);
    exit;
}

$conn->begin_transaction();
try {
    // Determine stock column name
    $stockColumn = 'stock_count';
    $stockCheck = $conn->query("SHOW COLUMNS FROM `products` LIKE 'stock_count'");
    if (!$stockCheck || $stockCheck->num_rows === 0) {
        $stockColumn = 'stock';
    }

    $getRentalStmt = $conn->prepare("SELECT product_id, quantity FROM active_rentals WHERE rental_id = ? AND status != 'returned' FOR UPDATE");
    $updateRentalStmt = $conn->prepare("UPDATE active_rentals SET status = 'returned' WHERE rental_id = ?");
    $updateStockStmt = $conn->prepare("UPDATE products SET `$stockColumn` = `$stockColumn` + ? WHERE product_id = ?");

    foreach ($rentalIds as $id) {
        $rentalId = intval($id);
        
        $getRentalStmt->bind_param('i', $rentalId);
        $getRentalStmt->execute();
        $rental = $getRentalStmt->get_result()->fetch_assoc();
        
        if ($rental) {
            // Mark as returned
            $updateRentalStmt->bind_param('i', $rentalId);
            $updateRentalStmt->execute();

            // Restore stock
            $updateStockStmt->bind_param('ii', $rental['quantity'], $rental['product_id']);
            $updateStockStmt->execute();
        }
    }

    $getRentalStmt->close();
    $updateRentalStmt->close();
    $updateStockStmt->close();

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Selected items returned successfully.']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Bulk return failed: ' . $e->getMessage()]);
}
?>