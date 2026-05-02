<?php
require_once __DIR__ . '/../connection.php';

class TransactionModel {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    public function create($userId, $productId, $type, $quantity, $totalAmount) {
        $stmt = $this->conn->prepare(
            'INSERT INTO transactions (user_id, product_id, type, quantity, total_amount) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('iisid', $userId, $productId, $type, $quantity, $totalAmount);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }
}
