<?php
header("Content-Type: application/json");
require_once "../db/database.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["new_posts" => 0, "new_comments" => 0, "new_likes" => 0]);
    exit;
}

$user_id = $_SESSION['user_id'];
$lastPostId = isset($_GET['lastPostId']) ? (int)$_GET['lastPostId'] : 0;
$lastCommentId = isset($_GET['lastCommentId']) ? (int)$_GET['lastCommentId'] : 0;
$lastLikeId = isset($_GET['lastLikeId']) ? (int)$_GET['lastLikeId'] : 0; // Новый параметр

$stmt = $pdo->query("SELECT MAX(id) AS max_post_id FROM posts");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$currentMaxPostId = (int)$row['max_post_id'];

$stmt = $pdo->prepare("
    SELECT comments.id, comments.post_id, users.username 
    FROM comments 
    JOIN users ON comments.user_id = users.id 
    JOIN posts ON comments.post_id = posts.id 
    WHERE posts.user_id = ? AND comments.id > ?
    ORDER BY comments.id DESC
");
$stmt->execute([$user_id, $lastCommentId]);
$newComments = $stmt->fetchAll(PDO::FETCH_ASSOC);
$commentCount = count($newComments);
$lastCommentId = $commentCount > 0 ? max(array_column($newComments, 'id')) : $lastCommentId;

$stmt = $pdo->prepare("
    SELECT likes.id, likes.post_id, users.username 
    FROM likes 
    JOIN users ON likes.user_id = users.id 
    JOIN posts ON likes.post_id = posts.id 
    WHERE posts.user_id = ? AND likes.id > ?
    ORDER BY likes.id DESC
");
$stmt->execute([$user_id, $lastLikeId]);
$newLikes = $stmt->fetchAll(PDO::FETCH_ASSOC);
$likeCount = count($newLikes);
$lastLikeId = $likeCount > 0 ? max(array_column($newLikes, 'id')) : $lastLikeId;

echo json_encode([
    "new_posts" => ($currentMaxPostId > $lastPostId) ? 1 : 0,
    "currentMaxPostId" => $currentMaxPostId,
    "new_comments" => $commentCount,
    "new_comments_data" => $newComments,
    "currentMaxCommentId" => $lastCommentId,
    "new_likes" => $likeCount,
    "new_likes_data" => $newLikes,
    "currentMaxLikeId" => $lastLikeId
]);

