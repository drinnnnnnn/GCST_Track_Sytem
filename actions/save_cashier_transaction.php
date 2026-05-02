<?php
require_once __DIR__ . '/security.php';
secureSessionStart();
requireAuth(['student', 'admincashier', 'superadmin']);
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db_connect.php';
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

$adminId = $_SESSION['admin_id'] ?? null;
$studentId = $_SESSION['student_id'] ?? null;

if (!$adminId && !$studentId) {
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit;
}

$userId = null;
if ($studentId) {
    $lookupStmt = $conn->prepare('SELECT id FROM users WHERE student_id = ? LIMIT 1');
    $lookupStmt->bind_param('s', $studentId);
    $lookupStmt->execute();
    $lookupStmt->bind_result($userId);
    if (!$lookupStmt->fetch() || !$userId) {
        $lookupStmt->close();
        echo json_encode(['success' => false, 'message' => 'Unable to resolve authenticated user.']);
        exit;
    }
    $lookupStmt->close();
}

$payload = json_decode(file_get_contents('php://input'), true);
if (!$payload || !isset($payload['items']) || !is_array($payload['items']) || count($payload['items']) === 0) {
    echo json_encode(['success' => false, 'message' => 'No items in cart.']);
    exit;
}

$subtotal = isset($payload['subtotal']) ? floatval($payload['subtotal']) : 0.0;
$discountPercent = isset($payload['discount_percent']) ? floatval($payload['discount_percent']) : 0.0;
$discountAmount = isset($payload['discount_amount']) ? floatval($payload['discount_amount']) : 0.0;
$totalAmount = isset($payload['total_amount']) ? floatval($payload['total_amount']) : 0.0;
$paymentReceived = isset($payload['payment_received']) ? floatval($payload['payment_received']) : 0.0;
$paymentStatus = isset($payload['payment_status']) && in_array($payload['payment_status'], ['paid', 'pending'], true) ? $payload['payment_status'] : 'paid';
$changeAmount = isset($payload['change_amount']) ? floatval($payload['change_amount']) : 0.0;

if ($totalAmount < 0 || $subtotal < 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid amount values.']);
    exit;
}

if ($paymentStatus === 'paid' && $paymentReceived < $totalAmount) {
    echo json_encode(['success' => false, 'message' => 'Payment must cover total amount for paid status.']);
    exit;
}

