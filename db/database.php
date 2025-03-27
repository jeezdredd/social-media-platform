<?php
require_once "auth/auth_check.php";
$host = 'localhost';
$dbname = 'webapp';
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
