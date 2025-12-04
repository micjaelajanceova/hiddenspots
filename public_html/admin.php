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

// ===== EDIT SPOT =====
if (isset($_POST['edit_spot'])) {
  $id = intval($_POST['id']);
  $name = trim($_POST['name']);
  $city = trim($_POST['city']);
  $address = trim($_POST['address']);

  $stmt = $pdo->prepare("UPDATE hidden_spots SET name=?, city=?, address=? WHERE id=?");
  $stmt->execute([$name, $city, $address, $id]);

  echo "<script>alert('Spot updated successfully'); window.location='admin.php';</script>";
  exit();
}


// ===== CREATE SPOT =====
if (isset($_POST['create_spot'])) {
  $name = trim($_POST['name']);
  $city = trim($_POST['city']);
  $address = trim($_POST['address']);
  
  $file_path = $_FILES['photo']['tmp_name'] ? 'uploads/'.basename($_FILES['photo']['name']) : null;
  if($file_path) move_uploaded_file($_FILES['photo']['tmp_name'], $file_path);

  $stmt = $pdo->prepare("INSERT INTO hidden_spots (name, city, address, file_path, created_at) VALUES (?, ?, ?, ?, NOW())");
  $stmt->execute([$name, $city, $address, $file_path]);
  echo "<script>alert('Spot created successfully'); window.location='admin.php';</script>";
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

// ===== EDIT USER =====
if (isset($_POST['edit_user'])) {
  $id = intval($_POST['id']);
  $name = trim($_POST['name']);
  $email = trim($_POST['email']);
  $role = trim($_POST['role']);

  $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, role=? WHERE id=?");
  $stmt->execute([$name, $email, $role, $id]);
  echo "<script>alert('User updated successfully'); window.location='admin.php';</script>";
  exit();
}

// ===== DELETE USER =====
if (isset($_POST['delete_user'])) {
  $id = intval($_POST['id']);
  $stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
  $stmt->execute([$id]);
  echo "<script>alert('User deleted successfully'); window.location='admin.php';</script>";
  exit();
}


// ===== UPDATE SITE INFO & STYLING =====
if (isset($_POST['update_site'])) {
  $description = trim($_POST['site_description']);
  $rules = trim($_POST['rules']);
  $contact = trim($_POST['contact_info']);
  $theme_color = trim($_POST['primary_color']);
  

  // Update site info
  $stmt = $pdo->prepare("
  UPDATE site_settings SET
      site_description=?,
      rules=?,
      contact_info=?,
      primary_color=?,
      about_title1=?,
      about_subtitle1=?,
      about_text1=?,
      about_title2=?,
      about_subtitle2=?,
      about_text2=?,
      how_title=?,
      how_subtitle=?,
      card1_title=?,
      card1_text=?,
      card2_title=?,
      card2_text=?,
      card3_title=?,
      card3_text=?
  WHERE id=1
  ");
  
  $stmt->execute([
      $description,
      $rules,
      $contact,
      $theme_color,
      $_POST['about_title1'],
      $_POST['about_subtitle1'],
      $_POST['about_text1'],
      $_POST['about_title2'],
      $_POST['about_subtitle2'],
      $_POST['about_text2'],
      $_POST['how_title'],
      $_POST['how_subtitle'],
      $_POST['card1_title'],
      $_POST['card1_text'],
      $_POST['card2_title'],
      $_POST['card2_text'],
      $_POST['card3_title'],
      $_POST['card3_text'],
  ]);
  

  echo "<script>alert('Site info updated successfully'); window.location='admin.php';</script>";
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
$users = $pdo->query("SELECT id, name, email, role, blocked FROM users ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

// ===== FETCH SITE INFO =====
$siteInfoStmt = $pdo->query("SELECT * FROM site_settings WHERE id=1 LIMIT 1");
$siteInfo = $siteInfoStmt->fetch(PDO::FETCH_ASSOC);

// Assign variables for About page
$about_title1    = $siteInfo['about_title1'] ?? '';
$about_subtitle1 = $siteInfo['about_subtitle1'] ?? '';
$about_text1     = $siteInfo['about_text1'] ?? '';

$about_title2    = $siteInfo['about_title2'] ?? '';
$about_subtitle2 = $siteInfo['about_subtitle2'] ?? '';
$about_text2     = $siteInfo['about_text2'] ?? '';

$how_title       = $siteInfo['how_title'] ?? '';
$how_subtitle    = $siteInfo['how_subtitle'] ?? '';

$card1_title     = $siteInfo['card1_title'] ?? '';
$card1_text      = $siteInfo['card1_text'] ?? '';

$card2_title     = $siteInfo['card2_title'] ?? '';
$card2_text      = $siteInfo['card2_text'] ?? '';

$card3_title     = $siteInfo['card3_title'] ?? '';
$card3_text      = $siteInfo['card3_text'] ?? '';

$siteDescription = $siteInfo['site_description'] ?? '';
$siteRules       = $siteInfo['rules'] ?? '';
$siteContact     = $siteInfo['contact_info'] ?? '';
$siteColor       = $siteInfo['primary_color'] ?? '';

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

      <h2 class="font-bold text-xl mb-4">About Section</h2>

      <label class="block font-semibold mb-1">About – Title (H1)</label>
      <input type="text" name="about_title1" class="w-full border p-2 rounded mb-4"
             value="<?= htmlspecialchars($siteInfo['about_title1'] ?? '') ?>">

      <label class="block font-semibold mb-1">About – Subtitle (H2)</label>
      <input type="text" name="about_subtitle1" class="w-full border p-2 rounded mb-4"
             value="<?= htmlspecialchars($siteInfo['about_subtitle1'] ?? '') ?>">

      <label class="block font-semibold mb-1">About – Text</label>
      <textarea name="about_text1" class="w-full border p-2 rounded mb-6" rows="4"><?= htmlspecialchars($siteInfo['about_text1'] ?? '') ?></textarea>


      <h2 class="font-bold text-xl mb-4">Explore Section</h2>

      <label class="block font-semibold mb-1">Explore – Title (H1)</label>
      <input type="text" name="about_title2" class="w-full border p-2 rounded mb-4"
             value="<?= htmlspecialchars($siteInfo['about_title2'] ?? '') ?>">

      <label class="block font-semibold mb-1">Explore – Subtitle (H2)</label>
      <input type="text" name="about_subtitle2" class="w-full border p-2 rounded mb-4"
             value="<?= htmlspecialchars($siteInfo['about_subtitle2'] ?? '') ?>">

      <label class="block font-semibold mb-1">Explore – Text</label>
      <textarea name="about_text2" class="w-full border p-2 rounded mb-6" rows="4"><?= htmlspecialchars($siteInfo['about_text2'] ?? '') ?></textarea>


      <h2 class="font-bold text-xl mb-4">How It Works Section</h2>

      <label class="block font-semibold mb-1">How – Title (H1)</label>
      <input type="text" name="how_title" class="w-full border p-2 rounded mb-4"
             value="<?= htmlspecialchars($siteInfo['how_title'] ?? '') ?>">

      <label class="block font-semibold mb-1">How – Subtitle (H2)</label>
      <input type="text" name="how_subtitle" class="w-full border p-2 rounded mb-6"
             value="<?= htmlspecialchars($siteInfo['how_subtitle'] ?? '') ?>">


      <h2 class="font-bold text-xl mb-4">Homepage Cards</h2>

      <!-- Card 1 -->
      <label class="block font-semibold mb-1">Card 1 – Title</label>
      <input type="text" name="card1_title" class="w-full border p-2 rounded mb-2"
             value="<?= htmlspecialchars($siteInfo['card1_title'] ?? '') ?>">

      <label class="block font-semibold mb-1">Card 1 – Text</label>
      <textarea name="card1_text" class="w-full border p-2 rounded mb-6" rows="3"><?= htmlspecialchars($siteInfo['card1_text'] ?? '') ?></textarea>

      <!-- Card 2 -->
      <label class="block font-semibold mb-1">Card 2 – Title</label>
      <input type="text" name="card2_title" class="w-full border p-2 rounded mb-2"
             value="<?= htmlspecialchars($siteInfo['card2_title'] ?? '') ?>">

      <label class="block font-semibold mb-1">Card 2 – Text</label>
      <textarea name="card2_text" class="w-full border p-2 rounded mb-6" rows="3"><?= htmlspecialchars($siteInfo['card2_text'] ?? '') ?></textarea>

      <!-- Card 3 -->
      <label class="block font-semibold mb-1">Card 3 – Title</label>
      <input type="text" name="card3_title" class="w-full border p-2 rounded mb-2"
             value="<?= htmlspecialchars($siteInfo['card3_title'] ?? '') ?>">

      <label class="block font-semibold mb-1">Card 3 – Text</label>
      <textarea name="card3_text" class="w-full border p-2 rounded mb-6" rows="3"><?= htmlspecialchars($siteInfo['card3_text'] ?? '') ?></textarea>

      <div class="space-y-6">

<!-- Site Description -->
<div class="flex flex-col">
  <label class="font-semibold text-gray-700 mb-1">Site Description</label>
  <textarea 
    name="site_description" 
    rows="4" 
    class="border rounded-lg p-3 focus:ring-2 focus:ring-blue-400 outline-none bg-gray-50"
  ><?= htmlspecialchars($siteDescription) ?></textarea>
</div>

<!-- Rules -->
<div class="flex flex-col">
  <label class="font-semibold text-gray-700 mb-1">Rules & Regulations</label>
  <textarea 
    name="rules" 
    rows="4" 
    class="border rounded-lg p-3 focus:ring-2 focus:ring-blue-400 outline-none bg-gray-50"
  ><?= htmlspecialchars($siteRules) ?></textarea>
</div>

<!-- Contact Info -->
<div class="flex flex-col">
  <label class="font-semibold text-gray-700 mb-1">Contact Information</label>
  <textarea 
    name="contact_info" 
    rows="4" 
    class="border rounded-lg p-3 focus:ring-2 focus:ring-blue-400 outline-none bg-gray-50"
  ><?= htmlspecialchars($siteContact) ?></textarea>
</div>

<!-- Primary Color -->
<div class="flex flex-col">
  <label class="font-semibold text-gray-700 mb-1">Primary Color</label>

  <div class="flex items-center gap-3">
    <input 
      type="color" 
      name="primary_color" 
      value="<?= htmlspecialchars($siteColor) ?>"
      class="w-12 h-12 p-0 border rounded cursor-pointer"
    >

    <span class="text-gray-600">Selected: <?= htmlspecialchars($siteColor) ?></span>
  </div>
</div>

</div>

      <button type="submit" name="update_site" class="w-full sm:w-auto py-2 px-4 bg-black text-white rounded-lg flex items-center justify-center gap-2 hover:bg-gray-800 transition mt-3">
        Save
      </button>

    </form>
  </div>
</div>





  <!-- SPOTS -->
<div id="spots" class="tab-content">

<!-- Add New Spot Form -->
<div class="mb-4 p-4 bg-gray-100 rounded">
    <h3 class="font-bold mb-2">Add New Spot</h3>
    <form method="POST" enctype="multipart/form-data" class="flex flex-wrap gap-2 items-end">
      <input type="text" name="name" placeholder="Name" class="border p-1 rounded text-xs sm:text-sm">
      <input type="text" name="city" placeholder="City" class="border p-1 rounded text-xs sm:text-sm">
      <input type="text" name="address" placeholder="Address" class="border p-1 rounded text-xs sm:text-sm">
      <input type="file" name="photo" class="text-xs sm:text-sm">
      <button type="submit" name="create_spot" class="bg-green-500 text-white px-2 py-1 rounded hover:bg-green-600 text-xs sm:text-sm">Add Spot</button>
    </form>
  </div>

  <div class="overflow-x-auto bg-gray-50 rounded-lg shadow p-4">
    <table class="min-w-full border-collapse w-full table-auto">
      <thead>
        <tr class="bg-gray-200 text-left">
          <th class="p-2 sm:p-3 border-b text-sm sm:text-base">ID</th>
          <th class="p-2 sm:p-3 border-b text-sm sm:text-base">Name</th>
          <th class="p-2 sm:p-3 border-b text-sm sm:text-base">City</th>
          <th class="p-2 sm:p-3 border-b text-sm sm:text-base">Address</th>
          <th class="p-2 sm:p-3 border-b text-sm sm:text-base">Photo</th>
          <th class="p-2 sm:p-3 border-b text-sm sm:text-base">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($spots as $s): ?>
<tr class="border-b hover:bg-gray-100 align-top">
    <td class="p-2 sm:p-3 text-sm sm:text-base"><?= $s['id'] ?></td>
    <td class="p-2 sm:p-3 text-sm sm:text-base">
        <form method="POST" class="flex items-center gap-2">
            <input type="hidden" name="id" value="<?= $s['id'] ?>">
            <input type="text" name="name" value="<?= htmlspecialchars($s['name']) ?>" class="border p-1 rounded text-xs sm:text-sm w-full">
        </form>
    </td>
    <td class="p-2 sm:p-3 text-sm sm:text-base">
        <input type="text" name="city" value="<?= htmlspecialchars($s['city']) ?>" class="border p-1 rounded text-xs sm:text-sm w-full" form="form-<?= $s['id'] ?>">
    </td>
    <td class="p-2 sm:p-3 text-sm sm:text-base">
        <input type="text" name="address" value="<?= htmlspecialchars($s['address']) ?>" class="border p-1 rounded text-xs sm:text-sm w-full" form="form-<?= $s['id'] ?>">
    </td>
    <td class="p-2 sm:p-3 text-sm sm:text-base">
        <?php if (!empty($s['file_path'])): ?>
            <img src="<?= htmlspecialchars($s['file_path']) ?>" class="w-12 sm:w-16 h-12 sm:h-16 object-cover rounded border">
        <?php endif; ?>
    </td>
    <td class="p-2 sm:p-3 text-sm sm:text-base">
        <form method="POST">
            <input type="hidden" name="id" value="<?= $s['id'] ?>">
            <button type="submit" name="edit_spot" class="bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 text-xs sm:text-sm">Save</button>
            <button type="submit" name="delete_spot" class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600 text-xs sm:text-sm" onclick="return confirm('Delete this spot?');">Delete</button>
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

            <!-- Text comment -->
            <td class="p-2 sm:p-3 text-sm sm:text-base">
              <form method="POST">
                <textarea name="text" class="border border-gray-300 rounded p-2 w-full text-xs sm:text-sm" rows="2"><?= htmlspecialchars($c['text']) ?></textarea>
                <input type="hidden" name="id" value="<?= $c['id'] ?>">
              </form>
            </td>

            <td class="p-2 sm:p-3 text-sm sm:text-base"><?= $c['created_at'] ?></td>

            <!-- Actions stĺpec -->
            <td class="p-2 sm:p-3 text-sm sm:text-base">
              <form method="POST" class="flex gap-2 flex-wrap">
                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                <button type="submit" name="edit_comment" class="bg-blue-500 text-white px-2 sm:px-3 py-1 rounded hover:bg-blue-600 text-xs sm:text-sm">Save</button>
                <button type="submit" name="delete_comment" class="bg-red-500 text-white px-2 sm:px-3 py-1 rounded hover:bg-red-600 text-xs sm:text-sm" onclick="return confirm('Delete this comment?');">Delete</button>
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

    <!-- Name -->
    <td class="p-2 sm:p-3 text-sm sm:text-base">
        <form method="POST" class="flex items-center gap-2">
            <input type="hidden" name="id" value="<?= $u['id'] ?>">
            <input type="text" name="name" value="<?= htmlspecialchars($u['name']) ?>" class="border p-1 rounded w-full text-xs sm:text-sm">
        </form>
    </td>

    <!-- Email -->
    <td class="p-2 sm:p-3 text-sm sm:text-base">
        <input type="email" name="email" value="<?= htmlspecialchars($u['email']) ?>" class="border p-1 rounded w-full text-xs sm:text-sm" form="form-<?= $u['id'] ?>">
    </td>

    <!-- Role -->
    <td class="p-2 sm:p-3 text-sm sm:text-base">
        <select name="role" class="border p-1 rounded w-full text-xs sm:text-sm" form="form-<?= $u['id'] ?>">
            <option value="user" <?= $u['role']=='user'?'selected':'' ?>>User</option>
            <option value="admin" <?= $u['role']=='admin'?'selected':'' ?>>Admin</option>
        </select>
    </td>

    <!-- Blocked / Actions -->
    <td class="p-2 sm:p-3 text-sm sm:text-base">
        <form method="POST" class="flex gap-2 flex-wrap">
            <input type="hidden" name="id" value="<?= $u['id'] ?>">
            <button type="submit" name="edit_user" class="bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 text-xs sm:text-sm">Save</button>
            <button type="submit" name="delete_user" class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600 text-xs sm:text-sm" onclick="return confirm('Delete this user?');">Delete</button>
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
    showTab('site');
});

</script>

<?php include 'includes/footer.php'; ?>
