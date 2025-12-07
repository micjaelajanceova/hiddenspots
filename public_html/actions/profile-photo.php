<?php
// Session handler
require_once __DIR__ . '/../classes/session.php';
$session = new SessionHandle();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../classes/User.php';

// Check if the user is logged in, otherwise block access
if (!$session->logged_in()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Store the logged-in user's ID for later use
$user_id = $session->get('user_id');
$userObj = new User($pdo);


// --- AJAX Remove profile photo ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_photo']) && $_POST['remove_photo'] == '1') {
    header('Content-Type: application/json');

    if ($userObj->removeProfilePhoto($user_id)) {
        unset($_SESSION['profile_photo']);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to remove photo']);
    }
    exit();
}

// --- AJAX Upload profile photo ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_photo']) && empty($_POST['update_password']) && !isset($_POST['remove_photo'])) {
    $response = ['success' => false, 'message' => 'Upload failed'];

    $file = $_FILES['profile_photo'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (!in_array($ext, $allowed)) {
            $response['message'] = 'Invalid file type.';
        } else {
            $targetDir = __DIR__ . '/../uploads/profile/';
            if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
            $filename = uniqid('pf_') . '.' . $ext;
            $dest = $targetDir . $filename;

            if (move_uploaded_file($file['tmp_name'], $dest)) {
                $relative = 'uploads/profile/' . $filename;

                if ($userObj->updateProfilePhoto($user_id, $relative)) {
                    $_SESSION['profile_photo'] = $relative;
                    $response = ['success' => true, 'path' => $relative];
                } else {
                    @unlink($dest);
                    $response['message'] = 'Failed to save photo in database.';
                }
            } else {
                $response['message'] = 'Could not move uploaded file.';
            }
        }
    } else {
        $response['message'] = 'Upload error code: ' . $file['error'];
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}