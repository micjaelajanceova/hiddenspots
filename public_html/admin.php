<?php
include 'includes/db.php';
include 'includes/header.php';
include 'classes/spot.php';
include 'includes/profile-header.php';

// Only admin can access
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: index.php");
  exit();
}

// ===== DELETE SPOT =====
if (isset($_POST['delete_spot'])) {
  $id = intval($_POST['id']);
  $stmt = $pdo->prepare("DELETE FROM hidden_spots WHERE id = ?");
  $stmt->execute([$id]);
  echo "<script>alert('Spot deleted successfully'); window.location='admin.php';</script>";
  exit();
}

// ===== DELETE COMMENT =====
if (isset($_POST['delete_comment'])) {
  $id = intval($_POST['id']);
  $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
  $stmt->execute([$id]);
  echo "<script>alert('Comment deleted successfully'); window.location='admin.php';</script>";
  exit();
}

// ===== EDIT COMMENT =====
if (isset($_POST['edit_comment'])) {
  $id = intval($_POST['id']);
  $text = trim($_POST['text']);
  $stmt = $pdo->prepare("UPDATE comments SET text = ? WHERE id = ?");
  $stmt->execute([$text, $id]);
  echo "<script>alert('Comment updated successfully'); window.location='admin.php';</script>";
  exit();
}

