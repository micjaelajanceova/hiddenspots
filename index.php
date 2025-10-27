<?php
include 'includes/db.php';
include 'includes/header.php';
include 'classes/spot.php';



require_once 'includes/db.php';
require_once 'classes/SessionHandle.php';
// session_start() je už v header.php
$session = new SessionHandle();

$spotObj = new Spot($pdo);

// HOT NEW PICTURES (Newest)
$newest = $spotObj->getNewest(20);

// TRENDING (Sticky) - from view_hot_pictures
try {
    $stmt = $pdo->query("SELECT * FROM view_hot_pictures");
    $sticky = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $sticky = [];
}

// LATEST COMMENTS - from view_latest_comments
try {
    $stmt = $pdo->query("SELECT * FROM view_latest_comments");
    $latestComments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $latestComments = [];
}
?>

<main class="flex-1 bg-white min-h-screen overflow-y-auto">   
  <div class="w-full px-4 sm:px-6 lg:px-8">

<!-- LOGIN / SIGNUP -->
<div class="mt-6 flex justify-end gap-2">
  <?php if(isset($_SESSION['user_id'])): ?>
    <div class="relative">
      <!-- Avatar s prvým písmenom mena (rovnaký štýl ako pri komentároch) -->
      <button id="profileBtn" class="flex items-center justify-center w-10 h-10 bg-black text-white rounded-full font-semibold text-lg">
        <?=strtoupper(substr($_SESSION['user_name'], 0, 1))?>
      </button>

      <!-- Dropdown menu -->
      <div id="profileMenu" class="absolute right-0 mt-2 w-48 bg-white border rounded-2xl shadow-lg hidden overflow-hidden z-50">
        <a href="profile.php" class="block px-4 py-2 text-sm hover:bg-gray-100">My Profile</a>
        <a href="upload.php" class="block px-4 py-2 text-sm hover:bg-gray-100">Upload</a>
        <div class="border-t my-1"></div>
        <a href="auth/logout.php" class="block px-4 py-2 text-sm text-red-600 font-semibold hover:bg-red-50">Logout</a>
      </div>
    </div>
  <?php else: ?>
    <!-- Tlačidlá pre neprihláseného používateľa -->
    <a href="auth/login.php?action=login" class="bg-black text-white px-4 py-2 rounded-full">Login</a>
    <a href="auth/login.php?action=register" class="bg-gray-200 text-black px-4 py-2 rounded-full">Register</a>
  <?php endif; ?>
</div>





   <!-- TRENDING (Sticky) -->
<section class="mt-12 pb-20">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="mb-3">TRENDING</h1>
      <h2 class="mt-1">Explore what most people miss.</h2>
    </div>
    <a href="trending.php" class="inline-flex items-center gap-2 bg-black text-white px-4 py-2 rounded-full">See more →</a>
  </div>

  <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    <?php if(!empty($sticky)): ?>
      <?php foreach(array_slice($sticky, 0, 3) as $s): ?>
        <article class="overflow-hidden bg-white shadow hover:shadow-lg flex flex-col h-full">
          <a href="spot-view.php?id=<?=htmlspecialchars($s['id'])?>" class="flex flex-col h-full">
            <div class="w-full h-96 bg-gray-200 overflow-hidden">
              <img src="<?=htmlspecialchars($s['file_path'])?>" alt="<?=htmlspecialchars($s['name'])?>" class="w-full h-full object-cover transform transition duration-300 hover:scale-105">
            </div>
            <div class="p-3 flex flex-col justify-between flex-1">
              <div>
                <h2 class="font-semibold"><?=htmlspecialchars($s['name'])?></h2>
                <p class="text-sm text-gray-600 mt-1"><?=htmlspecialchars(mb_strimwidth($s['description'] ?? '',0,120,'...'))?></p>
              </div>
              <div class="flex items-center justify-between mt-3 text-xs text-gray-400">
                <span><?=htmlspecialchars($s['city'])?> • <?=date("d M", strtotime($s['created_at'] ?? date("Y-m-d"))) ?></span>
                <span>Likes: <?=intval($s['likes'])?> • Comments: <?=intval($s['comments_count'])?></span>
              </div>
            </div>
          </a>
        </article>
      <?php endforeach; ?>
    <?php else: ?>
      <?php for($i=0;$i<3;$i++): ?>
        <div class="overflow-hidden bg-gray-100 h-96 rounded-lg"></div>
      <?php endfor; ?>
    <?php endif; ?>
  </div>
</section>

<!-- ABOUT -->
<section class="mt-12 pb-20">
  <div class="relative overflow-hidden shadow-md h-96">
    <!-- Background image -->
    <div class="absolute inset-0">
      <img src="assets/img/hiddenspot9.jpg" 
           alt="Hidden spot background" 
           class="w-full h-full object-cover">

    </div>

    <!-- Text content -->
    <div class="relative p-8 flex flex-col items-start max-w-xl">
      <h1 class="text-3xl">ABOUT HIDDEN SPOTS</h1>
      <p class="mt-2">
        A photo-sharing app for secret city places. Discover, save and share hidden gems in your town.
      </p>
      <a href="about.php" 
         class="inline-block mt-4 bg-black text-white px-5 py-2 rounded-full font-medium shadow">
         Learn more →
      </a>
    </div>
  </div>
