<?php
require_once __DIR__ . '/security.php';
secureSessionStart();
requireAuth(['student', 'admincashier', 'superadmin']);
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/email_helpers.php';
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

$adminId = $_SESSION['admin_id'] ?? null;

$payload = json_decode(file_get_contents('php://input'), true);
if (!$payload || !isset($payload['items']) || !is_array($payload['items']) || count($payload['items']) === 0) {
    jsonResponse(['success' => false, 'message' => 'No items in cart.'], 400);
}

// Allow student ID to come from the payload (selected by cashier) or session
$studentId = $payload['student_id'] ?? $_SESSION['student_id'] ?? null;

if (!$adminId && !$studentId) {
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit;
}

$userId = null;
$studentFullName = null;
if ($studentId) {
    $lookupStmt = $conn->prepare('SELECT id, first_name, last_name FROM users WHERE student_id = ? LIMIT 1');
    $lookupStmt->bind_param('s', $studentId);
    $lookupStmt->execute();
    $lookupStmt->bind_result($userId, $fName, $lName);
    if ($lookupStmt->fetch() && $userId) {
        $studentFullName = trim($fName . ' ' . $lName);
    } else {
        $lookupStmt->close();
        echo json_encode(['success' => false, 'message' => 'Unable to resolve authenticated user.']);
        exit;
    }
    $lookupStmt->close();
}

