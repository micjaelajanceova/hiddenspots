<?php
class SessionHandle {
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function logged_in() {
        return isset($_SESSION['user_id']);
    }
}
?>