</section>




   <!-- HOT NEW PICTURES -->
<section class="mt-12 pb-20">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-bold mb-3">HOT NEW PICTURES</h1>
      <h2 class="mt-1">Discover new pictures every day.</h2>
    </div>
    <a href="newest.php" class="inline-flex items-center gap-2 bg-black text-white px-4 py-2 rounded-full">See more →</a>
  </div>

  <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    <?php if(!empty($newest)): ?>
      <?php foreach(array_slice($newest, 0, 3) as $n): ?>
        <article class=" overflow-hidden bg-white shadow hover:shadow-lg flex flex-col h-full ">
          <a href="spot-view.php?id=<?=htmlspecialchars($n['id'])?>" class="flex flex-col h-full">
            <div class="w-full h-96 bg-gray-200 overflow-hidden ">
              <img src="<?=htmlspecialchars($n['file_path'])?>" alt="<?=htmlspecialchars($n['name'])?>" class="w-full h-full object-cover transform transition duration-300 hover:scale-105">
            </div>
            <div class="p-3 flex flex-col justify-between flex-1">
              <div>
                <h2 class="font-semibold"><?=htmlspecialchars($n['name'])?></h2>
                <p class="text-sm text-gray-600 mt-1"><?=htmlspecialchars(mb_strimwidth($n['description'] ?? '',0,120,'...'))?></p>
              </div>
              <div class="flex items-center justify-between mt-3 text-xs text-gray-400">
                <span><?=htmlspecialchars($n['city'])?> • <?=date("d M", strtotime($n['created_at']))?></span>
                <span>Likes: <?=intval($n['likes'])?> • Comments: <?=intval($n['comments_count'])?></span>
              </div>
            </div>
          </a>
        </article>
      <?php endforeach; ?>
    <?php else: ?>
      <?php for($i=0;$i<3;$i++): ?>
        <div class="overflow-hidden bg-gray-100 h-96 rounded-lg"></div>
      <?php endfor; ?>
    <?php endif; ?>
  </div>
</section>



  <!-- LATEST COMMENTS -->
<section class="mt-12 pb-20">
  <h1 class="mb-3">LATEST COMMENTS</h1>
  <h2 class="mt-1">See who else loves these hidden places.</h2>

  <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-6">
    <?php if(!empty($latestComments)): ?>
      <?php foreach(array_slice($latestComments, 0, 3) as $c): ?>
        <div class="bg-white shadow p-4" style="box-shadow: 0 10px 20px rgba(0,0,0,0.05), 0 -5px 10px rgba(0,0,0,0.05);">
          <div class="flex items-center gap-3">
            <!-- Prvé písmeno mena namiesto avataru -->
            <div class="w-10 h-10 bg-gray-400 rounded-full flex items-center justify-center text-white font-semibold text-lg">
              <?=strtoupper(substr($c['user_name'],0,1))?>
            </div>
            <div>
              <div class="font-medium"><?=htmlspecialchars($c['user_name'])?></div>
              <div class="text-xs text-gray-400"><?=date("d M Y", strtotime($c['created_at']))?></div>
            </div>
          </div>
          <p class="text-sm text-gray-600 mt-3"><?=htmlspecialchars(mb_strimwidth($c['text'],0,140,'...'))?></p>
          <a href="spot-view.php?id=<?=htmlspecialchars($c['spot_id'])?>" class="inline-block mt-3 bg-black text-white px-3 py-2 rounded-full text-sm">See post →</a>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="bg-gray-100 h-28"></div>
      <div class="bg-gray-100 h-28"></div>
      <div class="bg-gray-100 h-28"></div>
    <?php endif; ?>
  </div>
</section>



  <!-- UPLOAD CTA -->
<section class="mt-12 mb-20 lg:mb-5">
  <div class="bg-gray-800 p-10 flex flex-col items-center text-center">
    <h2 class="text-3xl font-bold text-white">UPLOAD A NEW PICTURE</h2>
    <h4 class="text-sm text-gray-300 mt-2">Share a secret spot with us.</h4>
    <a href="upload.php" id="uploadBtn2" class="mt-6 bg-white text-black px-8 py-3 rounded-full text-lg shadow hover:bg-gray-200 transition">
      + Upload
    </a>
  </div>
</section>


  </div>
</main>
<script>
// PROFILE MENU TOGGLE
const profileBtn = document.getElementById('profileBtn');
const profileMenu = document.getElementById('profileMenu');

if (profileBtn && profileMenu) {
    // klik na meno používateľa → otvorí menu
    profileBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        profileMenu.classList.toggle('hidden');
    });

    // klik mimo menu → zatvorí menu
    document.addEventListener('click', (e) => {
        if (!profileMenu.contains(e.target) && !profileBtn.contains(e.target)) {
            profileMenu.classList.add('hidden');
        }
    });
}
</script>



<?php include 'includes/footer.php'; ?>
