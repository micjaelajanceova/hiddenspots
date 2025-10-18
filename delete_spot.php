<?php
session_start();
include 'db.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    die("Access denied.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];

    $stmt = $pdo->prepare("DELETE FROM hidden_spots WHERE id = ?");
    $stmt->execute([$id]);

    header("Location: admin.php");
    exit;
}
?>
