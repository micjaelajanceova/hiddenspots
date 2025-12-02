<?php
class User {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    

    public function getById($user_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Return photo url OR null for initials
    public function getProfilePhoto($user_id) {
        $stmt = $this->pdo->prepare("SELECT profile_photo FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $photo = $stmt->fetchColumn();

        if ($photo && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $photo)) {
            return '/' . $photo;
        }

        return null;
    }
}
?>
