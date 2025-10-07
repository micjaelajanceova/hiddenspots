<?php
session_start();
require_once 'db.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php?redirect=upload.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $name = $_POST['name'];
    $city = $_POST['city'];
    $address = $_POST['address'];
    $description = $_POST['description'];
    $user_id = $_SESSION['user_id'];

    if(isset($_FILES['photo']) && $_FILES['photo']['error'] == 0){
        $uploadDir = 'uploads/';
        $filename = uniqid() . '_' . basename($_FILES['photo']['name']);
        $targetFile = $uploadDir . $filename;

        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg','jpeg','png','gif'];

        if(!in_array($fileType, $allowedTypes)){
            echo "Nepovolený typ súboru!";
            exit();
        }

        if(move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)){
            $stmt = $pdo->prepare("INSERT INTO hidden_spots 
                (user_id, name, description, city, address, type, file_path, created_at) 
                VALUES (:user_id, :name, :description, :city, :address, :type, :file_path, NOW())");

            $stmt->execute([
                ':user_id' => $user_id,
                ':name' => $name,
                ':description' => $description,
                ':city' => $city,
                ':address' => $address,
                ':type' => 'Nature',
                ':file_path' => $targetFile
            ]);

            header("Location: frontpage.php?upload=success");
            exit();
        } else {
            echo "Chyba pri nahrávaní súboru.";
        }
    } else {
        echo "Žiadny súbor alebo chyba uploadu.";
    }
}
?>
