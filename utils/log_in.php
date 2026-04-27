<?php 
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gcst_tracking_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'] ?? '';
    $password_input = $_POST['password'] ?? '';

    $stmt = $conn->prepare(
        "SELECT last_name, first_name, middle_name, password, status 
        FROM users 
        WHERE student_id = ?"
    );
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($first_name, $middle_name, $last_name, $hashed_password, $status);
        $stmt->fetch();

        if ($status === 'Suspended') {
            header("Location: http://localhost/GCST_Track_System/pages/sign_in.html?status=suspended");
            exit();
        }

        if ($hashed_password && password_verify($password_input, $hashed_password)) {
            $full_name = $first_name . ' ' . ($middle_name ? $middle_name . ' ' : '') . $last_name;

            $_SESSION['user_name'] = $full_name;
            $_SESSION['student_id'] = $student_id;
            header("Location: http://localhost/GCST_Track_System/pages/user/InUser_home.html");
            exit();
        } else {
            header("Location: http://localhost/GCST_Track_System/pages/sign_in.html?status=invalid");
            exit();
        }
    } else {
        header("Location: http://localhost/GCST_Track_System/pages/sign_in.html?status=invalid");
        exit();
    }

    $stmt->close();
}

$conn->close();
?>
