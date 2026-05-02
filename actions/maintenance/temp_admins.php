<?php
include __DIR__ . '/../../config/db_connect.php';
$conn->query('CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    status ENUM("active", "inactive") DEFAULT "active",
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)');
echo 'Admins table created.';
?>
