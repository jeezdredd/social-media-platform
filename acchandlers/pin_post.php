<?php
require_once "../auth/auth_check.php";
require_once "../db/database.php";


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

if (!isset($_POST['post_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Post ID is required']);
    exit;
}

$post_id = $_POST['post_id'];
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT user_id FROM posts WHERE id = ?");
$stmt->execute([$post_id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post || $post['user_id'] != $user_id) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'You can only pin your own posts']);
    exit;
}

$stmt = $pdo->prepare("SELECT is_pinned FROM posts WHERE id = ?");
$stmt->execute([$post_id]);
$current = $stmt->fetch(PDO::FETCH_ASSOC);

$newStatus = $current['is_pinned'] ? 0 : 1;

if ($newStatus === 1) {
    $stmt = $pdo->prepare("UPDATE posts SET is_pinned = 0 WHERE user_id = ? AND is_pinned = 1");
    $stmt->execute([$user_id]);
}

$stmt = $pdo->prepare("UPDATE posts SET is_pinned = ? WHERE id = ?");
$stmt->execute([$newStatus, $post_id]);

echo json_encode([
    'success' => true,
    'is_pinned' => $newStatus === 1,
    'message' => $newStatus === 1 ? 'Post pinned successfully' : 'Post unpinned successfully'
]);