<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/classes/spot.php';
require_once __DIR__ . '/classes/session.php';

// session start
$session = new SessionHandle();


$spot_id = $_GET['id'] ?? null;
if (!$spot_id) die("No ID provided.");

// Fetch spot
$spotObj = new Spot($pdo);
$spot = $spotObj->getById($spot_id);
if (!$spot) die("Spot not found.");


// Time ago function
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;

    if ($difference < 60) return "about " . $difference . " seconds ago";
    if ($difference < 3600) return "about " . floor($difference/60) . " minutes ago";
    if ($difference < 86400) return "about " . floor($difference/3600) . " hours ago";

    return date("d.m.Y H:i", $timestamp);
}

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

// Edit spot description
if (isset($_POST['edit_spot_id'], $_POST['edit_description'])) {
    if ($isAdmin || $_SESSION['user_id'] == $spot['user_id']) {
        $spotObj->updateDescription($_POST['edit_spot_id'], $_POST['edit_description']);
        header("Location: spot-view.php?id=" . $_POST['edit_spot_id']);
        exit();
    }
}

// Delete spot
if (isset($_POST['delete_spot_id'])) {
    if ($isAdmin || $_SESSION['user_id'] == $spot['user_id']) {
        $spotObj->deleteSpot($_POST['delete_spot_id']);
        header("Location: feed.php");
        exit();
    }
}


require_once __DIR__ . '/includes/header.php';


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

// Fetch spot owner's info
$user_name = $spot['user_name'];
$photo_url = $spot['profile_photo'];

?>


<!----------------------- HTML ------------------------------>
<main class="flex-1 bg-white min-h-screen pt-2 md:pt-8  md:pb-12 px-4 md:px-8 flex flex-col">

<!-- LOGIN / SIGNUP -->
<?php include 'includes/profile-header.php'; ?>


<!-- Spot title -->
<span class="text-gray-500 block">
    <?= htmlspecialchars($spot['city']) ?>
</span>

  <h1 class="text-3xl font-bold"><?=htmlspecialchars($spot['name'])?></h1>

  <button id="showCityMapBtn" 
        class="inline-flex items-center gap-1 w-fit px-3 py-1 bg-black text-white rounded text-sm my-4">
    Show on Map
    <span id="mapArrow" class="inline-block transition-transform duration-300">▼</span>
</button>

<div id="cityMap" style="display:none; height:400px; margin-top:12px;"></div>


<!-- Image + Right Panel -->
<div class="flex flex-col lg:flex-row gap-6 bg-white p-4 shadow">

  <!-- Spot Image -->
  <div class="relative flex-1 group overflow-hidden">
    <img src="<?= htmlspecialchars($spot['file_path']) ?>" 
         alt="<?= htmlspecialchars($spot['name']) ?>" 
         class="w-full h-[400px] md:h-[600px] object-cover transition duration-500" 
         id="spotImage">


    <div id="favToast" 
         class="absolute inset-0 flex items-center justify-center text-white text-sm font-medium
                bg-black bg-opacity-0 opacity-0 transition-all duration-500 pointer-events-none">
      <span class="bg-black bg-opacity-50 px-4 py-2 rounded-full">Saved to favourites</span>
    </div>
  </div>

<div class="w-full lg:w-1/2 flex flex-col px-4 gap-4 max-h-[600px] overflow-y-auto relative">

    <!-- Three dots menu for spot -->
<?php if($session->getUserId() && ($session->getUserId() == $spot['user_id'] || $isAdmin)): ?>
<div class="absolute top-2 right-2">
  <button id="spotMenuBtn" class="text-gray-500 hover:text-gray-700 text-xl font-bold">⋯</button>
  <div id="spotMenu" class="hidden absolute right-0 mt-1 w-36 bg-white border border-gray-300 rounded shadow-md z-50">
    <!-- Edit Spot Description -->
    <button id="editDescMenuBtn" type="button" class="w-full text-left px-3 py-2 text-sm hover:bg-gray-100">
      Edit Description
    </button>

    <!-- Delete Spot -->
    <form method="post" onsubmit="return confirm('Are you sure you want to delete this spot?')">
      <input type="hidden" name="delete_spot_id" value="<?= $spot['id'] ?>">
      <button type="submit" class="w-full text-left px-3 py-2 text-sm hover:bg-gray-100 text-red-600">
        Delete Spot
      </button>
    </form>
  </div>
