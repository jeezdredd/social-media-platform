<?php
$host = 'localhost';
$dbname = 'coursework';
$username = 'root';
$password = '';

// Establish variable $pdo for universal connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    header("Location: errors/500.php");
    die("Database connection error: " . $e->getMessage());
}
