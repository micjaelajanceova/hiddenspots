<?php
require_once __DIR__ . '/db.php'; 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$stmt = $pdo->query("SELECT primary_color, font_family  FROM site_settings WHERE id = 1 LIMIT 1");
$settings = $stmt->fetch(PDO::FETCH_ASSOC);
$primary_color = $settings['primary_color'] ?? '';
$siteFont = $settings['font_family'] ?? 'Arial'; 


if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}




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

<?php $isLoggedIn = isset($_SESSION['user_id']); ?>


<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HiddenSpots — discover your city's secret places</title>
    <link rel="icon" type="image/png" href="/assets/img/logo.svg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/css/style.css?v=<?php echo time(); ?>">
    
    <style>
        :root {
            --primary-color: <?= htmlspecialchars($primary_color) ?>;
        }
        body, input, textarea, select, button {
    font-family: <?= htmlspecialchars($siteFont) ?>, sans-serif !important;
}

    </style>

</head>

<?php
$show_navbar = $show_navbar ?? true; 
?>

<?php if ($show_navbar): ?>
<body class="flex flex-col min-h-screen">

  <div class="flex flex-1 flex-col md:flex-row">
<aside id="sidebar" class="hidden md:flex flex-col bg-gray-100 border-r sticky top-0 h-screen p-4 shadow-lg shadow-gray-300 z-10 transition-all duration-300 w-64">




  <div class="flex flex-col">

    <div id="sidebarHeader" class="flex items-center justify-between mb-10 transition-all">
  
  <!-- Logo (vľavo) -->
  <a href="/index.php" class="logo text-black hover:text-blue-500">
    <span class="sidebar-logo-full text-2xl font-bold">HiddenSpots</span>
    <img src="/assets/img/logo.svg" alt="HS" class="sidebar-logo-collapsed hidden h-16">
  </a>

  <!-- Toggle Button (vpravo) -->
    <button id="sidebarToggle" 
          class="flex items-center justify-center w-10 h-10 rounded-full hover:bg-gray-200 transition">
    <i class="ph-caret-left text-xl"></i> 
  </button>


</div>






    <!-- Menu links -->
    <nav class="flex flex-col pt-5 gap-8 text-black">
      <a href="/feed.php" class="flex items-center gap-4 font-semibold hover:text-blue-500">
      <i class="ph-rows text-lg"></i>
      <span class="sidebar-text">Feed</span>
      </a>
      <a href="/favourites.php" class="flex items-center gap-4 font-semibold hover:text-blue-500">
        <i class="ph-bookmark-simple text-lg"></i>
        <span class="sidebar-text">Favourites</span>
      </a>
      <a href="/trending.php" class="flex items-center gap-4 font-semibold hover:text-blue-500">
        <i class="ph-trend-up text-lg"></i>
        <span class="sidebar-text">Trending</span>
      </a>
      <a href="/about.php" class="flex items-center gap-4 font-semibold hover:text-blue-500">
        <i class="ph-question text-lg"></i>
        <span class="sidebar-text">About HS</span>
      </a>

      <?php if (isset($_SESSION['user_id']) && $user_role === 'admin'): ?>
    <a href="/admin.php" class="flex items-center gap-4 font-semibold hover:text-red-500">
      <i class="ph-shield-star text-lg"></i>
      <span class="sidebar-text">Admin Panel</span>
    </a>
    <?php endif; ?>

    </nav>
  </div>



  <!-- Upload Button -->
  <div class="mt-auto">

    <a href="#" id="desktopUploadBtn"
      class="w-full sm:w-auto py-2 px-4 bg-black text-white rounded-lg flex items-center justify-center gap-2 hover:bg-gray-800 transition">
        <span class="sidebar-upload-text">+ Upload</span>
        <span class="sidebar-upload-collapsed hidden">+</span>
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
        Select photo
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
    <div id="stepForm" class="hidden flex-1 h-full overflow-hidden flex flex-col md:flex-row">
      <!-- Image preview -->
      <div class="w-full md:w-1/2 h-[95vh]  md:h-full bg-black flex justify-center items-center overflow-hidden mb-4 md:mb-0">
        <img id="finalImage" class="w-full h-full object-cover" />
      </div>

      <!-- Form -->
      <div class="w-full md:w-1/2 p-6 overflow-y-auto">
        <h2 class="text-lg font-semibold mb-4">New Hidden Spot</h2>
        <form id="uploadForm" action="includes/upload.php" method="post" enctype="multipart/form-data" class="space-y-4">
          <input type="hidden" name="photoData" id="photoData">
          <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
          <input type="text" name="name" placeholder="Name" required class="w-full border rounded p-2 focus:ring-2 focus:ring-blue-400 outline-none" />
          <input type="text" name="city" placeholder="City" required class="w-full border rounded p-2 focus:ring-2 focus:ring-blue-400 outline-none" />
          <input type="text" name="address" placeholder="Address (optional)" class="w-full border rounded p-2 focus:ring-2 focus:ring-blue-400 outline-none" />

          
          <input type="hidden" name="latitude" id="latitude">
          <input type="hidden" name="longitude" id="longitude">

          <!-- MAP -->
          <div id="uploadMap" class="w-full h-56 md:h-64 rounded-lg shadow-md border border-gray-200 my-2"></div>

          <select name="category" required class="w-full border rounded p-2 bg-white focus:ring-2 focus:ring-blue-400 outline-none">
            <option value="">Select a category</option>
            <option>Nature</option>
            <option>Café & Restaurant</option>
            <option>Art & Culture</option>
            <option>Viewpoint</option>
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
    <a href="/index.php" class="flex items-center justify-center">
  <img src="/assets/img/logo.svg" alt="HS" class="h-10">
  </a>
  </div>

