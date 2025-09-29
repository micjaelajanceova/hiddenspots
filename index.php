<?php
include 'db.php';
include 'header.php';
include 'spot.php';
include 'user.php';
include 'admin.php';

$spotObj = new Spot($pdo);
$newest = $spotObj->getNewest(20);
?>

<main class="flex-1 p-6 overflow-auto">
    <div class="columns-1 sm:columns-2 md:columns-3 gap-4">
        <?php foreach($newest as $spot): ?>
            <div class="break-inside-avoid mb-4 bg-white rounded-lg shadow hover:shadow-lg transition overflow-hidden">
                <a href="spot.php?id=<?= $spot['id'] ?>">
                    <img src="<?= $spot['file_path'] ?>" alt="<?= $spot['name'] ?>" class="w-full object-cover">
                    <div class="p-3">
                        <h3 class="font-semibold text-lg"><?= $spot['name'] ?></h3>
                        <p class="text-gray-600 text-sm"><?= $spot['description'] ?></p>
                        <div class="flex justify-between items-center text-gray-400 text-xs mt-2">
                            <span><?= $spot['city'] ?> • <?= date("d M", strtotime($spot['created_at'])) ?></span>
                            <span>❤ <?= $spot['likes'] ?> • 💬 <?= $spot['comments_count'] ?></span>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<!-- Upload Modal -->
<div id="uploadModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-96">
        <h2 class="text-lg font-bold mb-4">Upload a new spot</h2>
        <form id="uploadForm" action="upload.php" method="post" enctype="multipart/form-data" class="flex flex-col gap-3">
            <input type="file" name="photo" required>
            <input type="text" name="name" placeholder="Name" required class="p-2 border rounded">
            <input type="text" name="city" placeholder="City" required class="p-2 border rounded">
            <input type="text" name="address" placeholder="Address" required class="p-2 border rounded">
            <textarea name="description" placeholder="Short tip / description" class="p-2 border rounded"></textarea>
            <div class="flex gap-2 justify-end">
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Upload</button>
                <button type="button" id="closeModal" class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
const uploadBtn = document.getElementById('uploadBtn');
const uploadModal = document.getElementById('uploadModal');
const closeModal = document.getElementById('closeModal');
uploadBtn.onclick = () => uploadModal.classList.remove('hidden');
closeModal.onclick = () => uploadModal.classList.add('hidden');
</script>

<?php include 'footer.php'; ?>
