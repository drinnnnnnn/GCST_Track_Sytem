<?php
require_once __DIR__ . '/admincashier_report_helpers.php';

function createEmailLogTable($conn, $table = 'email_logs') {
    $sql = "CREATE TABLE IF NOT EXISTS `$table` (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        recipient VARCHAR(255) NOT NULL,
        subject VARCHAR(255) NOT NULL,
        message TEXT,
        email_type VARCHAR(100) DEFAULT 'General',
        status ENUM('sent','failed','pending') NOT NULL DEFAULT 'pending',
        sent_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $conn->query($sql);
}

function sendEmailWithLog($conn, $recipient, $subject, $message, $emailType = 'General') {
    createEmailLogTable($conn);

    $status = 'sent';
    if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
        $status = 'failed';
    } else {
        $headers = "From: no-reply@granby.edu.ph\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        $htmlBody = "<html><body>" . $message . "</body></html>";
        if (!function_exists('mail') || !@mail($recipient, $subject, $htmlBody, $headers)) {
            $status = 'failed';
        }
    }

    $stmt = $conn->prepare('INSERT INTO email_logs (recipient, subject, message, email_type, status, sent_at) VALUES (?, ?, ?, ?, ?, NOW())');
    if ($stmt) {
        $stmt->bind_param('sssss', $recipient, $subject, $message, $emailType, $status);
        $stmt->execute();
        $insertId = $stmt->insert_id;
        $stmt->close();
    } else {
        $insertId = null;
    }

    return ['status' => $status, 'id' => $insertId];
}
