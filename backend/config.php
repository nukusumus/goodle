<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=goodle;charset=utf8', 'goodleuser', 'gooDLe103');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Connection failed: ' . $e->getMessage());
}
?>