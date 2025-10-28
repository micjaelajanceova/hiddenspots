<?php
if (session_status() == PHP_SESSION_NONE) {
  session_start();
}

$user_id = $_SESSION['user_id'] ?? null;  // null if not logged in
$user_rank = $_SESSION['user_rank'] ?? 'user'; // assume normal user by default
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
<a href="/hiddenspots/index.php" class="logo text-black hover:text-blue-500">
  <span class="text-3xl font-extrabold text-black">HiddenSpots</span>
</a>




    <!-- Menu links s ikonami -->
    <nav class="flex flex-col pt-5 gap-6 text-black">
      <a href="/hiddenspots/feed.php" class="flex items-center gap-4 font-semibold hover:text-blue-500">
        <i class="ph-house text-lg"></i> Feed
      </a>
      <a href="/hiddenspots/favourites.php" class="flex items-center gap-4 font-semibold hover:text-blue-500">
        <i class="ph-heart text-lg"></i> Favourites
      </a>
      <a href="/hiddenspots/trending.php" class="flex items-center gap-4 font-semibold hover:text-blue-500">
        <i class="ph-trend-up text-lg"></i> Trending
      </a>
      <a href="/hiddenspots/about.php" class="flex items-center gap-4 font-semibold hover:text-blue-500">
        <i class="ph-info text-lg"></i> About HS
      </a>

      <?php if(isset($_SESSION['user_email']) && $_SESSION['user_email'] === 'janceova.mi@gmail.com'): ?>
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

<!-- Upload Modal -->
<div id="uploadModal" class="fixed inset-0 bg-black bg-opacity-70 flex justify-center items-center hidden z-50">
  <div id="uploadContainer" class="bg-white rounded-2xl shadow-lg w-full max-w-3xl h-[80vh] flex flex-col overflow-hidden relative animate-[fadeIn_0.3s_ease]">
   
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
          <input type="text" name="address" placeholder="Address" class="w-full border rounded p-2 focus:ring-2 focus:ring-blue-400 outline-none" />
          
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




</aside>



  <!-- Mobile top logo -->
  <div class="md:hidden sticky top-0 border-b p-3 flex justify-center bg-gray-100 z-50">
    <a href="index.php" class="text-3xl font-extrabold text-black hover:text-blue-500">HS</a>
  </div>
<!-- Phosphor Icons CDN -->
<script src="https://unpkg.com/phosphor-icons"></script>

<!-- Mobile bottom menu -->
<nav class="fixed bottom-0 left-0 right-0 bg-white flex justify-around items-center p-2 md:hidden border-t shadow-md z-50">

  <!-- Home -->
  <a href="feed.php" class="text-gray-600 hover:text-blue-500">
    <i class="ph-house text-2xl"></i>
  </a>

  <!-- Favourites -->
  <a href="favourites.php" class="text-gray-600 hover:text-blue-500">
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
  <a href="trending.php" class="text-gray-600 hover:text-blue-500">
    <i class="ph-trend-up text-2xl"></i>
  </a>

  <!-- About -->
  <a href="about.php" class="text-gray-600 hover:text-blue-500">
    <i class="ph-info text-2xl"></i>
  </a>

</nav>


<script>
  const uploadModal = document.getElementById('uploadModal');
  const closeBtn = document.getElementById('closeUploadModal');

  // Vyber všetky tlačidlá, ktoré otvárajú popup
  const openBtns = document.querySelectorAll(
    'a[onclick*="uploadModal"], button.ph-plus, button[onclick*="uploadModal"]'
  );

  openBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      uploadModal.classList.remove('hidden');

      // reset krokov pri otvorení
      document.getElementById('stepSelect').classList.remove('hidden');
      document.getElementById('stepPreview').classList.add('hidden');
      document.getElementById('stepForm').classList.add('hidden');
      document.getElementById('photoInput').value = '';
      document.getElementById('previewImage').src = '';
      document.getElementById('finalImage').src = '';
      document.getElementById('photoData').value = '';
    });
  });

  closeBtn.addEventListener('click', () => {
    uploadModal.classList.add('hidden');
  });

  // Base64 + prechod medzi krokmi
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
  });

  document.getElementById('backBtn').addEventListener('click', () => {
    document.getElementById('stepForm').classList.add('hidden');
    document.getElementById('stepPreview').classList.remove('hidden');
  });
</script>

