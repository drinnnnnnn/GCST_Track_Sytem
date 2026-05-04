<?php
require_once __DIR__ . '/../connection.php';

class QueueModel {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    public function getActiveQueues() {
        $stmt = $this->conn->prepare('SELECT id, queue_number, user_id, status, created_at, served_at FROM queue WHERE status IN ("waiting", "serving") ORDER BY created_at ASC');
        $stmt->execute();
        $result = $stmt->get_result();
        $queues = [];
        while ($row = $result->fetch_assoc()) {
            $queues[] = $row;
        }
        $stmt->close();
        return $queues;
    }

    public function getQueueCounts() {
        $stmt = $this->conn->prepare('SELECT status, COUNT(*) AS total FROM queue GROUP BY status');
        $stmt->execute();
        $result = $stmt->get_result();
        $counts = [
            'waiting' => 0,
            'serving' => 0,
            'completed' => 0,
            'cancelled' => 0
        ];
        while ($row = $result->fetch_assoc()) {
            $status = $row['status'];
            if (array_key_exists($status, $counts)) {
                $counts[$status] = (int) $row['total'];
            }
        }
        $stmt->close();
        return $counts;
    }

    public function getById($queueId) {
        $stmt = $this->conn->prepare('SELECT id, queue_number, user_id, status, created_at, served_at, student_name, purpose FROM queue WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $queueId);
        $stmt->execute();
        $result = $stmt->get_result();
        $ticket = $result->fetch_assoc();
        $stmt->close();
        return $ticket;
    }

    public function updateStatus($queueId, $status) {
        $sql = 'UPDATE queue SET status = ?, served_at = CASE WHEN ? IN ("serving", "completed", "cancelled") THEN NOW() ELSE NULL END WHERE id = ?';
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ssi', $status, $status, $queueId);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public function getNextQueueNumber() {
        $sql = 'SELECT MAX(CAST(SUBSTRING(queue_number, 2) AS UNSIGNED)) AS max_num FROM queue WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)';
        $result = $this->conn->query($sql);
        $row = $result->fetch_assoc();
        $nextNum = ($row['max_num'] ?? 0) + 1;
        return 'Q' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
    }

    public function queueNumberExists($queueNumber) {
        $stmt = $this->conn->prepare('SELECT COUNT(*) AS count FROM queue WHERE queue_number = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)');
        $stmt->bind_param('s', $queueNumber);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return (int) ($row['count'] ?? 0) > 0;
    }

    public function create($queueNumber = null, $userId = null, $studentName = '', $purpose = '') {
        if ($queueNumber === null) {
            $queueNumber = $this->getNextQueueNumber();
        }

        $attempt = 0;
        while ($this->queueNumberExists($queueNumber) && $attempt < 5) {
            $queueNumber = $this->getNextQueueNumber();
            $attempt++;
        }

        if ($userId === null) {
            $stmt = $this->conn->prepare('INSERT INTO queue (queue_number, student_name, purpose, status) VALUES (?, ?, ?, "waiting")');
            $stmt->bind_param('sss', $queueNumber, $studentName, $purpose);
        } else {
            $stmt = $this->conn->prepare('INSERT INTO queue (queue_number, user_id, student_name, purpose, status) VALUES (?, ?, ?, ?, "waiting")');
            $stmt->bind_param('siss', $queueNumber, $userId, $studentName, $purpose);
        }

        $success = $stmt->execute();
        $insertId = $this->conn->insert_id;
        $stmt->close();

        return $success ? ['id' => $insertId, 'queue_number' => $queueNumber] : false;
    }
}
