<?php
header('Content-Type: application/json');
require_once 'admincashier_report_helpers.php';

$conn = connectAdminCashierDb();
if (!$conn) {
    echo json_encode(['error' => 'Connection failed']);
    exit;
}

$salesTable = findSalesTable($conn);
$salesSummary = getSalesSummary($conn, $salesTable);
$inventoryMetrics = getProductInventoryMetrics($conn);
$rentalMetrics = getActiveRentalMetrics($conn);
$queueCount = getQueueCount($conn);

$response = [
    'total_sales_today' => getSalesToday($conn, $salesTable),
    'total_inventory' => $inventoryMetrics['total_inventory'],
    'pending_queue' => $queueCount,
    'books_rented' => $rentalMetrics['active_rentals'],
    'books_sold' => $salesSummary['books_sold'],
    'total_transactions' => $salesSummary['total_transactions']
];

echo json_encode($response);
$conn->close();
