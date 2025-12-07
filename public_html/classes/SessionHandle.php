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

    public function get($key) {
        return $_SESSION[$key] ?? null;
    }

    public function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public function remove($key) {
        unset($_SESSION[$key]);
    }

    public function logout() {
        session_unset();
        session_destroy();
    }
}