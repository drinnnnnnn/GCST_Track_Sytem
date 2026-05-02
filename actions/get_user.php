<?php

session_start();
header('Content-Type: application/json');

$displayName = $_SESSION['user_name'] ?? $_SESSION['admin_name'] ?? null;
$role = $_SESSION['role'] ?? null;

echo json_encode([
  'student_id' => $_SESSION['student_id'] ?? null,
  'admin_id' => $_SESSION['admin_id'] ?? null,
  'role' => $role,
  'name' => $displayName,
  'logged_in' => isset($_SESSION['student_id']) || isset($_SESSION['admin_id'])
]);
?>
