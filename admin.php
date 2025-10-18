<?php
include 'db.php';
include 'header.php';
include 'spot.php';
include 'user.php';


if (!isset($_SESSION['user_rank']) || $_SESSION['user_rank'] !== 'admin') {
    header("Location: index.php");
    exit();
}


$stmt = $pdo->query("SELECT id, name, city, address, file_path, created_at FROM hidden_spots ORDER BY created_at DESC");
$spots = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel — HiddenSpots</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-8">
    <h1 class="text-4xl font-bold mb-6 text-center">Admin Panel</h1>

    <div class="overflow-x-auto bg-white rounded-lg shadow-md p-6">
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-gray-200 text-left">
                    <th class="p-3 border-b">ID</th>
                    <th class="p-3 border-b">Name</th>
                    <th class="p-3 border-b">City</th>
                    <th class="p-3 border-b">Address</th>
                    <th class="p-3 border-b">Photo</th>
                    <th class="p-3 border-b">Created</th>
                    <th class="p-3 border-b">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($spots as $s): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-3"><?= $s['id'] ?></td>
                        <td class="p-3"><?= htmlspecialchars($s['name']) ?></td>
                        <td class="p-3"><?= htmlspecialchars($s['city']) ?></td>
                        <td class="p-3"><?= htmlspecialchars($s['address']) ?></td>
                        <td class="p-3">
                            <?php if (!empty($s['file_path'])): ?>
                                <img src="<?= htmlspecialchars($s['file_path']) ?>" alt="Spot" class="w-16 h-16 object-cover rounded">
                            <?php endif; ?>
                        </td>
                        <td class="p-3"><?= $s['created_at'] ?></td>
                        <td class="p-3">
                            <form action="delete_spot.php" method="POST" onsubmit="return confirm('Delete this spot?');">
                                <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                <button type="submit" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</body>
</html>
