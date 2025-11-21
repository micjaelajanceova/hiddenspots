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
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/style.css?v=<?php echo time(); ?>">

</head>

<?php
$show_navbar = $show_navbar ?? true; 
?>

<?php if ($show_navbar): ?>
<body class="flex flex-col min-h-screen">

  <div class="flex flex-1 flex-col md:flex-row">
<aside class="hidden md:flex flex-col md:w-64 bg-gray-100 border-r sticky top-0 h-screen p-4 shadow-lg shadow-gray-300 z-10">



  <div class="flex flex-col gap-8">
<a href="/index.php" class="logo text-black hover:text-blue-500">
  <span class="text-2xl font-extrabold text-black">HiddenSpots</span>
</a>






    <!-- Menu links -->
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
    <a href="/admin.php" class="flex items-center gap-4 font-semibold hover:text-red-500">
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





