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

    
}

// Check if user liked/favorited
    $user_id = $_SESSION['user_id'] ?? 0;
    $liked = false;
    $favorited = false;

    if ($user_id) {
        // Like
        $stmt = $pdo->prepare("SELECT 1 FROM likes WHERE user_id=? AND spot_id=?");
        $stmt->execute([$user_id, $spot_id]);
        $liked = $stmt->fetch() ? true : false;

        // Favorite
        $stmt = $pdo->prepare("SELECT 1 FROM favorites WHERE user_id=? AND spot_id=?");
        $stmt->execute([$user_id, $spot_id]);
        $favorited = $stmt->fetch() ? true : false;
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
