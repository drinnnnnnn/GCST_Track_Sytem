<?php
require_once __DIR__ . '/../security.php';

secureSessionStart();

jsonResponse([
    'logged_in' => isset($_SESSION['student_id']) || isset($_SESSION['admin_id']),
    'role' => $_SESSION['role'] ?? null,
    'name' => $_SESSION['user_name'] ?? $_SESSION['admin_name'] ?? null,
    'student_id' => $_SESSION['student_id'] ?? null,
    'admin_id' => $_SESSION['admin_id'] ?? null,
    'session_id' => session_id()
]);