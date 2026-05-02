<?php
header('Content-Type: application/json');
include 'functions.php';

try {
    $backups = getRecentBackups();
    echo json_encode($backups);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>