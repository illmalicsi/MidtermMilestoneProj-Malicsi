<?php
// db.php - Central database connection using PDO

try {
    // Establish PDO connection with UTF-8 encoding and exception mode for errors
    $pdo = new PDO(
        'mysql:host=127.0.0.1;port=3306;dbname=our_karinderya;charset=utf8mb4',
        'root', '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    // Stop execution and display message if connection fails
    die("Connection failed: " . $e->getMessage());
}
?>