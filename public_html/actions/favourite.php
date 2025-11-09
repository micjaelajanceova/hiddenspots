<?php
session_start();
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['spot_id'])) {
    http_response_code(400);
    echo 'invalid_request';
    exit();
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo 'not_logged_in';
    exit();
}

$spot_id = intval($_POST['spot_id']);
$user_id = $_SESSION['user_id'];

// Check if already favorited
$stmt = $pdo->prepare("SELECT 1 FROM favorites WHERE user_id=? AND spot_id=?");
$stmt->execute([$user_id, $spot_id]);

if ($stmt->fetch()) {
    // Remove favorite
    $pdo->prepare("DELETE FROM favorites WHERE user_id=? AND spot_id=?")->execute([$user_id, $spot_id]);
    echo 'removed';
} else {
    // Add favorite
    $pdo->prepare("INSERT INTO favorites (user_id, spot_id) VALUES (?, ?)")->execute([$user_id, $spot_id]);
    echo 'added';
}
?>
