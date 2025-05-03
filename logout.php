<?php
// logout.php
require __DIR__ . '/config.php';  // starts session

// Destroy everything
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    setcookie(session_name(), '', time() - 3600);
}
session_destroy();

// Redirect back home
header('Location: index.php');
exit;
