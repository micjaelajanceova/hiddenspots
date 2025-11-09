<?php
// auth/edit-profile.php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = '';
$msg_type = '';

// --- AJAX: Remove profile photo ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_photo']) && $_POST['remove_photo'] == '1') {
    header('Content-Type: application/json');

    $stmt = $pdo->prepare("SELECT profile_photo FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $current = $stmt->fetchColumn();

    if ($current) {
        $pathOnDisk = __DIR__ . '/../' . $current;
        if (file_exists($pathOnDisk)) @unlink($pathOnDisk);
    }

    $stmt = $pdo->prepare("UPDATE users SET profile_photo = NULL WHERE id = ?");
    $stmt->execute([$user_id]);

    unset($_SESSION['profile_photo']);
    echo json_encode(['success' => true]);
    exit();
}

// --- AJAX: Upload profile photo ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_photo']) && empty($_POST['update_password']) && !isset($_POST['remove_photo'])) {
    $response = ['success' => false, 'message' => 'Upload failed'];

    if ($_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES['profile_photo']['tmp_name'];
        $orig = basename($_FILES['profile_photo']['name']);
        $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp','gif'];

        if (!in_array($ext, $allowed)) {
            $response['message'] = 'Invalid file type.';
        } else {
            $filename = uniqid('pf_') . '.' . $ext;
            $targetDir = __DIR__ . '/../uploads/profile/';
            if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
            $dest = $targetDir . $filename;

            if (move_uploaded_file($tmp, $dest)) {
                $relative = 'uploads/profile/' . $filename;

                // Delete old photo
                $stmt = $pdo->prepare("SELECT profile_photo FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $old = $stmt->fetchColumn();
                if ($old) {
                    $oldPath = __DIR__ . '/../' . $old;
                    if (file_exists($oldPath)) @unlink($oldPath);
                }

                $stmt = $pdo->prepare("UPDATE users SET profile_photo = ? WHERE id = ?");
                $stmt->execute([$relative, $user_id]);

                $_SESSION['profile_photo'] = $relative;
                $response = ['success' => true, 'path' => $relative];
            } else {
                $response['message'] = 'Could not move uploaded file.';
            }
        }
    } else {
        $response['message'] = 'Upload error code: ' . $_FILES['profile_photo']['error'];
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// --- Password update ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($new)) {
        $msg = 'New password cannot be empty.';
        $msg_type = 'error';
    } elseif ($new !== $confirm) {
        $msg = 'Passwords do not match.';
        $msg_type = 'error';
    } else {
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || !password_verify($current, $row['password'])) {
            $msg = 'Incorrect current password.';
            $msg_type = 'error';
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hash, $user_id]);
            $msg = 'Password updated successfully.';
            $msg_type = 'success';
        }
    }
}

