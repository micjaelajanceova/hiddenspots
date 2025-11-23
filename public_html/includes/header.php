<?php
require_once __DIR__ . '/db.php'; 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
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



<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HiddenSpots — discover your city's secret places</title>
    <link rel="icon" type="image/png" href="/assets/img/logo.svg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/hiddenspots/public_html/assets/css/style.css?v=<?php echo time(); ?>">
</head>

<?php
$show_navbar = $show_navbar ?? true; 
?>

<?php if ($show_navbar): ?>
<body class="flex flex-col min-h-screen">

  <div class="flex flex-1 flex-col md:flex-row">
<aside id="sidebar" class="hidden md:flex flex-col bg-gray-100 border-r sticky top-0 h-screen p-4 shadow-lg shadow-gray-300 z-10 transition-all duration-300 w-64">




  <div class="flex flex-col">
    <!-- Toggle Button -->

<button id="sidebarToggle" class="flex items-center justify-center w-3 h-8 rounded-full hover:bg-gray-200 transition self-end">
  <i class="ph-caret-left text-xl"></i>
</button>
    
<a href="/index.php" class="logo text-black hover:text-blue-500 mb-10">
  <span class="sidebar-logo-full text-2xl ">HiddenSpots</span>
  <img src="/assets/img/logo.svg" alt="HS" class="sidebar-logo-collapsed text-2xl hidden">
</a>






    <!-- Menu links -->
    <nav class="flex flex-col pt-5 gap-8 text-black">
      <a href="/feed.php" class="flex items-center gap-4 font-semibold hover:text-blue-500">
      <i class="ph-house text-lg"></i>
      <span class="sidebar-text">Feed</span>
      </a>
      <a href="/favourites.php" class="flex items-center gap-4 font-semibold hover:text-blue-500">
        <i class="ph-heart text-lg"></i>
        <span class="sidebar-text">Favourites</span>
      </a>
      <a href="/trending.php" class="flex items-center gap-4 font-semibold hover:text-blue-500">
        <i class="ph-trend-up text-lg"></i>
        <span class="sidebar-text">Trending</span>
      </a>
      <a href="/about.php" class="flex items-center gap-4 font-semibold hover:text-blue-500">
        <i class="ph-info text-lg"></i>
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
     class="w-full sm:w-auto py-2 px-4 bg-black text-white rounded-lg flex items-center justify-center gap-2 hover:bg-gray-800 transition"
     onclick="event.preventDefault(); document.getElementById('uploadModal').classList.remove('hidden');">
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
    <div id="stepForm" class="hidden flex flex-1 h-full overflow-hidden">
      <!-- Image preview -->
      <div class="w-1/2 h-full bg-black flex justify-center items-center overflow-hidden">
        <img id="finalImage" class="w-full h-full object-cover" />
      </div>

      <!-- Form -->
      <div class="w-1/2 p-6 overflow-y-auto">
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
          <div id="uploadMap" class="w-full h-56 rounded-lg shadow-md border border-gray-200 my-2"></div>

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
    <a href="/index.php" class="text-3xl font-extrabold text-black hover:text-blue-500">HS</a>
  </div>

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

 <div class="flex-1 flex flex-col min-h-screen">
<?php endif; ?>

<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
const uploadModal = document.getElementById('uploadModal');
const closeBtn = document.getElementById('closeUploadModal');
const openBtns = document.querySelectorAll('a[onclick*="uploadModal"], button.ph-plus');
const uploadForm = document.getElementById('uploadForm');
const photoInput = document.getElementById('photoInput');
const previewImage = document.getElementById('previewImage');
const finalImage = document.getElementById('finalImage');
const photoDataInput = document.getElementById('photoData');


const sidebar = document.getElementById('sidebar');
const toggleBtn = document.getElementById('sidebarToggle');

// Load state on page load
const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
if (isCollapsed) {
  collapseSidebar();
} else {
  expandSidebar();
}


toggleBtn.addEventListener('click', () => {
  const isCollapsed = sidebar.classList.toggle('sidebar-collapsed');

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


</script>

