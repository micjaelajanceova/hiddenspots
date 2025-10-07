<?php
require_once "db.php";

$msg = "";

if(isset($_POST['action'])){
    if($_POST['action'] === 'login'){
        $email = $_POST['email'];
        $password = $_POST['pass'];

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email'=>$email]);
        $user = $stmt->fetch();

        if($user && password_verify($password, $user['password'])){
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            header("Location: index.php");
            exit();
        }else{
            $msg = "Invalid login credentials.";
        }

    }elseif($_POST['action'] === 'register'){
        $username = $_POST['user'];
        $email = $_POST['email'];
        $password = password_hash($_POST['pass'], PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, birthDate, `rank`, badges) 
                               VALUES (:name, :email, :pass, :birthDate, :rank, :badges)");
        $stmt->execute([
            'name'=>$username,
            'email'=>$email,
            'pass'=>$password,
            'birthDate'=>$_POST['birthDate'] ?? null,
            'rank'=>'user',
            'badges'=>'newbie'
        ]);

        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['user_name'] = $username;
        header("Location: index.php");
        exit();
    }
}
?>

<?php include 'header.php'; ?>

<div class="flex flex-col items-center justify-center flex-1 w-full min-h-screen bg-gray-100 p-4">
    <div class="relative w-full max-w-md p-8 bg-white rounded-xl shadow-lg">
        <?php if($msg): ?>
            <div class="mb-4 text-red-600 font-semibold text-center"><?= $msg ?></div>
        <?php endif; ?>

        <div id="loginDiv">
            <h2 class="text-2xl font-bold text-center mb-6">Login</h2>
            <form method="post" class="space-y-4">
                <input type="hidden" name="action" value="login"/>
                <input type="email" name="email" placeholder="Email" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"/>
                <input type="password" name="pass" placeholder="Password" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"/>
                <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">Login</button>
            </form>
            <button onclick="toggleForms()" class="mt-4 w-full text-blue-600 hover:underline text-center">Switch to Register</button>
        </div>

        <div id="registerDiv" class="hidden">
            <h2 class="text-2xl font-bold text-center mb-6">Register</h2>
            <form method="post" class="space-y-4">
                <input type="hidden" name="action" value="register"/>
                <input type="text" name="user" placeholder="Username" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"/>
                <input type="email" name="email" placeholder="Email" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"/>
                <input type="password" name="pass" placeholder="Password" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"/>
                <input type="date" name="birthDate" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"/>
                <button type="submit" class="w-full bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 transition">Register</button>
            </form>
            <button onclick="toggleForms()" class="mt-4 w-full text-green-600 hover:underline text-center">Switch to Login</button>
        </div>

        <div class="absolute top-0 left-0 w-full h-full -z-10 rounded-xl overflow-hidden">
            <img src="assets/img/hiddenspot3.jpg" alt="Background" class="w-full h-full object-cover opacity-20">
        </div>
    </div>
</div>

<script>
function toggleForms(){
    document.getElementById('loginDiv').classList.toggle('hidden');
    document.getElementById('registerDiv').classList.toggle('hidden');
}
</script>

<?php include 'footer.php'; ?>
