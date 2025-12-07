<?php
require_once __DIR__ . '/../classes/session.php';
require_once __DIR__ . '/../classes/User.php';

$session = new SessionHandle();
$user_id = $session->getUserId();
$user_name = 'User';
$photo_url = null;

if ($user_id) {
    $userObj = new User($pdo);
    $user = $userObj->getById($user_id);
    if ($user) {
        $user_name = $user['name'] ?? $user_name;
        $photo_url = $user['profile_photo'] ?? null;
    }
}
?>

<?php if ($session->logged_in()): ?>
  <!-- STICKY PROFILE (only when logged in) -->
  <div class="fixed top-3 right-2 z-50 flex items-center justify-end md:w-auto profile-header">
    <div class="relative">
      <button id="profileBtn" 
  class="flex items-center justify-center w-10 h-10 bg-black text-white rounded-full font-semibold text-lg overflow-hidden 
         hover:ring-2 hover:ring-white hover:scale-105 transition duration-200 ease-in-out cursor-pointer">
        <?php if(!empty($photo_url)): ?>
          <img src="<?= htmlspecialchars('/' . ltrim($photo_url,'/')) ?>" alt="Profile" class="w-full h-full object-cover rounded-full">
        <?php else: ?>
          <?= strtoupper(substr($name, 0, 1)) ?>
        <?php endif; ?>
      </button>
      
      <div id="profileMenu" class="absolute right-0 mt-2 w-48 bg-white border rounded-2xl shadow-lg hidden overflow-hidden z-50">
        <a href="auth/my-profile.php" class="block px-4 py-2 text-sm hover:bg-gray-100">My Profile</a>
        <a href="auth/edit-profile.php" class="block px-4 py-2 text-sm hover:bg-gray-100">Edit</a>
        <div class="border-t my-1"></div>
        <a href="auth/logout.php" class="block px-4 py-2 text-sm text-red-600 font-semibold hover:bg-red-50">Logout</a>
      </div>
    </div>
  </div>

<?php else: ?>
  <!-- NON-STICKY LOGIN / REGISTER (for guests) -->
  <div class="relative md:absolute flex justify-center md:justify-end gap-3 z-40 md:left-1/2 md:-translate-x-1/2 
            md:top-5 md:right-5 md:left-auto md:translate-x-0 py-4 md:py-0">
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





(function () {

  function isVisible(el) {
    return !!el && window.getComputedStyle(el).display !== 'none' && el.offsetParent !== null;
  }


  document.addEventListener('click', function (e) {
    const profileBtn = document.getElementById('profileBtn');
    const profileMenu = document.getElementById('profileMenu');
    const cityMap = document.getElementById('cityMap');
    const showCityMapBtn = document.getElementById('showCityMapBtn');

    const clickedProfileBtn = profileBtn && profileBtn.contains(e.target);
    const clickedProfileMenu = profileMenu && profileMenu.contains(e.target);

    const clickedMap = cityMap && cityMap.contains(e.target);
    const clickedMapBtn = showCityMapBtn && showCityMapBtn.contains(e.target);

    // --- BEHAVIOUR:
    // 1) If user clicked profileBtn or profileMenu -> close the map (if open)
    if (clickedProfileBtn || clickedProfileMenu) {
      if (cityMap && isVisible(cityMap)) {
        cityMap.style.display = 'none';
      }
      // Let existing profile toggle logic run (do not stop propagation)
      return;
    }

    // 2) If user clicked map or mapBtn -> close profile menu (if open)
    if (clickedMap || clickedMapBtn) {
      if (profileMenu && !profileMenu.classList.contains('hidden')) {
        profileMenu.classList.add('hidden');
      }
      // If click was on mapBtn itself, also toggle the map (existing map button handler may do that)
      // We don't stopPropagation so existing handlers work.
      return;
    }

  }, true); // use capture phase to react early
})();


</script>
