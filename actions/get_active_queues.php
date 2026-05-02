<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../database/models/QueueModel.php';

$queueModel = new QueueModel();
$queues = $queueModel->getActiveQueues();
$counts = $queueModel->getQueueCounts();

echo json_encode(['queues' => $queues, 'counts' => $counts]);
?>