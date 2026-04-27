<?php
header('Content-Type: application/json');
include 'functions.php';

try {
    $stats = getSystemStats();
    echo json_encode($stats);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>