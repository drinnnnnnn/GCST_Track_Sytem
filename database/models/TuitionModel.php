<?php
require_once __DIR__ . '/../connection.php';

class TuitionModel {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    public function findByUserId($userId) {
        $stmt = $this->conn->prepare('SELECT * FROM tuition_fees WHERE user_id = ? LIMIT 1');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $fees = $result->fetch_assoc();
        $stmt->close();
        return $fees ?: null;
    }

    public function updatePayment($userId, $totalPaid, $balance, $paymentStatus) {
        $stmt = $this->conn->prepare('UPDATE tuition_fees SET total_paid = ?, balance = ?, payment_status = ?, updated_at = NOW() WHERE user_id = ?');
        $stmt->bind_param('ddsi', $totalPaid, $balance, $paymentStatus, $userId);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }
}
