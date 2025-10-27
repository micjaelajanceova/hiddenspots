<?php
include 'includes/db.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])){
    header("Location: auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT hs.* 
    FROM favorites f
    JOIN hidden_spots hs ON f.spot_id = hs.id
    WHERE f.user_id = :user_id
    ORDER BY hs.created_at DESC
");
$stmt->execute(['user_id'=>$user_id]);
$favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="min-h-screen p-6 max-w-7xl mx-auto">
<h1 class="text-3xl font-bold mb-6">My Favorites</h1>

<?php if(empty($favorites)): ?>
<p class="text-gray-500">You haven't added any favorite spots yet.</p>
<?php else: ?>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
<?php foreach($favorites as $spot): ?>
<div class="bg-white rounded-xl shadow p-4 flex flex-col">
<img src="<?=htmlspecialchars($spot['file_path'])?>" alt="<?=htmlspecialchars($spot['name'])?>" class="h-48 w-full object-cover rounded mb-4">
<h2 class="text-xl font-semibold"><?=htmlspecialchars($spot['name'])?></h2>
<p class="text-gray-500 text-sm"><?=htmlspecialchars($spot['city'])?></p>
<p class="text-gray-700 mt-2 text-sm"><?=htmlspecialchars($spot['description'])?></p>
<a href="spot-view.php?id=<?=intval($spot['id'])?>" class="mt-auto inline-block text-center bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 mt-4">View Spot</a>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>
