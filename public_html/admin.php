<?php
include 'includes/db.php';
include 'includes/header.php';
include 'classes/spot.php';
include 'includes/profile-header.php';

// Start session if not started
if (session_status() === PHP_SESSION_NONE) session_start();

// Only admin can access
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: index.php");
  exit();
}

// ---------- CSRF token ----------
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
function check_csrf($token) {
    return isset($token) && hash_equals($_SESSION['csrf_token'], $token);
}

// Helper: sanitize output
function e($v){ return htmlspecialchars($v ?? '','ENT_QUOTES','UTF-8'); }

// --------- Handle POST actions ----------
$errors = [];
$success = null;

// --- DELETE SPOT ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_spot'])) {
    if (!check_csrf($_POST['csrf_token'] ?? '')) { $errors[] = 'Invalid CSRF token'; }
    else {
        $id = intval($_POST['id']);
        $stmt = $pdo->prepare("DELETE FROM hidden_spots WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Spot deleted successfully";
    }
}

// --- CREATE SPOT ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_spot'])) {
    if (!check_csrf($_POST['csrf_token'] ?? '')) { $errors[] = 'Invalid CSRF token'; }
    else {
        $name = trim($_POST['name'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $description = trim($_POST['description'] ?? '');

        // basic validation
        if ($name === '') $errors[] = 'Name required';
        // handle file upload
        $uploaded_path = null;
        if (!empty($_FILES['photo']['name'])) {
            $up = $_FILES['photo'];
            if ($up['error'] === UPLOAD_ERR_OK) {
                $allowed = ['image/jpeg','image/png','image/webp'];
                if (!in_array(mime_content_type($up['tmp_name']), $allowed)) {
                    $errors[] = 'Only JPG/PNG/WEBP allowed';
                } else {
                    $ext = pathinfo($up['name'], PATHINFO_EXTENSION);
                    $filename = 'uploads/spot_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                    if (!is_dir(dirname(__FILE__).'/uploads')) {
                        @mkdir(dirname(__FILE__).'/uploads', 0755, true);
                    }
                    if (move_uploaded_file($up['tmp_name'], dirname(__FILE__).'/'.$filename)) {
                        $uploaded_path = $filename;
                    } else {
                        $errors[] = 'Failed to move uploaded file';
                    }
                }
            } else {
                $errors[] = 'Upload error';
            }
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare("INSERT INTO hidden_spots (name, city, address, description, file_path, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$name, $city, $address, $description, $uploaded_path]);
            $success = 'Spot created';
        }
    }
}

// --- EDIT SPOT ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_spot'])) {
    if (!check_csrf($_POST['csrf_token'] ?? '')) { $errors[] = 'Invalid CSRF token'; }
    else {
        $id = intval($_POST['id']);
        $name = trim($_POST['name'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($name === '') $errors[] = 'Name required';

        // optional file upload replacement
        if (!empty($_FILES['photo']['name'])) {
            $up = $_FILES['photo'];
            if ($up['error'] === UPLOAD_ERR_OK) {
                $allowed = ['image/jpeg','image/png','image/webp'];
                if (!in_array(mime_content_type($up['tmp_name']), $allowed)) {
                    $errors[] = 'Only JPG/PNG/WEBP allowed';
                } else {
                    $ext = pathinfo($up['name'], PATHINFO_EXTENSION);
                    $filename = 'uploads/spot_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                    if (!is_dir(dirname(__FILE__).'/uploads')) {
                        @mkdir(dirname(__FILE__).'/uploads', 0755, true);
                    }
                    if (move_uploaded_file($up['tmp_name'], dirname(__FILE__).'/'.$filename)) {
                        // update with new file path
                        $stmt = $pdo->prepare("UPDATE hidden_spots SET file_path = ? WHERE id = ?");
                        $stmt->execute([$filename, $id]);
                    } else {
                        $errors[] = 'Failed to move uploaded file';
                    }
                }
            } else {
                $errors[] = 'Upload error';
            }
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare("UPDATE hidden_spots SET name = ?, city = ?, address = ?, description = ? WHERE id = ?");
            $stmt->execute([$name, $city, $address, $description, $id]);
            $success = 'Spot updated';
        }
    }
}

// --- TOGGLE BLOCK USER ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_block'])) {
  if (!check_csrf($_POST['csrf_token'] ?? '')) { $errors[] = 'Invalid CSRF token'; }
  else {
    $id = intval($_POST['id']);
    $stmt = $pdo->prepare("SELECT blocked FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $newStatus = $user['blocked'] ? 0 : 1;
        $stmt = $pdo->prepare("UPDATE users SET blocked = ? WHERE id = ?");
        $stmt->execute([$newStatus, $id]);
        $success = "User " . ($newStatus ? 'blocked' : 'unblocked');
    }
  }
}

