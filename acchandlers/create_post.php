<?php
global $pdo;
session_start();
require_once "../db/database.php";

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["success" => false, "message" => "Вы не авторизованы."]);
    exit;
}

$user_id = $_SESSION["user_id"];
$content = trim($_POST["content"]);
$imagePath = null;

// Проверяем загрузку изображения
if (!empty($_FILES["image"]["name"])) {
    $targetDir = "../upload/";
    $imagePath = $targetDir . basename($_FILES["image"]["name"]);
    move_uploaded_file($_FILES["image"]["tmp_name"], $imagePath);
}

// Вставляем пост в базу
$stmt = $pdo->prepare("INSERT INTO posts (user_id, content, image, created_at) VALUES (?, ?, ?, NOW())");
$stmt->execute([$user_id, $content, $imagePath]);

header("Location: ../posts.php");
exit;
?>
