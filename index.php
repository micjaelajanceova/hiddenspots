<?php
include 'db.php';
include 'header.php';
include 'spot.php';
include 'user.php';
include 'admin.php';

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

    <!-- SEARCH -->
    <div class="mt-6">
      <form action="search.php" method="get" class="flex gap-3 items-center">
        <input name="query" type="search" placeholder="Search city — e.g. Copenhagen"
               class="flex-1 px-4 py-3 rounded-l-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-green-400" />
        <button type="submit" class="bg-black text-white px-4 py-3 rounded-r-lg font-semibold hover:opacity-95">
          Search
        </button>

        <?php if(isset($_SESSION['user_id'])): ?>
          <div class="ml-4 relative">
            <button id="profileBtn" class="flex items-center gap-2 bg-black text-white px-4 py-2 rounded-full">
              <?=htmlspecialchars($_SESSION['user_name'])?>
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
            <div id="profileMenu" class="absolute right-0 mt-2 w-40 bg-white border rounded shadow hidden">
              <a href="profile.php" class="block px-4 py-2 hover:bg-gray-100">My Profile</a>
              <a href="upload.php" class="block px-4 py-2 hover:bg-gray-100">Upload</a>
              <a href="logout.php" class="block px-4 py-2 hover:bg-gray-100">Logout</a>
            </div>
          </div>
        <?php else: ?>
          <a href="login.php" class="ml-4 bg-black text-white px-4 py-2 rounded-full">Login / Sign Up</a>
        <?php endif; ?>
      </form>
    </div>

    <!-- FILTER hamburger (Pinterest-like) -->
    <div class="mt-4">
      <button id="filterBtn" class="inline-flex items-center gap-2 px-3 py-2 rounded-full bg-gray-100 hover:bg-gray-200">
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4 7h16M4 12h16M4 17h16" stroke-width="2" stroke-linecap="round"/></svg>
        <span class="font-medium">Filters</span>
      </button>

      <div id="filterMenu" class="mt-3 hidden bg-white border rounded shadow p-4 w-[360px]">
        <div class="grid grid-cols-2 gap-3">
          <button class="py-2 px-3 rounded bg-gray-100">All</button>
          <button class="py-2 px-3 rounded bg-gray-100">Trending</button>
          <button class="py-2 px-3 rounded bg-gray-100">Newest</button>
          <button class="py-2 px-3 rounded bg-gray-100">Sticky</button>
          <button class="py-2 px-3 rounded bg-gray-100">Nature</button>
          <button class="py-2 px-3 rounded bg-gray-100">Cafés</button>
        </div>
        <div class="mt-3 text-sm text-gray-500">Click a filter to reload the feed (connect to PHP or AJAX).</div>
      </div>
    </div>

    <!-- TRENDING (Sticky) -->
    <section class="mt-8">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-2xl font-bold tracking-tight">TRENDING</h2>
          <p class="text-sm text-gray-500 mt-1">Explore what most people miss.</p>
        </div>
        <a href="trending.php" class="inline-flex items-center gap-2 bg-black text-white px-4 py-2 rounded-full">See more →</a>
      </div>

      <div class="mt-6 grid grid-cols-2 sm:grid-cols-4 gap-4">
        
        <?php if(!empty($sticky)): ?>
          <?php foreach($sticky as $s): ?>
            <a href="spot.php?id=<?=htmlspecialchars($s['id'])?>" class="group block rounded-lg overflow-hidden shadow hover:shadow-lg">
              <div class="w-full h-40 bg-gray-200">
                <img src="<?=htmlspecialchars($s['file_path'])?>" alt="<?=htmlspecialchars($s['name'])?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
              </div>
              <div class="px-3 py-2">
                <div class="font-semibold text-sm"><?=htmlspecialchars($s['name'])?></div>
                <div class="text-xs text-gray-400"><?=htmlspecialchars($s['city'])?> • <?=date("d M", strtotime($s['created_at']))?></div>
              </div>
            </a>
          <?php endforeach; ?>
        <?php else: ?>
          <?php for($i=0;$i<4;$i++): ?>
            <div class="overflow-hidden bg-gray-100 h-40"></div>
          <?php endfor; ?>
        <?php endif; ?>
      </div>
    </section>

    <!-- ABOUT -->
    <section class="mt-12">
      <div class="bg-gray-100 p-8 flex items-center gap-8">
        <div class="flex-1">
          <h3 class="text-2xl font-bold">ABOUT HIDDEN SPOTS</h3>
          <p class="text-sm text-gray-600 mt-2">A photo-sharing app for secret city places — discover, save and share hidden gems in your town.</p>
          <a href="about.php" class="inline-block mt-4 bg-black text-white px-5 py-2 rounded-full">Learn more →</a>
        </div>
        <div class="w-1/3 bg-gray-200 h-36 flex items-center justify-center text-gray-500">Visual / promo</div>
      </div>
    </section>

    <!-- FEED (Newest preview) -->
    <section class="mt-12">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-2xl font-bold">HOT NEW PICTURES</h2>
          <p class="text-sm text-gray-500 mt-1">Discover new pictures every day.</p>
        </div>
        <a href="newest.php" class="inline-flex items-center gap-2 bg-black text-white px-4 py-2 rounded-full">See more →</a>
      </div>

      <div class="mt-6 columns-1 sm:columns-2 lg:columns-3 gap-4 space-y-4">
        <?php if(!empty($newest)): ?>
          <?php foreach($newest as $n): ?>
            <article class="break-inside-avoid rounded-lg overflow-hidden bg-white shadow hover:shadow-lg">
              <a href="spot.php?id=<?=htmlspecialchars($n['id'])?>">
                <img src="<?=htmlspecialchars($n['file_path'])?>" alt="<?=htmlspecialchars($n['name'])?>" class="w-full object-cover">
                <div class="p-3">
                  <h3 class="font-semibold"><?=htmlspecialchars($n['name'])?></h3>
                  <p class="text-sm text-gray-600 mt-1"><?=htmlspecialchars(mb_strimwidth($n['description'],0,120,'...'))?></p>
                  <div class="flex items-center justify-between mt-3 text-xs text-gray-400">
                    <span><?=htmlspecialchars($n['city'])?> • <?=date("d M", strtotime($n['created_at']))?></span>
                    <span>❤️ <?=intval($n['likes'])?> • 💬 <?=intval($n['comments_count'])?></span>
                  </div>
                </div>
              </a>
            </article>
          <?php endforeach; ?>
        <?php else: ?>
          <?php for($i=0;$i<6;$i++): ?>
            <div class="break-inside-avoid overflow-hidden bg-gray-100 h-48"></div>
          <?php endfor; ?>
        <?php endif; ?>
      </div>
    </section>

    <!-- LATEST COMMENTS -->
    <section class="mt-12">
      <h2 class="text-2xl font-bold">LATEST COMMENTS</h2>
      <p class="text-sm text-gray-500 mt-1">See who else loves these hidden places.</p>

      <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-6">
        <?php if(!empty($latestComments)): ?>
          <?php foreach($latestComments as $c): ?>
            <div class="bg-white shadow rounded-lg p-4">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gray-200 rounded-full"></div>
                <div>
                  <div class="font-medium"><?=htmlspecialchars($c['user_name'])?></div>
                  <div class="text-xs text-gray-400"><?=date("d M Y", strtotime($c['created_at']))?></div>
                </div>
              </div>
              <p class="text-sm text-gray-600 mt-3"><?=htmlspecialchars(mb_strimwidth($c['text'],0,140,'...'))?></p>
              <a href="spot.php?id=<?=htmlspecialchars($c['spot_id'])?>" class="inline-block mt-3 bg-black text-white px-3 py-2 rounded-full text-sm">See post →</a>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="bg-gray-100  h-28"></div>
          <div class="bg-gray-100  h-28"></div>
          <div class="bg-gray-100  h-28"></div>
        <?php endif; ?>
      </div>
    </section>

    <!-- UPLOAD CTA -->
    <section class="mt-12 mb-20">
      <div class="bg-gray-100 p-8 flex items-center justify-between">
        <div>
          <h3 class="text-2xl font-bold">UPLOAD A NEW PICTURE</h3>
          <p class="text-sm text-gray-600 mt-2">Share a secret spot with us.</p>
        </div>
        <div>
          <button id="uploadBtn2" class="bg-black text-white px-6 py-3 rounded-full text-lg">+ Upload</button>
        </div>
      </div>
    </section>

  </div>
