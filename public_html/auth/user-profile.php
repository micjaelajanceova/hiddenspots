<?php
include __DIR__ . '/../includes/db.php';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../classes/spot.php';




$user_id = $_GET['user_id'] ?? $_SESSION['user_id'];
if (!$user_id) die("No user ID provided.");


$stmt = $pdo->prepare("SELECT id, name, profile_photo FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) die("User not found.");


$photo_url = null;
if (!empty($user['profile_photo'])) {
    $photo_url = '/' . $user['profile_photo'];
}


$spotObj = new Spot($pdo);
$stmt = $pdo->prepare("SELECT h.*, u.name AS user_name
FROM hidden_spots h
JOIN users u ON h.user_id = u.id
WHERE h.user_id = ?
ORDER BY h.created_at DESC
");
$stmt->execute([$user_id]);
$spots = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="flex-1 bg-white min-h-screen px-4 sm:px-6 lg:px-8 py-10">

  <!-- PROFILE HEADER -->
  <div class="flex items-center justify-center mb-6 flex-col gap-3">
    <div class="w-16 h-16 bg-black text-white flex items-center justify-center rounded-full text-2xl font-semibold overflow-hidden">
      <?php if($photo_url && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $user['profile_photo'])): ?>
        <img src="<?= htmlspecialchars($photo_url) ?>" alt="Profile" class="w-full h-full object-cover rounded-full">
      <?php else: ?>
        <?= strtoupper(substr($user['name'], 0, 1)) ?>
      <?php endif; ?>
    </div>
    <h1 class="text-2xl font-bold"><?= htmlspecialchars($user['name']) ?></h1>
  </div>


  <div class="border-t border-gray-300 mb-8"></div>

 <!-- USER'S PHOTO FEED -->
<?php if (!empty($spots)): ?>

  <!-- Masonry container -->
  <div id="masonry" class="mt-6">
    <?php foreach ($spots as $spot): ?>

      <?php include __DIR__ . '/../includes/photo-feed.php';  ?>
    <?php endforeach; ?>
    
  </div>


<?php else: ?>
  <p class="text-center text-gray-500 mt-10">This user hasn't uploaded any spots yet.</p>
<?php endif; ?>



  
  <div class="text-center mt-12">
    <a href="../index.php" class="text-gray-600 hover:underline">← Back to Home</a>
  </div>

</main>

<?php include '../includes/footer.php'; ?>
