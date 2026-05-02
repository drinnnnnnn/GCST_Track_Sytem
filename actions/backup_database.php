<?php
header('Content-Type: application/json');
include 'functions.php';

try {
    $success = backupDatabase();
    echo json_encode(['success' => $success, 'message' => $success ? 'Database backed up successfully' : 'Failed to backup database']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>