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
</head>
<div class="flex h-screen bg-gray-50">
  <!-- Sidebar -->
  <aside class="w-64 bg-white flex flex-col justify-between border-r p-4 sticky top-0">
    <div>
      <h1 class="text-2xl font-bold mb-6">HiddenSpots</h1>
      <nav class="flex flex-col gap-4">
        <a href="#" class="hover:text-blue-500">Feed</a>
        <a href="#" class="hover:text-blue-500">Favourites</a>
        <a href="#" class="hover:text-blue-500">Trending</a>
        <a href="#" class="hover:text-blue-500">About HS</a>
      </nav>
    </div>
    <button class="mt-6 w-full py-2 bg-black text-white rounded-lg flex items-center justify-center gap-2">
      <span>+</span> Upload
    </button>
  </aside>
