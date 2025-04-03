<?php
require_once "../db/database.php";
session_start();

if (!isset($_SESSION['user_id']) || !isset($_POST['post_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters']);
    exit;
}

$user_id = $_SESSION['user_id'];
$post_id = $_POST['post_id'];
$comment = isset($_POST['comment']) ? trim($_POST['comment']) : null;

try {
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->execute([$post_id]);
    $original_post = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$original_post) {
        throw new Exception('Original post not found');
    }

    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO posts (user_id, content, image, original_post_id, is_share, share_comment) 
                          VALUES (?, ?, ?, ?, 1, ?)");
    $stmt->execute([
        $user_id,
        $original_post['content'],
        $original_post['image'],
        $post_id,
        $comment
    ]);

    $stmt = $pdo->prepare("UPDATE posts SET shares_count = shares_count + 1 WHERE id = ?");
    $stmt->execute([$post_id]);

    $pdo->commit();

    echo json_encode(['success' => true, 'message' => 'Post shared successfully']);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}