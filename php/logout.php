<?php
session_start(); 
$role = $_SESSION['role'] ?? '';
$_SESSION = array();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 60*60,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
unset($_SESSION['login']);
session_destroy(); // destroy session
$redirect = $role === 'farmer'
    ? '../html/farmer-login.html'
    : '../LoginIndex.html';
header("Location: $redirect");
exit();
?>
