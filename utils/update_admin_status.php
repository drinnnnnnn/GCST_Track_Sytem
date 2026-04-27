<?php
header('Content-Type: application/json');
include 'functions.php';

$data = json_decode(file_get_contents('php://input'), true);
$adminId = $data['admin_id'] ?? null;
$status = $data['status'] ?? null;

if (!$adminId || !$status) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

try {
    $success = updateAdminStatus($adminId, $status);
    echo json_encode(['success' => $success]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>