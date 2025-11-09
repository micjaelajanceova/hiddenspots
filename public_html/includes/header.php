<?php
require_once __DIR__ . '/db.php'; // ← pridaj toto
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// zvyšok tvojho kódu...


if (isset($_SESSION['user_id'])) {
  $stmt = $pdo->prepare("SELECT blocked FROM users WHERE id = ?");
  $stmt->execute([$_SESSION['user_id']]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($user && $user['blocked']) {
      session_unset();
      session_destroy();
      header("Location: auth/login.php?error=blocked");
      exit();
  }
}

$user_id = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['role'] ?? 'user';
?>



<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HiddenSpots — discover your city's secret places</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body class="flex flex-col md:flex-row min-h-screen">

<!-- Desktop sidebar -->
<aside class="hidden md:flex flex-col md:w-64 bg-gray-100 border-r sticky top-0 h-screen p-4 shadow-lg shadow-gray-300 z-10">


  <!-- HORNY BLOK: Logo + Menu links -->
  <div class="flex flex-col gap-8">
    <!-- Logo -->
<a href="/index.php" class="logo text-black hover:text-blue-500">
  <span class="text-3xl font-extrabold text-black">HiddenSpots</span>
</a>






    <!-- Menu links s ikonami -->
    <nav class="flex flex-col pt-5 gap-6 text-black">
      <a href="/feed.php" class="flex items-center gap-4 font-semibold hover:text-blue-500">
        <i class="ph-house text-lg"></i> Feed
      </a>
      <a href="/favourites.php" class="flex items-center gap-4 font-semibold hover:text-blue-500">
        <i class="ph-heart text-lg"></i> Favourites
      </a>
      <a href="/trending.php" class="flex items-center gap-4 font-semibold hover:text-blue-500">
        <i class="ph-trend-up text-lg"></i> Trending
      </a>
      <a href="/about.php" class="flex items-center gap-4 font-semibold hover:text-blue-500">
        <i class="ph-info text-lg"></i> About HS
      </a>

      <?php if (isset($_SESSION['user_id']) && $user_role === 'admin'): ?>
    <a href="admin.php" class="flex items-center gap-4 font-semibold hover:text-red-500">
      <i class="ph-shield-star text-lg"></i> Admin Panel
    </a>
<?php endif; ?>

    </nav>
  </div>



  <!-- Upload Button -->
  <div class="mt-auto">
  <a href="#" id="desktopUploadBtn"
     class="w-full sm:w-auto py-2 px-4 bg-black text-white rounded-lg flex items-center justify-center gap-2 hover:bg-gray-800 transition"
     onclick="event.preventDefault(); document.getElementById('uploadModal').classList.remove('hidden');">
    <span>+</span> Upload
  </a>
  </div>
  </aside>
<!-- Upload Modal -->
<div id="uploadModal" class="fixed inset-0 bg-black bg-opacity-70 flex justify-center items-center hidden" style="z-index:9999;">
  <div id="uploadContainer" 
       class="bg-white rounded-2xl shadow-lg w-full h-full md:max-w-3xl md:h-[80vh] flex flex-col overflow-hidden relative animate-[fadeIn_0.3s_ease]">

    <!-- Header -->
    <div class="flex justify-between items-center p-4 border-b border-gray-200">
      <div class="text-center py-3 font-semibold text-lg">Create new post</div>
      <button id="closeUploadModal" class="text-black text-2xl hover:opacity-80">&times;</button>
    </div>

    <!-- STEP 1: Select Photo -->
    <div id="stepSelect" class="flex flex-col items-center justify-center flex-1 text-center p-4">
      <label for="photoInput" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg cursor-pointer transition">
        Select from your computer
      </label>
      <input type="file" id="photoInput" name="photo" accept="image/*" class="hidden">
    </div>

    <!-- STEP 2: Preview -->
    <div id="stepPreview" class="hidden flex items-center justify-center flex-1 bg-white relative overflow-hidden p-4">
      <img 
        id="previewImage" 
        class="max-w-[90%] max-h-[90%] object-contain rounded-lg transition-transform duration-300" 
      />
      <button 
        id="nextBtn" 
        class="absolute top-4 right-4 bg-blue-500 hover:bg-blue-600 text-white px-5 py-1.5 rounded-lg font-semibold transition">
        Next
      </button>
    </div>

    <!-- STEP 3: Form -->
    <div id="stepForm" class="hidden flex flex-1 h-full overflow-hidden">
      <!-- Image preview -->
      <div class="w-1/2 h-full bg-black flex justify-center items-center overflow-hidden">
        <img id="finalImage" class="w-full h-full object-cover" />
      </div>

      <!-- Form -->
      <div class="w-1/2 p-6 overflow-y-auto">
        <h2 class="text-lg font-semibold mb-4">New Hidden Spot</h2>
        <form id="uploadForm" action="upload.php" method="post" enctype="multipart/form-data" class="space-y-4">
          <input type="hidden" name="photoData" id="photoData">
          <input type="text" name="name" placeholder="Name" required class="w-full border rounded p-2 focus:ring-2 focus:ring-blue-400 outline-none" />
          <input type="text" name="city" placeholder="City" required class="w-full border rounded p-2 focus:ring-2 focus:ring-blue-400 outline-none" />
          <input type="text" name="address" placeholder="Address (optional)" class="w-full border rounded p-2 focus:ring-2 focus:ring-blue-400 outline-none" />

          <!-- Hidden lat/lng -->
          <input type="hidden" name="latitude" id="latitude">
          <input type="hidden" name="longitude" id="longitude">

          <!-- MAP -->
          <div id="uploadMap" class="w-full h-56 rounded-lg shadow-md border border-gray-200 my-2"></div>

          <select name="category" required class="w-full border rounded p-2 bg-white focus:ring-2 focus:ring-blue-400 outline-none">
            <option value="">Select a category</option>
            <option>Nature</option>
            <option>Cafés</option>
            <option>Urban</option>
            <option>Architecture</option>
            <option>Viewpoint</option>
            <option>Restaurant</option>
            <option>Other</option>
          </select>

          <textarea name="description" rows="3" placeholder="Description or tip" class="w-full border rounded p-2 focus:ring-2 focus:ring-blue-400 outline-none"></textarea>

          <div class="flex justify-end gap-3">
            <button type="button" id="backBtn" class="text-gray-600 hover:underline">Back</button>
            <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600 transition">Share</button>
          </div>
        </form>
      </div>
    </div>

  </div>
</div>





  <!-- Mobile top logo -->
  <div class="md:hidden sticky top-0 border-b p-3 flex justify-center bg-gray-100 z-50">
    <a href="/index.php" class="text-3xl font-extrabold text-black hover:text-blue-500">HS</a>
  </div>
<!-- Phosphor Icons CDN -->
<script src="https://unpkg.com/phosphor-icons"></script>

<!-- Mobile bottom menu -->
<nav class="fixed bottom-0 left-0 right-0 bg-white flex justify-around items-center p-2 md:hidden border-t shadow-md z-50">

  <!-- Home -->
  <a href="/feed.php" class="text-gray-600 hover:text-blue-500">
    <i class="ph-house text-2xl"></i>
  </a>

  <!-- Favourites -->
  <a href="/favourites.php" class="text-gray-600 hover:text-blue-500">
    <i class="ph-heart text-2xl"></i>
  </a>

<!-- Upload button (center, mobil) -->
<button 
    class="bg-black text-white p-3 rounded-full shadow-md"
    onclick="document.getElementById('uploadModal').classList.remove('hidden')"
>
    <i class="ph-plus text-2xl"></i>
</button>


  <!-- Trending -->
  <a href="/trending.php" class="text-gray-600 hover:text-blue-500">
    <i class="ph-trend-up text-2xl"></i>
  </a>

  <!-- About -->
  <a href="/about.php" class="text-gray-600 hover:text-blue-500">
    <i class="ph-info text-2xl"></i>
  </a>

</nav>


<!-- Leaflet Map Scripts -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
const uploadModal = document.getElementById('uploadModal');
const closeBtn = document.getElementById('closeUploadModal');

// Vyber všetky tlačidlá, ktoré otvárajú modal
const openBtns = document.querySelectorAll(
  'a[onclick*="uploadModal"], button.ph-plus, button[onclick*="uploadModal"]'
);


// INITIALIZÁCIA MAPY PRI NAČÍTANÍ

let uploadMap, uploadMarker;
document.addEventListener('DOMContentLoaded', () => {
    const mapEl = document.getElementById('uploadMap');
    if (!mapEl) return;

    // inicializácia mapy len raz
    uploadMap = L.map(mapEl).setView([55.6761, 12.5683], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(uploadMap);

    // klik na mapu → pridanie markeru
    uploadMap.on('click', function(e) {
        const { lat, lng } = e.latlng;
        if (uploadMarker) uploadMap.removeLayer(uploadMarker);
        uploadMarker = L.marker([lat, lng]).addTo(uploadMap)
            .bindPopup('Selected location').openPopup();
        document.querySelector('input[name="latitude"]').value = lat;
        document.querySelector('input[name="longitude"]').value = lng;
    });
});

// -------------------------------
// OTVORENIE / ZATVORENIE MODALU
// -------------------------------
openBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        uploadModal.classList.remove('hidden');

        // reset krokov
        document.getElementById('stepSelect').classList.remove('hidden');
        document.getElementById('stepPreview').classList.add('hidden');
        document.getElementById('stepForm').classList.add('hidden');
        document.getElementById('photoInput').value = '';
        document.getElementById('previewImage').src = '';
        document.getElementById('finalImage').src = '';
        document.getElementById('photoData').value = '';

        // po otvorení modalu → refresh layout mapy
        setTimeout(() => {
            if (uploadMap) uploadMap.invalidateSize();
        }, 100);
    });
});

