<?php
header('Content-Type: application/json');
include 'functions.php';

try {
    $success = optimizeDatabase();
    echo json_encode(['success' => $success, 'message' => $success ? 'Database optimized successfully' : 'Failed to optimize database']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>