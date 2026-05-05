<?php
require_once __DIR__ . '/security.php';
secureSessionStart();
requireAuth(['admincashier', 'superadmin']);
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db_connect.php';

$conn->begin_transaction();

try {
    // 1. Find pending orders older than 2 days
    $stmt = $conn->prepare("
        SELECT id, transaction_number, items 
        FROM cashier_transactions 
        WHERE payment_status = 'pending' 
        AND created_at < (NOW() - INTERVAL 2 DAY)
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $expiredOrders = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $voidedCount = 0;

    if (!empty($expiredOrders)) {
        // Determine stock column
        $stockColumn = 'stock_count';
        $colCheck = $conn->query("SHOW COLUMNS FROM `products` LIKE 'stock_count'");
        if (!$colCheck || $colCheck->num_rows === 0) $stockColumn = 'stock';

        foreach ($expiredOrders as $txn) {
            $txnNumber = $txn['transaction_number'];
            $items = json_decode($txn['items'], true);

            // Restore Stock
            if (is_array($items)) {
                foreach ($items as $item) {
                    $productId = intval($item['product_id']);
                    $qty = intval($item['quantity']);
                    
                    $updateStock = $conn->prepare("UPDATE products SET `$stockColumn` = `$stockColumn` + ? WHERE product_id = ?");
                    $updateStock->bind_param('ii', $qty, $productId);
                    $updateStock->execute();
                    $updateStock->close();
                }
            }

            // Update Transaction Status
            $updateTxn = $conn->prepare("UPDATE cashier_transactions SET payment_status = 'voided' WHERE id = ?");
            $updateTxn->bind_param('i', $txn['id']);
            $updateTxn->execute();
            $updateTxn->close();

            // Cancel associated rentals
            $updateRentals = $conn->prepare("UPDATE active_rentals SET status = 'returned', rejection_reason = 'Auto-voided: Order Expired (2-day limit)' WHERE transaction_number = ? AND status != 'returned'");
            $updateRentals->bind_param('s', $txnNumber);
            $updateRentals->execute();
            $updateRentals->close();

            if (function_exists('logAudit')) {
                logAudit('system', '0', 'auto_void_transaction', "Auto-voided expired transaction: $txnNumber");
            }
            $voidedCount++;
        }
    }

    $conn->commit();
    echo json_encode(['success' => true, 'voided_count' => $voidedCount]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}