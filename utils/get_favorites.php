<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

if (!isset($_GET['student_id'])) {
    echo json_encode([]);
    exit;
}
$student_id = $_GET['student_id'];

$conn = new mysqli("localhost", "root", "", "library_db");
if ($conn->connect_error) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT f.favorite_id, b.book_id, b.book_name, b.book_author, b.book_category
        FROM favorites f
        JOIN books b ON f.book_id = b.book_id
        WHERE f.student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();

$favorites = [];
while ($row = $result->fetch_assoc()) {
    $favorites[] = $row;
}
echo json_encode($favorites);

$stmt->close();
$conn->close();
?>
