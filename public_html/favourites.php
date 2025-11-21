<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

include 'includes/db.php';
include 'includes/header.php';
include 'includes/profile-header.php';


$user_id = $_SESSION['user_id'];

// Fetch user's favorites
$stmt = $pdo->prepare("
    SELECT hs.*, u.name AS user_name, u.profile_photo
    FROM favorites f
    JOIN hidden_spots hs ON f.spot_id = hs.id
    JOIN users u ON hs.user_id = u.id
    WHERE f.user_id = :user_id
    ORDER BY f.created_at DESC
");
$stmt->execute(['user_id'=>$user_id]);
$favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);

// User info
$user_photo = $_SESSION['profile_photo'] ?? null;
$photo_url = $user_photo ? '/' . $user_photo : null;
?>

<main class="flex-1 bg-white min-h-screen overflow-y-auto">   
  <div class="w-full px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-4">
      
      <!-- Profile photo + Title -->
      <div class="flex items-center gap-4">
        <?php if($photo_url && file_exists($_SERVER['DOCUMENT_ROOT'] . $photo_url)): ?>
          <img src="<?= htmlspecialchars($photo_url) ?>" alt="Profile" class="w-16 h-16 rounded-full object-cover border-2 border-black">
        <?php else: ?>
          <div class="w-16 h-16 rounded-full bg-black flex items-center justify-center text-xl font-bold text-white">
            <?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?>
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


    <div class="border-t border-gray-300 mb-6"></div>

    <!-- Favorites grid -->
    <?php if (!empty($favorites)): ?>

      <!-- Masonry container -->
      <div id="masonry" class="mt-6">

        <?php foreach ($favorites as $spot): ?>
        <?php include __DIR__ . '/includes/photo-feed.php';  ?>
    
    <?php endforeach; ?>

  </div>


<script src="https://cdn.jsdelivr.net/npm/macy@2"></script>
<script>
window.addEventListener('load', () => {
  const masonry = Macy({
    container: '#masonry',
    columns: 4,
    margin: 12,
    breakAt: { 1024: 3, 640: 2, 0: 1 },
    trueOrder: false,
    waitForImages: true
  });

  masonry.recalculate(true);
  document.getElementById('masonry').style.display = 'block';
});
</script>



<?php else: ?>
  <p class="text-center text-gray-500 mt-10">You haven't added any favorite spots yet.</p>
<?php endif; ?>


  </div>
</main>
</body>

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
