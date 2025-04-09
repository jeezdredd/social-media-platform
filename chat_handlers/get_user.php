<?php
session_start();
require_once __DIR__ . '/../auth/auth_check.php';
require_once __DIR__ . '/../db/database.php';

header('Content-Type: application/json');

try {
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("
        SELECT u.id, u.username, u.status, u.profile_pic, u.last_seen,
               (SELECT COUNT(*) FROM messages 
                WHERE sender_id = u.id 
                AND receiver_id = ? 
                AND is_read = FALSE) as unread_count
        FROM users u 
        WHERE u.id != ? 
        ORDER BY u.status DESC, unread_count DESC, u.username ASC
    ");
    $stmt->execute([$user_id, $user_id]);

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}