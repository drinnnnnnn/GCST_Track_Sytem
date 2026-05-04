<?php
require_once __DIR__ . '/security.php';
secureSessionStart();
requireAuth(['admincashier', 'superadmin']);
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db_connect.php';

$payload = json_decode(file_get_contents('php://input'), true) ?: [];
$rental_id = isset($payload['rental_id']) ? intval($payload['rental_id']) : 0;
$action = isset($payload['action']) ? $payload['action'] : 'approve';
$reason = isset($payload['reason']) ? $payload['reason'] : '';

if (!$rental_id) {
    echo json_encode(['success' => false, 'error' => 'Missing rental_id']);
    exit;
}

// Ensure column exists for rejection reasons
$check = $conn->query("SHOW COLUMNS FROM `active_rentals` LIKE 'rejection_reason'");
if (!$check || $check->num_rows === 0) {
    $conn->query("ALTER TABLE `active_rentals` ADD COLUMN `rejection_reason` TEXT DEFAULT NULL AFTER `return_date` ");
}

$status = ($action === 'reject') ? 'overdue' : 'active'; 

$stmt = $conn->prepare("UPDATE active_rentals SET status = ?, rejection_reason = ? WHERE rental_id = ?");
$stmt->bind_param('ssi', $status, $reason, $rental_id);
$success = $stmt->execute();
$stmt->close();
$conn->close();

echo json_encode(['success' => $success]);
?>