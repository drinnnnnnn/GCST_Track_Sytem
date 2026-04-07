<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow CORS if needed

// Database connection settings
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'library_db';

// Create connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => "Connection failed: " . $conn->connect_error]);
    exit;
}

// Query to get all users with student_id
$sql = "SELECT student_id, name, email FROM users";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $users = [];
    while($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    echo json_encode($users);
} else {
    echo json_encode([]);
}

$conn->close();
?>
