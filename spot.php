<?php
class Spot {
    private $pdo;
    public function __construct($pdo){
        $this->pdo = $pdo;
    }

    public function getNewest($limit=10){
        $stmt = $this->pdo->query("
            SELECT h.*, COUNT(c.id) AS comments_count, 
            (SELECT COUNT(*) FROM Likes l WHERE l.spot_id=h.id) AS likes 
            FROM HiddenSpots h 
            LEFT JOIN Comments c ON h.id=c.spot_id 
            GROUP BY h.id 
            ORDER BY h.created_at DESC 
            LIMIT $limit
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
