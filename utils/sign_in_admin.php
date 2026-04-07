<?php
// Database connection
$host = "localhost";
$db   = "library_db";
$user = "root"; // your MySQL username
$pass = "";     // your MySQL password
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get POST data
$email_or_id = trim($_POST['student_id']); // your login form input
$password = $_POST['password'];

// Basic validation
if (empty($email_or_id) || empty($password)) {
    die("Please fill in all fields.");
}

// Check user exists
$stmt = $conn->prepare("SELECT id, last_name, first_name, middle_name, role, email, password FROM admin_librarian_acc WHERE email = ?");
$stmt->bind_param("s", $email_or_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $stmt->close();
    die("No account found with this email.");
}

$stmt->bind_result($id, $last_name, $first_name, $middle_name, $role, $email, $hashed_password);
$stmt->fetch();

// Verify password
if ($hashed_password !== null && password_verify($password, $hashed_password)) {
    session_start();
    $_SESSION['user_id'] = $id;
    $_SESSION['name'] = $first_name . " " . $last_name;
    $_SESSION['role'] = $role;
    $_SESSION['email'] = $email;

    // Redirect to dashboard or admin panel
    header("Location: admin_dashboard.php");
    exit();
} else {
    die("Incorrect password.");
}

$stmt->close();
$conn->close();
?>
