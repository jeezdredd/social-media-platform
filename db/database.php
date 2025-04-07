<?php
mb_internal_encoding('UTF-8');
header('Content-Type: text/html; charset=utf-8');

$host = 'localhost';
$dbname = 'webapp';
$username = 'root';
$password = '';

// Establish variable $pdo for universal connection.
// Additional requirements: set unicode format to utf8mb4 and collation to utf8mb4_unicode_ci.
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ]);
} catch (PDOException $e) {
    header("Location: errors/500.php");
    die("Database connection error: " . $e->getMessage());
}
