<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db_connect.php';

$query = "SELECT product_id, product_name, product_author, product_category, product_description, product_image, price FROM products ORDER BY created_at DESC LIMIT 6";
$result = $conn->query($query);

$products = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

echo json_encode($products);
$conn->close();
?>
