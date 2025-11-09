<?php
session_start();

// Vymaž všetky premenné
$_SESSION = [];

// Ak sa používajú cookies pre session, odstráň ich tiež
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Odstráň aj vlastné login cookies, ak nejaké máš
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

session_destroy();

header("Location: login.php");
exit;
?>
