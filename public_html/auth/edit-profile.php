<?php
// start session
require_once __DIR__ . '/../classes/session.php';
$session = new SessionHandle();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/password-validate.php';
require_once __DIR__ . '/../classes/User.php';


// Redirect to login page if user is not logged in
if (!$session->logged_in()) {
    header("Location: login.php");
    exit();
}

// Load User class
$userObj = new User($pdo);

// Store the logged-in user's ID for queries
$user_id = $session->get('user_id');

// Initialize variables
$msg = '';
$msg_type = '';
$user = $userObj->getById($user_id);
$user_name = $user['name'] ?? '';
$user_email = $user['email'] ?? '';
$user_photo = $userObj->getProfilePhoto($user_id);
$photo_src = $user_photo ? '../' . $user_photo : null;
$session->set('profile_photo', $user_photo);

// --- Update Name ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_name'])) {
    $new_name = trim($_POST['name'] ?? '');

    if (empty($new_name)) {
        $msg = 'Name cannot be empty.';
        $msg_type = 'error';
    } elseif (!preg_match('/^[a-z0-9](?!.*[._]{2})[a-z0-9._]{1,18}[a-z0-9]$/', $new_name)) {
        $msg = "Username must be 3–20 characters, lowercase letters, numbers, dots, or underscores.";
        $msg_type = 'error';
    } elseif ($userObj->isNameTaken($new_name, $user_id)) {
        $msg = 'This name is already taken. Please choose another.';
        $msg_type = 'error';
    } elseif ($new_name === $user_name) {
        $msg = 'This is already your current name.';
        $msg_type = 'error';
    } else {
        if ($userObj->updateName($user_id, $new_name)) {
            $msg = 'Name updated successfully.';
            $msg_type = 'success';
            $user_name = $new_name;
        } else {
            $msg = 'Failed to update name.';
            $msg_type = 'error';
        }
    }
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
        $user = $userObj->getById($user_id);
        if (!$user || !password_verify($current, $user['password'])) {
            $msg = 'Incorrect current password.';
            $msg_type = 'error';
        } else {
            $valid = validatePassword($new);
            if ($valid !== true) {
                $msg = $valid;
                $msg_type = 'error';
            } else {
                if ($userObj->updatePassword($user_id, $new)) {
                    $msg = 'Password updated successfully.';
                    $msg_type = 'success';
                } else {
                    $msg = 'Failed to update password.';
                    $msg_type = 'error';
                }
            }
        }
    }
}




include __DIR__ . '/../includes/header.php';
?>

<main class="flex flex-col items-center min-h-screen w-full bg-gray-50 overflow-auto relative">
  <div class="bg-white shadow-xl p-6 w-full max-w-3xl m-4 my-10">

   
    <div class="flex justify-between items-center mb-12">
      <a href="my-profile.php" class="text-gray-600 hover:underline flex items-center gap-1">
        ← Back
      </a>
      <h1 class="text-2xl font-bold text-center flex-1">Edit Profile</h1>
    </div>


    <div class="flex flex-col md:flex-row gap-6">

      
      <div class="flex-1 flex flex-col items-center justify-start">
        <div class="relative w-36 h-36 rounded-full border-4 border-gray-200 shadow-sm flex items-center justify-center bg-white text-4xl font-bold overflow-hidden">
          <?php if ($user_photo): ?>
            <img id="photoPreview" src="<?= htmlspecialchars($photo_src) ?>" class="w-full h-full object-cover">
          <?php else: ?>
            <span id="photoLetter"><?= strtoupper($user_name[0] ?? 'U') ?></span>
            <img id="photoPreview" class="w-full h-full object-cover hidden">
          <?php endif; ?>
        </div>

        
        <div class="mt-2 flex gap-2">
          <button id="changePhotoBtn" type="button" class="bg-black text-white text-xs px-3 py-1 rounded-full hover:bg-gray-800 transition">
            Change
          </button>
          <button id="removePhotoBtn" type="button" class="text-red-600 text-sm hover:underline">
            Remove
          </button>
        </div>

        
        <p id="uploadStatus" class="text-sm mt-2 hidden"></p>
        <input id="photoInput" type="file" name="profile_photo" accept="image/*" class="absolute w-0 h-0 opacity-0">
      </div>

      <!-- Form: Name + Password -->
      <div class="flex-1">
        <form method="post" class="space-y-4">


        <!-- Email (disabled) -->
          <div>
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" disabled value="<?= htmlspecialchars($user_email) ?>" 
                   class="mt-1 block w-full rounded-md border-gray-300 bg-white p-2">
          </div>

          <!-- Name -->
          <div>
            <label class="block text-sm font-medium text-gray-700">Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($user_name) ?>" 
                   class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 p-2" required>
          </div>

        

          <hr class="my-4">

          <!-- Password -->
          <h2 class="text-lg font-semibold mb-3">Change Password</h2>
          <div>
            <label class="block text-sm text-gray-600">Current password</label>
            <input type="password" name="current_password" 
                   class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 p-2">
          </div>
          <div>
            <label class="block text-sm text-gray-600">New password</label>
            <input type="password" name="new_password" 
                   class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 p-2">
          </div>
          <div>
            <label class="block text-sm text-gray-600">Confirm new password</label>
            <input type="password" name="confirm_password" 
                   class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 p-2">
          </div>

          <!-- Hidden field for unified update -->
          <input type="hidden" name="update_profile" value="1">

          <!-- Messages -->
          <?php if ($msg): ?>
            <div class="text-sm <?= $msg_type === 'success' ? 'text-green-600' : 'text-red-600' ?> mt-1">
              <?= htmlspecialchars($msg) ?>
            </div>
          <?php endif; ?>

          <!-- Single Submit Button -->
          <div class="mt-4">
            <button type="submit" class="bg-black text-white px-4 py-2 rounded-full hover:bg-gray-800 transition w-full">
              Update Profile
            </button>
          </div>
        </form>
      </div>

    </div>
  </div>
</main>

<script src="/assets/js/profile-photo.js" defer></script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
