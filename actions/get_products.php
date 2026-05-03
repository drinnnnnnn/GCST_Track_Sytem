<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../database/models/ProductModel.php';

$productModel = new ProductModel();
$products = $productModel->getAll();

$search = trim((string)($_GET['search'] ?? ''));
$category = trim((string)($_GET['category'] ?? ''));
$availableOnly = isset($_GET['available_only']) && in_array(strtolower((string)$_GET['available_only']), ['1', 'true', 'yes'], true);

if ($category !== '') {
    $products = array_filter($products, function ($product) use ($category) {
        return mb_strtolower(trim((string)$product['product_category'] ?? '')) === mb_strtolower($category);
    });
}

if ($search !== '') {
    $searchTerm = mb_strtolower($search);
    $products = array_filter($products, function ($product) use ($searchTerm) {
        $text = mb_strtolower(trim((string)($product['product_name'] ?? '') . ' ' . ($product['product_description'] ?? '') . ' ' . ($product['product_category'] ?? '')));
        return mb_strpos($text, $searchTerm) !== false;
    });
}

if ($availableOnly) {
    $products = array_filter($products, function ($product) {
        return isset($product['stock']) && intval($product['stock']) > 0 && mb_strtolower(trim((string)$product['product_status'] ?? 'available')) === 'available';
    });
}

echo json_encode(array_values($products));
?>