</div>
<?php endif; ?>


    <!-- Author Info -->
<div class="flex items-center gap-2 mt-2">
    <?php 
    $spot_user_name = $spot['user_name'];
    $spot_user_photo = !empty($spot['profile_photo']) ? $spot['profile_photo'] : null;
    ?>
    <?php if($spot_user_photo): ?>
    <a href="auth/user-profile.php?user_id=<?= $spot['user_id'] ?>">
        <img src="<?= htmlspecialchars($spot_user_photo) ?>" 
             alt="<?= htmlspecialchars($spot_user_name) ?>" 
             class="w-10 h-10 rounded-full object-cover">
    </a>
<?php else: ?>
    <a href="auth/user-profile.php?user_id=<?= $spot['user_id'] ?>">
        <div class="w-10 h-10 bg-gray-400 rounded-full flex items-center justify-center text-white font-semibold">
            <?= strtoupper(substr($user_name,0,1)) ?>
        </div>
    </a>
<?php endif; ?>


    <a href="auth/user-profile.php?user_id=<?= $spot['user_id'] ?>" 
       class="font-semibold text-blue-600 hover:underline">
        @<?= htmlspecialchars($spot['user_name']) ?>
    </a>
</div>

<div class="text-gray-500 text-sm">
    <span>Posted <?= timeAgo($spot['created_at']) ?></span>
</div>


<!-- Description -->
<div class="flex flex-col gap-1">
  <div id="spotDescription"
     contenteditable="false"
     class="text-sm text-gray-700 w-full border border-transparent rounded transition-all break-words whitespace-normal overflow-visible"
     data-spot-id="<?= $spot['id'] ?>">
    <?= nl2br(htmlspecialchars($spot['description'])) ?>
</div>


  <!-- Character count -->
  <div id="descCharCount" class="text-xs text-gray-500 text-right hidden">
    0 / 1100 characters
  </div>

  <!-- Hidden save button -->
  <button id="saveDescBtn" class="hidden mt-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow-sm text-sm font-semibold transition-all duration-200">
    Save Description
  </button>
</div>

  <div class="flex items-center gap-2 text-gray-600">

  <!-- Like -->
  <button id="likeBtn" class="flex items-center gap-1 px-2 py-2 rounded-lg hover:bg-gray-100 transition-colors">
    <svg id="likeIcon" class="w-6 h-6 transition-colors <?= $liked ? 'text-red-600' : 'text-gray-400' ?>" fill="currentColor" viewBox="0 0 24 24">
      <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 
               2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 
               14.76 3 16.5 3 19.58 3 22 5.42 22 8.5
               c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
    </svg>
    <span id="likeCount" class="text-sm text-gray-600">
      <?php
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE spot_id=?");
        $stmt->execute([$spot_id]);
        echo $stmt->fetchColumn();
      ?>
    </span>
  </button>

  <!-- Comment -->
  <button onclick="document.getElementById('comments').scrollIntoView({behavior:'smooth'})" 
          class="flex items-center gap-1 px-2 py-2 rounded-lg hover:bg-gray-100 transition-colors">
    <svg class="w-6 h-6 text-gray-400 hover:text-gray-700 mt-1" fill="currentColor" viewBox="0 0 23 23">
      <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v10z"/>
    </svg>
    <span class="text-sm text-gray-600">
      <?= count($comments) ?>
    </span>
  </button>

  <!-- Favorite -->
  <button id="favBtn" class="flex items-center justify-center w-10 h-10 rounded-lg hover:bg-gray-100 transition-colors">
    <svg id="favIcon" class="w-6 h-6 <?= $favorited ? 'text-yellow-500' : 'text-gray-400' ?>" fill="currentColor" viewBox="0 0 24 24">
      <path d="M6 4c-1.1 0-2 .9-2 2v16l8-5.33L20 22V6c0-1.1-.9-2-2-2H6z"/>
    </svg>
  </button>

