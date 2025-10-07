<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="flex justify-between items-center p-6 bg-blue-600 text-white">
        <h1 class="text-2xl font-semibold">Admin Dashboard</h1>
        <a href="logout.php" class="text-white hover:underline">Logout</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-8">
        <a href="manage_spots.php" class="p-6 bg-white shadow rounded-xl hover:bg-gray-100">
            Manage Spots
        </a>
        <a href="#" class="p-6 bg-white shadow rounded-xl hover:bg-gray-100">
            Manage Users
        </a>
        <a href="#" class="p-6 bg-white shadow rounded-xl hover:bg-gray-100">
            Site Settings
        </a>
    </div>
</body>
</html>
