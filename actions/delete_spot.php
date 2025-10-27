<?php
require_once __DIR__ . '/../includes/db.php';
session_start();

// Kontrola prihlásenia
if (!isset($_SESSION['user_id'])) {
    die("Musíš byť prihlásený");
}

// Získaj id spotu
if (!isset($_POST['id'])) {
    die("Nešpecifikovaný spot");
}
$spot_id = $_POST['id'];

// Načítaj spot z DB
$stmt = $pdo->prepare("SELECT * FROM hidden_spots WHERE id = ?");
$stmt->execute([$spot_id]);
$spot = $stmt->fetch();

if (!$spot) {
    die("Spot neexistuje");
}

// Kontrola oprávnení
if ($_SESSION['role'] === 'admin') {
    // admin môže všetko → pokračujeme
} elseif ($_SESSION['role'] === 'user') {
    if ($spot['user_id'] != $_SESSION['user_id']) {
        die("Nemáš oprávnenie vymazať tento spot");
    }
} else {
    die("Neznáma rola");
}

// Vymazanie spotu
$stmt = $pdo->prepare("DELETE FROM hidden_spots WHERE id = ?");
$stmt->execute([$spot_id]);

header("Location: ../admin.php"); // presmerovanie späť do admin.php
exit();
