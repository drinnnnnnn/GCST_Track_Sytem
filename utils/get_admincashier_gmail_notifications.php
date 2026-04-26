<?php
header('Content-Type: application/json');
require_once 'admincashier_report_helpers.php';

$conn = connectAdminCashierDb();
if (!$conn) {
    echo json_encode(['error' => 'Connection failed']);
    exit;
}

// Assume there's a table for email logs, e.g., 'email_logs'
$emailTable = 'email_logs'; // You may need to create this table or adjust

// Check if table exists, if not, return empty data
if (!tableExists($conn, $emailTable)) {
    echo json_encode([
        'sent_today' => 0,
        'failed_emails' => 0,
        'pending_emails' => 0,
        'total_emails_sent' => 0,
        'email_logs' => []
    ]);
    $conn->close();
    exit;
}

// Get metrics
$sentToday = getEmailsSentToday($conn, $emailTable);
$failedEmails = getFailedEmails($conn, $emailTable);
$pendingEmails = getPendingEmails($conn, $emailTable);
$totalEmailsSent = getTotalEmailsSent($conn, $emailTable);

// Get email logs (last 100)
$emailLogs = getEmailLogs($conn, $emailTable, 100);

$response = [
    'sent_today' => $sentToday,
    'failed_emails' => $failedEmails,
    'pending_emails' => $pendingEmails,
    'total_emails_sent' => $totalEmailsSent,
    'email_logs' => $emailLogs
];

echo json_encode($response);
$conn->close();

function getEmailsSentToday($conn, $table) {
    $sql = "SELECT COUNT(*) as count FROM `$table` WHERE DATE(sent_at) = CURDATE() AND status = 'sent'";
    $result = $conn->query($sql);
    return $result ? ($result->fetch_assoc()['count'] ?? 0) : 0;
}

function getFailedEmails($conn, $table) {
    $sql = "SELECT COUNT(*) as count FROM `$table` WHERE status = 'failed'";
    $result = $conn->query($sql);
    return $result ? ($result->fetch_assoc()['count'] ?? 0) : 0;
}

function getPendingEmails($conn, $table) {
    $sql = "SELECT COUNT(*) as count FROM `$table` WHERE status = 'pending'";
    $result = $conn->query($sql);
    return $result ? ($result->fetch_assoc()['count'] ?? 0) : 0;
}

function getTotalEmailsSent($conn, $table) {
    $sql = "SELECT COUNT(*) as count FROM `$table` WHERE status = 'sent'";
    $result = $conn->query($sql);
    return $result ? ($result->fetch_assoc()['count'] ?? 0) : 0;
}

function getEmailLogs($conn, $table, $limit = 100) {
    $sql = "SELECT id, recipient, subject, email_type, status, sent_at FROM `$table` ORDER BY sent_at DESC LIMIT $limit";
    $result = $conn->query($sql);
    $logs = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $logs[] = [
                'id' => $row['id'],
                'recipient' => $row['recipient'],
                'subject' => $row['subject'],
                'email_type' => $row['email_type'],
                'status' => $row['status'],
                'timestamp' => $row['sent_at']
            ];
        }
    }
    return $logs;
}
?>