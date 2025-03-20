<?php
global $pdo;
require_once "../db/database.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Not logged in"]);
    exit;
}

$user_id = $_SESSION['user_id'];
$post_id = $_POST['post_id'] ?? null;

if (!$post_id) {
    echo json_encode(["status" => "error", "message" => "Invalid post ID"]);
    exit;
}

// Проверяем, ставил ли пользователь уже лайк
$query = "SELECT * FROM likes WHERE user_id = ? AND post_id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$user_id, $post_id]);
$like = $stmt->fetch();

if ($like) {
    // Если лайк уже есть, удаляем его
    $query = "DELETE FROM likes WHERE user_id = ? AND post_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id, $post_id]);

    // Уменьшаем счетчик лайков в таблице posts
    $query = "UPDATE posts SET likes_count = likes_count - 1 WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$post_id]);

    echo json_encode(["status" => "unliked"]);
} else {
    // Добавляем лайк
    $query = "INSERT INTO likes (user_id, post_id) VALUES (?, ?)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id, $post_id]);

    // Увеличиваем счетчик лайков в таблице posts
    $query = "UPDATE posts SET likes_count = likes_count + 1 WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$post_id]);

    echo json_encode(["status" => "liked"]);
}
?>
