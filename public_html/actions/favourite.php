<?php
// Session handler
require_once __DIR__ . '/../classes/session.php';
$session = new SessionHandle();

// Database connection
require_once '../includes/db.php';

// Validate request
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['spot_id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'invalid_request']);
    exit();
}

// User must be logged in to like a spot
if (!$session->logged_in()) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'invalid_request']);
    exit();
}

// Get spot ID and user ID
$spot_id = intval($_POST['spot_id']);
$user_id = $session->get('user_id');

// Check if already favorited
$stmt = $pdo->prepare("SELECT 1 FROM favorites WHERE user_id=? AND spot_id=?");
$stmt->execute([$user_id, $spot_id]);

if ($stmt->fetch()) {
    // Remove favorite if it exists
    $pdo->prepare("DELETE FROM favorites WHERE user_id=? AND spot_id=?")->execute([$user_id, $spot_id]);
    echo json_encode(['status' => 'removed']);
} else {
    // Add favorite if it doesn't exist
    $pdo->prepare("INSERT INTO favorites (user_id, spot_id) VALUES (?, ?)")->execute([$user_id, $spot_id]);
    echo json_encode(['status' => 'added']);
}
?>
