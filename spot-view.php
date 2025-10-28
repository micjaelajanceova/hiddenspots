<?php
include 'includes/db.php';
include 'classes/Spot.php';

$spot_id = $_GET['id'] ?? null;
if (!$spot_id) die("No ID provided.");

// Fetch spot
$spotObj = new Spot($pdo);
$spot = $spotObj->getById($spot_id);
if (!$spot) die("Spot not found.");

// Fetch spot owner's info
$stmt = $pdo->prepare("SELECT name, profile_photo FROM users WHERE id=?");
$stmt->execute([$spot['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$user_name = $user['name'] ?? 'Unknown';

// Fetch comments
$comments = $spotObj->getComments($spot_id);

// Admin check
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$edit_id = $_POST['edit_id'] ?? null;

// Handle POST for comments
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Add new comment
    if (isset($_POST['comment']) && isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("INSERT INTO comments (user_id, spot_id, text) VALUES (:user_id, :spot_id, :text)");
        $stmt->execute([
            'user_id' => $_SESSION['user_id'],
            'spot_id' => $spot_id,
            'text' => $_POST['text']
        ]);
        header("Location: spot-view.php?id=$spot_id#comments");
        exit();
    }

    // Edit comment
    if(isset($_POST['edit_comment_id'], $_POST['edit_text'])) {
        $stmt = $pdo->prepare($isAdmin
            ? "UPDATE comments SET text=:text WHERE id=:id"
            : "UPDATE comments SET text=:text WHERE id=:id AND user_id=:user_id");
        $stmt->execute($isAdmin ? [
            'text' => $_POST['edit_text'],
            'id' => $_POST['edit_comment_id']
        ] : [
            'text' => $_POST['edit_text'],
            'id' => $_POST['edit_comment_id'],
            'user_id' => $_SESSION['user_id']
        ]);
        header("Location: spot-view.php?id=$spot_id#comment-" . $_POST['edit_comment_id']);
        exit();
    }

    // Delete comment
    if(isset($_POST['delete_comment_id'])) {
        $stmt = $pdo->prepare($isAdmin
            ? "DELETE FROM comments WHERE id=:id"
            : "DELETE FROM comments WHERE id=:id AND user_id=:user_id");
        $stmt->execute($isAdmin ? ['id'=>$_POST['delete_comment_id']] : [
            'id'=>$_POST['delete_comment_id'],
            'user_id'=>$_SESSION['user_id']
        ]);
        header("Location: spot-view.php?id=$spot_id#comments");
        exit();
    }
}


include 'includes/header.php';

// Refresh comments
$comments = $spotObj->getComments($spot_id);

// Check if user liked/favorited
$user_id = $_SESSION['user_id'] ?? 0;
$liked = false;
$favorited = false;

if ($user_id) {
    // Like
    $stmt = $pdo->prepare("SELECT 1 FROM likes WHERE user_id=? AND spot_id=?");
    $stmt->execute([$user_id, $spot_id]);
    $liked = $stmt->fetch() ? true : false;

    // Favorite
    $stmt = $pdo->prepare("SELECT 1 FROM favorites WHERE user_id=? AND spot_id=?");
    $stmt->execute([$user_id, $spot_id]);
    $favorited = $stmt->fetch() ? true : false;
}
?>



<main class="flex-1 bg-gray-50 min-h-screen pt-8 pb-8 md:pb-12 px-4 md:px-8 max-w-7xl mx-auto flex flex-col gap-8">

<!-- LOGIN / SIGNUP -->
<?php include 'includes/profile-header.php'; ?>

  <!-- Spot title -->
  <div class="flex flex-col gap-2">
    <span class="text-gray-500 text-xs uppercase"><?=htmlspecialchars($spot['city'])?></span>
    <h1 class="text-3xl font-bold"><?=htmlspecialchars($spot['name'])?></h1>
  </div>

  <!-- Image + Actions -->
  <div class="flex flex-col md:flex-row gap-6 bg-white p-4 rounded-xl shadow">
    <div class="flex-1">
      <img src="<?=htmlspecialchars($spot['file_path'])?>" 
           alt="<?=htmlspecialchars($spot['name'])?>" 
           class="w-full h-[400px] md:h-[500px] object-cover rounded">
    </div>

    <div class="w-full md:w-72 flex flex-col gap-4">
      <div class="flex items-center gap-4 text-gray-600">
    <!-- Like (heart) -->
    <button id="likeBtn" class="relative w-30 h-30 flex items-center justify-center">
        <svg id="likeIcon" class="w-6 h-6 text-gray-400 transition-colors duration-300 <?= $liked ? 'text-red-600' : 'text-gray-400' ?>" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
        </svg>
    </button>

        <!-- Comment Icon -->
        <button onclick="document.getElementById('comments').scrollIntoView({behavior:'smooth'})" class="flex items-center gap-1">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2H7l-4 4V10a2 2 0 0 1 2-2h2"/>
          </svg>
          <?=count($comments)?>
        </button>

        <!-- Favorite Icon -->
<!-- Favorite (bookmark/star) -->
<button id="favBtn" class="relative w-10 h-10 flex items-center justify-center">

<svg id="favIcon" class="w-30 h-30 <?= $favorited ? 'text-yellow-500' : 'text-gray-400' ?>" fill="currentColor" viewBox="0 0 40 40">
    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
</svg>
    <!-- Animation checkmark -->
    <span id="favCheck" class="absolute inset-0 flex items-center justify-center opacity-0 pointer-events-none">
        <svg class="w-6 h-6 text-green-500 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
        </svg>
    </span>
</button>
      </div>

      <div class="mt-6 text-sm">
    <p class="mb-2">
    <a href="auth/user-profile.php?user_id=<?= $spot['user_id'] ?>" class="font-semibold text-blue-600 hover:underline">
        @<?=htmlspecialchars($user_name)?>
    </a>
    </p>
  <?=htmlspecialchars($spot['description'])?>
</div>
    </div>
  </div>

  <!-- Comments Section -->
  <section id="comments" class="mt-8 w-full">
    <!-- New comment -->
    <?php if(isset($_SESSION['user_id'])): ?>
      <form method="post" class="flex flex-col gap-2 mb-4">
        <textarea name="text" placeholder="Write your comment" 
                  class="p-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-400" required></textarea>
        <button type="submit" name="comment" 
                class="bg-green-500 text-white px-4 py-2 rounded-full font-semibold hover:bg-green-600 transition">
          Post
        </button>
      </form>
    <?php else: ?>
      <p class="text-gray-500 mb-4">Log in to comment.</p>
    <?php endif; ?>

<!-- Existing comments -->
<div class="flex flex-col gap-4">
  <?php if(!empty($comments)): ?>
    <?php foreach($comments as $c): ?>
      <div id="comment-<?=$c['id']?>" class="flex gap-3 items-start bg-gray-100 p-3 rounded-xl">
        <div class="w-10 h-10 bg-gray-400 rounded-full flex items-center justify-center text-white font-semibold">
          <?=strtoupper(substr($c['user_name'],0,1))?>
        </div>
        <div class="flex-1">

          <!-- Inline edit -->
          <?php if($edit_id == $c['id']): ?>
            <form method="post" class="mb-2">
              <input type="hidden" name="edit_comment_id" value="<?=$c['id']?>">
              <textarea name="edit_text" class="p-3 border border-gray-300 rounded-xl w-full focus:outline-none focus:ring-2 focus:ring-green-400"><?=htmlspecialchars($c['text'])?></textarea>
              <button type="submit" class="bg-blue-500 text-white px-4 py-1 rounded-full mt-2 text-sm">Update</button>
            </form>
          <?php else: ?>
            <p class="text-gray-700"><?=htmlspecialchars($c['text'])?></p>
          <?php endif; ?>

          <!-- Comment footer -->
          <div class="flex justify-between mt-2 text-xs text-gray-500">
            <span><?=date("d M Y", strtotime($c['created_at']))?></span>
            <?php if(isset($_SESSION['user_id']) && ($_SESSION['user_id']==$c['user_id'] || $isAdmin)): ?>
              <div class="flex gap-2">
                <!-- Edit -->
                <form method="post" style="display:inline;">
                  <input type="hidden" name="edit_id" value="<?=$c['id']?>">
                  <button type="submit" class="text-blue-600 hover:underline text-sm bg-transparent p-0">Edit</button>
                </form>
                <!-- Delete -->
                <form method="post" style="display:inline;" onsubmit="return confirm('Are you sure?')">
                  <input type="hidden" name="delete_comment_id" value="<?=$c['id']?>">
                  <button type="submit" class="text-red-600 hover:underline text-sm bg-transparent p-0">Delete</button>
                </form>
              </div>
            <?php endif; ?>
          </div>

        </div>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <p class="text-gray-500">No comments yet.</p>
  <?php endif; ?>
</div>

  </section>
</main>

<script>
// LIKE button
const likeBtn = document.getElementById('likeBtn');
const likeIcon = document.getElementById('likeIcon');

likeBtn?.addEventListener('click', ()=>{
    const spotId = <?= $spot_id ?>;
    fetch('actions/like.php', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'spot_id='+spotId
    }).then(r=>r.text()).then(res=>{
        if(res==='not_logged_in') return alert('You must be logged in to like!');
        if(res==='liked') {
            likeIcon.classList.remove('text-gray-400');
            likeIcon.classList.add('text-red-600');
        }
        if(res==='unliked') {
            likeIcon.classList.remove('text-red-600');
            likeIcon.classList.add('text-gray-400');
        }
    });
});

// FAVORITE button
const favBtn = document.getElementById('favBtn');
const favCheck = document.getElementById('favCheck');
const favIcon = document.getElementById('favIcon');

favBtn?.addEventListener('click', ()=>{
    const spotId = <?= $spot_id ?>;
    fetch('actions/favourite.php', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'spot_id='+spotId
    }).then(r=>r.text()).then(res=>{
        if(res==='not_logged_in') return alert('You must be logged in to favorite!');
        if(res==='added') {
            favIcon.classList.remove('text-gray-400');
            favIcon.classList.add('text-yellow-500');
            favCheck.classList.add('show');
            setTimeout(()=>favCheck.classList.remove('show'), 800);
        }
        if(res==='removed') {
            favIcon.classList.remove('text-yellow-500');
            favIcon.classList.add('text-gray-400');
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>
