<?php 
include 'includes/db.php'; 
$city = $_GET['city'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM HiddenSpots WHERE city LIKE ?");
$stmt->execute(["%$city%"]);
$results = $stmt->fetchAll();
?>
<?php include 'includes/header.php'; ?>

<section class="card">
  <h2>Search results for "<?= htmlspecialchars($city) ?>"</h2>
  <div class="masonry">
    <?php foreach($results as $spot){ ?>
      <article class="spot-card">
        <a href="spot.php?id=<?= $spot['id'] ?>">
          <img class="spot-photo" src="<?= $spot['file_path'] ?>" alt="<?= $spot['name'] ?>">
          <div class="spot-title"><?= $spot['name'] ?></div>
          <div class="spot-desc"><?= $spot['description'] ?></div>
        </a>
      </article>
    <?php } ?>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
