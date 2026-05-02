<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db_connect.php';

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

$adminId = $_SESSION['admin_id'] ?? null;
if (!$adminId) {
    echo json_encode(['success' => false, 'message' => 'Authentication required.']);
    exit;
}

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (strpos($contentType, 'application/json') !== false) {
    $payload = json_decode(file_get_contents('php://input'), true);
} else {
    $payload = $_POST;
}

$productId = isset($payload['product_id']) ? intval($payload['product_id']) : 0;
if ($productId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID.']);
    exit;
}

$stmt = $conn->prepare('DELETE FROM products WHERE product_id = ?');
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Failed to prepare delete statement.']);
    exit;
}
$stmt->bind_param('i', $productId);
if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Unable to delete product: ' . $stmt->error]);
    $stmt->close();
    exit;
}

$deleted = $stmt->affected_rows > 0;
$stmt->close();
if (!$deleted) {
    echo json_encode(['success' => false, 'message' => 'No product was deleted.']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Product deleted successfully.']);
exit;
?>
