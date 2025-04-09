<?php
// Start output buffering to catch any errors
ob_start();

// Set header first
header('Content-Type: application/json');

try {
    session_start();
    require_once __DIR__ . '/../auth/auth_check.php';
    require_once __DIR__ . '/../db/database.php';

    $user_id = $_SESSION['user_id'];

    if (!isset($_GET['receiver_id'])) {
        throw new Exception('No receiver specified');
    }

    $receiver_id = $_GET['receiver_id'];

    $stmt = $pdo->prepare("SELECT sender_id, receiver_id, content, created_at, is_read 
                       FROM messages
                       WHERE (sender_id = :user_id AND receiver_id = :receiver_id)
                          OR (sender_id = :receiver_id AND receiver_id = :user_id)
                       ORDER BY created_at ASC");
    $stmt->execute(['user_id' => $user_id, 'receiver_id' => $receiver_id]);

    // Clear any buffered output (potential errors/warnings)
    ob_end_clean();

    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    // Clear any buffered output
    ob_end_clean();
    echo json_encode(['error' => $e->getMessage()]);
}