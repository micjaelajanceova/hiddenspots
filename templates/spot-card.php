<?php
// očakáva sa $spot = [id, name, file_path, city, likes, created_at, description]

?>

<article class="spot-card">
  <a href="spot.php?id=<?= $spot['id'] ?>" style="color:inherit">
    <img class="spot-photo" src="assets/images/<?= $spot['file_path'] ?>" alt="<?= $spot['name'] ?>">
    <div style="padding:10px">
      <div class="spot-title"><?= $spot['name'] ?></div>
      <div class="spot-desc"><?= $spot['description'] ?></div>
    </div>
  </a>
  <div class="spot-meta">
    <div class="muted"><?= $spot['city'] ?> • <?= date("d M", strtotime($spot['created_at'])) ?></div>
    <div style="display:flex; gap:8px;">
      <form action="like.php" method="post" style="margin:0">
        <input type="hidden" name="spot_id" value="<?= $spot['id'] ?>">
        <button class="btn" type="submit" style="padding:6px 10px;font-size:13px">❤ <?= $spot['likes'] ?></button>
      </form>
      <a class="muted" href="spot.php?id=<?= $spot['id'] ?>#comments">💬 <?= $spot['comments_count'] ?></a>
    </div>
  </div>

  <?php
  // comments pre tento spot
  $stmtComments = $pdo->prepare("
    SELECT c.text, u.name AS user_name 
    FROM Comments c 
    JOIN Users u ON c.user_id = u.id 
    WHERE c.spot_id = ? 
    ORDER BY c.created_at DESC LIMIT 3
  ");
  $stmtComments->execute([$spot['id']]);
  $comments = $stmtComments->fetchAll();
  if($comments):
  ?>
    <div style="padding:10px 10px 0 10px; border-top:1px solid #eee;">
      <?php foreach($comments as $c): ?>
        <?php include 'comment.php'; ?>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</article>
