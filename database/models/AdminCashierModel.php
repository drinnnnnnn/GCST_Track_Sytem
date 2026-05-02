<?php
require_once __DIR__ . '/../connection.php';

class AdminCashierModel {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    public function findByEmail($email) {
        $stmt = $this->conn->prepare('SELECT * FROM admincashier_acc WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user ?: null;
    }

    public function authenticate($email, $password) {
        $user = $this->findByEmail($email);
        if (!$user || !password_verify($password, $user['password'])) {
            return null;
        }
        return $user;
    }
}