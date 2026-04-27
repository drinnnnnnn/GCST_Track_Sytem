<?php
header('Content-Type: application/json');
include 'functions.php';

try {
    $admins = getAdminAccounts();
    echo json_encode($admins);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>