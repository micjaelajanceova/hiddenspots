<?php
require_once __DIR__ . '/classes/session.php';
$session = new SessionHandle();

// Get search query from URL
$city = $_GET['query'] ?? '';

// Fetch spots filtered by city
$spotObj = new Spot($pdo);

// If a city is provided, filter by city; otherwise, get all spots
if (!empty($city)) {
    $stmt = $pdo->prepare("SELECT hs.*, u.name AS user_name 
                           FROM hidden_spots hs 
                           JOIN users u ON hs.user_id = u.id 
                           WHERE hs.city LIKE ? 
                           ORDER BY hs.created_at DESC");
    $stmt->execute(["%$city%"]);
} else {
    $stmt = $pdo->prepare("SELECT hs.*, u.name AS user_name 
                           FROM hidden_spots hs 
                           JOIN users u ON hs.user_id = u.id 
                           ORDER BY hs.created_at DESC");
    $stmt->execute();
}

// Fetch all matching spots
$spots = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