// --- TOGGLE FEATURED (Hot picture) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_featured'])) {
    if (!check_csrf($_POST['csrf_token'] ?? '')) { $errors[] = 'Invalid CSRF token'; }
    else {
        $id = intval($_POST['id']);
        $stmt = $pdo->prepare("SELECT featured FROM hidden_spots WHERE id = ?");
        $stmt->execute([$id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($r) {
            $new = $r['featured'] ? 0 : 1;
            $stmt = $pdo->prepare("UPDATE hidden_spots SET featured = ? WHERE id = ?");
            $stmt->execute([$new, $id]);
            $success = $new ? 'Marked as hot/featured' : 'Unmarked featured';
        }
    }
}

// --- EDIT USER (change name, email, role, reset password) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
    if (!check_csrf($_POST['csrf_token'] ?? '')) { $errors[] = 'Invalid CSRF token'; }
    else {
        $id = intval($_POST['id']);
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = trim($_POST['role'] ?? 'user');
        $reset_pwd = isset($_POST['reset_password']) && $_POST['reset_password'] === '1';

        if ($name === '' || $email === '') $errors[] = 'Name and email required';

        if (empty($errors)) {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?");
            $stmt->execute([$name, $email, $role, $id]);

            if ($reset_pwd) {
                // generate random password and hash it; here we use password_hash
                $newpw = bin2hex(random_bytes(4)); // 8 hex chars
                $hash = password_hash($newpw, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hash, $id]);
                $success = "User updated. Password reset to: $newpw (copy it now!)";
            } else {
                $success = "User updated";
            }
        }
    }
}

// --- EDIT COMMENT ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_comment'])) {
  if (!check_csrf($_POST['csrf_token'] ?? '')) { $errors[] = 'Invalid CSRF token'; }
  else {
    $id = intval($_POST['id']);
    $text = trim($_POST['text']);
    $stmt = $pdo->prepare("UPDATE comments SET text = ? WHERE id = ?");
    $stmt->execute([$text, $id]);
    $success = 'Comment updated';
  }
}

// --- DELETE COMMENT ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_comment'])) {
  if (!check_csrf($_POST['csrf_token'] ?? '')) { $errors[] = 'Invalid CSRF token'; }
  else {
    $id = intval($_POST['id']);
    $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
    $stmt->execute([$id]);
    $success = 'Comment deleted';
  }
}

// --- UPDATE SITE SETTINGS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_site_settings'])) {
    if (!check_csrf($_POST['csrf_token'] ?? '')) { $errors[] = 'Invalid CSRF token'; }
    else {
        $desc = $_POST['site_description'] ?? '';
        $rules = $_POST['rules'] ?? '';
        $contact = $_POST['contact_info'] ?? '';
        $color = trim($_POST['primary_color'] ?? '');

        $stmt = $pdo->prepare("UPDATE site_settings SET site_description = ?, rules = ?, contact_info = ?, primary_color = ?, updated_at = NOW() WHERE id = 1");
        $stmt->execute([$desc, $rules, $contact, $color]);
        $success = 'Site settings updated';
    }
}

