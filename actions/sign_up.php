<?php
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/../config/db_connect.php';

secureSessionStart();

if ($conn->connect_error) {
    header('Location: http://localhost/GCST_Track_System/pages/superadmin/sign_up.html?status=error&show=register');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id     = trim(filter_input(INPUT_POST, 'student_id', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '');
    $last_name      = trim(filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '');
    $first_name     = trim(filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '');
    $middle_name    = trim(filter_input(INPUT_POST, 'middle_name', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '');
    $email          = trim(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) ?? '');
    $password_raw   = $_POST['password'] ?? '';
    $confirm_pass   = $_POST['confirm_password'] ?? '';
    $sex            = trim(filter_input(INPUT_POST, 'sex', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '');
    $course         = trim(filter_input(INPUT_POST, 'course', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '');
    $year_section   = trim(filter_input(INPUT_POST, 'year_section', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '');
    $contact_number = trim(filter_input(INPUT_POST, 'contact_number', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '');
    $address        = trim(filter_input(INPUT_POST, 'address', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '');
    $status         = 'Pending';

    if ($student_id === '' || $last_name === '' || $first_name === '' || $email === '' || $password_raw === '' || $sex === '' || $course === '' || $year_section === '') {
        header('Location: http://localhost/GCST_Track_System/pages/superadmin/sign_up.html?status=invalid&show=register');
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: http://localhost/GCST_Track_System/pages/superadmin/sign_up.html?status=invalid_email&show=register');
        exit();
    }

    if ($password_raw !== $confirm_pass) {
        header('Location: http://localhost/GCST_Track_System/pages/superadmin/sign_up.html?status=nomatch&show=register');
        exit();
    }

    if (strlen($password_raw) < 8) {
        header('Location: http://localhost/GCST_Track_System/pages/superadmin/sign_up.html?status=weak_password&show=register');
        exit();
    }

    $check_stmt = $conn->prepare('SELECT 1 FROM users WHERE student_id = ? OR email = ?');
    $check_stmt->bind_param('ss', $student_id, $email);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        $check_stmt->close();
        header('Location: http://localhost/GCST_Track_System/pages/superadmin/sign_up.html?status=exists&show=register');
        exit();
    }

    $hashed_password = password_hash($password_raw, PASSWORD_DEFAULT);
    $stmt = $conn->prepare(
        'INSERT INTO users 
         (student_id, last_name, first_name, middle_name, email, password, sex, course, year_section, contact_number, address, status) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->bind_param(
        'ssssssssssss',
        $student_id,
        $last_name,
        $first_name,
        $middle_name,
        $email,
        $hashed_password,
        $sex,
        $course,
        $year_section,
        $contact_number,
        $address,
        $status
    );

    if ($stmt->execute()) {
        updateMemberCount($conn);
        $stmt->close();
        $check_stmt->close();
        header('Location: http://localhost/GCST_Track_System/pages/superadmin/sign_up.html?status=success&show=register');
        exit();
    }

    $stmt->close();
    $check_stmt->close();
}

function updateMemberCount($conn) {
    $result = $conn->query('SELECT COUNT(*) AS cnt FROM users');
    $row = $result->fetch_assoc();
    $total = (int) $row['cnt'];
    $conn->query('INSERT INTO count_items (total_members) SELECT ' . $total . ' WHERE NOT EXISTS (SELECT 1 FROM count_items)');
    $conn->query('UPDATE count_items SET total_members = ' . $total);
}

$conn->close();
?>