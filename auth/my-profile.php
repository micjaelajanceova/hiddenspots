<?php
include '../includes/db.php';
include '../includes/header.php';
include '../classes/Spot.php';

// check if user is logged in
if(!isset($_SESSION['user_id'])){
    header("Location: login.php?action=login");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'User';

// fetch only the logged-in user's spots
$spotObj = new Spot($pdo);
$stmt = $pdo->prepare("
    SELECT * FROM hidden_spots 
    WHERE user_id = ? 
    ORDER BY created_at DESC
");
$stmt->execute([$user_id]);
$mySpots = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="flex-1 bg-white min-h-screen px-4 sm:px-6 lg:px-8 py-10">

<!-- PROFILE HEADER -->
<div class="flex items-center justify-between mb-6">
  <div class="flex items-center gap-4">
    <div class="w-16 h-16 bg-black text-white flex items-center justify-center rounded-full text-2xl font-semibold">
      <?php
      $user_photo = $_SESSION['profile_photo'] ?? null;
      if ($user_photo): ?>
        <img src="<?= htmlspecialchars($user_photo) ?>" alt="Profile" class="w-full h-full object-cover rounded-full">
      <?php else: ?>
        <?= strtoupper(substr($user_name, 0, 1)) ?>
      <?php endif; ?>
    </div>
    <h1 class="text-2xl font-bold"><?= htmlspecialchars($user_name) ?></h1>
  </div>

  <!-- EDIT PROFILE BUTTON -->
  <a href="edit-profile.php" class="bg-gray-200 text-black px-4 py-2 rounded-full hover:bg-gray-300 transition">Edit Profile</a>
</div>

  <!-- DIVIDER LINE LIKE INSTAGRAM -->
  <div class="border-t border-gray-300 mb-8"></div>

  <!-- USER'S PHOTO FEED -->
  <?php if (!empty($mySpots)): ?>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
      <?php foreach ($mySpots as $spot): ?>
        <a href="../spot-view.php?id=<?= htmlspecialchars($spot['id']) ?>" class="block group relative overflow-hidden">
          <img src="<?= htmlspecialchars($spot['file_path']) ?>" alt="<?= htmlspecialchars($spot['name']) ?>" class="w-full h-48 object-cover rounded-lg transition-transform duration-300 group-hover:scale-105">
          <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-sm font-semibold">
            <?= htmlspecialchars($spot['name']) ?>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <p class="text-center text-gray-500 mt-10">You haven't uploaded any spots yet.</p>
  <?php endif; ?>

  <!-- BACK TO HOME LINK -->
  <div class="text-center mt-12">
    <a href="../index.php" class="text-gray-600 hover:underline">← Back to Home</a>
  </div>

</main>

<?php include '../includes/footer.php'; ?>
