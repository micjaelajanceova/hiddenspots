<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];


// --- AJAX Remove profile photo ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_photo']) && $_POST['remove_photo'] == '1') {
    header('Content-Type: application/json');

    $stmt = $pdo->prepare("SELECT profile_photo FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $current = $stmt->fetchColumn();

    if ($current) {
        $pathOnDisk = __DIR__ . '/../' . $current;
        if (file_exists($pathOnDisk)) @unlink($pathOnDisk);
    }

    $stmt = $pdo->prepare("UPDATE users SET profile_photo = NULL WHERE id = ?");
    $stmt->execute([$user_id]);

    unset($_SESSION['profile_photo']);
    echo json_encode(['success' => true]);
    exit();
}

// --- AJAX Upload profile photo ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_photo']) && empty($_POST['update_password']) && !isset($_POST['remove_photo'])) {
    $response = ['success' => false, 'message' => 'Upload failed'];

    if ($_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES['profile_photo']['tmp_name'];
        $orig = basename($_FILES['profile_photo']['name']);
        $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp','gif'];

        if (!in_array($ext, $allowed)) {
            $response['message'] = 'Invalid file type.';
        } else {
            $filename = uniqid('pf_') . '.' . $ext;
            $targetDir = __DIR__ . '/../uploads/profile/';
            if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
            $dest = $targetDir . $filename;

            if (move_uploaded_file($tmp, $dest)) {
                $relative = 'uploads/profile/' . $filename;

                
                $stmt = $pdo->prepare("SELECT profile_photo FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $old = $stmt->fetchColumn();
                if ($old) {
                    $oldPath = __DIR__ . '/../' . $old;
                    if (file_exists($oldPath)) @unlink($oldPath);
                }

                $stmt = $pdo->prepare("UPDATE users SET profile_photo = ? WHERE id = ?");
                $stmt->execute([$relative, $user_id]);

                $_SESSION['profile_photo'] = $relative;
                $response = ['success' => true, 'path' => $relative];
            } else {
                $response['message'] = 'Could not move uploaded file.';
            }
        }
    } else {
        $response['message'] = 'Upload error code: ' . $_FILES['profile_photo']['error'];
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}