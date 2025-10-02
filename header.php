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
<aside class="hidden md:flex flex-col md:w-64 bg-gray-50 border-r sticky top-0 h-screen p-4">
  
  <!-- HORNY BLOK: Logo + Menu links -->
  <div class="flex flex-col gap-8">
    <!-- Logo -->
    <a href="index.php" class="text-3xl font-extrabold text-black hover:text-blue-500">HS</a>

    <!-- Menu links -->
    <nav class="flex flex-col gap-4">
      <a href="feed.php" class="hover:text-blue-500">Feed</a>
      <a href="favourites.php" class="hover:text-blue-500">Favourites</a>
      <a href="trending.php" class="hover:text-blue-500">Trending</a>
      <a href="about.php" class="hover:text-blue-500">About HS</a>
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
  <div class="md:hidden sticky top-0 z-50 bg-gray-50 border-b p-4 flex justify-center">
    <a href="index.php" class="text-3xl font-extrabold text-black hover:text-blue-500">HS</a>
  </div>

  <!-- Mobile bottom menu -->
  <nav class="fixed bottom-0 left-0 right-0 bg-gray-50 flex justify-around p-3 md:hidden border-t z-50">
    <a href="feed.php" class="hover:text-blue-500">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2h-4a2 2 0 0 1-2-2v-5H9v5a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V9z"/>
      </svg>
    </a>
    <a href="favourites.php" class="hover:text-blue-500">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 0 1 6.364 0L12 7.636l1.318-1.318a4.5 4.5 0 0 1 6.364 6.364L12 21.364l-7.682-7.682a4.5 4.5 0 0 1 0-6.364z"/>
      </svg>
    </a>
    <a href="trending.php" class="hover:text-blue-500">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3c.132 0 .262.009.392.026a9 9 0 0 1 5.568 2.852 9 9 0 0 1 1.7 10.458c-.287.493-.668.933-1.124 1.307l-7.77 6.057a1 1 0 0 1-1.292 0l-7.77-6.057a7.002 7.002 0 0 1-1.124-1.307 9 9 0 0 1 1.7-10.458 9 9 0 0 1 5.568-2.852A1.993 1.993 0 0 1 12 3z"/>
      </svg>
    </a>
    <a href="about.php" class="hover:text-blue-500">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 3c4.418 0 8 3.582 8 8s-3.582 8-8 8-8-3.582-8-8 3.582-8 8-8z"/>
      </svg>
    </a>
    <button class="bg-black text-white p-2 rounded-lg">
      <span>+</span>
    </button>
  </nav>
