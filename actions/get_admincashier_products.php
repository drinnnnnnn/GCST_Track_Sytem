<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db_connect.php';
if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed']));
}

$sql = "SELECT product_id, product_name, product_description, COALESCE(stock_count, stock, 0) AS stock_count, 
        COALESCE(buy_price, price, 0.00) AS buy_price, COALESCE(rent_price, 0.00) AS rent_price, 
        product_category, product_image, barcode, COALESCE(product_status, 'available') AS product_status, 
        CASE WHEN COALESCE(stock_count, stock, 0) < 10 THEN 'Low Stock' ELSE 'In Stock' END AS status 
        FROM products";
$result = $conn->query($sql);

$products = [];
while($row = $result->fetch_assoc()) {
    $products[] = $row;
}
echo json_encode($products);
$conn->close();
?>