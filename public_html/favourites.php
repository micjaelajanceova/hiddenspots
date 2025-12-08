<?php
require_once __DIR__ . '/classes/session.php';
$session = new SessionHandle();

// Redirect to login if not logged in
if (!$session->logged_in()) {
    header("Location: auth/login.php");
    exit();
}

include 'includes/db.php';
include 'includes/header.php';
include 'includes/profile-header.php';

$userObj = new User($pdo);
$user_id = $session->get('user_id');

// Fetch user's favorites
$favorites = $userObj->getFavorites($user_id);

// Function to get photo URL
function getPhotoUrl($photo) {
    if ($photo) {
        return '/' . ltrim($photo, '/');
    }
    return null;
}

// User info
$user = $userObj->getById($user_id);
$photo_url = $userObj->getProfilePhoto($user_id);
$authorPhoto = getPhotoUrl($photo_url);
?>

<!----------------------- HTML ------------------------------>
<main class="flex-1 bg-white min-h-screen overflow-y-auto">   
  <div class="w-full px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-4">
      
      <!-- Profile photo + Title -->
      <div class="flex items-center gap-4">
        <?php if ($authorPhoto): ?>
          <img src="<?= htmlspecialchars($authorPhoto) ?>" alt="Profile" class="w-16 h-16 rounded-full object-cover border-2 border-black">
        <?php else: ?>
          <div class="w-16 h-16 rounded-full bg-black flex items-center justify-center text-xl font-bold text-white">
            <?= strtoupper(substr($session->get('user_name') ?? 'U', 0, 1)) ?>
          </div>
        <?php endif; ?>

        <div class="flex flex-col">
          <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight">My Favorites</h1>
          <p class="text-gray-500 text-sm md:text-base">Your saved hidden spots, updated recently</p>
        </div>
      </div>

      <!-- Count of saved spots -->
      <div class="text-gray-700 text-sm md:text-base font-semibold mt-0 md:mt-6">
        <?= count($favorites) ?> saved spots
      </div>

    </div>

    <div class="border-t border-gray-300 mb-0 md:mb-6"></div>

    <!-- Favorites grid -->
    <?php if (!empty($favorites)): ?>

      <!-- Masonry container -->
      <div id="masonry" class="mt-6">
      <?php foreach ($favorites as $spot): ?>

        <?php include __DIR__ . '/includes/photo-feed.php';  ?>
    
      <?php endforeach; ?>
      </div>

    <?php else: ?>
      <p class="text-center text-gray-500 mt-10">You haven't added any favorite spots yet.</p>
    <?php endif; ?>


  </div>
</main>


<?php include 'includes/footer.php'; ?>
