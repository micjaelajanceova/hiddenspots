<?php
class User {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Get all users
    public function getAll() {
    return $this->pdo->query("SELECT id, name, email, role, blocked FROM users ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
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

    // Remove profile photo
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

    // Update user info
    public function updateUser($id, $name, $email, $role) {
    $stmt = $this->pdo->prepare("UPDATE users SET name=?, email=?, role=? WHERE id=?");
    return $stmt->execute([$name, $email, $role, $id]);
    }

    // Delete user
    public function deleteUser($id) {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id=?");
        return $stmt->execute([$id]);
    }

    // Toggle block/unblock user
    public function toggleBlock($id) {
    $stmt = $this->pdo->prepare("SELECT blocked FROM users WHERE id=?");
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $newStatus = $user['blocked'] ? 0 : 1;
        $stmt = $this->pdo->prepare("UPDATE users SET blocked=? WHERE id=?");
        return $stmt->execute([$newStatus, $id]);
    }
    return false;
    }
    
    // Fetch user's favorites
    public function getFavorites($user_id) {
    $stmt = $this->pdo->prepare("
        SELECT hs.*, u.name AS user_name, u.profile_photo
        FROM favorites f
        JOIN hidden_spots hs ON f.spot_id = hs.id
        JOIN users u ON hs.user_id = u.id
        WHERE f.user_id = :user_id
        ORDER BY f.created_at DESC
    ");
    $stmt->execute(['user_id' => $user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}
?>
