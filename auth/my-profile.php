<?php
include __DIR__ . '/../includes/db.php';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../classes/Spot.php';

// check if user is logged in
if(!isset($_SESSION['user_id'])){
    header("Location: login.php?action=login");
    exit();
}

$user_id = $_SESSION['user_id'];
// Fetch user info (for profile header)
$stmt = $pdo->prepare("SELECT name, profile_photo FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$user_name = $user['name'] ?? 'User';
$user_photo = $user['profile_photo'] ?? null;

// načítanie profilovej fotky z DB
$stmt = $pdo->prepare("SELECT profile_photo FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user_photo = $stmt->fetchColumn();

// načítanie spotov používateľa cez Spot class
$spotObj = new Spot($pdo);
$mySpots = $spotObj->getByUser($user_id);
?>

<main class="flex-1 bg-white min-h-screen px-4 sm:px-6 lg:px-8 py-10">

<!-- PROFILE HEADER -->
<div class="flex items-center justify-between mb-6">
  <div class="flex items-center gap-4">
    <div class="w-16 h-16 bg-black text-white flex items-center justify-center rounded-full text-2xl font-semibold">
      <?php if($user_photo && file_exists(__DIR__ . '/../' . $user_photo)): ?>
        <img src="../<?= htmlspecialchars($user_photo) ?>" alt="Profile" class="w-full h-full object-cover rounded-full">
      <?php else: ?>
        <?= strtoupper(substr($user_name, 0, 1)) ?>
      <?php endif; ?>
    </div>
    <h1 class="text-2xl font-bold"><?= htmlspecialchars($user_name) ?></h1>
  </div>

  <!-- EDIT PROFILE BUTTON -->
  <a href="edit-profile.php" class="bg-gray-200 text-black px-4 py-2 rounded-full hover:bg-gray-300 transition">Edit Profile</a>
</div>

<div class="border-t border-gray-300 mb-8"></div>

<!-- USER'S PHOTO FEED -->
<?php if (!empty($mySpots)): ?>
  <div class="w-full columns-2 sm:columns-3 lg:columns-4 gap-4 space-y-4">
      <?php foreach ($mySpots as $spot): ?>
      <a href="../spot-view.php?id=<?= htmlspecialchars($spot['id']) ?>" class="block break-inside-avoid overflow-hidden group relative">
          <img src="../<?= htmlspecialchars($spot['file_path']) ?>" 
              alt="<?= htmlspecialchars($spot['name']) ?>" 
              class="w-full object-cover transition-transform duration-300 group-hover:scale-105">
          <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-sm font-semibold">
          <?= htmlspecialchars($spot['name']) ?>
          </div>
      </a>
      <?php endforeach; ?>
  </div>
<?php else: ?>
  <p class="text-center text-gray-500 mt-10">This user hasn't uploaded any spots yet.</p>
<?php endif; ?>

<div class="text-center mt-12">
  <a href="../index.php" class="text-gray-600 hover:underline">← Back to Home</a>
</div>

</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
