<?php
session_start();

// Clear session data and remove session cookie for a complete logout
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}
session_destroy();

$redirect = 'http://localhost/GCST_Track_System/pages/user/user_home.html';
if (isset($_GET['redirect'])) {
    $target = basename($_GET['redirect']);
    if (preg_match('/^[a-zA-Z0-9_\-]+\.html$/', $target)) {
        $redirect = 'http://localhost/GCST_Track_System/pages/' . $target;
    }
}

header("Location: $redirect");
exit();
?>
