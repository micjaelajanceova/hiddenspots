<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/spot.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?action=login");
    exit();
}

$user_id = $_SESSION['user_id'];

// Use User class to fetch current user info
$userObj = new User($pdo);
$user = $userObj->getById($user_id);
if (!$user) die("User not found.");

// Define username and get the user's profile photo path (or null for initials)
$user_name = $user['name'];
$photo_url = $userObj->getProfilePhoto($user_id);

// Create Spot object to fetch user's uploaded spots
$spotObj = new Spot($pdo);
$mySpots = $spotObj->getByUser($user_id);
?>

<main class="flex-1 bg-white min-h-screen px-4 sm:px-6 lg:px-8 py-10">

<!-- PROFILE HEADER -->
<div class="flex items-center justify-between mb-6">
  <div class="flex items-center gap-4">
    <div class="w-16 h-16 bg-black text-white flex items-center justify-center rounded-full text-2xl font-semibold">
     <?php if ($photo_url): ?>
        <img src="<?= htmlspecialchars($photo_url) ?>" alt="Profile" class="w-full h-full object-cover rounded-full">
    <?php else: ?>
        <?= strtoupper(substr($user_name, 0, 1)) ?>
    <?php endif; ?>
    </div>
    <h1 class="text-2xl font-bold"><?= htmlspecialchars($user_name) ?></h1>
  </div>

  <!-- EDIT PROFILE BUTTON -->
  <a href="edit-profile.php" class="bg-gray-200 text-black px-4 py-2 rounded-full hover:bg-gray-300 transition text-sm sm:px-4 sm:py-2 sm:text-base">Edit Profile</a>
</div>

<div class="border-t border-gray-300 mb-8"></div>

<!-- USER'S PHOTO FEED -->
<?php if (!empty($mySpots)): ?>
  
  <!-- Masonry container -->
  <div id="masonry" class="mt-6">

      <?php foreach ($mySpots as $spot): ?>

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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
