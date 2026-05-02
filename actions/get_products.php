<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../database/models/ProductModel.php';

$productModel = new ProductModel();
$products = $productModel->getAll();

echo json_encode($products);
?>