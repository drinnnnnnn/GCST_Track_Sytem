<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'utils/db_connect.php';
$res = $conn->query('SHOW TABLES');
if (!$res) {
    echo 'ERROR: ' . $conn->error . "\n";
    exit(1);
}
while ($row = $res->fetch_array()) {
    echo $row[0] . "\n";
}
$conn->close();
?>