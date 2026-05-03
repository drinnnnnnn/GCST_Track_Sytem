<?php
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/../config/db_connect.php';

secureSessionStart();
requireAuth(['student']);
header('Content-Type: application/json');

$student_id = $_SESSION['student_id'] ?? null;
if (!$student_id) {
    jsonResponse(['success' => false, 'message' => 'Authentication required.'], 401);
}

$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload)) {
    $payload = $_POST;
}

$first_name = trim((string)($payload['first_name'] ?? ''));
$last_name = trim((string)($payload['last_name'] ?? ''));
$email = trim((string)($payload['email'] ?? ''));
$contact_number = trim((string)($payload['contact_number'] ?? ''));
$phone = trim((string)($payload['phone'] ?? ''));

if ($first_name === '' || $last_name === '' || $email === '') {
    jsonResponse(['success' => false, 'message' => 'First name, last name, and email are required.'], 422);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(['success' => false, 'message' => 'Invalid email address.'], 422);
}

$stmt = $conn->prepare('SELECT id FROM users WHERE email = ? AND student_id != ? LIMIT 1');
$stmt->bind_param('ss', $email, $student_id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->close();
    jsonResponse(['success' => false, 'message' => 'Email is already in use by another account.'], 409);
}
$stmt->close();

$updateStmt = $conn->prepare('UPDATE users SET first_name = ?, last_name = ?, email = ?, contact_number = ?, phone = ? WHERE student_id = ?');
$updateStmt->bind_param('ssssss', $first_name, $last_name, $email, $contact_number, $phone, $student_id);
$success = $updateStmt->execute();
$updateStmt->close();

if (!$success) {
    jsonResponse(['success' => false, 'message' => 'Unable to update profile. Please try again.'], 500);
}

logAudit('student', $student_id, 'update_profile', 'Updated profile information.');
jsonResponse(['success' => true, 'message' => 'Profile updated successfully.']);
