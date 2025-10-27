<?php
include '../includes/db.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $spot_id = intval($_POST['spot_id'] ?? 0);

    if (!$user_id) {
        http_response_code(403);
        echo 'not_logged_in';
        exit();
    }

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
