<?php
require_once __DIR__ . '/security.php';
secureSessionStart();
requireAuth(['admincashier', 'superadmin']);
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db_connect.php';

$txnNumber = $_GET['transaction_number'] ?? '';

if (empty($txnNumber)) {
    echo json_encode(['success' => false, 'message' => 'Transaction number is required.']);
    exit;
}

$stmt = $conn->prepare("
    SELECT ct.*, u.student_id, a.name as cashier_name 
    FROM cashier_transactions ct 
    LEFT JOIN users u ON ct.user_id = u.id 
    LEFT JOIN admins a ON ct.cashier_id = a.admin_id 
    WHERE ct.transaction_number = ? 
    LIMIT 1
");

$stmt->bind_param('s', $txnNumber);
$stmt->execute();
$result = $stmt->get_result();
$txn = $result->fetch_assoc();
$stmt->close();

echo json_encode(['success' => !!$txn, 'transaction' => $txn, 'message' => $txn ? '' : 'Transaction not found.']);