<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch notifications
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Notifications</title>
</head>
<body>
<h2>Your Notifications</h2>

<?php if (empty($notifications)): ?>
    <p>No notifications yet.</p>
<?php else: ?>
    <?php foreach ($notifications as $n): ?>
        <div style="margin-bottom:10px; padding:10px; border:1px solid #ccc;">
            <strong><?= htmlspecialchars($n['type']) ?></strong><br>
            <?= htmlspecialchars($n['message']) ?><br>
            <small><?= $n['created_at'] ?></small>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>
