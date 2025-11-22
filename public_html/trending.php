<?php include 'includes/header.php'; ?>

<?php
require_once 'includes/db.php';

// 1) Top 6 z posledných 7 dní
$stmtWeek = $pdo->query("
    SELECT hs.*, COUNT(l.id) AS total_likes
    FROM hidden_spots hs
    LEFT JOIN likes l ON hs.id = l.spot_id
    WHERE hs.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY hs.id
    ORDER BY total_likes DESC
    LIMIT 6
");
$trendingWeek = $stmtWeek->fetchAll(PDO::FETCH_ASSOC);

// 2) TOP 9 celkovo
$stmtAll = $pdo->query("
    SELECT hs.*, COUNT(l.id) AS total_likes
    FROM hidden_spots hs
    LEFT JOIN likes l ON hs.id = l.spot_id
    GROUP BY hs.id
    ORDER BY total_likes DESC
    LIMIT 9
");

$trendingAll = $stmtAll->fetchAll(PDO::FETCH_ASSOC);
?>

<h2 class="text-2xl font-bold mb-4">🔥 Trending this week</h2>

<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-4 mb-10">
<?php foreach ($trendingWeek as $spot): ?>
    <a href="spot.php?id=<?= $spot['id'] ?>" 
       class="bg-white rounded-xl shadow hover:shadow-lg transition p-2">
       
        <img src="<?= $spot['file_path'] ?>" 
             alt="" 
             class="w-full h-32 object-cover rounded-lg mb-2">

        <p class="text-sm font-semibold text-center truncate">
            <?= htmlspecialchars($spot['name']) ?>
        </p>
    </a>
<?php endforeach; ?>
</div>


<h2 class="text-2xl font-bold mb-4">📈 Trending all-time</h2>

<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-9 gap-4">
<?php foreach ($trendingAll as $spot): ?>
    <a href="spot.php?id=<?= $spot['id'] ?>" 
       class="bg-white rounded-xl shadow hover:shadow-lg transition p-2">
       
        <img src="<?= $spot['file_path'] ?>" 
             alt="" 
             class="w-full h-32 object-cover rounded-lg mb-2">

        <p class="text-sm font-semibold text-center truncate">
            <?= htmlspecialchars($spot['name']) ?>
        </p>
    </a>
<?php endforeach; ?>
</div>

<?php include 'includes/footer.php'; ?>
