<?php
require_once __DIR__ . '/../config/db_connect.php';
if ($conn->connect_error) {
    echo json_encode([]);
    exit();
}

$sql = "SELECT student_id, last_name, first_name, middle_name, email, contact_number, course, year_section FROM users WHERE status = 'Suspended'";
$result = $conn->query($sql);

$users = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}
header('Content-Type: application/json');
echo json_encode($users);
$conn->close();
?>
