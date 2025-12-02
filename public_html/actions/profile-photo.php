<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

// Check if the user is logged in, otherwise block access
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Store the logged-in user's ID for later use
$user_id = $_SESSION['user_id'];


// --- AJAX Remove profile photo ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_photo']) && $_POST['remove_photo'] == '1') {
    header('Content-Type: application/json');

    // Get current profile photo path from database
    $stmt = $pdo->prepare("SELECT profile_photo FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $current = $stmt->fetchColumn();

    // If a photo exists, delete it from the server
    if ($current) {
        $pathOnDisk = __DIR__ . '/../' . $current;
        if (file_exists($pathOnDisk)) @unlink($pathOnDisk);
    }

    // Remove photo reference from database
    $stmt = $pdo->prepare("UPDATE users SET profile_photo = NULL WHERE id = ?");
    $stmt->execute([$user_id]);

    // Remove photo info from session
    unset($_SESSION['profile_photo']);

    // Respond back with success
    echo json_encode(['success' => true]);
    exit();
}

// --- AJAX Upload profile photo ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_photo']) && empty($_POST['update_password']) && !isset($_POST['remove_photo'])) {
    $response = ['success' => false, 'message' => 'Upload failed'];

    // Check if file was uploaded without errors
    if ($_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES['profile_photo']['tmp_name'];
        $orig = basename($_FILES['profile_photo']['name']);
        $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp','gif'];

        if (!in_array($ext, $allowed)) {
            $response['message'] = 'Invalid file type.';
        } else {
            // Create a unique name for the uploaded file
            $filename = uniqid('pf_') . '.' . $ext;
            $targetDir = __DIR__ . '/../uploads/profile/';

            // Create folder if it doesn't exist
            if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
            $dest = $targetDir . $filename;

            // Move the uploaded file to the target folder
            if (move_uploaded_file($tmp, $dest)) {
                $relative = 'uploads/profile/' . $filename;

                // Remove old photo from server if exists
                $stmt = $pdo->prepare("SELECT profile_photo FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $old = $stmt->fetchColumn();
                if ($old) {
                    $oldPath = __DIR__ . '/../' . $old;
                    if (file_exists($oldPath)) @unlink($oldPath);
                }

                // Update database with new photo path
                $stmt = $pdo->prepare("UPDATE users SET profile_photo = ? WHERE id = ?");
                $stmt->execute([$relative, $user_id]);

                // Update session with new photo
                $_SESSION['profile_photo'] = $relative;

                // Respond back with success and new file path
                $response = ['success' => true, 'path' => $relative];
            } else {
                $response['message'] = 'Could not move uploaded file.';
            }
        }
    } else {
        $response['message'] = 'Upload error code: ' . $_FILES['profile_photo']['error'];
    }

    // Send JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}