<?php
header('Content-Type: application/json');
require_once 'admincashier_report_helpers.php';

$conn = connectAdminCashierDb();
if (!$conn) {
    echo json_encode(['error' => 'Connection failed']);
    exit;
}

$period = isset($_GET['period']) ? strtolower($_GET['period']) : 'week';
$validPeriods = ['today', 'week', 'month', 'year'];
if (!in_array($period, $validPeriods, true)) {
    $period = 'week';
}

$salesTable = findSalesTable($conn);
$salesSummary = getSalesSummary($conn, $salesTable, ...getSalesPeriodDateRange($period));
$averageTransactionValue = $salesSummary['total_transactions'] > 0 ? round($salesSummary['total_sales'] / $salesSummary['total_transactions'], 2) : 0.0;
$salesTrend = getSalesTrendForPeriod($conn, $salesTable, $period);
$topProducts = getTopProducts($conn, $salesTable, 6);
$salesHistory = getSalesHistory($conn, $salesTable, $period, 100);

$response = [
    'total_sales' => $salesSummary['total_sales'],
    'total_transactions' => $salesSummary['total_transactions'],
    'average_transaction_value' => $averageTransactionValue,
    'total_items_sold' => $salesSummary['books_sold'],
    'sales_labels' => $salesTrend['labels'],
    'sales_data' => $salesTrend['data'],
    'top_products' => $topProducts,
    'history' => $salesHistory,
    'selected_period' => $period
];

echo json_encode($response);
$conn->close();
