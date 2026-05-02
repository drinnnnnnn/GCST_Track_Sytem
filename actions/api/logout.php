<?php
require_once __DIR__ . '/../security.php';

secureSessionStart();
destroySession();
jsonResponse(['success' => true, 'message' => 'Logged out successfully.']);

$conn->close();
?>