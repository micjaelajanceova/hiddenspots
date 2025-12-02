<?php
class User {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Fetch user by ID
    public function getById($user_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Fetch user by email
    public function getByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Check if email exists
    public function existsByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetchColumn() !== false;
    }

    // Check if username exists
    public function existsByUsername($name) {
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE name = ?");
        $stmt->execute([$name]);
        return $stmt->fetchColumn() !== false;
    }

    // Create a new user
    public function createUser($name, $email, $password) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO users (name, email, password, role, badges) VALUES (?, ?, ?, 'user', 'newbie')");
        return $stmt->execute([$name, $email, $passwordHash]);
    }

    // Verify password for login
    public function verifyPassword($email, $password) {
        $user = $this->getByEmail($email);
        if (!$user) return false;
        return password_verify($password, $user['password']) ? $user : false;
    }

    // --- Profile photo methods ---
    public function getProfilePhoto($user_id) {
        $stmt = $this->pdo->prepare("SELECT profile_photo FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $photo = $stmt->fetchColumn();
        if ($photo && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $photo)) {
            return '/' . $photo;
        }
        return null; // fallback to initials
    }

    public function updateProfilePhoto($user_id, $path) {
        // Remove old photo if exists
        $old = $this->getProfilePhoto($user_id);
        if ($old && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $old)) {
            @unlink($_SERVER['DOCUMENT_ROOT'] . '/' . $old);
        }

        $stmt = $this->pdo->prepare("UPDATE users SET profile_photo = ? WHERE id = ?");
        return $stmt->execute([$path, $user_id]);
    }

    public function removeProfilePhoto($user_id) {
        $old = $this->getProfilePhoto($user_id);
        if ($old && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $old)) {
            @unlink($_SERVER['DOCUMENT_ROOT'] . '/' . $old);
        }

        $stmt = $this->pdo->prepare("UPDATE users SET profile_photo = NULL WHERE id = ?");
        return $stmt->execute([$user_id]);
    }

    // Update user password
    public function updatePassword($user_id, $newPassword) {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        return $stmt->execute([$hash, $user_id]);
    }
}
?>
