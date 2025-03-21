<?php
global $pdo;
session_start();
require_once "../db/database.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "You need to be logged in."]);
    exit;
}

$post_id = $_POST['post_id'] ?? null;
if (!$post_id) {
    echo json_encode(["success" => false, "message" => "Unknown or incorrect post id."]);
    exit;
}

// Determine if the post belongs to user
$stmt = $pdo->prepare("SELECT user_id FROM posts WHERE id = ?");
$stmt->execute([$post_id]);
$post = $stmt->fetch();

if (!$post || $post['user_id'] != $_SESSION['user_id']) {
    echo json_encode(["success" => false, "message" => "You don't have permission to delete this post."]);
    exit;
}

// Delete post (in case of cascade comments in DB, they will be deleted simultaneously)
$stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
$stmt->execute([$post_id]);

echo json_encode(["success" => true, "message" => "Post successfully deleted."]);
exit;

