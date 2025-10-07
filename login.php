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
            header("Location: index.php");
            exit();
        } else {
            $msg = "Incorrect email or password.";
        }
    }

    if ($_POST['action'] === 'register') {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $_POST['email']]);
        if ($stmt->fetch()) {
            $msg = "Email already exists.";
        } else {
            $passwordHash = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, birthDate, `rank`, badges) VALUES (:name, :email, :password, :birthDate, 'user', 'newbie')");
            $stmt->execute([
                'name' => $_POST['name'],
                'email' => $_POST['email'],
                'password' => $passwordHash,
                'birthDate' => $_POST['birthDate'] ?? null
            ]);
            $msg = "Account created! You can now log in.";
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
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="min-h-screen flex items-center justify-center bg-cover bg-center" style="background-image: url('assets/img/login-bg.jpg');">

<div class="bg-white bg-opacity-90 p-10 rounded-xl shadow-xl max-w-md w-full">
    <h2 class="text-2xl font-bold mb-6 text-center">Welcome to Hidden Spots</h2>

    <?php if ($msg): ?>
        <p class="text-red-500 text-center mb-4"><?= htmlspecialchars($msg) ?></p>
    <?php endif; ?>

    <div class="flex justify-center gap-4 mb-6">
        <button id="loginBtn" class="px-4 py-2 bg-black text-white rounded">Login</button>
        <button id="registerBtn" class="px-4 py-2 bg-gray-800 text-white rounded">Register</button>
    </div>

    <form id="loginForm" class="flex flex-col gap-4" method="post">
        <input type="hidden" name="action" value="login">
        <input type="email" name="email" placeholder="Email" required class="border rounded p-2 w-full">
        <input type="password" name="password" placeholder="Password" required class="border rounded p-2 w-full">
        <button type="submit" class="bg-black text-white py-2 rounded mt-2">Login</button>
    </form>

    <form id="registerForm" class="flex flex-col gap-4 hidden" method="post">
        <input type="hidden" name="action" value="register">
        <input type="text" name="name" placeholder="Username" required class="border rounded p-2 w-full">
        <input type="email" name="email" placeholder="Email" required class="border rounded p-2 w-full">
        <input type="password" name="password" placeholder="Password" required class="border rounded p-2 w-full">
        <input type="date" name="birthDate" class="border rounded p-2 w-full">
        <button type="submit" class="bg-black text-white py-2 rounded mt-2">Register</button>
    </form>
</div>

<script>
const loginBtn = document.getElementById('loginBtn');
const registerBtn = document.getElementById('registerBtn');
const loginForm = document.getElementById('loginForm');
const registerForm = document.getElementById('registerForm');

loginBtn.addEventListener('click', () => {
    loginForm.classList.remove('hidden');
    registerForm.classList.add('hidden');
});

registerBtn.addEventListener('click', () => {
    registerForm.classList.remove('hidden');
    loginForm.classList.add('hidden');
});
</script>

</body>
</html>
