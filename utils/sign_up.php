<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "library_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $student_id     = $_POST['student_id'];
    $last_name      = $_POST['last_name'];
    $first_name     = $_POST['first_name'];
    $middle_name    = $_POST['middle_name'];
    $email          = $_POST['email'];
    $password       = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $sex            = $_POST['sex'];
    $course         = $_POST['course'];
    $year_section   = $_POST['year_section'];
    $contact_number = $_POST['contact_number'];
    $address        = $_POST['address'];
    $status         = 'Active';

    $check_stmt = $conn->prepare(
        "SELECT 1 FROM users WHERE student_id = ? OR email = ?"
    );
    $check_stmt->bind_param("ss", $student_id, $email);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        header("Location: http://localhost/GCST_Track_System/pages/sign_up.html?status=exists&show=register");
        exit();
    }

    if ($_POST['password'] !== $_POST['confirm_password']) {
        header("Location: http://localhost/GCST_Track_System/pages/sign_up.html?status=nomatch&show=register");
        exit();
    }

    $stmt = $conn->prepare(
        "INSERT INTO users 
        (student_id, last_name, first_name, middle_name, email, password, sex, course, year_section, contact_number, address, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    $stmt->bind_param(
        "ssssssssssss",
        $student_id,
        $last_name,
        $first_name,
        $middle_name,
        $email,
        $password,
        $sex,
        $course,
        $year_section,
        $contact_number,
        $address,
        $status
    );

    if ($stmt->execute()) {
        updateMemberCount($conn);
        header("Location: http://localhost/GCST_Track_System/pages/sign_up.html?status=success&show=register");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $check_stmt->close();
}

function updateMemberCount($conn) {
    $result = $conn->query("SELECT COUNT(*) AS cnt FROM users");
    $row = $result->fetch_assoc();
    $total = (int)$row['cnt'];

    $conn->query(
        "INSERT INTO count_items (total_members)
        SELECT $total
        WHERE NOT EXISTS (SELECT 1 FROM count_items)"
    );

    $conn->query(
        "UPDATE count_items SET total_members = $total"
    );
}

$conn->close();
?>
