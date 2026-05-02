<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../database/models/QueueModel.php';

if (!isset($_SESSION['student_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not authenticated']);
    exit;
}

$queueModel = new QueueModel();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode(['success' => true, 'queue_number' => $queueModel->getNextQueueNumber()]);
    exit;
}

$payload = json_decode(file_get_contents('php://input'), true) ?: [];
$queueNumber = $payload['queue_number'] ?? null;
$userId = (int) $_SESSION['student_id'];

$result = $queueModel->create($queueNumber, $userId);
if ($result !== false) {
    $ticket = $queueModel->getById($result['id']);
    echo json_encode(['success' => true, 'queue_number' => $result['queue_number'], 'ticket' => $ticket]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to generate queue']);
}
?>