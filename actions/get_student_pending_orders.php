<?php
require_once __DIR__ . '/security.php';
secureSessionStart();
requireAuth(['student']);
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db_connect.php';

$studentId = $_SESSION['student_id'] ?? null;
if (!$studentId) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Resolve userId
$lookupStmt = $conn->prepare('SELECT id FROM users WHERE student_id = ? LIMIT 1');
$lookupStmt->bind_param('s', $studentId);
$lookupStmt->execute();
$lookupStmt->bind_result($userId);
if (!$lookupStmt->fetch()) {
    $lookupStmt->close();
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}
$lookupStmt->close();

// Fetch pending transactions from cashier_transactions
$stmt = $conn->prepare("SELECT 
    transaction_number, items, created_at, is_urgent, scheduled_at, payment_status,
    (CASE WHEN created_at < DATE_SUB(NOW(), INTERVAL 2 DAY) THEN 1 ELSE 0 END) as is_expired
    FROM cashier_transactions 
    WHERE user_id = ? AND payment_status = 'pending' 
    ORDER BY created_at DESC");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

echo json_encode(['success' => true, 'orders' => $orders]);
?>