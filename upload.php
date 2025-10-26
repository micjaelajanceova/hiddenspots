<?php
// upload.php
require_once 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$error = null;
$success = null;

// require login
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

// categories for select
$categories = [
    'Nature',
    'Cafés',
    'Urban',
    'Architecture',
    'Viewpoint',
    'Restaurant',
    'Other'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int) $_SESSION['user_id'];
    $name = trim($_POST['name'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');

    // basic validation
    if ($name === '' || $city === '' || $category === '') {
        $error = "Please fill required fields: Name, City and Category.";
    } elseif (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        $error = "Please select a photo to upload (max 5MB).";
    } else {
        // upload directory (must exist)
        $targetDir = __DIR__ . '/uploads/';
        if (!is_dir($targetDir)) {
            $error = "Uploads directory does not exist. Please create 'uploads/' with write permissions.";
        }

        if (!$error) {
            $originalName = basename($_FILES['photo']['name']);
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif','webp'];

            if (!in_array($ext, $allowed, true)) {
                $error = "Invalid file type. Allowed: " . implode(', ', $allowed);
            } elseif ($_FILES['photo']['size'] > 5 * 1024 * 1024) {
                $error = "File too large. Max 5MB.";
            } else {
                // unique filename
                $safeName = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                $targetPath = $targetDir . $safeName;
                $webPath = 'uploads/' . $safeName;

                if (!move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
                    $error = "Error moving uploaded file.";
                } else {
                    // insert into DB
                    try {
                        $sql = "INSERT INTO hidden_spots
                                (user_id, name, description, city, address, type, file_path, created_at)
                                VALUES (:user_id, :name, :description, :city, :address, :type, :file_path, NOW())";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([
                            ':user_id' => $user_id,
                            ':name' => $name,
                            ':description' => $description,
                            ':city' => $city,
                            ':address' => $address,
                            ':type' => $category,
                            ':file_path' => $webPath
                        ]);

                        header("Location: index.php?upload=success");
                        exit();
                    } catch (PDOException $e) {
                        if (file_exists($targetPath)) unlink($targetPath);
                        $error = "Database error: " . $e->getMessage();
                    }
                }
            }
        }
    }
}

include 'header.php';
?>

<main class="flex-1 bg-white p-8 min-h-screen">
  <div class="max-w-3xl mx-auto">
    <h1 class="text-3xl font-bold mb-6">Upload a New Hidden Spot</h1>

    <?php if ($error): ?>
      <div class="mb-4 bg-red-100 text-red-700 p-3 rounded"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="upload.php" method="post" enctype="multipart/form-data" class="space-y-4">
      <div>
        <label class="block font-medium mb-1">Photo <span class="text-gray-400">(max 5MB)</span></label>
        <input type="file" name="photo" accept="image/*" required class="w-full border rounded p-2" />
        <img id="preview" class="mt-3 w-48 h-48 object-cover rounded hidden" />
      </div>

      <div>
        <label class="block font-medium mb-1">Name</label>
        <input type="text" name="name" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" class="w-full border rounded p-2" />
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block font-medium mb-1">City</label>
          <input type="text" name="city" required value="<?= htmlspecialchars($_POST['city'] ?? '') ?>" class="w-full border rounded p-2" />
        </div>
        <div>
          <label class="block font-medium mb-1">Category</label>
          <select name="category" required class="w-full border rounded p-2 bg-white">
            <option value="">Select a category</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= htmlspecialchars($cat) ?>" <?= (isset($_POST['category']) && $_POST['category'] === $cat) ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div>
        <label class="block font-medium mb-1">Address</label>
        <input type="text" name="address" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>" class="w-full border rounded p-2" />
      </div>

      <div>
        <label class="block font-medium mb-1">Description / Tip</label>
        <textarea name="description" rows="4" class="w-full border rounded p-2"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
      </div>

      <div class="flex items-center gap-3">
        <button type="submit" class="bg-black text-white px-6 py-2 rounded hover:opacity-95">Upload</button>
        <a href="index.php" class="text-sm text-gray-600 hover:underline">Cancel</a>
      </div>
    </form>
  </div>
</main>

<script>
document.querySelector('input[name="photo"]').addEventListener('change', function(e){
    const preview = document.getElementById('preview');
    const file = e.target.files[0];
    if(file){
        preview.src = URL.createObjectURL(file);
        preview.classList.remove('hidden');
    } else {
        preview.classList.add('hidden');
    }
});
</script>

<?php include 'includes/footer.php'; ?>
