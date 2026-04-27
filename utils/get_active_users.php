<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gcst_tracking_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode([]));
}

$sql = "SELECT student_id, last_name, first_name, middle_name, email, contact_number, course, year_section FROM users WHERE status = 'Active'";
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
