<?php
include 'db.php';
include 'header.php';
include 'spot.php';
include 'user.php';

// Admin access check
if (!isset($_SESSION['user_id']) || $_SESSION['user_email'] !== 'janceova.mi@gmail.com') {
    header("Location: index.php");
    exit();
}

// Fetch hidden spots
$spots = $pdo->query("SELECT id, name, city, address, file_path, created_at FROM hidden_spots ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// Fetch users
$users = $pdo->query("SELECT id, name, email, rank, blocked FROM users ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

// Fetch comments
$comments = $pdo->query("SELECT c.id, c.content, c.user_id, u.name AS username, c.spot_id FROM comments c JOIN users u ON c.user_id = u.id ORDER BY c.id DESC")->fetchAll(PDO::FETCH_ASSOC);
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

<div class="grid gap-10">

    <!-- Hidden Spots -->
    <section class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold mb-4">Hidden Spots</h2>
        <div class="overflow-x-auto">
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
                        <td class="p-3 flex gap-2">
                            <form action="delete_spot.php" method="POST" onsubmit="return confirm('Delete this spot?');">
                                <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                <button class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Users -->
    <section class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold mb-4">Users</h2>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-200 text-left">
                        <th class="p-3 border-b">ID</th>
                        <th class="p-3 border-b">Name</th>
                        <th class="p-3 border-b">Email</th>
                        <th class="p-3 border-b">Rank</th>
                        <th class="p-3 border-b">Blocked</th>
                        <th class="p-3 border-b">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-3"><?= $u['id'] ?></td>
                        <td class="p-3"><?= htmlspecialchars($u['name']) ?></td>
                        <td class="p-3"><?= htmlspecialchars($u['email']) ?></td>
                        <td class="p-3"><?= $u['rank'] ?></td>
                        <td class="p-3"><?= $u['blocked'] ? 'Yes' : 'No' ?></td>
                        <td class="p-3 flex gap-2">
                            <form action="toggle_block_user.php" method="POST">
                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                <button class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">
                                    <?= $u['blocked'] ? 'Unblock' : 'Block' ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Comments -->
    <section class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold mb-4">Comments</h2>
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-200 text-left">
                        <th class="p-3 border-b">ID</th>
                        <th class="p-3 border-b">User</th>
                        <th class="p-3 border-b">Spot ID</th>
                        <th class="p-3 border-b">Content</th>
                        <th class="p-3 border-b">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($comments as $c): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-3"><?= $c['id'] ?></td>
                        <td class="p-3"><?= htmlspecialchars($c['username']) ?></td>
                        <td class="p-3"><?= $c['spot_id'] ?></td>
                        <td class="p-3"><?= htmlspecialchars($c['content']) ?></td>
                        <td class="p-3">
                            <form action="delete_comment.php" method="POST" onsubmit="return confirm('Delete this comment?');">
                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                <button class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

</div>

</body>
</html>
