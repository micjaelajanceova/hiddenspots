<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/classes/spot.php';


require_once __DIR__ . '/includes/search.php';
require_once __DIR__ . '/includes/map.php';

$spotObj = new Spot($pdo);

$city = $_GET['query'] ?? '';
$filter_type = $_GET['type'] ?? '';


// Fetch all types for filter dropdown
$types = $spotObj->getAllTypes();

// Fetch spots
$spots = $spotObj->search($city, $filter_type);
?>

<!----------------------- HTML ------------------------------>
<main class="flex-1 bg-white min-h-screen overflow-y-auto pt-2 md:pt-6 px-4 sm:px-6 lg:px-8"> 

  <?php include 'includes/profile-header.php'; ?>

  <!-- SEARCH -->
  <div class=" flex flex-wrap items-center gap-2 mb-6 mt-6 sm:mt-base">


    <form action="feed.php" method="get" class="flex gap-2 items-center w-auto">
      <input 
        name="query" 
        type="search" 
        placeholder="Search city..."
        value="<?= htmlspecialchars($city) ?>"
        class="w-full max-w-xs sm:max-w-lg px-3 py-1 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-black text-sm"
   
      />
      <button type="submit" class="bg-gray-200 text-black px-3 py-1 rounded text-sm hover:opacity-95">
        Search
      </button>
    </form>

    <!-- FILTER DROPDOWN -->
  <div class="relative mx-2">
    <button id="filterBtn" class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-gray-200 hover:bg-gray-300 text-sm ">
      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <path d="M4 7h16M4 12h16M4 17h16" stroke-width="2" stroke-linecap="round"/>
      </svg>
    </button>
    <div id="filterDropdown" class="hidden absolute mt-2 right-0 w-40 bg-white border border-gray-300 rounded shadow-lg z-50">
      <a href="feed.php?<?= $city ? 'query='.urlencode($city) : '' ?>" 
         class=" block px-4 py-2 hover:bg-gray-100 <?= $filter_type==''?'font-semibold':'' ?>">All</a>
      <?php foreach($types as $type): ?>
        <a href="feed.php?<?= $city ? 'query='.urlencode($city).'&' : '' ?>type=<?= urlencode($type) ?>" 
           class="block px-4 py-2 hover:bg-gray-100 <?= $filter_type==$type?'font-semibold':'' ?>">
          <?= htmlspecialchars($type) ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>

    <!-- SHOW MAP BUTTON -->
    <button id="showMap" class=" px-3 py-1 bg-black text-white rounded text-sm">
      Show on Map
      <span id="feedMapArrow" class="inline-block transition-transform duration-300">▼</span>
    </button>

</div>


<div id="feedMap" style="display:none; height:500px; margin-top:16px;"></div>

<!-- PHOTO FEED -->
<?php if (!empty($spots)): ?>
  
  <!-- Masonry container -->
  <div id="masonry" class="mt-6">
    
    <?php foreach ($spots as $spot): ?>
    
      <?php include __DIR__ . '/includes/photo-feed.php';  ?>
  
    <?php endforeach; ?>

  </div>
  
  <?php else: ?>
  <p class="text-center text-gray-500 mt-10">No spots uploaded yet.</p>
  <?php endif; ?>

</main>


<script>const spots = <?= json_encode($spots) ?>;</script>


<?php require_once __DIR__ . '/includes/footer.php'; ?>