</div>

    <!-- Comments Section -->
    <section id="comments" class="flex flex-col gap-4">
     
<?php if($session->getUserId()): ?>
<div class="relative">
    <form method="post">
        <textarea name="text" placeholder="Write your comment"
            id="commentText"
            class="w-full p-3 border border-gray-300 resize-none rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-200 pr-12 text-sm"></textarea>
        <button type="submit" name="comment"
            id="postText"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 font-semibold cursor-not-allowed transition-colors"
            disabled>
            Post
        </button>
    </form>
</div>
<?php endif; ?>

     <!-- Existing Comments -->
<div class="flex flex-col gap-3">
<?php foreach($comments as $c): ?>
    <div id="comment-<?= $c['id'] ?>" class="flex gap-3 items-start bg-gray-100 p-3 rounded-lg">
        <!-- Comment Author Photo -->
        <div class="w-10 h-10 rounded-full overflow-hidden flex-shrink-0">
            <?php $comment_user_photo = !empty($c['profile_photo']) ? $c['profile_photo'] : null; ?>
            <?php if($comment_user_photo): ?>
                <a href="auth/user-profile.php?user_id=<?= $c['user_id'] ?>">
                    <img src="<?= htmlspecialchars($comment_user_photo) ?>" 
                         alt="<?= htmlspecialchars($c['user_name']) ?>" 
                         class="w-full h-full object-cover rounded-full">
                </a>
            <?php else: ?>
                <a href="auth/user-profile.php?user_id=<?= $c['user_id'] ?>">
                    <div class="w-10 h-10 bg-gray-400 rounded-full flex items-center justify-center text-white font-semibold">
                        <?= strtoupper(substr($c['user_name'],0,1)) ?>
                    </div>
                </a>
            <?php endif; ?>
        </div>
        <!-- Comment content -->
        <div class="flex-1">
            <!-- User name -->
            <a href="auth/user-profile.php?user_id=<?= $c['user_id'] ?>" class="font-semibold text-blue-600 hover:underline block mb-1">
                @<?= htmlspecialchars($c['user_name']) ?>
            </a>

            <!-- Comment text -->
            <?php if($edit_id == $c['id']): ?>
                <form method="post" class="mb-2">
                    <input type="hidden" name="edit_comment_id" value="<?=$c['id']?>">
                    <textarea name="edit_text" class="p-3 border border-gray-300 rounded-xl w-full focus:outline-none focus:ring-2 focus:ring-green-400 text-sm"><?=htmlspecialchars($c['text'])?></textarea>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-1 rounded-full mt-2 text-sm">Update</button>
                </form>
            <?php else: ?>
                <div class="text-gray-700 text-sm break-words whitespace-normal break-all pr-3">
                <?=htmlspecialchars($c['text'])?>
            </div>
            <?php endif; ?>

            <div class="flex justify-between mt-2 text-xs text-gray-500">
                <span>Posted <?= timeAgo($c['created_at']) ?></span>
                <?php if($session->getUserId() && ($session->getUserId()==$c['user_id'] || $isAdmin)): ?> 
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
</main>

<!-- js for spot view -->
<script src="/assets/js/spot.js" defer></script>
<script src="/assets/js/map.js" defer></script>

<!-- Pass php variables to JavaScript -->
<script>
const spotId = <?= $spot_id ?>;
const spotLat = <?= $spot['latitude'] ?>;
const spotLng = <?= $spot['longitude'] ?>;
const lat = <?= $spot['latitude'] ?? '0' ?>;
const lng = <?= $spot['longitude'] ?? '0' ?>;
</script>


<?php require_once __DIR__ . '/includes/footer.php'; ?>
