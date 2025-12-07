<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/password-validate.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/spot.php';

// Start session 
require_once __DIR__ . '/../classes/session.php';
$session = new SessionHandle();

$userObj = new User($pdo);
// Initialize message and success flag
$msg = '';
$success = false;

// Load background images for slideshow
$spotObj = new Spot($pdo);
$bgImages = $spotObj->getRecentFiles(10);

// Handle form submissions
$showRegisterForm = false;
if (isset($_POST['action'])) {

    // ===== LOGIN =====
    if ($_POST['action'] === 'login') {
        $user = $userObj->getByEmail($_POST['email']);

        if ($user && password_verify($_POST['password'], $user['password'])) {
            if ($user['blocked']) {
                $msg = "Your account has been blocked. Please contact the administrator.";
            } else {
                $session->set('user_id', $user['id']);
                $session->set('user_name', $user['name']);
                $session->set('user_email', $user['email']);
                $session->set('role', $user['role']);
                $session->set('profile_photo', $user['profile_photo'] ?? null);
                header($user['role'] === 'admin' ? "Location: ../admin.php" : "Location: ../index.php");
                exit();
            }
        } else {
            $msg = "Incorrect email or password.";
        }
    }

    // ===== REGISTER =====
    if ($_POST['action'] === 'register') {
        $username = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        // Validation
        if ($password !== $passwordConfirm) {
            $msg = "Passwords do not match.";
        } elseif (!preg_match('/^[a-z0-9](?!.*[._]{2})[a-z0-9._]{1,18}[a-z0-9]$/', $username)) {
            $msg = "Username must be 3–20 characters, lowercase letters, numbers, dots, or underscores.";
        } elseif ($userObj->existsByEmail($email)) {
            $msg = "Email already exists.";
        } elseif ($userObj->existsByUsername($username)) {
            $msg = "Username already exists.";
        } else {
            // Password strength check
            $validationResult = validatePassword($password);
            if ($validationResult !== true) {
                $msg = $validationResult;
            } else {
                // Create user
                $userObj->createUser($username, $email, $password);
                $msg = "Account created! You can now log in.";
                $success = true;
            }
        }
    }
}
?>

<?php
// Hide navbar on login/register page when its false, only include head
$show_navbar = false;
include __DIR__ . '/../includes/header.php';
?>

<!--  HTML STARTS HERE -->
<main class="min-h-screen flex items-center justify-center relative overflow-hidden">

<!-- Background slideshow -->
<div id="bgSlideshow" class="absolute inset-0 z-0">
  <?php foreach($bgImages as $img): ?>
    <div class="bg-slide" style="background-image: url('../<?= htmlspecialchars($img) ?>');"></div>
  <?php endforeach; ?>
</div>

<!-- Back button -->
<button onclick="history.back()" class="absolute top-5 left-5 px-3 py-1 bg-gray-200 rounded hover:bg-gray-300 text-gray-800 z-10">← Back</button>

<!-- Login/Register Form -->
<div id="loginContainer" 
     data-msg="<?= htmlspecialchars($msg ?? '') ?>" 
     data-success="<?= isset($success) && $success ? 'true' : 'false' ?>"
     class="bg-white bg-opacity-90 p-10 rounded-xl shadow-xl max-w-md w-full z-10 relative mx-2 md:mx-0">
    <h2 class="text-2xl font-bold mb-6 text-center">Welcome to HiddenSpots</h2>

    <?php if ($msg): ?>
    <?php 
        // Check if the message indicates success
        $isSuccess = str_contains($msg, 'Account created'); 
        $msgColor = $isSuccess ? 'text-green-600' : 'text-red-600';
    ?>
    <!-- Use the calculated class, remove any hardcoded red class -->
    <p class="<?= $msgColor ?> font-bold text-center mb-4"><?= htmlspecialchars($msg) ?></p>
    <?php endif; ?>

    <!-- LOGIN FORM -->
    <form id="loginForm" class="flex flex-col gap-4" method="post">
        <input type="hidden" name="action" value="login">
        <input type="email" name="email" placeholder="Email" required class="border rounded p-2 w-full">
        <input type="password" name="password" placeholder="Password" required class="border rounded p-2 w-full">
        <button type="submit" class="bg-black text-white py-2 rounded mt-2">Login</button>
        <p class="text-center mt-4 text-gray-600">
            Don’t have an account? 
            <button type="button" id="showRegister" class="text-blue-600 font-semibold hover:underline">Register</button>
        </p>
    </form>

    <!-- REGISTER FORM -->
    <form id="registerForm" class="flex flex-col gap-4 hidden mt-4" method="post">
        <input type="hidden" name="action" value="register">
        <input type="text" name="name" placeholder="username" required class="border rounded p-2 w-full">
        <input type="email" name="email" placeholder="Email" required class="border rounded p-2 w-full">
        <input type="password" name="password" placeholder="Password" required class="border rounded p-2 w-full">
        <input type="password" name="password_confirm" placeholder="Repeat Password" required class="border rounded p-2 w-full">
        <button type="submit" class="bg-black text-white py-2 rounded mt-2">Register</button>
        <p class="text-center mt-2 text-gray-600">
            Already have an account? 
            <button type="button" id="showLogin" class="text-blue-600 font-semibold hover:underline">Login</button>
        </p>
    </form>
    </main>

<script src="/assets/js/login.js" defer></script>