<?php
require_once __DIR__ . '/../classes/session.php';
require_once '../includes/db.php';
require_once __DIR__ . '/../classes/spot.php';

$session = new SessionHandle();
$spotObj = new Spot($pdo);

// Validate request
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['spot_id'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'invalid_request']);
    exit();
}

// User must be logged in
if (!$session->logged_in()) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'not_logged_in']);
    exit();
}

$spot_id = intval($_POST['spot_id']);
$user_id = $session->get('user_id');

// Toggle like
if ($spotObj->isLikedByUser($spot_id, $user_id)) {
    $spotObj->unlike($spot_id, $user_id);
    $status = 'unliked';
} else {
    $spotObj->like($spot_id, $user_id);
    $status = 'liked';
}

// Return updated like count
$likeCount = $spotObj->countLikes($spot_id);
echo json_encode(['status' => $status, 'likes' => $likeCount]);
