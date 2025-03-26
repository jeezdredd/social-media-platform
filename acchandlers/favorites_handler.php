<?php
session_start();
require_once "../db/database.php"; // Подключаем БД

if (!isset($_SESSION['user_id'])) {
    die("Ошибка: пользователь не авторизован.");
}

$user_id = $_SESSION['user_id'];
$post_id = $_POST['post_id'] ?? 0;

if ($post_id > 0) {
    // Проверяем, есть ли пост в избранном
    $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND post_id = ?");
    $stmt->execute([$user_id, $post_id]);
    $favorite = $stmt->fetch();

    if ($favorite) {
        // Удаляем из избранного
        $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND post_id = ?");
        $stmt->execute([$user_id, $post_id]);
        echo "removed";
    } else {
        // Добавляем в избранное
        $stmt = $pdo->prepare("INSERT INTO favorites (user_id, post_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $post_id]);
        echo "added";
    }
} else {
    echo "Ошибка: некорректный ID поста.";
}

