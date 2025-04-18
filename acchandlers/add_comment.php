<?php
mb_internal_encoding('UTF-8');
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once "../db/database.php";
require_once "../websocket/notify.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "You must be logged in."]);
    exit;
}

$user_id = $_SESSION['user_id'];
$post_id = $_POST['post_id'] ?? null;
$content = trim($_POST['content'] ?? '');

if (!$post_id || empty($content)) {
    echo json_encode(["success" => false, "message" => "Invalid data."]);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
    $stmt->execute([$post_id, $user_id, $content]);

    // Retrieve new ID and time in format year-month-date + hours-minutes-seconds
    $comment_id = $pdo->lastInsertId();
    $created_at = date("Y-m-d H:i:s");

    // Retrieve user ID and profile picture
    $stmtUser = $pdo->prepare("SELECT username, profile_pic FROM users WHERE id = ?");
    $stmtUser->execute([$user_id]);
    $userData = $stmtUser->fetch(PDO::FETCH_ASSOC);

    $stmtPost = $pdo->prepare("SELECT user_id FROM posts WHERE id = ?");
    $stmtPost->execute([$post_id]);
    $postData = $stmtPost->fetch(PDO::FETCH_ASSOC);

    if ($user_id != $postData['user_id']) {
        notifyNewComment($postData['user_id'], $user_id, $userData['username']);
    }

    echo json_encode([
        "success" => true,
        "message" => "Comment added!",
        "comment" => [
            "id" => $comment_id,
            "content" => htmlspecialchars($content),
            "created_at" => $created_at,
            "username" => htmlspecialchars($userData['username']),
            "profile_pic" => htmlspecialchars($userData['profile_pic'] ?: 'upload/default.jpg'),
            "user_id" => $user_id
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
exit();

