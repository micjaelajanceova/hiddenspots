<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$city = $_GET['query'] ?? '';

// Fetch spots filtered by city
$spotObj = new Spot($pdo);

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

$spots = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
