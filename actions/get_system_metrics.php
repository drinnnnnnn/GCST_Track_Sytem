<?php
header('Content-Type: application/json');
include 'functions.php';

try {
    $metrics = getSystemMetrics();
    echo json_encode($metrics);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>