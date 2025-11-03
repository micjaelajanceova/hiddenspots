<?php
// includes/profile-header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Use $profile_user if set, otherwise fallback to logged-in user
$name = $profile_user['name'] ?? ($_SESSION['user_name'] ?? 'User');
$photo = $profile_user['profile_photo'] ?? ($_SESSION['profile_photo'] ?? null);

// Generate proper URL for the photo
$photo_url = null;
if (!empty($photo)) {
    $photo_url = '/hiddenspots/' . $photo;
}
?>

<?php if(isset($_SESSION['user_id'])): ?>
  <!-- STICKY PROFILE (only when logged in) -->
  <div class="fixed top-0 right-0 z-50 px-4 py-3 flex justify-end items-center w-full md:w-auto">
    <div class="relative">
      <button id="profileBtn" class="flex items-center justify-center w-10 h-10 bg-black text-white rounded-full font-semibold text-lg overflow-hidden">
        <?php if($photo_url && file_exists($_SERVER['DOCUMENT_ROOT'] . '/hiddenspots/' . $photo)): ?>
          <img src="<?= htmlspecialchars($photo_url) ?>" alt="Profile" class="w-full h-full object-cover rounded-full">
        <?php else: ?>
          <?= strtoupper(substr($name, 0, 1)) ?>
        <?php endif; ?>
      </button>
      
      <div id="profileMenu" class="absolute right-0 mt-2 w-48 bg-white border rounded-2xl shadow-lg hidden overflow-hidden z-50">
        <a href="auth/my-profile.php" class="block px-4 py-2 text-sm hover:bg-gray-100">My Profile</a>
        <a href="/hiddenspots/upload.php" class="block px-4 py-2 text-sm hover:bg-gray-100">Upload</a>
        <div class="border-t my-1"></div>
        <a href="auth/logout.php" class="block px-4 py-2 text-sm text-red-600 font-semibold hover:bg-red-50">Logout</a>
      </div>
    </div>
  </div>

<?php else: ?>
  <!-- NON-STICKY LOGIN / REGISTER (for guests) -->
  <div class="mt-6 flex justify-end gap-2 px-4 sm:px-6 lg:px-8">
    <a href="auth/login.php?action=login" class="bg-black text-white px-4 py-2 rounded-full">Login</a>
    <a href="auth/login.php?action=register" class="bg-gray-200 text-black px-4 py-2 rounded-full">Register</a>
  </div>
<?php endif; ?>


<script>
// PROFILE MENU TOGGLE
const profileBtn = document.getElementById('profileBtn');
const profileMenu = document.getElementById('profileMenu');

if (profileBtn && profileMenu) {
  profileBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    profileMenu.classList.toggle('hidden');
  });

  document.addEventListener('click', (e) => {
    if (!profileMenu.contains(e.target) && !profileBtn.contains(e.target)) {
      profileMenu.classList.add('hidden');
    }
  });
}
</script>
