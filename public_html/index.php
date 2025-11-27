<?php
include 'includes/db.php';
include 'includes/header.php';
include 'classes/spot.php';



require_once 'includes/db.php';
require_once 'classes/SessionHandle.php';

$session = new SessionHandle();

$spotObj = new Spot($pdo);

// HOT NEW PICTURES
$newest = $spotObj->getNewest(20);

// TRENDING (Sticky)
try {
  $stmt = $pdo->query("
  SELECT v.*, u.name AS user_name
  FROM view_hot_pictures v
  JOIN users u ON v.user_id = u.id
");

    $sticky = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $sticky = [];
}

// LATEST COMMENTS
try {
  $stmt = $pdo->query("SELECT * FROM view_latest_comments ORDER BY created_at DESC LIMIT 3");
  $latestComments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $latestComments = [];
}

?>

<main class="flex-1 bg-white min-h-screen overflow-y-auto pt-10">   
  <div class="w-full px-4 sm:px-6 lg:px-8">

<!-- LOGIN / SIGNUP -->
<?php include 'includes/profile-header.php'; ?>



   <!-- TRENDING (Sticky) -->
<section class="mt-12 pb-20">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-4xl font-bold mb-3">Trending</h1>
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

              
            <small class="absolute top-2 left-2 bg-black/60 text-white text-xs px-2 py-1 rounded">
                  @<?= htmlspecialchars($s['user_name'] ?? 'unknown') ?>
              </small>

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

    <div class="absolute inset-0">
      <img src="assets/img/index1.JPEG" 
           alt="Hidden spot background" 
           class="w-full h-full object-cover">

    </div>

    <div class="relative p-8 flex flex-col items-start max-w-xl">
      <h6 class="text-4xl font-bold text-white">About HiddenSpot</h6>
      <p3 class="mt-2 text-white">
        A photo-sharing app for secret city places. Discover, save and share hidden gems in your town.
      </p3>
     <a href="about.php" 
   class="inline-block mt-4 bg-white text-black px-5 py-2 rounded-full font-medium shadow 
          transition-transform transition-shadow duration-1000 ease-in-out hover:shadow-xl hover:scale-105">
   Learn more →
</a>



    </div>
  </div>
</section>




   <!-- HOT NEW PICTURES -->
<section class="mt-12 pb-20">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-4xl font-bold mb-3">Hot new pictures</h1>
      <h2 class="mt-1">Discover new pictures every day.</h2>
    </div>
    <a href="feed.php" class="inline-flex items-center gap-2 bg-black text-white px-4 py-2 rounded-full">See more →</a>
  </div>

  <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    <?php if(!empty($newest)): ?>
      <?php foreach(array_slice($newest, 0, 3) as $n): ?>
        <article class=" overflow-hidden bg-white shadow hover:shadow-lg flex flex-col h-full ">
          <a href="spot-view.php?id=<?=htmlspecialchars($n['id'])?>" class="flex flex-col h-full">
            <div class="w-full h-96 bg-gray-200 overflow-hidden ">


            <small class="absolute top-2 left-2 bg-black/60 text-white text-xs px-2 py-1 rounded">
                  @<?= htmlspecialchars($n['user_name'] ?? 'unknown') ?>
              </small>

              <img src="<?=htmlspecialchars($n['file_path'])?>" alt="<?=htmlspecialchars($n['name'])?>" class="w-full h-full object-cover transform transition duration-300 hover:scale-105">
            </div>
            <div class="p-3 flex flex-col justify-between flex-1">
              <div>
                <h2 class="font-semibold"><?=htmlspecialchars($n['name'])?></h2>
                <p class="text-sm text-gray-600 mt-1"><?=htmlspecialchars(mb_strimwidth($n['description'] ?? '',0,70,'...'))?></p>
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
  <h1 class="text-4xl font-bold mb-3">Latest comments</h1>
  <h2 class="mt-1">See who else loves these hidden places.</h2>

  <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-6">
    <?php
    $stmt = $pdo->query("
        SELECT c.*, u.name AS user_name, u.profile_photo
        FROM comments c
        JOIN users u ON c.user_id = u.id
        ORDER BY c.created_at DESC
        LIMIT 3
    ");
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($comments as $c):
        $photo_url = !empty($c['profile_photo']) ? htmlspecialchars($c['profile_photo']) : null;
    ?>
      <div class="bg-white shadow p-4 flex flex-col justify-between" style="box-shadow: 0 10px 20px rgba(0,0,0,0.05), 0 -5px 10px rgba(0,0,0,0.05);">
        <div class="flex items-center gap-3">
          <?php if($photo_url): ?>
            <a href="auth/user-profile.php?user_id=<?= $c['user_id'] ?>">
              <img src="<?= $photo_url ?>" alt="<?= htmlspecialchars($c['user_name']) ?>" class="w-10 h-10 rounded-full object-cover">
            </a>
          <?php else: ?>
            <a href="auth/user-profile.php?user_id=<?= $c['user_id'] ?>">
              <div class="w-10 h-10 bg-black rounded-full flex items-center justify-center text-white font-semibold text-lg">
                <?= strtoupper(substr($c['user_name'],0,1)) ?>
              </div>
            </a>
          <?php endif; ?>
          <div>
            <div class="font-medium"><?= htmlspecialchars($c['user_name']) ?></div>
            <div class="text-xs text-gray-400"><?= date("d M Y", strtotime($c['created_at'])) ?></div>
          </div>
        </div>
        <p class="text-sm text-gray-600 mt-3 whitespace-normal break-all"><?= htmlspecialchars(mb_strimwidth($c['text'],0,140,'...')) ?></p>
        <a href="spot-view.php?id=<?= htmlspecialchars($c['spot_id']) ?>" class=" mt-auto inline-block mt-3 bg-black text-white px-3 py-2 rounded-full text-sm self-start">See post →</a>
      </div>
    <?php endforeach; ?>
  </div>
</section>







<!-- UPLOAD CTA -->
<section class="mt-12 mb-20 lg:mb-5">
  <div class="bg-black p-10 flex flex-col items-center text-center">
    <h2 class="text-3xl font-bold text-white">Upload a new picture</h2>
    <h4 class="text-sm text-gray-300 mt-2">Share a secret spot with us.</h4>
    <button 
      id="openUploadModal" 
      class="mt-6 bg-white text-black px-8 py-3 rounded-full text-lg shadow hover:bg-gray-200 transition"
      onclick="document.getElementById('uploadModal').classList.remove('hidden')"
    >
      + Upload
    </button>
  </div>
</section>


  </div>
</main>


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



<?php include 'includes/footer.php'; ?>
