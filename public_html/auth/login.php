<?php
require_once __DIR__ . '/../includes/db.php';
session_start();

$msg = '';
$success = false;


$bgImages = [];
try {
    $stmt = $pdo->query("SELECT file_path FROM hidden_spots ORDER BY created_at DESC");
    $bgImages = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $bgImages = ['assets/img/default-bg.jpg']; // fallback
}

// Handle login/register
if (isset($_POST['action'])) {
    if ($_POST['action'] === 'login') {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $_POST['email']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if ($user['blocked']) {
                $msg = "Your account has been blocked. Please contact the administrator.";
            } elseif (password_verify($_POST['password'], $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['profile_photo'] = $user['profile_photo'] ?? null;

                if ($user['role'] === 'admin') {
                    header("Location: ../admin.php");
                    exit();
                } else {
                    header("Location: ../index.php");
                    exit();
                }
            } else {
                $msg = "Incorrect email or password.";
            }
        } else {
            $msg = "Incorrect email or password.";
        }
    } 

    if ($_POST['action'] === 'register') {
            if ($_POST['password'] !== ($_POST['password_confirm'] ?? '')) {
                $msg = "Passwords do not match.";
            } else {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
                $stmt->execute(['email' => $_POST['email']]);
                if ($stmt->fetch()) {
                    $msg = "Email already exists.";
                } else {
                    $passwordHash = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, badges) 
                        VALUES (:name, :email, :password, 'user', 'newbie')");
                    $stmt->execute([
                        'name' => $_POST['name'],
                        'email' => $_POST['email'],
                        'password' => $passwordHash
                    ]);
                    $msg = "Account created! You can now log in.";
                    $success = true;
            }
        }
    }
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login / Register - Hidden Spots</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>

  .bg-slide {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
    opacity: 0;
    transition: opacity 1s ease-in-out;
    transform: scale(1);
    animation: zoom 10s infinite alternate;
  }
  @keyframes zoom {
    0% { transform: scale(1); }
    100% { transform: scale(1.02); }
  }
</style>
</head>
<body class="min-h-screen flex items-center justify-center relative overflow-hidden">

<!-- Background slideshow -->
<div id="bgSlideshow" class="absolute inset-0 z-0">
  <?php foreach($bgImages as $img): ?>
    <div class="bg-slide" style="background-image: url('../<?= htmlspecialchars($img) ?>');"></div>
  <?php endforeach; ?>
</div>

<!-- Back button -->
<button onclick="history.back()" class="absolute top-5 left-5 px-3 py-1 bg-gray-200 rounded hover:bg-gray-300 text-gray-800 z-10">← Back</button>

<!-- Login/Register Form -->
<div class="bg-white bg-opacity-90 p-10 rounded-xl shadow-xl max-w-md w-full z-10 relative">
    <h2 class="text-2xl font-bold mb-6 text-center">Welcome to HiddenSpots</h2>

    <?php if ($msg): ?>
        <?php 
            $isSuccess = str_contains($msg, 'Account created'); 
            $msgColor = $isSuccess ? 'text-green-600' : 'text-red-600';
        ?>
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
        <input type="text" name="name" placeholder="Username" required class="border rounded p-2 w-full">
        <input type="email" name="email" placeholder="Email" required class="border rounded p-2 w-full">
        <input type="password" name="password" placeholder="Password" required class="border rounded p-2 w-full">
        <input type="password" name="password_confirm" placeholder="Repeat Password" required class="border rounded p-2 w-full">
        <button type="submit" class="bg-black text-white py-2 rounded mt-2">Register</button>
        <p class="text-center mt-2 text-gray-600">
            Already have an account? 
            <button type="button" id="showLogin" class="text-blue-600 font-semibold hover:underline">Login</button>
        </p>
    </form>
</div>

<script>
// Toggle login/register
const showRegister = document.getElementById('showRegister');
const showLogin = document.getElementById('showLogin');
const loginForm = document.getElementById('loginForm');
const registerForm = document.getElementById('registerForm');

// Toggle login/register buttons
showRegister?.addEventListener('click', () => {
    loginForm.classList.add('hidden');
    registerForm.classList.remove('hidden');
});
showLogin?.addEventListener('click', () => {
    registerForm.classList.add('hidden');
    loginForm.classList.remove('hidden');
});

// PHP values
const msg = <?= json_encode($msg ?? '') ?>;
const success = <?= isset($success) && $success ? 'true' : 'false' ?>;

// Decide which form to show
if (success) {
    loginForm.classList.remove('hidden');
    registerForm.classList.add('hidden');
} else if (msg.includes('Email already exists') || msg.includes('Passwords do not match')) {
    loginForm.classList.add('hidden');
    registerForm.classList.remove('hidden');
} else {
    
    const params = new URLSearchParams(window.location.search);
    const action = params.get('action');
    if (action === 'register') {
        loginForm.classList.add('hidden');
        registerForm.classList.remove('hidden');
    } else {
        loginForm.classList.remove('hidden');
        registerForm.classList.add('hidden');
    }
}



// Background slideshow
const slides = document.querySelectorAll('.bg-slide');
let current = 0;
slides[current].style.opacity = 1;

setInterval(() => {
    slides[current].style.opacity = 0;
    current = (current + 1) % slides.length;
    slides[current].style.opacity = 1;
}, 5000); 
</script>

</body>
</html>
