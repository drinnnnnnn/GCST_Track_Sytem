<?php
header('Content-Type: application/json');
include 'functions.php';

$data = json_decode(file_get_contents('php://input'), true);
$adminId = $data['admin_id'] ?? null;

if (!$adminId) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

try {
    $success = deleteAdmin($adminId);
    echo json_encode(['success' => $success]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>