<?php
require_once __DIR__ . '/../classes/session.php';
require_once '../includes/db.php';
require_once __DIR__ . '/../classes/spot.php';

$session = new SessionHandle();
$spotObj = new Spot($pdo);

// Initialize
$spot_id = 0;
$user_id = $session->logged_in() ? $session->get('user_id') : 0;

// Handle GET request for like count
if (isset($_GET['count'])) {
    $spot_id = intval($_GET['count']);
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE spot_id=?");
    $stmt->execute([$spot_id]);
    echo $stmt->fetchColumn();
    exit();
}

// Handle POST request for like/unlike
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['spot_id'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'invalid_request']);
        exit();
    }

    if (!$session->logged_in()) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'not_logged_in']);
        exit();
    }

    $spot_id = intval($_POST['spot_id']);

    if ($spotObj->isLikedByUser($spot_id, $user_id)) {
        // Unlike
        $stmt = $pdo->prepare("DELETE FROM likes WHERE user_id=? AND spot_id=?");
        $stmt->execute([$user_id, $spot_id]);
        $status = 'unliked';
    } else {
        // Like
        $stmt = $pdo->prepare("INSERT INTO likes (user_id, spot_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $spot_id]);
        $status = 'liked';
    }

    // Return JSON with updated like count
    $likeCount = $spotObj->countLikes($spot_id);
    echo json_encode(['status' => $status, 'likes' => $likeCount]);
    exit();
}

// Invalid request if not POST or GET count
http_response_code(400);
echo json_encode(['status' => 'error', 'message' => 'invalid_request']);
exit();
?>
