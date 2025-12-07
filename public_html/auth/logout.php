<?php
// start session
require_once __DIR__ . '/../classes/SessionHandle.php';
$session = new SessionHandle();

// Clear all session variables
$session->logout();

// Delete session cookie if it exists
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Delete "remember me" cookie if it exists
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Redirect user to login page
header("Location: login.php");
exit;
?>
