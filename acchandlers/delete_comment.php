<?php
global $pdo;
session_start();
require_once "../db/database.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "You must be logged in to do that."]);
    exit;
}

$comment_id = $_POST['comment_id'] ?? null;
if (!$comment_id) {
    echo json_encode(["success" => false, "message" => "Unknown comment ID."]);
    exit;
}

// Determine if post belongs to current user
$stmt = $pdo->prepare("SELECT user_id FROM comments WHERE id = ?");
$stmt->execute([$comment_id]);
$comment = $stmt->fetch();

if (!$comment || $comment['user_id'] != $_SESSION['user_id']) {
    echo json_encode(["success" => false, "message" => "You dont have permission to do that."]);
    exit;
}

// Delete commment
$stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
$stmt->execute([$comment_id]);

echo json_encode(["success" => true, "message" => "Comment successfully deleted."]);
exit;

