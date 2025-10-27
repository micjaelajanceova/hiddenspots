<?php
require_once 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int) $_SESSION['user_id'];
    $name = trim($_POST['name'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $photoData = $_POST['photoData'] ?? '';

    if (!$name || !$city || !$category || !$photoData) {
        $error = "Please fill all required fields and select a photo.";
    } else {
        // decode Base64
        if (preg_match('/^data:image\/(\w+);base64,/', $photoData, $type)) {
            $data = substr($photoData, strpos($photoData, ',') + 1);
            $data = base64_decode($data);
            $ext = strtolower($type[1]); // jpg, png, gif, webp
            $allowed = ['jpg','jpeg','png','gif','webp'];
            if (!in_array($ext, $allowed)) {
                $error = "Invalid image type.";
            } else {
                $fileName = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
                $filePath = __DIR__ . '/uploads/' . $fileName;
                if (!file_put_contents($filePath, $data)) {
                    $error = "Failed to save image.";
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
                            ':file_path' => 'uploads/' . $fileName
                        ]);
                        header("Location: index.php?upload=success");
                        exit();
                    } catch (PDOException $e) {
                        if (file_exists($filePath)) unlink($filePath);
                        $error = "Database error: " . $e->getMessage();
                    }
                }
            }
        } else {
            $error = "Invalid image data.";
        }
    }
}

if ($error) {
    echo "<p style='color:red;'>$error</p>";
    echo "<a href='index.php'>Go back</a>";
}
?>
