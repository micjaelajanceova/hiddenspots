<?php
require __DIR__ . '/constants.php';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);

     // set UTF-8 for everything so the database handles special characters correctly
    $pdo->exec("SET character_set_client = utf8mb4");
    $pdo->exec("SET character_set_connection = utf8mb4");
    $pdo->exec("SET character_set_results = utf8mb4");
    
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}


?>
