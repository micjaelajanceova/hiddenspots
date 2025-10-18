<?php
include 'db.php';
include 'header.php';
include 'spot.php';
include 'user.php';

// Only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['user_email'] !== 'janceova.mi@gmail.com') {
    header("Location: index.php");
    exit();
}

// Fetch data
$spots = $pdo->query("SELECT * FROM hidden_spots ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$comments = $pdo->query("
    SELECT c.id, c.text, c.created_at, u.name AS user_name, hs.name AS spot_name 
    FROM comments c 
    JOIN users u ON c.user_id = u.id 
    JOIN hidden_spots hs ON c.spot_id = hs.id 
    ORDER BY c.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
$users = $pdo->query("SELECT id, name, email, `rank`, blocked FROM users ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="flex-1 bg-white min-h-screen overflow-y-auto">
  <div class="w-full px-4 sm:px-6 lg:px-8 max-w-6xl mx-auto py-8">

    <h1 class="text-4xl font-bold mb-8 text-center">Admin Panel</h1>

    <!-- Tabs -->
    <div class="flex justify-center mb-6 gap-4 flex-wrap">
      <button onclick="showTab('spots')" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Spots</button>
      <button onclick="showTab('comments')" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">Comments</button>
      <button onclick="showTab('users')" class="px-4 py-2 bg-purple-500 text-white rounded hover:bg-purple-600">Users</button>
    </div>

    <!-- SPOTS -->
    <div id="spots" class="tab-content">
      <h2 class="text-2xl font-bold mb-4">Hidden Spots</h2>
      <div class="overflow-x-auto bg-gray-50 rounded-lg shadow p-4">
        <table class="min-w-full table-auto border-collapse">
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
              <tr class="border-b hover:bg-gray-100">
                <td class="p-3"><?= $s['id'] ?></td>
                <td class="p-3"><?= htmlspecialchars($s['name']) ?></td>
                <td class="p-3"><?= htmlspecialchars($s['city']) ?></td>
                <td class="p-3"><?= htmlspecialchars($s['address']) ?></td>
                <td class="p-3">
                  <?php if (!empty($s['file_path'])): ?>
                    <img src="<?= htmlspecialchars($s['file_path']) ?>" class="w-16 h-16 object-cover rounded">
                  <?php endif; ?>
                </td>
                <td class="p-3"><?= $s['created_at'] ?></td>
                <td class="p-3">
                  <form action="delete_spot.php" method="POST" onsubmit="return confirm('Delete this spot?');">
                    <input type="hidden" name="id" value="<?= $s['id'] ?>">
                    <button type="submit" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- COMMENTS -->
    <div id="comments" class="tab-content hidden mt-6">
      <h2 class="text-2xl font-bold mb-4">Comments</h2>
      <div class="overflow-x-auto bg-gray-50 rounded-lg shadow p-4">
        <table class="min-w-full table-auto border-collapse">
          <thead>
            <tr class="bg-gray-200 text-left">
              <th class="p-3 border-b">ID</th>
              <th class="p-3 border-b">User</th>
              <th class="p-3 border-b">Spot</th>
              <th class="p-3 border-b">Text</th>
              <th class="p-3 border-b">Created</th>
              <th class="p-3 border-b">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($comments as $c): ?>
              <tr class="border-b hover:bg-gray-100">
                <td class="p-3"><?= $c['id'] ?></td>
                <td class="p-3"><?= htmlspecialchars($c['user_name']) ?></td>
                <td class="p-3"><?= htmlspecialchars($c['spot_name']) ?></td>
                <td class="p-3"><?= htmlspecialchars($c['text']) ?></td>
                <td class="p-3"><?= $c['created_at'] ?></td>
                <td class="p-3">
                  <form action="delete_comment.php" method="POST" onsubmit="return confirm('Delete this comment?');">
                    <input type="hidden" name="id" value="<?= $c['id'] ?>">
                    <button type="submit" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- USERS -->
    <div id="users" class="tab-content hidden mt-6">
      <h2 class="text-2xl font-bold mb-4">Users</h2>
      <div class="overflow-x-auto bg-gray-50 rounded-lg shadow p-4">
        <table class="min-w-full table-auto border-collapse">
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
              <tr class="border-b hover:bg-gray-100">
                <td class="p-3"><?= $u['id'] ?></td>
                <td class="p-3"><?= htmlspecialchars($u['name']) ?></td>
                <td class="p-3"><?= htmlspecialchars($u['email']) ?></td>
                <td class="p-3"><?= htmlspecialchars($u['rank']) ?></td>
                <td class="p-3"><?= $u['blocked'] ? 'Yes' : 'No' ?></td>
                <td class="p-3">
                  <form action="toggle_block.php" method="POST">
                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                    <button type="submit" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">
                      <?= $u['blocked'] ? 'Unblock' : 'Block' ?>
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</main>

<script>
function showTab(tabId){
  document.querySelectorAll('.tab-content').forEach(tab => tab.classList.add('hidden'));
  document.getElementById(tabId).classList.remove('hidden');
}
</script>

<?php include 'footer.php'; ?>
