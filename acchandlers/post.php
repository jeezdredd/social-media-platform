<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

global $pdo;
session_start();
require_once '../db/database.php';

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$content = trim($_POST['content'] ?? '');

if (empty($content)) {
    // Перенаправляем обратно с сообщением об ошибке
    $_SESSION['error_message'] = "Пост не может быть пустым.";
    header("Location: ../posts.php");
    exit();
}

$imagePath = null;

// Проверяем загрузку изображения
if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = "../upload/";
    $imageName = uniqid() . "_" . basename($_FILES["image"]["name"]);
    $imagePath = $uploadDir . $imageName;

    if (!move_uploaded_file($_FILES["image"]["tmp_name"], $imagePath)) {
        $_SESSION['error_message'] = "Ошибка загрузки изображения.";
        header("Location: ../posts.php");
        exit();
    }

    $imagePath = "upload/" . $imageName; // относительный путь
}

try {
    $stmt = $pdo->prepare("INSERT INTO posts (user_id, content, image) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $content, $imagePath]);

    // Устанавливаем сообщение об успехе
    $_SESSION['success_message'] = "Пост опубликован!";
    header("Location: ../posts.php"); // Редирект на posts.php
    exit();
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Ошибка базы данных: " . $e->getMessage();
    header("Location: ../posts.php");
    exit();
}
