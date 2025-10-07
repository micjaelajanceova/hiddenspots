<?php
session_start();
$user_id = $_SESSION['user_id'] ?? 1;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HiddenSpots — discover your city's secret places</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="flex flex-col md:flex-row min-h-screen">

  <!-- Desktop sidebar -->
<!-- Desktop sidebar -->
<aside class="hidden md:flex flex-col md:w-64 bg-gray-100 border-r sticky top-0 h-screen p-4 shadow-lg shadow-gray-300 z-10">

  <!-- HORNY BLOK: Logo + Menu links -->
  <div class="flex flex-col gap-8">
    <!-- Logo -->
<a href="index.php" class="logo text-black hover:text-blue-500">
  <span class="text-5xl font-extrabold">H</span><span class="text-5xl font-semibold">IDDEN </span><span class="text-5xl font-extrabold">S</span><span class="text-5xl font-semibold">POTS</span>
</a>




    <!-- Menu links s ikonami -->
    <nav class="flex flex-col pt-5 gap-6 text-black">
      <a href="feed.php" class="flex items-center gap-4 font-semibold hover:text-blue-500">
        <i class="ph-house text-lg"></i> FEED
      </a>
      <a href="favourites.php" class="flex items-center gap-4 font-semibold hover:text-blue-500">
        <i class="ph-heart text-lg"></i> FAVOURITES
      </a>
      <a href="trending.php" class="flex items-center gap-4 font-semibold hover:text-blue-500">
        <i class="ph-trend-up text-lg"></i> TRENDING
      </a>
      <a href="about.php" class="flex items-center gap-4 font-semibold hover:text-blue-500">
        <i class="ph-info text-lg"></i> ABOUT HS
      </a>
    </nav>
  </div>

  <!-- SPODNY BLOK: Upload button -->
  <div class="mt-auto">
    <button class="w-full py-2 bg-black text-white rounded-lg flex items-center justify-center gap-2">
      <span>+</span> Upload
    </button>
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

  <!-- Upload button (center, rovno) -->
  <button class="bg-black text-white p-3 rounded-full shadow-md">
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



