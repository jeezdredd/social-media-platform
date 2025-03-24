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

// Проверяем, ставил ли пользователь лайк
$query = "SELECT COUNT(*) FROM likes WHERE user_id = ? AND post_id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$user_id, $post_id]);
$likeExists = $stmt->fetchColumn();

if ($likeExists) {
    // Удаляем лайк
    $query = "DELETE FROM likes WHERE user_id = ? AND post_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id, $post_id]);

    // Уменьшаем счетчик лайков, но не ниже 0
    $query = "UPDATE posts SET likes_count = GREATEST(likes_count - 1, 0) WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$post_id]);

    echo json_encode(["status" => "unliked"]);
} else {
    // Добавляем лайк
    $query = "INSERT INTO likes (user_id, post_id) VALUES (?, ?)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id, $post_id]);

    // Увеличиваем счетчик лайков
    $query = "UPDATE posts SET likes_count = likes_count + 1 WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$post_id]);

    // Если у пользователя был дизлайк, удаляем его
    $query = "DELETE FROM dislikes WHERE user_id = ? AND post_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id, $post_id]);

    // Уменьшаем счетчик дизлайков, но не ниже 0
    $query = "UPDATE posts SET dislikes_count = GREATEST(dislikes_count - 1, 0) WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$post_id]);

    echo json_encode(["status" => "liked"]);
}
?>
