<?php
include 'includes/header.php';
require_once 'includes/db.php';

// 1) TOP 6 za posledných 7 dní
$stmtWeek = $pdo->query("
    SELECT hs.*, COUNT(l.id) AS total_likes
    FROM hidden_spots hs
    LEFT JOIN likes l ON hs.id = l.spot_id
    WHERE hs.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY hs.id
    ORDER BY total_likes DESC
    LIMIT 6
");
$trendingWeek = $stmtWeek->fetchAll(PDO::FETCH_ASSOC);

// 2) TOP 9 celkovo
$stmtAll = $pdo->query("
    SELECT hs.*, COUNT(l.id) AS total_likes
    FROM hidden_spots hs
    LEFT JOIN likes l ON hs.id = l.spot_id
    GROUP BY hs.id
    ORDER BY total_likes DESC
    LIMIT 6
");
$trendingAll = $stmtAll->fetchAll(PDO::FETCH_ASSOC);
?>

<main class="flex-1 bg-white min-h-screen overflow-y-auto pt-2 md:pt-10">

<?php include 'includes/profile-header.php'; ?>

  <div class="w-full px-4 sm:px-6 lg:px-8">

    <!-- THIS WEEK TRENDING -->
    <?php if (count($trendingWeek) > 0): ?>
    <section class="mt-2 md:mt-6 pb-2 md:pb-20">
    <h1 class="text-4xl font-bold mb-3">🔥 Trending this week</h1>
  <h2 class="mt-1 text-gray-600">Most popular uploads from the last 7 days.</h2>

  <div 
    class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 overflow-x-auto snap-x snap-mandatory sm:overflow-visible flex-nowrap sm:grid flex sm:block">
    <?php foreach($trendingWeek as $index => $s): ?>
      <article 
        class="relative overflow-hidden bg-white shadow hover:shadow-lg flex flex-col h-full min-w-[80%] sm:min-w-0 snap-start">
        <a href="spot-view.php?id=<?=htmlspecialchars($s['id'])?>" class="flex flex-col h-full">
          
          <!-- IMAGE WRAPPER -->
          <div class="relative w-full h-96 bg-gray-200 overflow-hidden">

            <!-- BADGE  -->
            <?php if ($index < 3): ?>
                <div class="absolute top-3 left-3 bg-blue-300 text-white font-bold px-3 py-1 z-10">
                    TOP #<?= $index + 1 ?>
                </div>
                <?php endif; ?>

            <!-- IMAGE -->
            <img 
              src="<?=htmlspecialchars($s['file_path'])?>"
              alt="<?=htmlspecialchars($s['name'])?>"
              class="w-full h-full object-cover transform transition duration-300 hover:scale-105"
            >
          </div>

          <!-- TEXT -->
          <div class="p-3 flex flex-col justify-between flex-1">
            <div>
              <h2 class="font-semibold"><?=htmlspecialchars($s['name'])?></h2>
              <p class="text-sm text-gray-600 mt-1">
                <?=htmlspecialchars(mb_strimwidth($s['description'] ?? '',0,120,'...'))?>
              </p>
            </div>
            <div class="flex items-center justify-between mt-3 text-xs text-gray-400">
              <span><?=htmlspecialchars($s['city'])?> • <?=date("d M", strtotime($s['created_at']))?></span>
              <span>Likes:<?=intval($s['total_likes'])?></span>
            </div>
          </div>

        </a>
      </article>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

    <!-- ALL TIME TRENDING -->
    <section class="mt-2 md:mt-6 pb-2 md:pb-20">
    <h1 class=" text-4xl font-bold mb-3">Trending all-time</h1>
  <h2 class="mt-1 text-gray-600">Most liked posts ever uploaded.</h2>

  <div 
    class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 overflow-x-auto snap-x snap-mandatory sm:overflow-visible flex-nowrap sm:grid flex sm:block">
    <?php foreach($trendingAll as $index => $s): ?>
      <article 
        class="relative overflow-hidden bg-white shadow hover:shadow-lg flex flex-col h-full min-w-[80%] sm:min-w-0 snap-start">
        <a href="spot-view.php?id=<?=htmlspecialchars($s['id'])?>" class="flex flex-col h-full">
          
          <!-- IMAGE -->
          <div class="relative w-full h-96 bg-gray-200 overflow-hidden">

            <!-- BADGE -->
            <?php if ($index < 3): ?>
                <div class="absolute top-3 left-3 bg-blue-300 text-white font-bold px-3 py-1 z-10">
                    TOP #<?= $index + 1 ?>
                </div>
            <?php endif; ?>
            <img 
              src="<?=htmlspecialchars($s['file_path'])?>"
              alt="<?=htmlspecialchars($s['name'])?>"
              class="w-full h-full object-cover transform transition duration-300 hover:scale-105">
          </div>

          <div class="p-3 flex flex-col justify-between flex-1">
            <div>
              <h2 class="font-semibold"><?=htmlspecialchars($s['name'])?></h2>
              <p class="text-sm text-gray-600 mt-1">
                <?=htmlspecialchars(mb_strimwidth($s['description'] ?? '',0,120,'...'))?>
              </p>
            </div>
            <div class="flex items-center justify-between mt-3 text-xs text-gray-400">
              <span><?=htmlspecialchars($s['city'])?> • <?=date("d M", strtotime($s['created_at']))?></span>
              <span>Likes:<?=intval($s['total_likes'])?></span>
            </div>
          </div>

        </a>
      </article>
    <?php endforeach; ?>
  </div>
</section>

  </div>
</main>

<?php include 'includes/footer.php'; ?>