closeBtn.addEventListener('click', () => {
    uploadModal.classList.add('hidden');
});

// -------------------------------
// BASE64 FOTO + PRECHOD MEDZI KROKMI
// -------------------------------
const photoInput = document.getElementById('photoInput');
const previewImage = document.getElementById('previewImage');
const finalImage = document.getElementById('finalImage');
const photoDataInput = document.getElementById('photoData');

photoInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            previewImage.src = event.target.result;
            finalImage.src = event.target.result;
            photoDataInput.value = event.target.result;

            // prechod do kroku 2
            document.getElementById('stepSelect').classList.add('hidden');
            document.getElementById('stepPreview').classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    }
});

document.getElementById('nextBtn').addEventListener('click', () => {
    document.getElementById('stepPreview').classList.add('hidden');
    document.getElementById('stepForm').classList.remove('hidden');

    // po zobrazení formu → refresh mapy
    setTimeout(() => {
        if (uploadMap) uploadMap.invalidateSize();
    }, 100);
});

document.getElementById('backBtn').addEventListener('click', () => {
    document.getElementById('stepForm').classList.add('hidden');
    document.getElementById('stepPreview').classList.remove('hidden');
});


// -------------------------------
// CITY → MOVE MAP (bez markeru)
// -------------------------------
const cityInput = document.querySelector('input[name="city"]');

