<?php
include 'db.php';
include 'Spot.php';
include 'header.php'; // session already started

$spot_id = $_GET['id'] ?? null;
if (!$spot_id) die("No ID provided.");

// Fetch spot
$spotObj = new Spot($pdo);
$spot = $spotObj->getById($spot_id);
if (!$spot) die("Spot not found.");

// Fetch comments
$comments = $spotObj->getComments($spot_id);

// Handle Save to Favorites, Comments, Edit, Delete
$favorite_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Save to favorites
    if (isset($_POST['favorite'])) {
        if (!isset($_SESSION['user_id'])) {
            header("Location: login.php");
            exit();
        }
        $stmt = $pdo->prepare("INSERT IGNORE INTO favorites (user_id, spot_id) VALUES (:user_id, :spot_id)");
        $stmt->execute([
            'user_id' => $_SESSION['user_id'],
            'spot_id' => $spot_id
        ]);
        $favorite_msg = "Added to favorites!";
    }

    // Add new comment
    if (isset($_POST['comment'])) {
        if (!isset($_SESSION['user_id'])) {
            header("Location: login.php");
            exit();
        }
        $stmt = $pdo->prepare("INSERT INTO comments (user_id, spot_id, text) VALUES (:user_id, :spot_id, :text)");
        $stmt->execute([
            'user_id' => $_SESSION['user_id'],
            'spot_id' => $spot_id,
            'text' => $_POST['text']
        ]);
    }

    // Edit comment
    if (isset($_POST['edit_comment_id']) && isset($_POST['edit_text'])) {
        $stmt = $pdo->prepare("UPDATE comments SET text = :text WHERE id = :id AND user_id = :user_id");
        $stmt->execute([
            'text' => $_POST['edit_text'],
            'id' => $_POST['edit_comment_id'],
            'user_id' => $_SESSION['user_id']
        ]);
    }

    // Delete comment
    if (isset($_POST['delete_comment_id'])) {
        $stmt = $pdo->prepare("DELETE FROM comments WHERE id = :id AND user_id = :user_id");
        $stmt->execute([
            'id' => $_POST['delete_comment_id'],
            'user_id' => $_SESSION['user_id']
        ]);
    }

    // Refresh comments after any action
    $comments = $spotObj->getComments($spot_id);
}

// Track which comment is being edited inline
$edit_id = $_POST['edit_id'] ?? null;

?>

<main class="flex-1 bg-white min-h-screen py-4 md:py-20 max-w-7xl mx-auto flex flex-col gap-8">

  <!-- Spot title -->
  <div class="flex flex-col gap-2">
    <span class="text-gray-500 text-xs uppercase"><?=htmlspecialchars($spot['city'])?></span>
    <h1 class="text-3xl font-bold"><?=htmlspecialchars($spot['name'])?></h1>
  </div>

  <!-- Image + Actions -->
  <div class="flex flex-col md:flex-row gap-6">
    <div class="flex-1">
      <img src="<?=htmlspecialchars($spot['file_path'])?>" 
           alt="<?=htmlspecialchars($spot['name'])?>" 
           class="w-full h-[600px] md:h-[600px] object-cover">
    </div>

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

      <!-- Save to Favorites -->
      <form method="post">
        <button name="favorite" type="submit" class="bg-black text-white px-4 py-2 rounded-full w-full">
          Save to Favorites
        </button>
      </form>
      <?php if(!empty($favorite_msg)): ?>
        <p class="text-green-600 mt-2"><?=htmlspecialchars($favorite_msg)?></p>
      <?php endif; ?>

      <div class="mt-6 text-gray-700 text-sm">
        <?=htmlspecialchars($spot['description'])?>
      </div>
    </div>
  </div>

  <!-- Comments Section -->
  <section class="mt-8 w-full">

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
          <div class="flex gap-3 items-start bg-gray-100 p-3 rounded-xl">
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
                <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $c['user_id']): ?>
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

<?php include 'footer.php'; ?>
