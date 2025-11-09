<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/classes/spot.php';


require_once __DIR__ . '/includes/search.php';
require_once __DIR__ . '/includes/map.php';
$city = $_GET['query'] ?? '';
$filter_type = $_GET['type'] ?? '';


$types_stmt = $pdo->query("SELECT DISTINCT type FROM hidden_spots WHERE type IS NOT NULL AND type != ''");
$types = $types_stmt->fetchAll(PDO::FETCH_COLUMN);


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





<main class="flex-1 bg-white min-h-screen overflow-y-auto pt-6 px-4 sm:px-6 lg:px-8"> 


  <?php include 'includes/profile-header.php'; ?>

  <!-- SEARCH -->
  <div class=" flex items-center gap-2 mb-6">
    <form action="feed.php" method="get" class="flex gap-2 items-center w-auto">
      <input 
        name="query" 
        type="search" 
        placeholder="Search city — e.g. Copenhagen"
        value="<?= htmlspecialchars($city) ?>"
        class="w-64 px-3 py-1 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-green-400 text-sm"
   
      />
      <button type="submit" class="bg-gray-200 text-black px-3 py-1 rounded text-sm hover:opacity-95">
        Search
      </button>
    </form>

    <!-- FILTER DROPDOWN -->
  <div class="relative ml-2">
    <button id="filterBtn" class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-gray-200 hover:bg-gray-300 text-sm ">
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
  <!-- SHOW MAP BUTTON -->
<button id="showMap" class="ml-2 px-3 py-1 bg-black text-white rounded text-sm">
    Map
</button>

</div>


<div id="feedMap" style="display:none; height:500px; margin-top:16px;"></div>


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
document.addEventListener('DOMContentLoaded', () => {
  const btn = document.getElementById('filterBtn');
  const dropdown = document.getElementById('filterDropdown');
  const mapBtn = document.getElementById('showMap');
  const mapDiv = document.getElementById('feedMap');

  if (btn && dropdown && mapDiv) {
    btn.addEventListener('click', e => {
      e.preventDefault();
      e.stopPropagation();
      dropdown.classList.toggle('hidden');


      if (!dropdown.classList.contains('hidden') && mapDiv.style.display === 'block') {
        mapDiv.style.display = 'none';
      }
    });


    document.addEventListener('click', () => {
      dropdown.classList.add('hidden');
    });
  }
});


// MAP TOGGLE
const mapBtn = document.getElementById('showMap');
const mapDiv = document.getElementById('feedMap');
let feedMap; 

mapBtn.addEventListener('click', () => {
    mapDiv.style.display = mapDiv.style.display === 'none' ? 'block' : 'none';

    if (mapDiv.style.display === 'block') {
        setTimeout(() => {
            if (!feedMap) initFeedMap(); 
            else feedMap.invalidateSize();
        }, 100);
    }
});

function initFeedMap() {
    const spots = <?= json_encode($spots) ?>;


    let mapCenter = [55.6761, 12.5683]; // default Copenhagen
    const firstSpot = spots.find(s => s.latitude && s.longitude);
    if(firstSpot) mapCenter = [parseFloat(firstSpot.latitude), parseFloat(firstSpot.longitude)];

    feedMap = L.map('feedMap').setView(mapCenter, 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(feedMap);

 
    spots.forEach(spot => {
    const lat = parseFloat(spot.latitude);
    const lng = parseFloat(spot.longitude);
    if(!isNaN(lat) && !isNaN(lng)) {
        const marker = L.marker([lat, lng]).addTo(feedMap);
        const popupContent = `
            <div style="text-align:center; max-width:200px;">
                <img src="${spot.file_path}" 
                     alt="${spot.name}" 
                     style="width:100%; height:120px; object-fit:cover; border-radius:6px; margin-bottom:5px;" />
                <b><a href="spot-view.php?id=${spot.id}"
                      style="color:#1d4ed8; text-decoration:none;">
                    ${spot.name}
                </a></b><br>
                ${spot.address}<br>
                <small>@${spot.user_name}</small>
            </div>`;
        marker.bindPopup(popupContent);
        }
    });
}


</script>


<?php require_once __DIR__ . '/includes/footer.php'; ?>
