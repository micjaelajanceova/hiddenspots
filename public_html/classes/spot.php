<?php
class Spot {
    private $pdo;
    public function __construct($pdo){
        $this->pdo = $pdo;
    }

    // Get the newest spots with optional limit (default 10)
    public function getNewest($limit=10){
        $stmt = $this->pdo->query("
            SELECT 
            h.*, 
            u.name AS user_name,
            COUNT(c.id) AS comments_count,
            (SELECT COUNT(*) FROM likes l WHERE l.spot_id = h.id) AS likes
        FROM hidden_spots h
        LEFT JOIN users u ON h.user_id = u.id
        LEFT JOIN comments c ON h.id = c.spot_id
        GROUP BY h.id
        ORDER BY h.created_at DESC
        LIMIT $limit

        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get a spot by its ID, including the number of likes
    public function getById($id){
        $stmt = $this->pdo->prepare("
            SELECT h.*, 
            (SELECT COUNT(*) FROM likes l WHERE l.spot_id = h.id) AS likes
            FROM hidden_spots h
            WHERE h.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get comments for a specific spot
    public function getComments($spot_id){
    $stmt = $this->pdo->prepare("
        SELECT 
            c.id,
            c.user_id,
            c.text,
            c.created_at,
            u.name AS user_name,
            u.profile_photo
        FROM comments c
        LEFT JOIN users u ON c.user_id = u.id
        WHERE c.spot_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$spot_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Get all spots uploaded by a specific user
        public function getByUser($user_id){
        $stmt = $this->pdo->prepare("
            SELECT h.*, u.name AS user_name
            FROM hidden_spots h
            JOIN users u ON h.user_id = u.id
            WHERE h.user_id = ?
            ORDER BY h.created_at DESC

        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Update the description of a specific spot
    public function updateDescription($spot_id, $new_description) {
    $stmt = $this->pdo->prepare("UPDATE hidden_spots SET description = ? WHERE id = ?");
    return $stmt->execute([$new_description, $spot_id]);
}
    // Delete a spot and its associated comments and likes
    public function deleteSpot($spot_id) {

    $stmt = $this->pdo->prepare("DELETE FROM comments WHERE spot_id = ?");
    $stmt->execute([$spot_id]);

    $stmt = $this->pdo->prepare("DELETE FROM likes WHERE spot_id = ?");
    $stmt->execute([$spot_id]);

    $stmt = $this->pdo->prepare("DELETE FROM hidden_spots WHERE id = ?");
    return $stmt->execute([$spot_id]);
}
    // Get recent file paths for background images
    public function getRecentFiles($limit = 10) {
    try {
        $stmt = $this->pdo->query("SELECT file_path FROM hidden_spots ORDER BY created_at DESC LIMIT $limit");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        return ['/assets/img/default-bg.jpg']; // fallback
    }
}

}
?>
