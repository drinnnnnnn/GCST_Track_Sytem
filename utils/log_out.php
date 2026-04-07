<?php
session_start();
session_unset();
session_destroy();
header("Location: http://localhost/GCST_Track_System/pages/user/user_home.html");
exit();
?>