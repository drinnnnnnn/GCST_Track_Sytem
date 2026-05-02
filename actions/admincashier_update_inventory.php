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

if (!$payload || !isset($payload['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request payload.']);
    exit;
}

$productId = intval($payload['product_id']);
if ($productId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product ID.']);
    exit;
}

$productName = trim($payload['product_name'] ?? '');
$category = trim($payload['product_category'] ?? '');
$productStatus = trim($payload['product_status'] ?? 'available');
$barcode = trim($payload['barcode'] ?? '');
$buyPrice = isset($payload['buy_price']) ? floatval($payload['buy_price']) : null;
$rentPrice = isset($payload['rent_price']) ? floatval($payload['rent_price']) : null;
$stockCount = isset($payload['stock_count']) ? intval($payload['stock_count']) : null;

if ($productName === '') {
    echo json_encode(['success' => false, 'message' => 'Product name is required.']);
    exit;
}

if ($stockCount === null || $stockCount < 0) {
    echo json_encode(['success' => false, 'message' => 'Stock count must be a non-negative number.']);
    exit;
}

if ($buyPrice === null || $buyPrice < 0) {
    echo json_encode(['success' => false, 'message' => 'Price must be a valid number.']);
    exit;
}

if ($rentPrice === null || $rentPrice < 0) {
    echo json_encode(['success' => false, 'message' => 'Rent price must be a valid number.']);
    exit;
}

$productStatus = in_array($productStatus, ['available', 'unavailable'], true) ? $productStatus : 'available';

$stockColumn = 'stock_count';
$stockCheck = $conn->query("SHOW COLUMNS FROM `products` LIKE 'stock_count'");
if (!$stockCheck || $stockCheck->num_rows === 0) {
    $stockColumn = 'stock';
}

$priceColumn = 'buy_price';
$priceCheck = $conn->query("SHOW COLUMNS FROM `products` LIKE 'buy_price'");
if (!$priceCheck || $priceCheck->num_rows === 0) {
    $priceColumn = 'price';
}

$productImagePath = null;
if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $fileTmpPath = $_FILES['product_image']['tmp_name'];
    $fileName = basename($_FILES['product_image']['name']);
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    if (in_array($fileExt, $allowedExtensions, true)) {
        $newFileName = uniqid('product_', true) . '.' . $fileExt;
        $destPath = $uploadDir . $newFileName;
        if (move_uploaded_file($fileTmpPath, $destPath)) {
            $productImagePath = 'uploads/' . $newFileName;
        }
    }
}

$updateFields = "product_name = ?, product_category = ?, product_status = ?, barcode = ?, $priceColumn = ?, rent_price = ?, `$stockColumn` = ?";
$params = [$productName, $category, $productStatus, $barcode, $buyPrice, $rentPrice, $stockCount];
$types = 'ssssddi';
if ($productImagePath !== null) {
    $updateFields .= ', product_image = ?';
    $params[] = $productImagePath;
    $types .= 's';
}
$updateFields .= ' WHERE product_id = ?';
$params[] = $productId;
$types .= 'i';

$sql = "UPDATE products SET $updateFields";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Failed to prepare update statement.']);
    exit;
}

$bindValues = array_merge([$types], $params);
$tmp = [];
foreach ($bindValues as $key => $value) {
    $tmp[$key] = &$bindValues[$key];
}
call_user_func_array([$stmt, 'bind_param'], $tmp);

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Unable to update product: ' . $stmt->error]);
    $stmt->close();
    exit;
}
$stmt->close();

$selectSql = "SELECT product_id, product_name, COALESCE(stock_count, stock, 0) AS stock_count, COALESCE(buy_price, price, 0.00) AS buy_price, COALESCE(rent_price, 0.00) AS rent_price, product_category, product_image, barcode, COALESCE(product_status, 'available') AS product_status, CASE WHEN COALESCE(stock_count, stock, 0) = 0 THEN 'Out of Stock' WHEN COALESCE(stock_count, stock, 0) < 10 THEN 'Low Stock' ELSE 'In Stock' END AS status FROM products WHERE product_id = ? LIMIT 1";
$selectStmt = $conn->prepare($selectSql);
$selectStmt->bind_param('i', $productId);
$selectStmt->execute();
$result = $selectStmt->get_result();
$product = $result->fetch_assoc();
$selectStmt->close();

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product updated but could not load refreshed data.']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Inventory updated successfully.', 'product' => $product]);
exit;
?>