</main>

<!-- Upload Modal -->
<div id="uploadModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
  <div class="bg-white rounded-lg p-6 w-full max-w-lg mx-4">
    <h2 class="text-xl font-bold mb-3">Upload a new spot</h2>
    <form action="upload.php" method="post" enctype="multipart/form-data" class="space-y-3">
      <input type="file" name="photo" accept="image/*" required class="block w-full" />
      <input type="text" name="name" placeholder="Name" required class="w-full border rounded p-2" />
      <input type="text" name="city" placeholder="City" required class="w-full border rounded p-2" />
      <input type="text" name="address" placeholder="Address" class="w-full border rounded p-2" />
      <textarea name="description" placeholder="Short description / tip" class="w-full border rounded p-2"></textarea>
      <div class="flex justify-end gap-2">
        <button type="button" id="cancelUpload" class="px-4 py-2 rounded border">Cancel</button>
        <button type="submit" class="px-4 py-2 rounded bg-black text-white">Upload</button>
      </div>
    </form>
  </div>
</div>

<script>
// toggle filter menu
document.getElementById('filterBtn')?.addEventListener('click', () => {
  document.getElementById('filterMenu')?.classList.toggle('hidden');
});

// upload modal toggles
const uploadBtn2 = document.getElementById('uploadBtn2');
const uploadModal = document.getElementById('uploadModal');
const cancelUpload = document.getElementById('cancelUpload');

if(uploadBtn2) uploadBtn2.addEventListener('click', () => uploadModal.classList.remove('hidden'));
if(cancelUpload) cancelUpload.addEventListener('click', () => uploadModal.classList.add('hidden'));
uploadModal?.addEventListener('click', (e) => {
  if(e.target === uploadModal) uploadModal.classList.add('hidden');
});
</script>

<?php include 'footer.php'; ?>
