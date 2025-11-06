<?php
session_start();
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

<!-- Image + Right Panel -->
<div class="flex flex-col md:flex-row gap-6 bg-white p-4 shadow">

  <!-- Spot Image -->
  <div class="relative flex-1 group overflow-hidden">
    <img src="<?= htmlspecialchars($spot['file_path']) ?>" 
         alt="<?= htmlspecialchars($spot['name']) ?>" 
         class="w-full h-[400px] md:h-[600px] object-cover transition duration-500" 
         id="spotImage">

    <!-- Toast over image -->
    <div id="favToast" 
         class="absolute inset-0 flex items-center justify-center text-white text-sm font-medium
                bg-black bg-opacity-0 opacity-0 transition-all duration-500 pointer-events-none">
      <span class="bg-black bg-opacity-50 px-4 py-2 rounded-full">Saved to favourites</span>
    </div>
  </div>

  <!-- Right Panel: Actions, Author, Description, Comments -->
  <div class="w-full md:w-2/5 flex flex-col gap-4 max-h-[600px] overflow-y-auto">

    

    <!-- Author Info -->
    <div class="flex items-center gap-2 mt-2">
      <?php 
        $user_photo_url = !empty($user['profile_photo']) ? '/hiddenspots/' . $user['profile_photo'] : null;
      ?>
      <?php if($user_photo_url && file_exists($_SERVER['DOCUMENT_ROOT'] . '/hiddenspots/' . $user['profile_photo'])): ?>
        <img src="<?= htmlspecialchars($user_photo_url) ?>" alt="<?= htmlspecialchars($user_name) ?>" class="w-10 h-10 rounded-full object-cover">
      <?php else: ?>
        <div class="w-10 h-10 bg-gray-400 rounded-full flex items-center justify-center text-white font-semibold">
          <?= strtoupper(substr($user_name,0,1)) ?>
        </div>
      <?php endif; ?>
      <a href="auth/user-profile.php?user_id=<?= $spot['user_id'] ?>" class="font-semibold text-blue-600 hover:underline">
        @<?=htmlspecialchars($user_name)?>
      </a>
    </div>

        <!-- Description -->
    <div class="text-sm text-gray-700">
      <?= htmlspecialchars($spot['description']) ?>
    </div>

    <!-- Like / Comment / Favorite Buttons -->
    <div class="flex items-center gap-4 text-gray-600">
      <!-- Like -->
      <button id="likeBtn" class="relative flex items-center gap-1 group">
        <svg id="likeIcon" class="w-6 h-6 transition-colors duration-300 <?= $liked ? 'text-red-600' : 'text-gray-400' ?>" fill="currentColor" viewBox="0 0 24 24">
          <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 
                   2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 
                   14.76 3 16.5 3 19.58 3 22 5.42 22 8.5
                   c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
        </svg>
        <span id="likeCount" class="text-sm text-gray-600 ml-1">
          <?php
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE spot_id=?");
            $stmt->execute([$spot_id]);
            echo $stmt->fetchColumn();
          ?>
        </span>
      </button>

      <!-- Comment Icon -->
      <button onclick="document.getElementById('comments').scrollIntoView({behavior:'smooth'})" class="flex items-center gap-1 text-gray-400 hover:text-gray-700 transition-colors">
        <svg class="w-6 h-6 text-gray-400 hover:text-gray-700" fill="currentColor" viewBox="0 0 24 24">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v10z"/>
        </svg>
        <span class="text-gray-400 font-extralarge"><?= count($comments) ?></span>
      </button>

      <!-- Favorite -->
      <button id="favBtn" class="relative w-10 h-10 flex items-center justify-center">
        <svg id="favIcon" class="w-6 h-6 <?= $favorited ? 'text-yellow-500' : 'text-gray-400' ?> " fill="currentColor" viewBox="0 0 24 24">
          <path d="M6 4c-1.1 0-2 .9-2 2v16l8-5.33L20 22V6c0-1.1-.9-2-2-2H6z"/>
        </svg>
      </button>
    </div>



    <!-- Comments Section -->
    <section id="comments" class="flex flex-col gap-4">
      <!-- New Comment -->
      <?php if(isset($_SESSION['user_id'])): ?>
        <form method="post" class="flex flex-col gap-2">
          <textarea name="text" placeholder="Write your comment" class="p-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-gray-200" required></textarea>
          <button type="submit" name="comment" class="bg-black text-white px-4 py-2 rounded-full font-semibold hover:bg-gray-200 hover:text-black transition">Post</button>
        </form>
      <?php else: ?>
        <p class="text-gray-500">Log in to comment.</p>
      <?php endif; ?>

      <!-- Existing Comments -->
      <div class="flex flex-col gap-3">
        <?php foreach($comments as $c): ?>
          <div id="comment-<?=$c['id']?>" class="flex gap-3 items-start bg-gray-100 p-3 rounded-xl">
            <div class="w-10 h-10 rounded-full overflow-hidden flex-shrink-0">
              <?php 
                $photo_url = !empty($c['profile_photo']) ? '/hiddenspots/' . $c['profile_photo'] : null;
              ?>
              <?php if($photo_url && file_exists($_SERVER['DOCUMENT_ROOT'] . '/hiddenspots/' . $c['profile_photo'])): ?>
                <img src="<?= htmlspecialchars($photo_url) ?>" alt="<?= htmlspecialchars($c['user_name']) ?>" class="w-full h-full object-cover rounded-full">
              <?php else: ?>
                <div class="w-full h-full bg-gray-400 flex items-center justify-center text-white font-semibold">
                  <?= strtoupper(substr($c['user_name'],0,1)) ?>
                </div>
              <?php endif; ?>
            </div>

            <div class="flex-1">
              <?php if($edit_id == $c['id']): ?>
                <form method="post" class="mb-2">
                  <input type="hidden" name="edit_comment_id" value="<?=$c['id']?>">
                  <textarea name="edit_text" class="p-3 border border-gray-300 rounded-xl w-full focus:outline-none focus:ring-2 focus:ring-green-400"><?=htmlspecialchars($c['text'])?></textarea>
                  <button type="submit" class="bg-blue-500 text-white px-4 py-1 rounded-full mt-2 text-sm">Update</button>
                </form>
              <?php else: ?>
                <p class="text-gray-700"><?=htmlspecialchars($c['text'])?></p>
              <?php endif; ?>

              <div class="flex justify-between mt-2 text-xs text-gray-500">
                <span><?=date("d M Y", strtotime($c['created_at']))?></span>
                <?php if(isset($_SESSION['user_id']) && ($_SESSION['user_id']==$c['user_id'] || $isAdmin)): ?>
                  <div class="flex gap-2">
                    <form method="post" style="display:inline;">
                      <input type="hidden" name="edit_id" value="<?=$c['id']?>">
                      <button type="submit" class="text-blue-600 hover:underline text-sm bg-transparent p-0">Edit</button>
                    </form>
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
      </div>
    </section>

  </div>
