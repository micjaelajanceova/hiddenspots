<?php
include 'db.php';
include 'header.php';
include 'spot.php';
include 'user.php';
include 'admin.php';

$spotObj = new Spot($pdo);
$newest = $spotObj->getNewest(20);
?>
 <div class="flex justify-between mb-6">
    <h2 class="text-3xl font-bold">DISCOVER WHAT OTHERS OVERLOOK.</h2>
    <p class="text-gray-500 font-semibold">SHARE YOUR SECRET SPOTS WITH THE WORLD.</p>
  </div>

  <!-- Newest pictures -->
  <h3 class="text-xl font-bold mb-4">Newest spots</h3>
  <div class="grid grid-cols-3 gap-4 mb-10">
    <?php
    require 'classes/Image.php';
    $imgModel = new ImageModel();
    $newest = $imgModel->getRecent(6);
    foreach($newest as $img): ?>
      <div>
        <img src="uploads/<?=htmlspecialchars($img['filename'])?>" 
             class="w-full rounded-lg shadow"/>
        <p><?=htmlspecialchars($img['title'])?></p>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Sticky / trending -->
  <h3 class="text-xl font-bold mb-4">Trending spots</h3>
  <div class="grid grid-cols-3 gap-4">
    <?php
    $trending = $imgModel->getSticky(6);
    foreach($trending as $img): ?>
      <div>
        <img src="uploads/<?=htmlspecialchars($img['filename'])?>" 
             class="w-full rounded-lg shadow"/>
        <p><?=htmlspecialchars($img['title'])?></p>
      </div>
    <?php endforeach; ?>
  </div>


<script>
const uploadBtn = document.getElementById('uploadBtn');
const uploadModal = document.getElementById('uploadModal');
const closeModal = document.getElementById('closeModal');
uploadBtn.onclick = () => uploadModal.classList.remove('hidden');
closeModal.onclick = () => uploadModal.classList.add('hidden');
</script>

<?php include 'footer.php'; ?>
