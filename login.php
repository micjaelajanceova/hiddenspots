<?php
require_once "db.php";
session_start();

$msg = '';

if (isset($_POST['action'])) {
    if ($_POST['action'] === 'login') {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $_POST['email']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($_POST['password'], $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];

            // Admin check
            if ($user['email'] === 'janceova.mi@gmail.com') {
                header("Location: admin.php");
            } else {
                header("Location: index.php");
            }
            exit();
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
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, `rank`, badges) 
                                       VALUES (:name, :email, :password, 'user', 'newbie')");
                $stmt->execute([
                    'name' => $_POST['name'],
                    'email' => $_POST['email'],
                    'password' => $passwordHash
                ]);
                $msg = "Account created! You can now log in.";
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
</head>
<body class="min-h-screen flex items-center justify-center bg-cover bg-center relative" style="background-image: url('assets/img/hiddenspot1.jpeg');">

<!-- Back button on top-left -->
<button onclick="history.back()" class="absolute top-5 left-5 px-3 py-1 bg-gray-200 rounded hover:bg-gray-300 text-gray-800">← Back</button>

<div class="bg-white bg-opacity-90 p-10 rounded-xl shadow-xl max-w-md w-full">
    <h2 class="text-2xl font-bold mb-6 text-center">Welcome to HiddenSpots</h2>

    <?php if ($msg): ?>
        <p class="text-red-600 font-bold text-center mb-4"><?= htmlspecialchars($msg) ?></p>
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
// Toggle between login and register
const showRegister = document.getElementById('showRegister');
const showLogin = document.getElementById('showLogin');
const loginForm = document.getElementById('loginForm');
const registerForm = document.getElementById('registerForm');

showRegister?.addEventListener('click', () => {
    loginForm.classList.add('hidden');
    registerForm.classList.remove('hidden');
});

showLogin?.addEventListener('click', () => {
    registerForm.classList.add('hidden');
    loginForm.classList.remove('hidden');
});
</script>

</body>
</html>
