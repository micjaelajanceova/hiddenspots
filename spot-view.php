<?php
include 'db.php';
include 'spot.php';
include 'header.php';

$spot_id = $_GET['id'] ?? null;
if (!$spot_id) die("No ID provided.");

$spotObj = new Spot($pdo);
$spot = $spotObj->getById($spot_id);
if (!$spot) die("Spot with ID $spot_id not found.");

$comments = $spotObj->getComments($spot_id);
?>

<main class="bg-white min-h-screen p-4 md:p-12 max-w-10xl mx-auto flex flex-col gap-8 w-full">

  <!-- NAZOV A LOKACIA -->
  <div class="flex flex-col gap-2">
    <span class="text-gray-500 text-xs uppercase"><?=htmlspecialchars($spot['city'])?></span>
    <h1 class="text-3xl font-bold"><?=htmlspecialchars($spot['name'])?></h1>
  </div>

  <!-- OBRAZOK + AKCIE -->
  <div class="flex flex-col md:flex-row gap-6">
    
    <!-- OBRAZOK -->
    <div class="flex-1 relative">
      <img src="<?=htmlspecialchars($spot['file_path'])?>" 
           alt="<?=htmlspecialchars($spot['name'])?>" 
           class="w-full h-[600px] md:h-[600px] object-cover">
    </div>

    <!-- AKCIE -->
    <div class="w-full md:w-72 flex flex-col gap-4">
      <div class="flex items-center gap-3 text-gray-600">
        <span class="flex items-center gap-1">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
          </svg>
          <?=intval($spot['likes'])?>
        </span>
        <span class="flex items-center gap-1">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H7l-4 4V10a2 2 0 0 1 2-2h2"/>
          </svg>
          <?=count($comments)?>
        </span>
      </div>
      <button class="bg-black text-white px-4 py-2 rounded-full">Save</button>
      
      <div class="mt-6 text-gray-700 text-sm">
        <?=htmlspecialchars($spot['description'])?>
      </div>
    </div>

  </div>

  <!-- KOMENTARE -->
  <section class="mt-8 w-full">
    <form action="add_comment.php" method="post" class="flex gap-2 mb-4">
      <input type="hidden" name="spot_id" value="<?=$spot_id?>">
      <input type="text" name="text" placeholder="Write your comment" class="flex-1 p-3 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-green-400" required>
      <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded-full font-semibold hover:bg-green-600 transition">Post</button>
    </form>

    <div class="flex flex-col gap-4">
      <?php if(!empty($comments)): ?>
        <?php foreach($comments as $c): ?>
          <div class="flex gap-3 items-start bg-gray-100 p-3 rounded-xl">
            <div class="w-10 h-10 bg-gray-400 rounded-full flex items-center justify-center text-white font-semibold"><?=strtoupper(substr($c['user_name'],0,1))?></div>
            <div class="flex-1">
              <div class="flex justify-between">
                <span class="font-medium"><?=htmlspecialchars($c['user_name'])?></span>
                <span class="text-xs text-gray-500"><?=date("d M Y", strtotime($c['created_at']))?></span>
              </div>
              <p class="text-gray-700 mt-1"><?=htmlspecialchars($c['text'])?></p>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="text-gray-500">No comments yet.</p>
      <?php endif; ?>
    </div>
  </section>

</main>


<?php include 'footer.php'; ?>