<script src="https://unpkg.com/phosphor-icons"></script>

<!-- Mobile bottom menu -->
<nav class="fixed bottom-0 left-0 right-0 bg-white flex justify-around items-center p-2 md:hidden border-t shadow-md z-50">

  <!-- Home -->
  <a href="/feed.php" class="text-gray-600 hover:text-blue-500 flex items-center justify-center">
    <i class="ph-rows text-2xl"></i>
  </a>

  <!-- Favourites -->
  <a href="/favourites.php" class="text-gray-600 hover:text-blue-500 flex items-center justify-center">
    <i class="ph-bookmark-simple text-2xl"></i>
  </a>

<!-- Upload button (center, mobil) -->
<button id="mobileUploadBtn"
        class="md:hidden bg-black text-white p-3 rounded-full shadow-md flex items-center justify-center">
  <i class="ph-plus text-2xl"></i>
</button>


  <!-- Trending -->
  <a href="/trending.php" class="text-gray-600 hover:text-blue-500 flex items-center justify-center">
    <i class="ph-trend-up text-2xl"></i>
  </a>

  <!-- About -->
  <a href="/about.php" class="text-gray-600 hover:text-blue-500 flex items-center justify-center">
    <i class="ph-question text-2xl"></i>
  </a>
</nav>

 <div class="flex-1 flex flex-col min-h-screen pb-16 md:pb-0">
<?php endif; ?>

<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
const uploadModal = document.getElementById('uploadModal');
const closeBtn = document.getElementById('closeUploadModal');
const mobileUploadBtn = document.getElementById('mobileUploadBtn');
const uploadForm = document.getElementById('uploadForm');
const photoInput = document.getElementById('photoInput');
const previewImage = document.getElementById('previewImage');
const finalImage = document.getElementById('finalImage');
const photoDataInput = document.getElementById('photoData');



const sidebar = document.getElementById('sidebar');
const toggleBtn = document.getElementById('sidebarToggle');



toggleBtn.addEventListener('click', () => {
  const isCollapsed = sidebar.classList.toggle('sidebar-collapsed');

    const header = document.getElementById('sidebarHeader');

    if (isCollapsed) {
      // Collapse → toggle hore, logo dole
      header.classList.add('flex-col', 'items-center', 'gap-3');
      header.classList.remove('flex-row', 'justify-between');
    } else {
      // Expand → logo vľavo, toggle vpravo
      header.classList.remove('flex-col', 'items-center', 'gap-3');
      header.classList.add('flex-row', 'justify-between');
    }

    if (isCollapsed) {
      header.classList.add('flex-col', 'items-center', 'gap-3');
      header.classList.remove('flex-row', 'justify-between');
    } else {
      header.classList.remove('flex-col', 'items-center', 'gap-3');
      header.classList.add('flex-row', 'justify-between');
    }

  const icon = toggleBtn.querySelector('i');
  if (isCollapsed) {
    icon.classList.remove('ph-caret-left');
    icon.classList.add('ph-caret-right');
  } else {
    icon.classList.remove('ph-caret-right');
    icon.classList.add('ph-caret-left');
  }

  if (isCollapsed) {
    sidebar.classList.remove('w-64', 'p-4');
    sidebar.classList.add('w-16', 'p-2');
    document.querySelectorAll('.sidebar-text').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('#sidebar nav a').forEach(link => {
      link.classList.remove('justify-start', 'gap-4');
      link.classList.add('justify-center', 'gap-0');
    });
  } else {
    sidebar.classList.remove('w-16', 'p-2');
    sidebar.classList.add('w-64', 'p-4');
    document.querySelectorAll('.sidebar-text').forEach(el => el.classList.remove('hidden'));
    document.querySelectorAll('#sidebar nav a').forEach(link => {
      link.classList.remove('justify-center', 'gap-0');
      link.classList.add('justify-start', 'gap-4');
    });
  }

  document.querySelector('.sidebar-logo-full').classList.toggle('hidden');
  document.querySelector('.sidebar-logo-collapsed').classList.toggle('hidden');
  document.querySelector('.sidebar-upload-text').classList.toggle('hidden');
  document.querySelector('.sidebar-upload-collapsed').classList.toggle('hidden');

 sidebar.addEventListener('transitionend', (e) => {
  if (e.propertyName === 'width' || e.propertyName === 'padding-left') {
    if (typeof initMasonry === 'function') {
      initMasonry();
    } else if (window.masonry) {
      window.masonry.recalculate(true);
    }
  }
});

});

document.addEventListener('DOMContentLoaded', () => {

const isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;
const uploadModal = document.getElementById('uploadModal');


    // Main index button
    const mainBtn = document.getElementById('openUploadModal');
    mainBtn?.addEventListener('click', e => {
        e.preventDefault();
        if (!isLoggedIn) return window.location.href = '/auth/login.php';
        uploadModal.classList.remove('hidden');
    });

// Desktop upload button
document.getElementById('desktopUploadBtn').addEventListener('click', e => {
    e.preventDefault();
    if (!isLoggedIn) {
        window.location.href = '/auth/login.php';
        return;
    }
    document.getElementById('uploadModal').classList.remove('hidden');
});

// Mobile upload button
document.getElementById('mobileUploadBtn')?.addEventListener('click', e => {
    e.preventDefault();
    if (!isLoggedIn) {
        window.location.href = '/auth/login.php';
        return;
    }
    document.getElementById('uploadModal').classList.remove('hidden');
});
});


</script>
</body>
</html>

