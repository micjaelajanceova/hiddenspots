<?php
include 'includes/db.php';
include 'includes/header.php';
include 'includes/profile-header.php';

if (!isset($_SESSION['user_id'])){
    header("Location: auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's favorites
$stmt = $pdo->prepare("
    SELECT hs.*, u.name AS user_name, u.profile_photo
    FROM favorites f
    JOIN hidden_spots hs ON f.spot_id = hs.id
    JOIN users u ON hs.user_id = u.id
    WHERE f.user_id = :user_id
    ORDER BY hs.created_at DESC
");
$stmt->execute(['user_id'=>$user_id]);
$favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);

// User info
$user_photo = $_SESSION['profile_photo'] ?? null;
$photo_url = $user_photo ? '/hiddenspots/' . $user_photo : null;
?>

<main class="flex-1 bg-gray-50 min-h-screen overflow-y-auto">   
  <div class="w-full px-4 sm:px-6 lg:px-8 py-8">

    <!-- Instagram-like header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-4">
      
      <!-- Profile photo + Title -->
      <div class="flex items-center gap-4">
        <?php if($photo_url && file_exists($_SERVER['DOCUMENT_ROOT'] . $photo_url)): ?>
          <img src="<?= htmlspecialchars($photo_url) ?>" alt="Profile" class="w-16 h-16 rounded-full object-cover border-2 border-green-500">
        <?php else: ?>
          <div class="w-16 h-16 rounded-full bg-gray-300 flex items-center justify-center text-xl font-bold text-white">
            <?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?>
          </div>
        <?php endif; ?>

        <div class="flex flex-col">
          <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight">My Favorites</h1>
          <p class="text-gray-500 text-sm md:text-base">Your saved hidden spots, updated recently</p>
        </div>
      </div>

      <!-- Count of saved spots -->
      <div class="text-gray-700 text-sm md:text-base font-semibold">
        <?= count($favorites) ?> saved spots
      </div>

    </div>

    <!-- Divider line -->
    <div class="border-t border-gray-300 mb-6"></div>

    <!-- Favorites grid -->
<?php if (!empty($favorites)): ?>
  <div class="columns-2 sm:columns-3 lg:columns-4 gap-4 space-y-4">
    <?php foreach ($favorites as $spot): ?>
      <a href="spot-view.php?id=<?= htmlspecialchars($spot['id']) ?>" 
         class="block break-inside-avoid overflow-hidden group relative">
        
        <img src="<?= htmlspecialchars($spot['file_path']) ?>" 
             alt="<?= htmlspecialchars($spot['name']) ?>" 
             class="w-full object-cover transition-transform duration-300 group-hover:scale-105">
        
        <!-- Hover overlay with spot name -->
        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-sm font-semibold rounded-xl">
          <?= htmlspecialchars($spot['name']) ?>
        </div>

        <!-- User name tag -->
        <div class="absolute bottom-1 left-1 text-white text-xs bg-black/50 px-1 rounded">
          @<?= htmlspecialchars($spot['user_name']) ?>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
<?php else: ?>
  <p class="text-center text-gray-500 mt-10">You haven't added any favorite spots yet.</p>
<?php endif; ?>


  </div>
</main>

<script>
// PROFILE MENU TOGGLE
const profileBtn = document.getElementById('profileBtn');
const profileMenu = document.getElementById('profileMenu');
if(profileBtn && profileMenu){
  profileBtn.addEventListener('click', e => {
    e.stopPropagation();
    profileMenu.classList.toggle('hidden');
  });
  document.addEventListener('click', e => {
    if(!profileMenu.contains(e.target) && !profileBtn.contains(e.target)){
      profileMenu.classList.add('hidden');
    }
  });
});
</script>

<?php include 'includes/footer.php'; ?>
