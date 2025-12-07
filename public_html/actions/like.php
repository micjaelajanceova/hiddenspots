<?php
// Database connection
require_once '../includes/db.php';

// Session handler
require_once __DIR__ . '/../classes/session.php';
$session = new SessionHandle();


// Handle like/unlike action
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $spot_id = intval($_POST['spot_id'] ?? 0);

    // User must be logged in to like a spot
    if (!$session->logged_in()) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'not_logged_in']);
        exit();
    }

// Get user ID from session
$user_id = $session->get('user_id');

    // Check if already liked
    $stmt = $pdo->prepare("SELECT id FROM likes WHERE user_id=? AND spot_id=?");
    $stmt->execute([$user_id, $spot_id]);
    $liked = $stmt->fetch();

    if ($liked) {
        // Unlike
        $pdo->prepare("DELETE FROM likes WHERE user_id=? AND spot_id=?")->execute([$user_id, $spot_id]);
        echo 'unliked';
    } else {
        // Like
        $pdo->prepare("INSERT INTO likes (user_id, spot_id) VALUES (?,?)")->execute([$user_id, $spot_id]);
        echo 'liked';
    }
    exit();
}

// Return updated like count (for AJAX)
if (isset($_GET['count'])) {
    $spot_id = intval($_GET['count']);
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE spot_id=?");
    $stmt->execute([$spot_id]);
    echo $stmt->fetchColumn();
    exit();
}
?>
