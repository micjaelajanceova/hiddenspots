<?php
include 'includes/db.php';
include 'includes/header.php';
include 'classes/Spot.php';

include 'includes/search.php';

$city = $_GET['query'] ?? '';
$filter_type = $_GET['type'] ?? '';

// Získame všetky unikátne typy pre filter buttony
$types_stmt = $pdo->query("SELECT DISTINCT type FROM hidden_spots WHERE type IS NOT NULL AND type != ''");
$types = $types_stmt->fetchAll(PDO::FETCH_COLUMN);

// Fetch spots s možným filtrovaním
$sql = "SELECT hs.*, u.name AS user_name 
        FROM hidden_spots hs 
        JOIN users u ON hs.user_id = u.id 
        WHERE 1=1";

$params = [];

if(!empty($city)){
    $sql .= " AND hs.city LIKE ?";
    $params[] = "%$city%";
}

if(!empty($filter_type)){
    $sql .= " AND hs.type = ?";
    $params[] = $filter_type;
}

$sql .= " ORDER BY hs.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$spots = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>





<main class="flex-1 bg-white min-h-screen overflow-y-auto pt-8 px-4 sm:px-6 lg:px-8"> 
  <!-- pt-24 pridáva priestor nad feed, aby sticky search neprekryl fotky -->

  <?php include 'includes/profile-header.php'; ?>

  <!-- STICKY SEARCH LIŠTA -->
  <div class="sticky z-50 flex items-center gap-2 mb-6">
    <form action="feed.php" method="get" class="flex gap-2 items-center w-auto">
      <input 
        name="query" 
        type="search" 
        placeholder="Search city — e.g. Copenhagen"
        value="<?= htmlspecialchars($city) ?>"
        class="w-64 px-3 py-1 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-green-400 text-sm"
        required
      />
      <button type="submit" class="bg-black text-white px-3 py-1 rounded text-sm hover:opacity-95">
        Search
      </button>
    </form>

    <!-- FILTER DROPDOWN -->
  <div class="relative ml-2">
    <button id="filterBtn" class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-gray-100 hover:bg-gray-200 text-sm">
      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <path d="M4 7h16M4 12h16M4 17h16" stroke-width="2" stroke-linecap="round"/>
      </svg>
    </button>
    <div id="filterDropdown" class="hidden absolute mt-2 right-0 w-40 bg-white border border-gray-300 rounded shadow-lg z-50">
      <a href="feed.php?<?= $city ? 'query='.urlencode($city) : '' ?>" 
         class="block px-4 py-2 hover:bg-gray-100 <?= $filter_type==''?'font-semibold':'' ?>">All</a>
      <?php foreach($types as $type): ?>
        <a href="feed.php?<?= $city ? 'query='.urlencode($city).'&' : '' ?>type=<?= urlencode($type) ?>" 
           class="block px-4 py-2 hover:bg-gray-100 <?= $filter_type==$type?'font-semibold':'' ?>">
          <?= htmlspecialchars($type) ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>



    <!-- ALL SPOTS PHOTO FEED -->
    <?php if (!empty($spots)): ?>
      <div class="columns-2 sm:columns-3 lg:columns-4 gap-4 space-y-4 mt-6">
        <?php foreach ($spots as $spot): ?>
          <a href="spot-view.php?id=<?= htmlspecialchars($spot['id']) ?>" 
             class="block break-inside-avoid overflow-hidden group relative mb-4">
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
  // FILTER DROPDOWN TOGGLE
const filterBtn = document.getElementById('filterBtn');
const filterDropdown = document.getElementById('filterDropdown');

if(filterBtn && filterDropdown){
  filterBtn.addEventListener('click', e => {
    e.stopPropagation();
    filterDropdown.classList.toggle('hidden');
  });

  document.addEventListener('click', e => {
    if(!filterDropdown.contains(e.target) && !filterBtn.contains(e.target)){
      filterDropdown.classList.add('hidden');
    }
  });
}
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
