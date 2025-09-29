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
<body class="bg-gray-100 font-sans text-gray-800 flex">
<!-- Sidebar -->
<aside class="w-64 bg-white h-screen shadow-lg flex flex-col">
    <div class="p-4 flex items-center gap-3 border-b border-gray-200">
        <div class="w-12 h-12 rounded-lg bg-gradient-to-r from-orange-400 to-yellow-300 flex items-center justify-center text-white font-bold text-lg">HS</div>
        <div>
            <div class="font-bold text-lg">HiddenSpots</div>
            <div class="text-gray-500 text-sm">discover your city's secret places</div>
        </div>
    </div>
    <nav class="flex-1 mt-4 flex flex-col gap-2">
        <a href="index.php" class="px-4 py-2 hover:bg-gray-100 rounded flex items-center gap-2">Feed</a>
        <a href="#" class="px-4 py-2 hover:bg-gray-100 rounded flex items-center gap-2">Trending</a>
        <a href="#" class="px-4 py-2 hover:bg-gray-100 rounded flex items-center gap-2">Newest</a>
        <a href="#" class="px-4 py-2 hover:bg-gray-100 rounded flex items-center gap-2">Notifications</a>
        <a href="settings.php" class="px-4 py-2 hover:bg-gray-100 rounded flex items-center gap-2">Settings</a>
    </nav>
    <div class="p-4 border-t border-gray-200">
        <button id="uploadBtn" class="w-full py-2 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition">+ Upload</button>
    </div>
</aside>
