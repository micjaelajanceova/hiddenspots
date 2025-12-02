<?php
// --- Password validation function ---
function validatePassword($password) {
    if (strlen($password) < 6 || strlen($password) > 50) {
        return "Password must be 6–50 characters.";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return "Password must contain at least one uppercase letter.";
    }
    if (!preg_match('/[a-z]/', $password)) {
        return "Password must contain at least one lowercase letter.";
    }
    if (!preg_match('/[0-9]/', $password)) {
        return "Password must contain at least one number.";
    }
    return true;
}