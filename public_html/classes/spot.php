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
    SELECT 
        h.*, 
        u.name AS user_name,
        u.profile_photo,
        (SELECT COUNT(*) FROM likes l WHERE l.spot_id = h.id) AS likes
    FROM hidden_spots h
    LEFT JOIN users u ON h.user_id = u.id
    WHERE h.id = ?
    LIMIT 1
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
    // get all comments for admin view
    public function getAllComments() {
    $stmt = $this->pdo->query("
        SELECT c.*, u.name AS user_name, h.name AS spot_name
        FROM comments c
        JOIN users u ON c.user_id = u.id
        JOIN hidden_spots h ON c.spot_id = h.id
        ORDER BY c.created_at DESC
    ");
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
    // Update basic info
    public function updateSpot($spot_id, $name, $city, $address) {
    $stmt = $this->pdo->prepare("UPDATE hidden_spots SET name=?, city=?, address=? WHERE id=?");
    return $stmt->execute([$name, $city, $address, $spot_id]);
}

    // Create new spot
    public function createSpot($user_id, $name, $city, $address, $file_path, $description='', $latitude=null, $longitude=null, $type=null) {
    $stmt = $this->pdo->prepare("
        INSERT INTO hidden_spots
        (user_id, name, city, address, file_path, description, latitude, longitude, type, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    return $stmt->execute([$user_id, $name, $city, $address, $file_path, $description, $latitude, $longitude, $type]);
    }

    // ADMIN: Update comment text
    public function updateComment($comment_id, $text){
        $stmt = $this->pdo->prepare("
            UPDATE comments 
            SET text = ? 
            WHERE id = ?
        ");
        return $stmt->execute([$text, $comment_id]);
    }

    // ADMIN: Delete comment
    public function deleteComment($comment_id){
        $stmt = $this->pdo->prepare("DELETE FROM comments WHERE id = ?");
        return $stmt->execute([$comment_id]);
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
    // Get recent file paths for background images of spots
    public function getRecentFiles($limit = 10) {
    try {
        $stmt = $this->pdo->query("SELECT file_path FROM hidden_spots ORDER BY created_at DESC LIMIT $limit");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        return ['/assets/img/default-bg.jpg']; // fallback
    }
}
    // Get all unique types of hidden spots
    public function getAllTypes() {
        $stmt = $this->pdo->query("SELECT DISTINCT type FROM hidden_spots WHERE type IS NOT NULL AND type != ''");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    // Search spots by city and/or type
    public function search($city = '', $type = '') {
        $sql = "SELECT hs.*, u.name AS user_name 
                FROM hidden_spots hs 
                JOIN users u ON hs.user_id = u.id 
                WHERE 1=1";
        $params = [];
        if(!empty($city)) {
            $sql .= " AND hs.city LIKE ?";
            $params[] = "%$city%";
        }
        if(!empty($type)) {
            $sql .= " AND hs.type = ?";
            $params[] = $type;
        }
        $sql .= " ORDER BY hs.created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get trending spots based on likes and comments
    public function getTrending($limit=20){
    try {
        $stmt = $this->pdo->query("SELECT * FROM view_hot_pictures LIMIT $limit");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
    }

    // Get latest comments across all spots
    public function getLatestComments($limit = 3){
    $limit = (int)$limit;
    $stmt = $this->pdo->query("
        SELECT c.*, u.name AS user_name, u.profile_photo
        FROM comments c
        LEFT JOIN users u ON c.user_id = u.id
        ORDER BY c.created_at DESC
        LIMIT $limit
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
     // Get top trending spots this week (last 7 days)
    public function getTrendingWeek($limit = 6) {
        $stmt = $this->pdo->query("
            SELECT 
                hs.*,
                u.name AS user_name,
                COUNT(DISTINCT l.id) AS total_likes,
                COUNT(DISTINCT c.id) AS total_comments,
                (COUNT(DISTINCT l.id) + COUNT(DISTINCT c.id)) AS trending_score
            FROM hidden_spots hs
            LEFT JOIN likes l ON hs.id = l.spot_id
            LEFT JOIN comments c ON hs.id = c.spot_id
            LEFT JOIN users u ON hs.user_id = u.id
            WHERE hs.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY hs.id
            HAVING trending_score > 0
            ORDER BY trending_score DESC
            LIMIT $limit
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
     // Get top trending spots all-time
    public function getTrendingAll($limit = 6) {
        $stmt = $this->pdo->query("
            SELECT 
                hs.*,
                u.name AS user_name,
                COUNT(DISTINCT l.id) AS total_likes,
                COUNT(DISTINCT c.id) AS total_comments,
                (COUNT(DISTINCT l.id) + COUNT(DISTINCT c.id)) AS trending_score
            FROM hidden_spots hs
            LEFT JOIN likes l ON hs.id = l.spot_id
            LEFT JOIN comments c ON hs.id = c.spot_id
            LEFT JOIN users u ON hs.user_id = u.id
            GROUP BY hs.id
            ORDER BY trending_score DESC
            LIMIT $limit
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Check if a spot is liked by a specific user
    public function isLikedByUser($spot_id, $user_id){
    $stmt = $this->pdo->prepare("SELECT 1 FROM likes WHERE user_id=? AND spot_id=?");
    $stmt->execute([$user_id, $spot_id]);
    return $stmt->fetch() ? true : false;
    }

    // Check if a spot is favorited by a specific user
    public function isFavoritedByUser($spot_id, $user_id){
        $stmt = $this->pdo->prepare("SELECT 1 FROM favorites WHERE user_id=? AND spot_id=?");
        $stmt->execute([$user_id, $spot_id]);
        return $stmt->fetch() ? true : false;
    }

    // Count likes for a specific spot
    public function countLikes($spot_id){
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM likes WHERE spot_id=?");
        $stmt->execute([$spot_id]);
        return (int)$stmt->fetchColumn();
    }
    // Add a new comment
    public function addComment($spot_id, $user_id, $text){
        $stmt = $this->pdo->prepare("INSERT INTO comments (user_id, spot_id, text) VALUES (?, ?, ?)");
        return $stmt->execute([$user_id, $spot_id, $text]);
    }

    // Edit a comment (admin can edit any, user can edit own)
    public function editComment($comment_id, $user_id, $text, $isAdmin=false){
        if($isAdmin){
            $stmt = $this->pdo->prepare("UPDATE comments SET text=? WHERE id=?");
            return $stmt->execute([$text, $comment_id]);
        } else {
            $stmt = $this->pdo->prepare("UPDATE comments SET text=? WHERE id=? AND user_id=?");
            return $stmt->execute([$text, $comment_id, $user_id]);
        }
    }

    // Delete a comment (admin can delete any, user can delete own)
    public function deleteCommentByUser($comment_id, $user_id, $isAdmin=false){
        if($isAdmin){
            $stmt = $this->pdo->prepare("DELETE FROM comments WHERE id=?");
            return $stmt->execute([$comment_id]);
        } else {
            $stmt = $this->pdo->prepare("DELETE FROM comments WHERE id=? AND user_id=?");
            return $stmt->execute([$comment_id, $user_id]);
        }
    }
}
?>