if (cityInput) {
  function debounce(fn, delay) {
    let timeout;
    return function (...args) {
      clearTimeout(timeout);
      timeout = setTimeout(() => fn.apply(this, args), delay);
    };
  }

  cityInput.addEventListener('input', debounce(async function () {
    const city = cityInput.value.trim();
    if (!city || !uploadMap) return;

    try {
      const response = await fetch(`geocode.php?q=${encodeURIComponent(city)}`);
      const data = await response.json();

      if (data && data.length > 0) {
        const { lat, lon } = data[0];
        uploadMap.setView([lat, lon], 13);
      } else {
        console.warn('City not found');
      }
    } catch (err) {
      console.error('Error fetching city:', err);
    }
  }, 500)); // 500ms debounce
}


// -------------------------------
// ADDRESS → MAP MARKER + súradnice
// -------------------------------
const addressInput = document.querySelector('input[name="address"]');

if (addressInput) {
  function debounce(fn, delay) {
    let timeout;
    return function (...args) {
      clearTimeout(timeout);
      timeout = setTimeout(() => fn.apply(this, args), delay);
    };
  }

  addressInput.addEventListener('input', debounce(async function () {
    const address = addressInput.value.trim();
    if (!address || !uploadMap) return;

    try {
      const response = await fetch(`geocode.php?q=${encodeURIComponent(address)}`);
      const data = await response.json();

      if (data && data.length > 0) {
        const { lat, lon } = data[0];
        uploadMap.setView([lat, lon], 14);

        if (uploadMarker) uploadMap.removeLayer(uploadMarker);
        uploadMarker = L.marker([lat, lon]).addTo(uploadMap)
          .bindPopup('Selected location').openPopup();

        document.querySelector('input[name="latitude"]').value = lat;
        document.querySelector('input[name="longitude"]').value = lon;
      } else {
        console.warn('Address not found. You can click on the map to set location.');
      }
    } catch (err) {
      console.error('Error fetching address:', err);
    }
  }, 500)); // debounce 500ms
}


// -------------------------------
// FORM VALIDATION FIX
// -------------------------------
const uploadForm = document.getElementById('uploadForm');
uploadForm.addEventListener('submit', (e) => {
  const lat = document.getElementById('latitude').value.trim();
  const lng = document.getElementById('longitude').value.trim();
  const address = document.querySelector('input[name="address"]').value.trim();

  // Ak nie sú súradnice a nebola zadaná adresa
  if ((!lat || !lng) && !address) {
    e.preventDefault();
    alert('Please select a location either by entering an address or clicking on the map.');
    return false;
  }
});




function hideFeedMap() {
  const m = document.getElementById('feedMap');
  const mapBtn = document.getElementById('showMap');
  if (m) {
    m.style.display = 'none'; // úplne zatvorí mapu
  }
}

function showFeedMap() {
  const m = document.getElementById('feedMap');
  const mapBtn = document.getElementById('showMap');
  if (m) {
    m.style.display = 'block'; // znovu otvorí mapu
  }
}

openBtns.forEach(btn => {
  btn.addEventListener('click', (e) => {
    e.preventDefault();
    uploadModal.classList.remove('hidden');
    hideFeedMap(); // zavrie mapu ako pri kliknutí na "Show Map"
    setTimeout(() => { if (uploadMap) uploadMap.invalidateSize(); }, 200);
  });
});

closeBtn.addEventListener('click', () => {
  uploadModal.classList.add('hidden');
  showFeedMap(); // znovu otvorí mapu
});





function debounce(fn, delay) {
  let timeout;
  return function (...args) {
    clearTimeout(timeout);
    timeout = setTimeout(() => fn.apply(this, args), delay);
  };
}

cityInput.addEventListener('input', debounce(async function () {
    const city = cityInput.value.trim();
    if (!city || !uploadMap) return;
    try {
        const res = await fetch(`geocode.php?q=${encodeURIComponent(city)}`);
        const data = await res.json();
        if (data.length) {
            const { lat, lon } = data[0];
            uploadMap.setView([lat, lon], 13);
        }
    } catch (err) {
        console.error(err);
    }
}, 500));


</script>



