<?php
include 'includes/db.php';
include 'includes/header.php';
include 'classes/Spot.php';

// Fetch all spots with user info
$spotObj = new Spot($pdo);
$stmt = $pdo->prepare("SELECT hs.*, u.name AS user_name FROM hidden_spots hs JOIN users u ON hs.user_id = u.id ORDER BY created_at DESC");
$stmt->execute();
$spots = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="flex-1 bg-white min-h-screen overflow-y-auto">   
  <div class="w-full px-4 sm:px-6 lg:px-8">

    <!-- SEARCH -->
    <div class="mt-6">
      <form action="search.php" method="get" class="flex gap-3 items-center">
        <input name="query" type="search" placeholder="Search city — e.g. Copenhagen"
               class="flex-1 px-4 py-3 border border-gray-200 focus:outline-none focus:ring-2 focus:ring-green-400" />
        <button type="submit" class="bg-black text-white px-4 py-3 font-semibold hover:opacity-95">
          Search
        </button>

        <?php if(isset($_SESSION['user_id'])): ?>
          <div class="ml-4 relative">
            <button id="profileBtn" class="flex items-center gap-2 bg-black text-white px-4 py-2 rounded-full">
              <?=htmlspecialchars($_SESSION['user_name'])?>
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
            <div id="profileMenu" class="absolute right-0 mt-2 w-40 bg-white border rounded shadow hidden">
              <a href="profile.php" class="block px-4 py-2 hover:bg-gray-100">My Profile</a>
              <a href="upload.php" class="block px-4 py-2 hover:bg-gray-100">Upload</a>
              <a href="logout.php" class="block px-4 py-2 hover:bg-gray-100">Logout</a>
            </div>
          </div>
        <?php else: ?>
          <a href="login.php" class="ml-4 bg-black text-white px-4 py-2 rounded-full">Login / Sign Up</a>
        <?php endif; ?>
      </form>
    </div>

    <!-- FILTER hamburger (Pinterest-like) -->
    <div class="mt-4">
      <button id="filterBtn" class="inline-flex items-center gap-2 px-3 py-2 rounded-full bg-gray-100 hover:bg-gray-200">
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4 7h16M4 12h16M4 17h16" stroke-width="2" stroke-linecap="round"/></svg>
        <span class="font-medium">Filters</span>
      </button>

      <div id="filterMenu" class="mt-3 hidden bg-white border rounded shadow p-4 w-[360px]">
        <div class="grid grid-cols-2 gap-3">
          <button class="py-2 px-3 rounded bg-gray-100">All</button>
          <button class="py-2 px-3 rounded bg-gray-100">Trending</button>
          <button class="py-2 px-3 rounded bg-gray-100">Newest</button>
          <button class="py-2 px-3 rounded bg-gray-100">Sticky</button>
          <button class="py-2 px-3 rounded bg-gray-100">Nature</button>
          <button class="py-2 px-3 rounded bg-gray-100">Cafés</button>
        </div>
        <div class="mt-3 text-sm text-gray-500">Click a filter to reload the feed (connect to PHP or AJAX).</div>
      </div>
    </div>

    <!-- DIVIDER -->
    <div class="border-t border-gray-300 my-6"></div>

    <!-- ALL SPOTS PHOTO FEED -->
    <?php if (!empty($spots)): ?>
      <div class="columns-2 sm:columns-3 lg:columns-4 gap-4 space-y-4">
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
