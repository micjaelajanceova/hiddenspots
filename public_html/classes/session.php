<?php
class SessionHandle {
    // Start the session if not already started
    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
    // Check if user is logged in
    public function logged_in() {
        return isset($_SESSION['user_id']);
    }
    // Generic getter for session values
    public function get($key) {
        return $_SESSION[$key] ?? null;
    }
    // Generic setter for session values
    public function set($key, $value) {
        $_SESSION[$key] = $value;
    }
    // Remove a session variable
    public function remove($key) {
        unset($_SESSION[$key]);
    }
    // Log the user out
    public function logout() {
        session_unset();
        session_destroy();
    }
    // Get the current logged-in user's ID
    public function getUserId() {
        return $this->get('user_id');
    }
}