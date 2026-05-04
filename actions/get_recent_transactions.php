<?php
require_once __DIR__ . '/security.php';
secureSessionStart();
requireAuth(['admincashier', 'superadmin']);
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db_connect.php';

$sql = "SELECT * FROM cashier_transactions ORDER BY created_at DESC LIMIT 50";
$result = $conn->query($sql);

$transactions = [];
if ($result) {
    while($row = $result->fetch_assoc()) {
        if (isset($row['items'])) {
            $row['items'] = json_decode($row['items'], true);
        }
        $transactions[] = $row;
    }
}
echo json_encode($transactions);
$conn->close();
?>