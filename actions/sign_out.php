<?php
require_once __DIR__ . '/security.php';

secureSessionStart();
destroySession();

$redirect = 'http://localhost/GCST_Track_System/pages/index1.html';
if (isset($_GET['redirect'])) {
    $target = basename($_GET['redirect']);
    if (preg_match('/^[a-zA-Z0-9_\-]+\.html$/', $target)) {
        $redirect = 'http://localhost/GCST_Track_System/pages/' . $target;
    }
}

header("Location: $redirect");
exit();
?>