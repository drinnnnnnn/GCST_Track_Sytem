<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../database/models/QueueModel.php';
require_once __DIR__ . '/../config/db_connect.php';

if (!isset($_SESSION['student_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not authenticated']);
    exit;
}

$queueModel = new QueueModel();

$payload = json_decode(file_get_contents('php://input'), true) ?: [];
$queueNumber = $payload['queue_number'] ?? null;
$studentName = $payload['student_name'] ?? '';
$purpose = $payload['purpose'] ?? '';
$studentId = $_SESSION['student_id'];

$userId = null;
$lookupStmt = $conn->prepare('SELECT id FROM users WHERE student_id = ? LIMIT 1');
$lookupStmt->bind_param('s', $studentId);
$lookupStmt->execute();
$lookupStmt->bind_result($userId);
if (!$lookupStmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'User record not found']);
    exit;
}
$lookupStmt->close();

// Rate limiting: Check if the user has generated a ticket in the last 30 minutes
$checkStmt = $conn->prepare("SELECT created_at FROM queue WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
$checkStmt->bind_param('i', $userId);
$checkStmt->execute();
$checkStmt->bind_result($lastCreatedAt);
if ($checkStmt->fetch()) {
    $lastTime = strtotime($lastCreatedAt);
    $diff = time() - $lastTime;
    if ($diff < 1800) { // 1800 seconds = 30 minutes
        $minutesLeft = ceil((1800 - $diff) / 60);
        $checkStmt->close();
        echo json_encode(['success' => false, 'error' => "Rate limit reached. You can only generate one ticket every 30 minutes. Please wait $minutesLeft more minute(s)."]);
        exit;
    }
}
$checkStmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode(['success' => true, 'queue_number' => $queueModel->getNextQueueNumber()]);
    exit;
}

$result = $queueModel->create($queueNumber, $userId, $studentName, $purpose);
if ($result !== false) {
    $ticket = $queueModel->getById($result['id']);
    echo json_encode(['success' => true, 'queue_number' => $result['queue_number'], 'ticket' => $ticket]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to generate queue']);
}
?>