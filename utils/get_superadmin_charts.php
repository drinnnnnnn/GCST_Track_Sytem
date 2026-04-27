<?php
header('Content-Type: application/json');
include 'functions.php';

try {
    $data = getChartData();
    echo json_encode($data);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>