$subtotal = isset($payload['subtotal']) ? floatval($payload['subtotal']) : 0.0;
$discountPercent = isset($payload['discount_percent']) ? floatval($payload['discount_percent']) : 0.0;
$discountAmount = isset($payload['discount_amount']) ? floatval($payload['discount_amount']) : 0.0;
$totalAmount = isset($payload['total_amount']) ? floatval($payload['total_amount']) : 0.0;
$paymentReceived = isset($payload['payment_received']) ? floatval($payload['payment_received']) : 0.0;
$paymentStatus = isset($payload['payment_status']) && in_array($payload['payment_status'], ['paid', 'pending'], true) ? $payload['payment_status'] : 'paid';
$changeAmount = isset($payload['change_amount']) ? floatval($payload['change_amount']) : 0.0;
$receiptNumber = isset($payload['receipt_number']) ? trim((string)$payload['receipt_number']) : null;

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
    `receipt_number` VARCHAR(100) DEFAULT NULL,
    `user_id` INT(11) DEFAULT NULL,
    `student_name` VARCHAR(255) DEFAULT NULL,
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
    UNIQUE KEY `uniq_transaction_number` (`transaction_number`),
    UNIQUE KEY `uniq_receipt_number` (`receipt_number`),
    KEY `idx_cashier_transactions_user_id` (`user_id`),
    KEY `idx_cashier_transactions_cashier_id` (`cashier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$conn->query($createTableSql);

// Create the active_rentals table if it does not exist.
$createRentalsTableSql = "CREATE TABLE IF NOT EXISTS `active_rentals` (
    `rental_id` INT(11) NOT NULL AUTO_INCREMENT,
    `transaction_number` VARCHAR(50) NOT NULL,
    `student_id` VARCHAR(50) NOT NULL,
    `product_id` INT(11) NOT NULL,
    `quantity` INT(11) NOT NULL DEFAULT 1,
    `rental_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `return_date` DATETIME NOT NULL,
    `rejection_reason` TEXT DEFAULT NULL,
    `status` ENUM('active','returned','overdue','pending_renewal') NOT NULL DEFAULT 'active',
    PRIMARY KEY (`rental_id`),
    KEY `idx_active_rentals_student` (`student_id`),
    KEY `idx_active_rentals_transaction` (`transaction_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$conn->query($createRentalsTableSql);

$typeColumnInfo = $conn->query("SHOW COLUMNS FROM `cashier_transactions` LIKE 'transaction_type'");
if ($typeColumnInfo && $typeColumnInfo->num_rows > 0) {
    $typeDef = $typeColumnInfo->fetch_assoc()['Type'];
    if (strpos($typeDef, "'mixed'") === false) {
        $conn->query("ALTER TABLE `cashier_transactions` MODIFY COLUMN `transaction_type` ENUM('buy','rent','mixed') NOT NULL");
    }
}

$userCheck = $conn->query("SHOW COLUMNS FROM `cashier_transactions` LIKE 'user_id'");
if (!$userCheck || $userCheck->num_rows === 0) {
    $conn->query("ALTER TABLE `cashier_transactions` ADD COLUMN `user_id` INT(11) DEFAULT NULL AFTER `receipt_number` , ADD INDEX (`user_id`) ");
}

$nameCheck = $conn->query("SHOW COLUMNS FROM `cashier_transactions` LIKE 'student_name'");
if (!$nameCheck || $nameCheck->num_rows === 0) {
    $conn->query("ALTER TABLE `cashier_transactions` ADD COLUMN `student_name` VARCHAR(255) DEFAULT NULL AFTER `user_id` ");
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

        if ($type === 'rent' && !$studentId) {
            throw new Exception('Student ID is required for rental items.');
        }

        if ($productId <= 0 || $quantity <= 0 || !in_array($type, $allowedTypes, true)) {
            throw new Exception('Invalid cart item data.');
        }

        $itemTypes[] = $type;

        $stmt = $conn->prepare("SELECT product_id, product_name, product_category, COALESCE(stock_count, stock, 0) AS available_stock, COALESCE(buy_price, price, 0.00) AS buy_price, COALESCE(rent_price, 0.00) AS rent_price FROM products WHERE product_id = ? LIMIT 1");
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$product) {
            throw new Exception('Product not found.');
        }

        if ($type === 'buy' && strtolower($product['product_category'] ?? '') === 'books') {
            throw new Exception('Books can only be rented. Invalid item: ' . $product['product_name']);
        }

        if ($type === 'rent' && strtolower($product['product_category'] ?? '') !== 'books') {
            throw new Exception('Rental is only allowed for Books. Invalid item: ' . $product['product_name']);
        }

        if ($product['available_stock'] < $quantity) {
            throw new Exception('Insufficient stock for ' . $product['product_name']);
        }

        $unitPrice = $type === 'rent' ? floatval($product['rent_price']) : floatval($product['buy_price']);
        if ($unitPrice <= 0) {
            throw new Exception('Invalid selected price for ' . $product['product_name']);
        }

        $duration = ($type === 'rent') ? intval($item['duration'] ?? 1) : 1;
        $durationUnit = $item['duration_unit'] ?? ($type === 'rent' ? 'days' : null);

        $returnDate = null;
        if ($type === 'rent') {
            $unit = in_array($durationUnit, ['hours', 'days', 'weeks', 'months']) ? $durationUnit : 'days';
            $returnDate = date('Y-m-d H:i:s', strtotime("+$duration $unit"));
        }

        $itemTotal = round($unitPrice * $duration * $quantity, 2);

        $cartItems[] = [
            'product_id' => $productId,
            'product_name' => $product['product_name'],
            'type' => $type,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'duration' => $duration,
            'duration_unit' => $durationUnit,
            'return_date' => $returnDate,
            'total' => $itemTotal,
        ];

        $updateStmt = $conn->prepare("UPDATE products SET `$stockColumn` = `$stockColumn` - ? WHERE product_id = ?");
        $updateStmt->bind_param('ii', $quantity, $productId);
        $updateStmt->execute();
        $updateStmt->close();
    }

    $transactionType = count(array_unique($itemTypes)) === 1 ? $itemTypes[0] : 'mixed';
    $transactionNumber = 'ORDER-' . time() . '-' . bin2hex(random_bytes(4));
    $itemsJson = json_encode($cartItems, JSON_UNESCAPED_UNICODE);
    $cashierId = $adminId ?? 0;

    if ($receiptNumber !== null && $receiptNumber !== '') {
        $existingReceipt = $conn->prepare('SELECT 1 FROM cashier_transactions WHERE receipt_number = ? LIMIT 1');
        $existingReceipt->bind_param('s', $receiptNumber);
        $existingReceipt->execute();
        $existingReceipt->store_result();
        if ($existingReceipt->num_rows > 0) {
            throw new Exception('Duplicate receipt number detected.');
        }
        $existingReceipt->close();
    }

    $insertStmt = $conn->prepare("INSERT INTO cashier_transactions (transaction_number, receipt_number, user_id, student_name, cashier_id, transaction_type, items, subtotal, discount_percent, discount_amount, total_amount, payment_received, change_amount, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $insertStmt->bind_param('ssiss ss ddd ddd s', $transactionNumber, $receiptNumber, $userId, $studentFullName, $cashierId, $transactionType, $itemsJson, $subtotal, $discountPercent, $discountAmount, $totalAmount, $paymentReceived, $changeAmount, $paymentStatus);
    if (!$insertStmt->execute()) {
        throw new Exception('Could not save transaction: ' . $insertStmt->error);
    }
    $insertStmt->close();

    if ($userId) {
        $itemInsert = $conn->prepare('INSERT INTO transactions (user_id, product_id, type, quantity, total_amount) VALUES (?, ?, ?, ?, ?)');
        $rentalInsert = $conn->prepare('INSERT INTO active_rentals (transaction_number, student_id, product_id, quantity, return_date, status) VALUES (?, ?, ?, ?, ?, "active")');

        foreach ($cartItems as $item) {
            $itemInsert->bind_param('iisid', $userId, $item['product_id'], $item['type'], $item['quantity'], $item['total']);
            if (!$itemInsert->execute()) {
                throw new Exception('Failed to record item transaction: ' . $itemInsert->error);
            }

            if ($item['type'] === 'rent') {
                $rentalInsert->bind_param('ssiis', $transactionNumber, $studentId, $item['product_id'], $item['quantity'], $item['return_date']);
                if (!$rentalInsert->execute()) {
                    throw new Exception('Failed to record active rental: ' . $rentalInsert->error);
                }
            }
        }
        $itemInsert->close();
        $rentalInsert->close();
    }

    $conn->commit();

    $emailStatus = 'skipped';
    $userEmail = null;
    if ($userId) {
        $emailStmt = $conn->prepare('SELECT email, first_name, last_name FROM users WHERE id = ? LIMIT 1');
        if ($emailStmt) {
            $emailStmt->bind_param('i', $userId);
            $emailStmt->execute();
            $emailResult = $emailStmt->get_result();
            $userData = $emailResult ? $emailResult->fetch_assoc() : null;
            $emailStmt->close();

            if ($userData && filter_var($userData['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
                $userEmail = $userData['email'];
                $studentFullName = trim(($userData['first_name'] ?? '') . ' ' . ($userData['last_name'] ?? ''));
                $qrUrl = 'https://chart.googleapis.com/chart?cht=qr&chs=400x400&chl=' . rawurlencode($transactionNumber) . '&chld=L|1';
                $inlineQr = "<div style=\"text-align:center;margin:30px 0;padding:20px;border:2px dashed #2563eb;border-radius:16px;background:#f8fafc;\">" .
                    "<div style=\"font-size:18px;font-weight:bold;color:#2563eb;margin-bottom:15px;letter-spacing:1px;\">PRESENT TO CASHIER</div>" .
                    "<img src=\"{$qrUrl}\" alt=\"Order QR Code\" style=\"max-width:100%;height:auto;border:1px solid #ddd;border-radius:12px;\" />" .
                    "</div>";
                $emailBody = "<p>Hi " . htmlspecialchars($studentFullName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . ",</p>" .
                    "<p>Your order has been placed. Please present the QR code below to the cashier for quick scanning.</p>" .
                    "<p style='color:#dc2626;'><strong>Note:</strong> This QR code will expire and become invalid after 2 days (48 hours).</p>" .
                    "<p><strong>Order reference:</strong> " . htmlspecialchars($transactionNumber, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</p>" .
                    $inlineQr .
                    "<p>Order total: ₱" . number_format($totalAmount, 2, '.', ',') . "</p>" .
                    "<p>Thank you for using GCST Tracking System.</p>";

                $sendResult = sendEmailWithLog($conn, $userEmail, 'Your GCST Order QR Code', $emailBody, 'Order Confirmation');
                $emailStatus = $sendResult['status'] === 'sent' ? 'sent' : 'failed';
            }
        }
    }

    logAudit($adminId ? 'admincashier' : 'student', $adminId ?? $studentId, 'save_cashier_transaction', 'Saved transaction ' . $transactionNumber . ' and email status: ' . $emailStatus);
    echo json_encode([
        'success' => true,
        'message' => 'Transaction completed successfully.',
        'transaction_number' => $transactionNumber,
        'receipt_number' => $receiptNumber,
        'transaction_id' => $conn->insert_id,
        'payment_status' => $paymentStatus,
        'receipt' => [
            'transaction_number' => $transactionNumber,
            'receipt_number' => $receiptNumber,
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