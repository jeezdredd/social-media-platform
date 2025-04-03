<?php
require_once "../db/database.php";
require_once "../websocket/notify.php";
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

// Check if user already liked this post
$query = "SELECT COUNT(*) FROM likes WHERE user_id = ? AND post_id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$user_id, $post_id]);
$likeExists = $stmt->fetchColumn();

if ($likeExists) {
    // Remove like
    $query = "DELETE FROM likes WHERE user_id = ? AND post_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id, $post_id]);

    // Decrease likes counter, not below 0
    $query = "UPDATE posts SET likes_count = GREATEST(likes_count - 1, 0) WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$post_id]);

    echo json_encode(["status" => "unliked"]);
} else {
    // Add like
    $query = "INSERT INTO likes (user_id, post_id) VALUES (?, ?)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id, $post_id]);

    // Increase likes counter
    $query = "UPDATE posts SET likes_count = likes_count + 1 WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$post_id]);

    // Get post owner ID
    $stmtPost = $pdo->prepare("SELECT user_id FROM posts WHERE id = ?");
    $stmtPost->execute([$post_id]);
    $postData = $stmtPost->fetch(PDO::FETCH_ASSOC);

    // Get username for notification
    $stmtUser = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $stmtUser->execute([$user_id]);
    $userData = $stmtUser->fetch(PDO::FETCH_ASSOC);

    // Only notify if the liker is not the post owner
    if ($user_id != $postData['user_id']) {
        notifyNewLike($postData['user_id'], $user_id, $userData['username']);
    }

    // If user had disliked, remove dislike
    $query = "DELETE FROM dislikes WHERE user_id = ? AND post_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id, $post_id]);

    // Decrease dislikes counter, not below 0
    $query = "UPDATE posts SET dislikes_count = GREATEST(dislikes_count - 1, 0) WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$post_id]);

    echo json_encode(["status" => "liked"]);
}
?>
