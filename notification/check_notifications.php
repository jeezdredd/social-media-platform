<?php
global $pdo;
require_once "../db/database.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["new_posts" => 0, "new_comments" => 0]);
    exit;
}

$lastPostId = isset($_GET['lastPostId']) ? (int)$_GET['lastPostId'] : 0;
$lastCommentId = isset($_GET['lastCommentId']) ? (int)$_GET['lastCommentId'] : 0;

// Retrieve maximum post ID
$stmt = $pdo->query("SELECT MAX(id) AS max_post_id FROM posts");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$currentMaxPostId = (int)$row['max_post_id'];

// Retrieve maximum comment ID
$stmt = $pdo->query("SELECT MAX(id) AS max_comment_id FROM comments");
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$currentMaxCommentId = (int)$row['max_comment_id'];

// Determine how many new posts added
$newPosts = ($currentMaxPostId > $lastPostId) ? $currentMaxPostId - $lastPostId : 0;
$newComments = ($currentMaxCommentId > $lastCommentId) ? $currentMaxCommentId - $lastCommentId : 0;

//Transfer retreived data to js
echo json_encode([
    "new_posts" => $newPosts,
    "new_comments" => $newComments,
    "currentMaxPostId" => $currentMaxPostId,
    "currentMaxCommentId" => $currentMaxCommentId
]);

