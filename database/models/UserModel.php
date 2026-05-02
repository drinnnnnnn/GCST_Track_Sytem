<?php
require_once __DIR__ . '/../connection.php';

class UserModel {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    public function findByStudentId($studentId) {
        $stmt = $this->conn->prepare('SELECT * FROM users WHERE student_id = ? LIMIT 1');
        $stmt->bind_param('s', $studentId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user ?: null;
    }

    public function findByEmail($email) {
        $stmt = $this->conn->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user ?: null;
    }

    public function exists($studentId, $email) {
        $stmt = $this->conn->prepare('SELECT 1 FROM users WHERE student_id = ? OR email = ? LIMIT 1');
        $stmt->bind_param('ss', $studentId, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();
        return $exists;
    }

    public function create(array $data) {
        $stmt = $this->conn->prepare(
            'INSERT INTO users (student_id, last_name, first_name, middle_name, email, password, sex, course, year_section, contact_number, address, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param(
            'ssssssssssss',
            $data['student_id'],
            $data['last_name'],
            $data['first_name'],
            $data['middle_name'],
            $data['email'],
            $data['password'],
            $data['sex'],
            $data['course'],
            $data['year_section'],
            $data['contact_number'],
            $data['address'],
            $data['status']
        );
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public function authenticate($studentId, $password) {
        $user = $this->findByStudentId($studentId);
        if (!$user || !password_verify($password, $user['password'])) {
            return null;
        }
        return $user;
    }
}
