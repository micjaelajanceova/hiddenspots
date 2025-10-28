<?php
include '../includes/db.php';
session_start();

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'User';
$user_photo = $_SESSION['profile_photo'] ?? null;

// Handle profile photo upload
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK){
    $fileTmp = $_FILES['profile_photo']['tmp_name'];
    $fileName = uniqid() . '-' . basename($_FILES['profile_photo']['name']);
    $targetDir = '../uploads/profile/';
    if(!is_dir($targetDir)) mkdir($targetDir, 0755, true);
    $targetFile = $targetDir . $fileName;

    if(move_uploaded_file($fileTmp, $targetFile)){
        $stmt = $pdo->prepare("UPDATE users SET profile_photo=? WHERE id=?");
        $stmt->execute([$targetFile, $user_id]);
        $_SESSION['profile_photo'] = $targetFile;
        $user_photo = $targetFile;
    }
    header("Location: my-profile.php");
    exit();
}
?>

<main class="flex-1 bg-white min-h-screen px-4 sm:px-6 lg:px-8 py-10">
  <h1 class="text-2xl font-bold mb-6">Edit Profile</h1>

  <form action="" method="post" enctype="multipart/form-data" class="flex flex-col gap-4 max-w-md">
    <label class="flex flex-col gap-2">
      Profile Photo:
      <input type="file" name="profile_photo" accept="image/*">
    </label>
    <?php if($user_photo): ?>
      <img src="<?= htmlspecialchars($user_photo) ?>" alt="Profile" class="w-24 h-24 object-cover rounded-full">
    <?php endif; ?>
    <button type="submit" class="bg-black text-white px-4 py-2 rounded-full hover:bg-gray-800 transition">Save Changes</button>
  </form>

  <div class="mt-8">
    <a href="my-profile.php" class="text-gray-600 hover:underline">← Back to Profile</a>
  </div>
</main>

<?php include '../includes/footer.php'; ?>
