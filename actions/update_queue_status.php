<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../database/models/QueueModel.php';

$data = json_decode(file_get_contents('php://input'), true);
$queue_id = $data['queue_id'] ?? null;
$status = $data['status'] ?? null;

if (!$queue_id || !$status) {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit;
}

if (!in_array($status, ['serving', 'completed', 'cancelled'], true)) {
    echo json_encode(['success' => false, 'error' => 'Invalid status']);
    exit;
}

$queueModel = new QueueModel();
$success = $queueModel->updateStatus($queue_id, $status);

echo json_encode(['success' => $success]);
?>