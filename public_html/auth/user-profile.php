<?php
include __DIR__ . '/../includes/db.php';
include __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../classes/User.php';
include __DIR__ . '/../classes/spot.php';

// Get user ID – either from the URL or from the logged-in user's session
$user_id = $_GET['user_id'] ?? $_SESSION['user_id'];
if (!$user_id) die("No user ID provided.");

// Use User class to fetch user info
$userObj = new User($pdo);
$user = $userObj->getById($user_id);

if (!$user) die("User not found.");

// Get user's profile photo path (or fallback)
$photo_url = $userObj->getProfilePhoto($user_id);


// Fetch all spots uploaded by this user
$spotObj = new Spot($pdo);
$spots = $spotObj->getByUser($user_id);
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
