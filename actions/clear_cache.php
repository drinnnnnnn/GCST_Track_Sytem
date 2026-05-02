<?php
header('Content-Type: application/json');
include 'functions.php';

try {
    $success = clearCache();
    echo json_encode(['success' => $success, 'message' => $success ? 'Cache cleared successfully' : 'Failed to clear cache']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>