<?php
require_once __DIR__ . '/security.php';
secureSessionStart();
requireAuth(['admincashier', 'superadmin']);
header('Content-Type: application/json');
require_once __DIR__ . '/../config/db_connect.php';

// Ensure active rentals are synced to overdue status before fetching pending renewals
$conn->query("UPDATE active_rentals SET status = 'overdue' WHERE status = 'active' AND return_date < NOW()");

$sql = "SELECT r.*, p.product_name, u.first_name, u.last_name 
        FROM active_rentals r
        JOIN products p ON r.product_id = p.product_id
        JOIN users u ON r.student_id = u.student_id
        WHERE r.status = 'pending_renewal'
        ORDER BY r.rental_date ASC";
$result = $conn->query($sql);

$renewals = [];
if ($result) {
    while($row = $result->fetch_assoc()) {
        $renewals[] = $row;
    }
}
echo json_encode($renewals);
$conn->close();
?>