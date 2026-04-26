<?php
header('Content-Type: application/json');
$host = "localhost";
$username = "root";
$password = "";
$dbname = "gcst_tracking_system";
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed']));
}

$sql = "SELECT product_id, product_name, stock_count, buy_price, rent_price, product_category, barcode, 
        CASE WHEN stock_count < 10 THEN 'Low Stock' ELSE 'In Stock' END as status 
        FROM products";
$result = $conn->query($sql);

$products = [];
while($row = $result->fetch_assoc()) {
    $products[] = $row;
}
echo json_encode($products);
$conn->close();
?>