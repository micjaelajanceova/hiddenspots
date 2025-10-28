<?php
// ------------------------
// EDIT PROFILE PAGE
// ------------------------
session_start();
include __DIR__ . '/../includes/db.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'User';

// --- HANDLE PROFILE PHOTO UPLOAD ---
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {

    $fileTmp = $_FILES['profile_photo']['tmp_name'];
    $fileName = uniqid() . '-' . basename($_FILES['profile_photo']['name']);
    $targetDir = __DIR__ . '/../uploads/profile/';
    if(!is_dir($targetDir)) mkdir($targetDir, 0755, true);
    $targetFile = $targetDir . $fileName;

    if(move_uploaded_file($fileTmp, $targetFile)){
        // Save relative path to DB (relative to project)
        $relativePath = 'uploads/profile/' . $fileName;

        $stmt = $pdo->prepare("UPDATE users SET profile_photo=? WHERE id=?");
        $stmt->execute([$relativePath, $user_id]);

        // Update session
        $_SESSION['profile_photo'] = $relativePath;
    }

    // Redirect before any HTML output
    header("Location: my-profile.php");
    exit();
}

// --- LOAD PROFILE PHOTO ---
$stmt = $pdo->prepare("SELECT profile_photo FROM users WHERE id=?");
$stmt->execute([$user_id]);
$user_photo = $stmt->fetchColumn();

// --- INCLUDE HEADER ---
include __DIR__ . '/../includes/header.php';
?>

<main class="flex-1 bg-white min-h-screen px-4 sm:px-6 lg:px-8 py-10">
  <h1 class="text-2xl font-bold mb-6">Edit Profile</h1>

  <form action="" method="post" enctype="multipart/form-data" class="flex flex-col gap-4 max-w-md">
    <label class="flex flex-col gap-2">
      Profile Photo:
      <input type="file" name="profile_photo" accept="image/*">
    </label>

    <?php if($user_photo && file_exists(__DIR__ . '/../' . $user_photo)): ?>
      <img src="../<?= htmlspecialchars($user_photo) ?>" alt="Profile" class="w-24 h-24 object-cover rounded-full">
    <?php else: ?>
      <div class="w-24 h-24 bg-black text-white flex items-center justify-center rounded-full text-2xl font-semibold">
        <?= strtoupper(substr($user_name, 0, 1)) ?>
      </div>
    <?php endif; ?>

    <button type="submit" class="bg-black text-white px-4 py-2 rounded-full hover:bg-gray-800 transition">Save Changes</button>
  </form>

  <div class="mt-8">
    <a href="my-profile.php" class="text-gray-600 hover:underline">← Back to Profile</a>
  </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
