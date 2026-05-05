<?php
require_once __DIR__ . '/security.php';
secureSessionStart();
requireAuth(['admincashier', 'superadmin']);
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db_connect.php';

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 10;
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$from = isset($_GET['from']) ? $conn->real_escape_string($_GET['from']) : '';
$to = isset($_GET['to']) ? $conn->real_escape_string($_GET['to']) : '';
$offset = ($page - 1) * $limit;

$conditions = [];
if ($search !== "") {
    $conditions[] = "(t.transaction_number LIKE '%$search%' OR u.student_id LIKE '%$search%' OR t.student_name LIKE '%$search%')";
}
if ($from !== "") {
    $conditions[] = "DATE(t.created_at) >= '$from'";
}
if ($to !== "") {
    $conditions[] = "DATE(t.created_at) <= '$to'";
}

$whereClause = !empty($conditions) ? " WHERE " . implode(" AND ", $conditions) : "";

$countResult = $conn->query("SELECT COUNT(*) as total FROM cashier_transactions t LEFT JOIN users u ON t.user_id = u.id $whereClause");
$totalRows = $countResult ? $countResult->fetch_assoc()['total'] : 0;

// SQL query to fetch recent transactions with student_id and cashier_name
$sql = "SELECT 
            t.*, 
            u.student_id, 
            CONCAT_WS(' ', a.first_name, a.last_name) AS cashier_name
        FROM cashier_transactions t
        LEFT JOIN users u ON t.user_id = u.id
        LEFT JOIN admincashier_acc a ON t.cashier_id = a.id
        $whereClause
        ORDER BY t.created_at DESC
        LIMIT $limit OFFSET $offset";

$result = $conn->query($sql);
$transactions = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        if (isset($row['items']) && is_string($row['items'])) {
            $row['items'] = json_decode($row['items'], true);
        }
        $transactions[] = $row;
    }
}

echo json_encode([
    'transactions' => $transactions,
    'total_pages' => ceil($totalRows / $limit),
    'current_page' => $page,
    'total_count' => (int)$totalRows
]);
exit;