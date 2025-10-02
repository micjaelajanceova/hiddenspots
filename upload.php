<?php
session_start();
include 'db.php'; // PDO connection

if(!isset($_SESSION['user_id'])){
    die("Musíš byť prihlásený!");
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $name = $_POST['name'];
    $city = $_POST['city'];
    $address = $_POST['address'];
    $description = $_POST['description'];
    $user_id = $_SESSION['user_id'];

    // kontrola súboru
    if(isset($_FILES['photo']) && $_FILES['photo']['error'] == 0){
        $uploadDir = 'uploads/';
        $filename = uniqid() . '_' . basename($_FILES['photo']['name']);
        $targetFile = $uploadDir . $filename;

        if(move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)){
            // vložiť do databázy
            $stmt = $pdo->prepare("INSERT INTO hidden_spots 
                (user_id, name, description, city, address, type, file_path) 
                VALUES (:user_id, :name, :description, :city, :address, :type, :file_path)");

            $stmt->execute([
                ':user_id' => $user_id,
                ':name' => $name,
                ':description' => $description,
                ':city' => $city,
                ':address' => $address,
                ':type' => 'Nature', // alebo môžeš pridať select pre typ
                ':file_path' => $targetFile
            ]);

            echo "Fotka bola úspešne nahraná!";
        } else {
            echo "Chyba pri nahrávaní súboru.";
        }
    } else {
        echo "Žiadny súbor alebo chyba uploadu.";
    }
}
?>
