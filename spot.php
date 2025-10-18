<?php
class Spot {
    private $pdo;
    public function __construct($pdo){
        $this->pdo = $pdo;
    }

    public function getNewest($limit=10){
        $stmt = $this->pdo->query("
            SELECT h.*, COUNT(c.id) AS comments_count, 
            (SELECT COUNT(*) FROM likes l WHERE l.spot_id = h.id) AS likes 
            FROM hidden_spots h
            LEFT JOIN comments c ON h.id = c.spot_id
            GROUP BY h.id
            ORDER BY h.created_at DESC
            LIMIT $limit
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

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

    public function getComments($spot_id){
        $stmt = $this->pdo->prepare("
            SELECT c.*, u.name AS user_name, c.user_id
            FROM comments c
            LEFT JOIN users u ON c.user_id = u.id
            WHERE c.spot_id = ?
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$spot_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
