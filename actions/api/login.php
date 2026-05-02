<?php
require_once __DIR__ . '/../security.php';
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../../database/models/UserModel.php';
require_once __DIR__ . '/../../database/models/AdminCashierModel.php';

secureSessionStart();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Invalid request method.'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    jsonResponse(['success' => false, 'message' => 'Invalid JSON body.'], 400);
}

$type = filterJsonString($input, 'type');
$password = trim((string)($input['password'] ?? ''));

if ($type === 'user') {
    $studentId = filterJsonString($input, 'identifier');
    if ($studentId === '' || $password === '') {
        jsonResponse(['success' => false, 'message' => 'Student ID and password are required.'], 422);
    }

    $userModel = new UserModel();
    $user = $userModel->authenticate($studentId, $password);

    if (!$user) {
        jsonResponse(['success' => false, 'message' => 'Invalid credentials.'], 401);
    }

    if ($user['status'] === 'Suspended') {
        jsonResponse(['success' => false, 'message' => 'Your account is suspended.'], 403);
    }

    session_regenerate_id(true);
    $_SESSION['student_id'] = $studentId;
    $_SESSION['user_name'] = trim($user['first_name'] . ' ' . ($user['middle_name'] ? $user['middle_name'] . ' ' : '') . $user['last_name']);
    $_SESSION['role'] = 'user';

    jsonResponse([
        'success' => true,
        'role' => 'user',
        'redirect' => '/GCST_Track_System/pages/user/InUser_home.html',
        'name' => $_SESSION['user_name']
    ]);
}

if ($type === 'admincashier') {
    $email = filterJsonString($input, 'identifier');
    if ($email === '' || $password === '') {
        jsonResponse(['success' => false, 'message' => 'Email and password are required.'], 422);
    }

    $adminModel = new AdminCashierModel();
    $admin = $adminModel->authenticate($email, $password);

    if (!$admin) {
        jsonResponse(['success' => false, 'message' => 'Invalid credentials.'], 401);
    }

    session_regenerate_id(true);
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_name'] = trim($admin['first_name'] . ' ' . ($admin['middle_name'] ? $admin['middle_name'] . ' ' : '') . $admin['last_name']);
    $_SESSION['role'] = 'admincashier';

    jsonResponse([
        'success' => true,
        'role' => 'admincashier',
        'redirect' => '/GCST_Track_System/pages/admincashier/admincashier_dashb.html',
        'name' => $_SESSION['admin_name']
    ]);
}

jsonResponse(['success' => false, 'message' => 'Unsupported login type.'], 400);

$conn->close();
?>