// ---------- Fetch data ----------
$spots = $pdo->query("SELECT * FROM hidden_spots ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$comments = $pdo->query("
    SELECT c.id, c.text, c.created_at, u.name AS user_name, u.id AS user_id, hs.name AS spot_name 
    FROM comments c 
    LEFT JOIN users u ON c.user_id = u.id 
    LEFT JOIN hidden_spots hs ON c.spot_id = hs.id 
    ORDER BY c.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
$users = $pdo->query("SELECT id, name, email, role, blocked FROM users ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$site = $pdo->query("SELECT * FROM site_settings WHERE id = 1 LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if (!$site) {
    $site = [
        'site_description' => '',
        'rules' => '',
        'contact_info' => '',
        'primary_color' => '#579692'
    ];
}
?>

<main class="flex-1 min-h-screen overflow-y-auto">
<div class="w-full px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto py-8">

  <!-- Admin header -->
  <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 gap-4">
    <!-- Title -->
    <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight">Admin Panel</h1>

    <!-- Tabs -->
    <div class="flex gap-3 flex-wrap mt-4 md:mt-0">
      <button id="tab-spots" onclick="showTab('spots')" class="tab-btn px-5 py-2 rounded-full font-medium shadow">Spots</button>
      <button id="tab-comments" onclick="showTab('comments')" class="tab-btn px-5 py-2 rounded-full font-medium shadow">Comments</button>
      <button id="tab-users" onclick="showTab('users')" class="tab-btn px-5 py-2 rounded-full font-medium shadow">Users</button>
      <button id="tab-site" onclick="showTab('site')" class="tab-btn px-5 py-2 rounded-full font-medium shadow">Site Settings</button>
    </div>
  </div>

  <div class="border-t border-gray-300 mb-6"></div>

  <?php if ($errors): ?>
    <div class="bg-red-100 text-red-800 p-3 rounded mb-4">
      <?php foreach ($errors as $err) echo '<div>'.e($err).'</div>'; ?>
    </div>
  <?php endif; ?>
  <?php if ($success): ?>
    <div class="bg-green-100 text-green-800 p-3 rounded mb-4">
      <?= e($success) ?>
    </div>
  <?php endif; ?>

<!-- SPOTS -->
<div id="spots" class="tab-content">
  <div class="mb-4">
    <h2 class="text-xl font-semibold mb-2">Create new spot</h2>
    <form method="POST" enctype="multipart/form-data" class="bg-gray-50 p-4 rounded">
      <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <input name="name" placeholder="Name" class="border p-2" />
        <input name="city" placeholder="City" class="border p-2" />
        <input name="address" placeholder="Address" class="border p-2" />
        <input type="file" name="photo" accept="image/*" class="border p-2" />
        <textarea name="description" placeholder="Description" class="border p-2 col-span-2"></textarea>
      </div>
      <div class="mt-3">
        <button type="submit" name="create_spot" class="bg-green-600 text-white px-3 py-1 rounded">Create Spot</button>
      </div>
    </form>
  </div>

  <div class="overflow-x-auto bg-gray-50 rounded-lg shadow p-4">
    <table class="min-w-full border-collapse w-full table-auto">
      <thead>
        <tr class="bg-gray-200 text-left">
          <th class="p-2 sm:p-3 border-b text-sm sm:text-base">ID</th>
          <th class="p-2 sm:p-3 border-b text-sm sm:text-base">Name</th>
          <th class="p-2 sm:p-3 border-b text-sm sm:text-base">City</th>
          <th class="p-2 sm:p-3 border-b text-sm sm:text-base">Photo</th>
          <th class="p-2 sm:p-3 border-b text-sm sm:text-base">Created</th>
          <th class="p-2 sm:p-3 border-b text-sm sm:text-base">Featured</th>
          <th class="p-2 sm:p-3 border-b text-sm sm:text-base">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($spots as $s): ?>
          <tr class="border-b hover:bg-gray-100 align-top">
            <td class="p-2 sm:p-3 text-sm sm:text-base"><?= e($s['id']) ?></td>
            <td class="p-2 sm:p-3 text-sm sm:text-base"><?= e($s['name']) ?></td>
            <td class="p-2 sm:p-3 text-sm sm:text-base"><?= e($s['city']) ?></td>
            <td class="p-2 sm:p-3 text-sm sm:text-base">
              <?php if (!empty($s['file_path'])): ?>
                <img src="<?= e($s['file_path']) ?>" class="w-12 sm:w-16 h-12 sm:h-16 object-cover rounded">
              <?php endif; ?>
            </td>
            <td class="p-2 sm:p-3 text-sm sm:text-base"><?= e($s['created_at']) ?></td>
            <td class="p-2 sm:p-3 text-sm sm:text-base">
              <form method="POST" style="display:inline-block;">
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="id" value="<?= e($s['id']) ?>">
                <button type="submit" name="toggle_featured" class="<?= $s['featured'] ? 'bg-yellow-500' : 'bg-gray-300' ?> text-white px-2 py-1 rounded text-xs">
                  <?= $s['featured'] ? 'Featured' : 'Mark featured' ?>
                </button>
              </form>
            </td>
            <td class="p-2 sm:p-3 text-sm sm:text-base">
              <!-- Edit button toggle to show inline edit form -->
              <details class="mb-1">
                <summary class="cursor-pointer text-blue-600">Edit</summary>
                <form method="POST" enctype="multipart/form-data" class="mt-2">
                  <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                  <input type="hidden" name="id" value="<?= e($s['id']) ?>">
                  <input name="name" value="<?= e($s['name']) ?>" class="border p-1 w-full mb-1" />
                  <input name="city" value="<?= e($s['city']) ?>" class="border p-1 w-full mb-1" />
                  <input name="address" value="<?= e($s['address']) ?>" class="border p-1 w-full mb-1" />
                  <textarea name="description" class="border p-1 w-full mb-1"><?= e($s['description']) ?></textarea>
                  <input type="file" name="photo" accept="image/*" class="border p-1 mb-2 w-full" />
                  <div class="flex gap-2">
                    <button type="submit" name="edit_spot" class="bg-blue-500 text-white px-2 py-1 rounded text-xs">Save</button>
                    <form method="POST" onsubmit="return confirm('Delete this spot?');">
                      <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                      <input type="hidden" name="id" value="<?= e($s['id']) ?>">
                    </form>
                  </div>
                </form>
              </details>

              <form method="POST" onsubmit="return confirm('Delete this spot?');" style="display:inline-block;margin-left:6px;">
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="id" value="<?= e($s['id']) ?>">
                <button type="submit" name="delete_spot" class="bg-red-500 text-white px-2 py-1 rounded text-xs">Delete</button>
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
            <td class="p-2 sm:p-3 text-sm sm:text-base"><?= e($c['id']) ?></td>
            <td class="p-2 sm:p-3 text-sm sm:text-base"><?= e($c['user_name']) ?> (id: <?= e($c['user_id']) ?>)</td>
            <td class="p-2 sm:p-3 text-sm sm:text-base"><?= e($c['spot_name']) ?></td>
            <td class="p-2 sm:p-3 text-sm sm:text-base">
              <form method="POST" class="flex flex-col gap-2">
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                <textarea name="text" class="border border-gray-300 rounded p-2 w-full text-xs sm:text-sm" rows="2"><?= e($c['text']) ?></textarea>
                <input type="hidden" name="id" value="<?= e($c['id']) ?>">
                <div class="flex gap-2 flex-wrap">
                  <button type="submit" name="edit_comment" class="bg-blue-500 text-white px-2 sm:px-3 py-1 rounded hover:bg-blue-600 text-xs sm:text-sm">Save</button>
                  <button type="submit" name="delete_comment" class="bg-red-500 text-white px-2 sm:px-3 py-1 rounded hover:bg-red-600 text-xs sm:text-sm" onclick="return confirm('Delete this comment?');">Delete</button>
                </div>
              </form>
            </td>
            <td class="p-2 sm:p-3 text-sm sm:text-base"><?= e($c['created_at']) ?></td>
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
          <th class="p-2 sm:p-3 border-b text-sm sm:text-base">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
          <tr class="border-b hover:bg-gray-100 align-top">
            <td class="p-2 sm:p-3 text-sm sm:text-base"><?= e($u['id']) ?></td>
            <td class="p-2 sm:p-3 text-sm sm:text-base"><?= e($u['name']) ?></td>
            <td class="p-2 sm:p-3 text-sm sm:text-base"><?= e($u['email']) ?></td>
            <td class="p-2 sm:p-3 text-sm sm:text-base"><?= e($u['role']) ?></td>
            <td class="p-2 sm:p-3 text-sm sm:text-base"><?= $u['blocked'] ? 'Yes' : 'No' ?></td>
            <td class="p-2 sm:p-3 text-sm sm:text-base">
              <details>
                <summary class="cursor-pointer text-blue-600">Edit</summary>
                <form method="POST" class="mt-2">
                  <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                  <input type="hidden" name="id" value="<?= e($u['id']) ?>">
                  <input name="name" value="<?= e($u['name']) ?>" class="border p-1 w-full mb-1" />
                  <input name="email" value="<?= e($u['email']) ?>" class="border p-1 w-full mb-1" />
                  <select name="role" class="border p-1 w-full mb-1">
                    <option value="user" <?= $u['role'] === 'user' ? 'selected' : '' ?>>user</option>
                    <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>admin</option>
                  </select>
                  <label class="flex items-center gap-2"><input type="checkbox" name="reset_password" value="1"> Reset password (generate)</label>
                  <div class="mt-2 flex gap-2">
                    <button type="submit" name="edit_user" class="bg-blue-500 text-white px-2 py-1 rounded text-xs">Save</button>
                  </div>
                </form>
              </details>

              <form method="POST" style="display:inline-block;margin-left:6px;">
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="id" value="<?= e($u['id']) ?>">
                <button type="submit" name="toggle_block" class="<?= $u['blocked'] ? 'bg-red-500' : 'bg-blue-500' ?> text-white px-2 sm:px-3 py-1 rounded hover:opacity-80 text-xs sm:text-sm">
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

<!-- SITE SETTINGS -->
<div id="site" class="tab-content hidden mt-6">
  <div class="bg-gray-50 rounded-lg shadow p-4">
    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
      <h3 class="font-semibold text-lg mb-2">Site Description</h3>
      <textarea name="site_description" rows="4" class="w-full border p-2 mb-3"><?= e($site['site_description'] ?? '') ?></textarea>

      <h3 class="font-semibold text-lg mb-2">Rules and Regulations</h3>
      <textarea name="rules" rows="6" class="w-full border p-2 mb-3"><?= e($site['rules'] ?? '') ?></textarea>

      <h3 class="font-semibold text-lg mb-2">Contact Information</h3>
      <input name="contact_info" class="w-full border p-2 mb-3" value="<?= e($site['contact_info'] ?? '') ?>" />

      <h3 class="font-semibold text-lg mb-2">Styling (Primary color)</h3>
      <input name="primary_color" class="w-40 border p-2 mb-3" value="<?= e($site['primary_color'] ?? '') ?>" />

      <div>
        <button type="submit" name="update_site_settings" class="bg-green-600 text-white px-3 py-1 rounded">Save settings</button>
      </div>
    </form>
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
    if (activeBtn) {
      activeBtn.classList.remove('bg-gray-200', 'text-gray-800');
      activeBtn.classList.add('bg-black', 'text-white');
    }
  }
  document.addEventListener('DOMContentLoaded', function() {
      showTab('spots');
  });
</script>

<?php include 'includes/footer.php'; ?>
