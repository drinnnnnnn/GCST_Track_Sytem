<?php
header('Content-Type: application/json');
include 'functions.php';

try {
    $success = testEmailService();
    echo json_encode(['success' => $success, 'message' => $success ? 'Email service tested successfully' : 'Failed to test email service']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>