<?php
include '../includes/db.php';
include '../includes/header.php';
include '../classes/Spot.php';

$user_id = $_GET['user_id'] ?? null;
if (!$user_id) die("No user ID provided.");

// Fetch user info
$stmt = $pdo->prepare("SELECT id, name FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) die("User not found.");

// Fetch spots posted by this user
$spotObj = new Spot($pdo);
$stmt = $pdo->prepare("SELECT * FROM hidden_spots WHERE user_id=? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$spots = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="flex-1 bg-white min-h-screen px-4 sm:px-6 lg:px-8 py-10">

  <!-- PROFILE HEADER -->
  <div class="flex items-center justify-center mb-6 flex-col gap-3">
    <div class="w-16 h-16 bg-black text-white flex items-center justify-center rounded-full text-2xl font-semibold">
      <?= strtoupper(substr($user['name'], 0, 1)) ?>
    </div>
    <h1 class="text-2xl font-bold"><?= htmlspecialchars($user['name']) ?></h1>
  </div>

  <!-- DIVIDER LINE LIKE INSTAGRAM -->
  <div class="border-t border-gray-300 mb-8"></div>

  <!-- USER'S PHOTO FEED -->
  <?php if (!empty($spots)): ?>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
      <?php foreach ($spots as $spot): ?>
        <a href="../spot-view.php?id=<?= htmlspecialchars($spot['id']) ?>" class="block group relative overflow-hidden">
          <img src="../<?= htmlspecialchars($spot['file_path']) ?>" alt="<?= htmlspecialchars($spot['name']) ?>" class="w-full h-48 object-cover rounded-lg transition-transform duration-300 group-hover:scale-105">
          <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-sm font-semibold">
            <?= htmlspecialchars($spot['name']) ?>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <p class="text-center text-gray-500 mt-10">This user hasn't uploaded any spots yet.</p>
  <?php endif; ?>

  <!-- BACK TO HOME LINK -->
  <div class="text-center mt-12">
    <a href="../index.php" class="text-gray-600 hover:underline">← Back to Home</a>
  </div>

</main>

<?php include '../includes/footer.php'; ?>