// Create the cashier_transactions table if it does not exist.
$createTableSql = "CREATE TABLE IF NOT EXISTS `cashier_transactions` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `transaction_number` VARCHAR(50) NOT NULL,
    `cashier_id` INT(11) NOT NULL,
    `transaction_type` ENUM('buy','rent','mixed') NOT NULL,
    `items` TEXT NOT NULL,
    `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `discount_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `discount_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `total_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `payment_received` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `change_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `payment_status` ENUM('paid','pending') NOT NULL DEFAULT 'pending',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_cashier_transactions_cashier_id` (`cashier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$conn->query($createTableSql);

$typeColumnInfo = $conn->query("SHOW COLUMNS FROM `cashier_transactions` LIKE 'transaction_type'");
if ($typeColumnInfo && $typeColumnInfo->num_rows > 0) {
    $typeDef = $typeColumnInfo->fetch_assoc()['Type'];
    if (strpos($typeDef, "'mixed'") === false) {
        $conn->query("ALTER TABLE `cashier_transactions` MODIFY COLUMN `transaction_type` ENUM('buy','rent','mixed') NOT NULL");
    }
}

$stockColumn = 'stock_count';
$stockCheck = $conn->query("SHOW COLUMNS FROM `products` LIKE 'stock_count'");
if (!$stockCheck || $stockCheck->num_rows === 0) {
    $stockColumn = 'stock';
}

$allowedTypes = ['buy', 'rent'];
$itemTypes = [];
$cartItems = [];
$conn->begin_transaction();
try {
    foreach ($payload['items'] as $item) {
        $productId = intval($item['product_id'] ?? 0);
        $quantity = intval($item['quantity'] ?? 0);
        $type = strtolower(trim($item['type'] ?? $item['item_type'] ?? 'buy'));

        if ($productId <= 0 || $quantity <= 0 || !in_array($type, $allowedTypes, true)) {
            throw new Exception('Invalid cart item data.');
        }

        $itemTypes[] = $type;

        $stmt = $conn->prepare("SELECT product_id, product_name, COALESCE(stock_count, stock, 0) AS available_stock, COALESCE(buy_price, price, 0.00) AS buy_price, COALESCE(rent_price, 0.00) AS rent_price FROM products WHERE product_id = ? LIMIT 1");
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$product) {
            throw new Exception('Product not found.');
        }

        if ($product['available_stock'] < $quantity) {
            throw new Exception('Insufficient stock for ' . $product['product_name']);
        }

        $unitPrice = $type === 'rent' ? floatval($product['rent_price']) : floatval($product['buy_price']);
        if ($unitPrice <= 0) {
            throw new Exception('Invalid selected price for ' . $product['product_name']);
        }

        $itemTotal = round($unitPrice * $quantity, 2);
        $cartItems[] = [
            'product_id' => $productId,
            'product_name' => $product['product_name'],
            'type' => $type,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total' => $itemTotal,
        ];

        $updateStmt = $conn->prepare("UPDATE products SET `$stockColumn` = `$stockColumn` - ? WHERE product_id = ?");
        $updateStmt->bind_param('ii', $quantity, $productId);
        $updateStmt->execute();
        $updateStmt->close();
    }

    $transactionType = count(array_unique($itemTypes)) === 1 ? $itemTypes[0] : 'mixed';
    $transactionNumber = 'ORDER-' . time() . '-' . bin2hex(random_bytes(4));
    $itemsJson = $conn->real_escape_string(json_encode($cartItems, JSON_UNESCAPED_UNICODE));
    $cashierId = $adminId ?? 0;

    $insertStmt = $conn->prepare("INSERT INTO cashier_transactions (transaction_number, cashier_id, transaction_type, items, subtotal, discount_percent, discount_amount, total_amount, payment_received, change_amount, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $insertStmt->bind_param('sissdddddds', $transactionNumber, $cashierId, $transactionType, $itemsJson, $subtotal, $discountPercent, $discountAmount, $totalAmount, $paymentReceived, $changeAmount, $paymentStatus);
    if (!$insertStmt->execute()) {
        throw new Exception('Could not save transaction: ' . $insertStmt->error);
    }
    $insertStmt->close();

    if ($userId) {
        $itemInsert = $conn->prepare('INSERT INTO transactions (user_id, product_id, type, quantity, total_amount) VALUES (?, ?, ?, ?, ?)');
        foreach ($cartItems as $item) {
            $itemInsert->bind_param('iisid', $userId, $item['product_id'], $item['type'], $item['quantity'], $item['total']);
            if (!$itemInsert->execute()) {
                throw new Exception('Failed to record item transaction: ' . $itemInsert->error);
            }
        }
        $itemInsert->close();
    }

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Transaction completed successfully.',
        'transaction_number' => $transactionNumber,
        'transaction_id' => $conn->insert_id,
        'payment_status' => $paymentStatus,
        'receipt' => [
            'transaction_number' => $transactionNumber,
            'transaction_type' => $transactionType,
            'items' => $cartItems,
            'subtotal' => number_format($subtotal, 2, '.', ''),
            'discount_amount' => number_format($discountAmount, 2, '.', ''),
            'total_amount' => number_format($totalAmount, 2, '.', ''),
            'payment_received' => number_format($paymentReceived, 2, '.', ''),
            'change_amount' => number_format($changeAmount, 2, '.', ''),
            'payment_status' => ucfirst($paymentStatus),
            'created_at' => date('Y-m-d H:i:s')
        ]
    ]);
    exit;
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}
?>