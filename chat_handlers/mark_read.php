<?php
session_start();
require_once __DIR__ . '/../auth/auth_check.php';
require_once __DIR__ . '/../db/database.php';

header('Content-Type: application/json');

try {
    $user_id = $_SESSION['user_id'];

    if (!isset($_POST['sender_id'])) {
        throw new Exception('No sender specified');
    }

    $sender_id = $_POST['sender_id'];


    $stmt = $pdo->prepare("UPDATE messages 
                          SET is_read = TRUE 
                          WHERE sender_id = :sender_id 
                          AND receiver_id = :receiver_id 
                          AND is_read = FALSE");

    $stmt->execute([
        'sender_id' => $sender_id,
        'receiver_id' => $user_id
    ]);

    echo json_encode(['success' => true, 'count' => $stmt->rowCount()]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}