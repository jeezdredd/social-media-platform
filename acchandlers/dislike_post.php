<?php
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

// Check if user has placed a dislike
$query = "SELECT COUNT(*) FROM dislikes WHERE user_id = ? AND post_id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$user_id, $post_id]);
$dislikeExists = $stmt->fetchColumn();

if ($dislikeExists) {
    // Delete dislike
    $query = "DELETE FROM dislikes WHERE user_id = ? AND post_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id, $post_id]);

    // Decrease dislike button, but not below 0
    $query = "UPDATE posts SET dislikes_count = GREATEST(dislikes_count - 1, 0) WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$post_id]);

    echo json_encode(["status" => "undisliked"]);
} else {
    // Add dislike
    $query = "INSERT INTO dislikes (user_id, post_id) VALUES (?, ?)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id, $post_id]);

    // Increase dislike
    $query = "UPDATE posts SET dislikes_count = dislikes_count + 1 WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$post_id]);

    // If user had liked, remove it
    $query = "DELETE FROM likes WHERE user_id = ? AND post_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id, $post_id]);

    // Decrease like button, but not below 0
    $query = "UPDATE posts SET likes_count = GREATEST(likes_count - 1, 0) WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$post_id]);

    echo json_encode(["status" => "disliked"]);
}
