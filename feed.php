<?php
include 'includes/db.php';
include 'includes/header.php';
include 'classes/Spot.php';
include 'includes/profile-header.php';



// Fetch all spots with user info
$spotObj = new Spot($pdo);
$stmt = $pdo->prepare("SELECT hs.*, u.name AS user_name FROM hidden_spots hs JOIN users u ON hs.user_id = u.id ORDER BY created_at DESC");
$stmt->execute();
$spots = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="flex-1 bg-white min-h-screen overflow-y-auto">   
  <div class="w-full px-4 sm:px-6 lg:px-8">



    <!-- ALL SPOTS PHOTO FEED -->
    <?php if (!empty($spots)): ?>
      <div class="columns-2 sm:columns-3 lg:columns-4 gap-4 space-y-4 my-6">
        <?php foreach ($spots as $spot): ?>
          <a href="spot-view.php?id=<?= htmlspecialchars($spot['id']) ?>" class="block break-inside-avoid overflow-hidden group relative">
            <img src="<?= htmlspecialchars($spot['file_path']) ?>" 
                 alt="<?= htmlspecialchars($spot['name']) ?>" 
                 class="w-full object-cover transition-transform duration-300 group-hover:scale-105">
            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-sm font-semibold">
              <?= htmlspecialchars($spot['name']) ?>
            </div>
            <div class="absolute bottom-1 left-1 text-white text-xs bg-black/50 px-1">
              @<?= htmlspecialchars($spot['user_name']) ?>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="text-center text-gray-500 mt-10">No spots uploaded yet.</p>
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
