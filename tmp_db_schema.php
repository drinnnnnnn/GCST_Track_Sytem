<?php
require_once 'utils/db_connect.php';
foreach(['products','sales'] as $t) {
    echo 'TABLE ' . $t . "\n";
    $res = $conn->query('DESCRIBE ' . $t);
    if (!$res) {
        echo 'ERROR: ' . $conn->error . "\n";
        continue;
    }
    while ($row = $res->fetch_assoc()) {
        echo $row['Field'] . ' ' . $row['Type'] . ' ' . $row['Null'] . ' ' . $row['Key'] . ' ' . $row['Default'] . ' ' . $row['Extra'] . "\n";
    }
}
$conn->close();
?>