// --- Load user data ---
$stmt = $pdo->prepare("SELECT name, email, profile_photo FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$user_name = $user['name'] ?? ($_SESSION['user_name'] ?? '');
$user_email = $user['email'] ?? ($_SESSION['user_email'] ?? '');
$user_photo = $user['profile_photo'] ?? ($_SESSION['profile_photo'] ?? null);
$photo_src = $user_photo ? '../' . $user_photo : null;

include __DIR__ . '/../includes/header.php';
?>

<main class="flex flex-col items-center min-h-screen w-screen bg-gray-50 overflow-auto relative">
  <div class="bg-white shadow-xl p-6 w-full max-w-3xl m-4 my-10">

    <!-- Header with Back Button inside container -->
    <div class="flex justify-between items-center mb-12">
      <a href="my-profile.php" class="text-gray-600 hover:underline flex items-center gap-1">
        ← Back
      </a>
      <h1 class="text-2xl font-bold text-center flex-1">Edit Profile</h1>
    </div>

    <?php if ($msg): ?>
      <div class="mb-4 text-sm <?= $msg_type === 'success' ? 'text-green-600' : 'text-red-600' ?>">
        <?= htmlspecialchars($msg) ?>
      </div>
    <?php endif; ?>

    <div class="flex flex-col md:flex-row gap-6">

      <!-- LEFT: Photo -->
      <div class="flex-1 flex flex-col items-center">
        <div class="relative w-36 h-36 rounded-full border-4 border-gray-200 shadow-sm flex items-center justify-center bg-gray-200 text-4xl font-bold text-white overflow-hidden">
          <?php if ($user_photo): ?>
            <img id="photoPreview" src="<?= htmlspecialchars($photo_src) ?>" class="w-full h-full object-cover">
          <?php else: ?>
            <span id="photoLetter"><?= strtoupper($user_name[0] ?? 'U') ?></span>
            <img id="photoPreview" class="w-full h-full object-cover hidden">
          <?php endif; ?>
        </div>

        <!-- Buttons side by side -->
        <div class="mt-2 flex gap-2">
          <button id="changePhotoBtn" type="button" class="bg-black text-white text-xs px-3 py-1 rounded-full hover:bg-gray-800 transition">
            Change
          </button>
          <button id="removePhotoBtn" type="button" class="text-red-600 text-sm hover:underline">
            Remove
          </button>
        </div>

        <!-- Status message hidden initially -->
        <p id="uploadStatus" class="text-sm mt-2 hidden"></p>
        <input id="photoInput" type="file" name="profile_photo" accept="image/*" class="absolute w-0 h-0 opacity-0">
      </div>

      <!-- RIGHT: Info + Password -->
      <div class="flex-1">
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Name</label>
          <input type="text" disabled value="<?= htmlspecialchars($user_name) ?>" class="mt-1 block w-full rounded-md border-gray-300 bg-white p-2">
        </div>

        <div class="mb-6">
          <label class="block text-sm font-medium text-gray-700">Email</label>
          <input type="email" disabled value="<?= htmlspecialchars($user_email) ?>" class="mt-1 block w-full rounded-md border-gray-300 bg-white p-2">
        </div>

        <hr class="my-4">

        <h2 class="text-lg font-semibold mb-3">Change Password</h2>
        <form method="post" class="space-y-3">
          <input type="hidden" name="update_password" value="1">
          <div>
            <label class="block text-sm text-gray-600">Current password</label>
            <input type="password" name="current_password" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 p-2" required>
          </div>
          <div>
            <label class="block text-sm text-gray-600">New password</label>
            <input type="password" name="new_password" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 p-2" required>
          </div>
          <div>
            <label class="block text-sm text-gray-600">Confirm new password</label>
            <input type="password" name="confirm_password" class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 p-2" required>
          </div>
          <div class="mt-4">
            <button type="submit" class="bg-black text-white px-4 py-2 rounded-full hover:bg-gray-800 transition w-full">Update Password</button>
          </div>
        </form>
      </div>

    </div>
  </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const photoInput = document.getElementById('photoInput');
  const changePhotoBtn = document.getElementById('changePhotoBtn');
  const removePhotoBtn = document.getElementById('removePhotoBtn');
  const photoPreview = document.getElementById('photoPreview');
  const photoLetter = document.getElementById('photoLetter');
  const uploadStatus = document.getElementById('uploadStatus');

  // Open file picker
  changePhotoBtn.addEventListener('click', (e) => {
    e.preventDefault();
    photoInput.click();
  });

  // Upload photo
  photoInput.addEventListener('change', async (e) => {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = (ev) => {
      photoPreview.src = ev.target.result;
      photoPreview.classList.remove('hidden');
      if(photoLetter) photoLetter.style.display = 'none';
    };
    reader.readAsDataURL(file);

    uploadStatus.classList.remove('hidden');
    uploadStatus.textContent = 'Uploading...';

    const fd = new FormData();
    fd.append('profile_photo', file);

    try {
      const res = await fetch(window.location.href, { method: 'POST', body: fd });
      const data = await res.json();
if (data.success) {
    photoPreview.src = '../' + data.path;
    uploadStatus.textContent = 'Photo saved!';
    uploadStatus.classList.remove('hidden');
    uploadStatus.classList.remove('text-red-600'); // remove any previous error
    uploadStatus.classList.add('text-green-600');   // make it green
    setTimeout(() => uploadStatus.classList.add('hidden'), 2000);
} else {
    uploadStatus.textContent = 'Error: ' + (data.message || 'Upload failed');
    uploadStatus.classList.remove('text-green-600');
    uploadStatus.classList.add('text-red-600');
    setTimeout(() => uploadStatus.classList.add('hidden'), 2000);
}

    } catch (err) {
      uploadStatus.textContent = 'Upload failed.';
      setTimeout(() => uploadStatus.classList.add('hidden'), 2000);
    }
  });

  // Remove photo
  removePhotoBtn.addEventListener('click', async (e) => {
    e.preventDefault();
    if (!confirm('Remove profile photo?')) return;

    try {
      const res = await fetch(window.location.href, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'remove_photo=1'
      });
      const data = await res.json();
      if (data.success) {
        photoPreview.src = '';
        photoPreview.classList.add('hidden');
        if(photoLetter) photoLetter.style.display = 'block';
      } else {
        alert('Could not remove photo.');
      }
    } catch (err) {
      alert('Could not remove photo.');
    }
  });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