// ===== TOGGLE BLOCK USER =====
if (isset($_POST['toggle_block'])) {
  $id = intval($_POST['id']);

  $stmt = $pdo->prepare("SELECT blocked FROM users WHERE id = ?");
  $stmt->execute([$id]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($user) {
      $newStatus = $user['blocked'] ? 0 : 1;
      $stmt = $pdo->prepare("UPDATE users SET blocked = ? WHERE id = ?");
      $stmt->execute([$newStatus, $id]);
      echo "<script>alert('User ".($newStatus ? 'blocked' : 'unblocked')." successfully'); window.location='admin.php';</script>";
      exit();
  }
}

// ===== UPDATE SITE INFO & STYLING =====
if (isset($_POST['update_site'])) {
  $description = trim($_POST['description']);
  $rules = trim($_POST['rules']);
  $contact = trim($_POST['contact']);
  $theme_color = trim($_POST['theme_color']);
  $font_size = trim($_POST['font_size']); // len ak chceš zachovať font_size

  // Update site info
  $stmt = $pdo->prepare("
      UPDATE site_settings 
      SET site_description=?, rules=?, contact_info=?, primary_color=?
      WHERE id=1
  ");
  $stmt->execute([$description, $rules, $contact, $theme_color]);

  echo "<script>alert('Site info updated successfully'); window.location='admin.php';</script>";
  exit();
}

// ===== FETCH SITE INFO =====
$siteInfo = $pdo->query("SELECT * FROM site_settings WHERE id=1")->fetch(PDO::FETCH_ASSOC);



// Fetch data
$spots = $pdo->query("SELECT * FROM hidden_spots ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$comments = $pdo->query("
    SELECT c.id, c.text, c.created_at, u.name AS user_name, hs.name AS spot_name 
    FROM comments c 
    JOIN users u ON c.user_id = u.id 
    JOIN hidden_spots hs ON c.spot_id = hs.id 
    ORDER BY c.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
$users = $pdo->query("SELECT id, name, email, role, blocked FROM users ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

// ===== FETCH SITE INFO =====
$siteInfo = $pdo->query("SELECT * FROM site_settings LIMIT 1")->fetch(PDO::FETCH_ASSOC);

// ===== FETCH STYLING SETTINGS =====
$stylingSettings = $pdo->query("SELECT * FROM styling_settings LIMIT 1")->fetch(PDO::FETCH_ASSOC);


?>

<main class="flex-1 min-h-screen overflow-y-auto">
<div class="w-full px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto py-8">

  <!-- Admin header -->
  <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
    <!-- Title -->
    <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight">Admin Panel</h1>

    <!-- Tabs -->
    <div class="flex gap-3 flex-wrap mt-4 md:mt-0">
      <button id="tab-site" onclick="showTab('site')" class="tab-btn px-5 py-2 rounded-full font-medium shadow">Site Info</button>
      <button id="tab-spots" onclick="showTab('spots')" class="tab-btn px-5 py-2 rounded-full font-medium shadow">Spots</button>
      <button id="tab-comments" onclick="showTab('comments')" class="tab-btn px-5 py-2 rounded-full font-medium shadow">Comments</button>
      <button id="tab-users" onclick="showTab('users')" class="tab-btn px-5 py-2 rounded-full font-medium shadow">Users</button>
    </div>
  </div>

  <div class="border-t border-gray-300 mb-6"></div>

<!-- SITE INFO TAB -->
<div id="site" class="tab-content hidden mt-6">
  <div class="overflow-x-auto bg-gray-50 rounded-lg shadow p-4">
    <form method="POST">
      
      <div class="mb-4">
        <label class="block font-semibold mb-1">Description</label>
        <textarea name="description" class="w-full border p-2 rounded" rows="3"><?= htmlspecialchars($siteInfo['site_description']) ?></textarea>
      </div>
      
      <div class="mb-4">
        <label class="block font-semibold mb-1">Rules</label>
        <textarea name="rules" class="w-full border p-2 rounded" rows="3"><?= htmlspecialchars($siteInfo['rules']) ?></textarea>
      </div>

      <div class="mb-4">
        <label class="block font-semibold mb-1">Contact</label>
        <input type="text" name="contact" class="w-full border p-2 rounded" value="<?= htmlspecialchars($siteInfo['contact_info']) ?>">
      </div>

      <div class="mb-4">
        <label class="block font-semibold mb-1">Theme color</label>
        <input type="color" name="theme_color" class="w-full h-10 p-1 rounded border" value="<?= htmlspecialchars($siteInfo['primary_color']) ?>">
      </div>

      <button type="submit" name="update_site" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Save</button>
    </form>
  </div>
</div>



  <!-- SPOTS -->
<div id="spots" class="tab-content">
  <div class="overflow-x-auto bg-gray-50 rounded-lg shadow p-4">
    <table class="min-w-full border-collapse w-full table-auto">
      <thead>
        <tr class="bg-gray-200 text-left">
          <th class="p-2 sm:p-3 border-b text-sm sm:text-base">ID</th>
          <th class="p-2 sm:p-3 border-b text-sm sm:text-base">Name</th>
          <th class="p-2 sm:p-3 border-b text-sm sm:text-base">City</th>
          <th class="p-2 sm:p-3 border-b text-sm sm:text-base">Address</th>
          <th class="p-2 sm:p-3 border-b text-sm sm:text-base">Photo</th>
          <th class="p-2 sm:p-3 border-b text-sm sm:text-base">Created</th>
          <th class="p-2 sm:p-3 border-b text-sm sm:text-base">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($spots as $s): ?>
          <tr class="border-b hover:bg-gray-100 align-top">
            <td class="p-2 sm:p-3 text-sm sm:text-base"><?= $s['id'] ?></td>
            <td class="p-2 sm:p-3 text-sm sm:text-base"><?= htmlspecialchars($s['name']) ?></td>
            <td class="p-2 sm:p-3 text-sm sm:text-base"><?= htmlspecialchars($s['city']) ?></td>
            <td class="p-2 sm:p-3 text-sm sm:text-base"><?= htmlspecialchars($s['address']) ?></td>
            <td class="p-2 sm:p-3 text-sm sm:text-base">
              <?php if (!empty($s['file_path'])): ?>
                <img src="<?= htmlspecialchars($s['file_path']) ?>" class="w-12 sm:w-16 h-12 sm:h-16 object-cover rounded">
              <?php endif; ?>
            </td>
            <td class="p-2 sm:p-3 text-sm sm:text-base"><?= $s['created_at'] ?></td>
            <td class="p-2 sm:p-3 text-sm sm:text-base">
              <form method="POST" onsubmit="return confirm('Delete this spot?');">
                <input type="hidden" name="id" value="<?= $s['id'] ?>">
                <button type="submit" name="delete_spot" class="bg-red-500 text-white px-2 sm:px-3 py-1 rounded hover:bg-red-600 text-xs sm:text-sm">Delete</button>
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
  <div class="overflow-x-auto bg-gray-50 rounded-lg shadow p-4">
    <table class="min-w-full border-collapse w-full table-auto">
      <thead>
        <tr class="bg-gray-200 text-left">
          <th class="p-2 sm:p-3 border-b text-sm sm:text-base">ID</th>
          <th class="p-2 sm:p-3 border-b text-sm sm:text-base">User</th>
          <th class="p-2 sm:p-3 border-b text-sm sm:text-base">Spot</th>
          <th class="p-2 sm:p-3 border-b text-sm sm:text-base">Text</th>
          <th class="p-2 sm:p-3 border-b text-sm sm:text-base">Created</th>
          <th class="p-2 sm:p-3 border-b text-sm sm:text-base">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($comments as $c): ?>
          <tr class="border-b hover:bg-gray-100 align-top">
            <td class="p-2 sm:p-3 text-sm sm:text-base"><?= $c['id'] ?></td>
            <td class="p-2 sm:p-3 text-sm sm:text-base"><?= htmlspecialchars($c['user_name']) ?></td>
            <td class="p-2 sm:p-3 text-sm sm:text-base"><?= htmlspecialchars($c['spot_name']) ?></td>
            <td class="p-2 sm:p-3 text-sm sm:text-base">
              <form method="POST" class="flex flex-col gap-2">
                <textarea name="text" class="border border-gray-300 rounded p-2 w-full text-xs sm:text-sm" rows="2"><?= htmlspecialchars($c['text']) ?></textarea>
                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                <div class="flex gap-2 flex-wrap">
                  <button type="submit" name="edit_comment" class="bg-blue-500 text-white px-2 sm:px-3 py-1 rounded hover:bg-blue-600 text-xs sm:text-sm">Save</button>
                  <button type="submit" name="delete_comment" class="bg-red-500 text-white px-2 sm:px-3 py-1 rounded hover:bg-red-600 text-xs sm:text-sm" onclick="return confirm('Delete this comment?');">Delete</button>
                </div>
              </form>
            </td>
            <td class="p-2 sm:p-3 text-sm sm:text-base"><?= $c['created_at'] ?></td>
            <td class="p-2 sm:p-3 text-sm sm:text-base"></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- USERS -->
<div id="users" class="tab-content hidden mt-6">
  <div class="overflow-x-auto bg-gray-50 rounded-lg shadow p-4">
    <table class="min-w-full border-collapse w-full table-auto">
      <thead>
        <tr class="bg-gray-200 text-left">
          <th class="p-2 sm:p-3 border-b text-sm sm:text-base">ID</th>
          <th class="p-2 sm:p-3 border-b text-sm sm:text-base">Name</th>
          <th class="p-2 sm:p-3 border-b text-sm sm:text-base">Email</th>
          <th class="p-2 sm:p-3 border-b text-sm sm:text-base">Role</th>
          <th class="p-2 sm:p-3 border-b text-sm sm:text-base">Blocked</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
          <tr class="border-b hover:bg-gray-100 align-top">
            <td class="p-2 sm:p-3 text-sm sm:text-base"><?= $u['id'] ?></td>
            <td class="p-2 sm:p-3 text-sm sm:text-base"><?= htmlspecialchars($u['name']) ?></td>
            <td class="p-2 sm:p-3 text-sm sm:text-base"><?= htmlspecialchars($u['email']) ?></td>
            <td class="p-2 sm:p-3 text-sm sm:text-base"><?= htmlspecialchars($u['role']) ?></td>
            <td class="p-2 sm:p-3 text-sm sm:text-base">
              <form method="POST" style="display:inline-block;">
                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                <button type="submit" name="toggle_block" 
                    class="<?= $u['blocked'] ? 'bg-red-500' : 'bg-blue-500' ?> text-white px-2 sm:px-3 py-1 rounded hover:opacity-80 text-xs sm:text-sm">
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

  document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.classList.remove('bg-black', 'text-white');
    btn.classList.add('bg-gray-200', 'text-gray-800');
  });

  const activeBtn = document.getElementById('tab-' + tabId);
  activeBtn.classList.remove('bg-gray-200', 'text-gray-800');
  activeBtn.classList.add('bg-black', 'text-white');
}

document.addEventListener('DOMContentLoaded', function() {
    showTab('spots');
});

</script>

<?php include 'includes/footer.php'; ?>
