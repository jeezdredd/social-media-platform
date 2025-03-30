<?php
// Start session first before any output
session_start();
// Use absolute paths with __DIR__
require_once __DIR__ . '/../auth/auth_check.php';
require_once __DIR__ . '/../db/database.php';

// Set JSON header
header('Content-Type: application/json');

try {
    $user_id = $_SESSION['user_id'];

    if (!isset($_POST['receiver_id'], $_POST['message']) || empty($_POST['message'])) {
        echo json_encode(['error' => 'Empty message']);
        exit;
    }

    $receiver_id = $_POST['receiver_id'];
    $content = trim($_POST['message']);

    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, content, created_at)
                        VALUES (:sender_id, :receiver_id, :content, NOW())");
    $stmt->execute([
        'sender_id' => $user_id,
        'receiver_id' => $receiver_id,
        'content' => $content
    ]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>