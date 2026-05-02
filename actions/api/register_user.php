<?php
require_once __DIR__ . '/../security.php';
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../../database/models/UserModel.php';

secureSessionStart();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Invalid request method.'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    jsonResponse(['success' => false, 'message' => 'Invalid JSON body.'], 400);
}

$studentId = filterJsonString($input, 'student_id');
$lastName = filterJsonString($input, 'last_name');
$firstName = filterJsonString($input, 'first_name');
$middleName = filterJsonString($input, 'middle_name');
$email = filterJsonString($input, 'email');
$password = trim((string)($input['password'] ?? ''));
$sex = filterJsonString($input, 'sex');
$course = filterJsonString($input, 'course');
$yearSection = filterJsonString($input, 'year_section');
$contactNumber = filterJsonString($input, 'contact_number');
$address = filterJsonString($input, 'address');

if ($studentId === '' || $lastName === '' || $firstName === '' || $email === '' || $password === '' || $sex === '' || $course === '' || $yearSection === '' || $contactNumber === '' || $address === '') {
    jsonResponse(['success' => false, 'message' => 'All fields are required.'], 422);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(['success' => false, 'message' => 'Invalid email address.'], 422);
}

if (strlen($password) < 8) {
    jsonResponse(['success' => false, 'message' => 'Password must be at least 8 characters.'], 422);
}

$userModel = new UserModel();
if ($userModel->exists($studentId, $email)) {
    jsonResponse(['success' => false, 'message' => 'Student ID or email already exists.'], 409);
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$userData = [
    'student_id' => $studentId,
    'last_name' => $lastName,
    'first_name' => $firstName,
    'middle_name' => $middleName,
    'email' => $email,
    'password' => $hashedPassword,
    'sex' => $sex,
    'course' => $course,
    'year_section' => $yearSection,
    'contact_number' => $contactNumber,
    'address' => $address,
    'status' => 'Active'
];

if ($userModel->create($userData)) {
    jsonResponse(['success' => true, 'message' => 'Registration successful.']);
}

jsonResponse(['success' => false, 'message' => 'Registration failed.'], 500);