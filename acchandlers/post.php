<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

global $pdo;
session_start();
require_once '../db/database.php';

// Check if authorized
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$content = trim($_POST['content'] ?? '');

if (empty($content)) {
    $_SESSION['error_message'] = "Post cannot be empty.";
    header("Location: ../posts.php");
    exit();
}

$imagePath = null;

if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = "../upload/";
    $imageName = uniqid() . "_" . basename($_FILES["image"]["name"]);
    $imagePath = $uploadDir . $imageName;

    if (!move_uploaded_file($_FILES["image"]["tmp_name"], $imagePath)) {
        $_SESSION['error_message'] = "Error loading image.";
        header("Location: ../posts.php");
        exit();
    }

    $imagePath = "upload/" . $imageName;
}

try {
    $stmt = $pdo->prepare("INSERT INTO posts (user_id, content, image) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $content, $imagePath]);

    $_SESSION['success_message'] = "Post published!";
    header("Location: ../posts.php");
    exit();
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Database fatal error: " . $e->getMessage();
    header("Location: ../posts.php");
    exit();
}