</div>
</main>


<script>
const favBtn = document.getElementById('favBtn');
const favIcon = document.getElementById('favIcon');
const favToast = document.getElementById('favToast');
const spotImage = document.getElementById('spotImage');

favBtn.addEventListener('click', () => {
  const spotId = <?= $spot_id ?>;
  fetch('actions/favourite.php', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: 'spot_id=' + spotId
  })
  .then(r => r.text())
  .then(res => {
    if (res === 'not_logged_in') return alert('You must be logged in to favorite!');

    if (res === 'added') {
      favIcon.classList.remove('text-gray-400');
      favIcon.classList.add('text-yellow-500');
      showFavToast("Saved to favourites");
    }

    if (res === 'removed') {
      favIcon.classList.remove('text-yellow-500');
      favIcon.classList.add('text-gray-400');
      showFavToast("Removed from favourites");
    }
  });
});

function showFavToast(message) {
  favToast.querySelector('span').textContent = message;

  // brief dark fade over the image
  favToast.classList.remove('opacity-0');
  favToast.classList.add('opacity-100');
  favToast.classList.add('bg-opacity-20'); // subtle darkening

  setTimeout(() => {
    favToast.classList.remove('bg-opacity-20');
    favToast.classList.add('bg-opacity-0');
  }, 500); // dark fade lasts only 0.5s

  setTimeout(() => {
    favToast.classList.remove('opacity-100');
    favToast.classList.add('opacity-0');
  }, 3000); // text fades out after 3s
}

const likeBtn = document.getElementById('likeBtn');
const likeIcon = document.getElementById('likeIcon');
const likeCount = document.getElementById('likeCount');

likeBtn.addEventListener('click', () => {
  const spotId = <?= $spot_id ?>;
  fetch('actions/like.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'spot_id=' + spotId
  })
  .then(res => res.text())
  .then(data => {
    if (data === 'not_logged_in') {
      alert('You must be logged in to like!');
      return;
    }

    if (data === 'liked') {
      likeIcon.classList.remove('text-gray-400');
      likeIcon.classList.add('text-red-600');
    } else if (data === 'unliked') {
      likeIcon.classList.remove('text-red-600');
      likeIcon.classList.add('text-gray-400');
    }

    // Refresh like count
    fetch('actions/like.php?count=' + spotId)
      .then(r => r.text())
      .then(count => {
        likeCount.textContent = count;
      });
  });
});


</script>

<?php include 'includes/footer.php'; ?>
