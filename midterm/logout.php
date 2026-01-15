<?php
session_start();
session_destroy();
// Clear Remember Me Cookie
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, "/");
}
header('Location: login.php');
exit;
?>
