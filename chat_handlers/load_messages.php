<?php
require_once "auth/auth_check.php";
session_start();
require_once __DIR__ . '/../db/database.php';


$user_id = $_SESSION['user_id'];

if (!isset($_GET['receiver_id'])) {
    die("Ошибка: не указан получатель.");
}

$receiver_id = $_GET['receiver_id'];

$stmt = $pdo->prepare("SELECT sender_id, receiver_id, content, created_at FROM messages 
                       WHERE (sender_id = :user_id AND receiver_id = :receiver_id) 
                          OR (sender_id = :receiver_id AND receiver_id = :user_id) 
                       ORDER BY created_at ASC");
$stmt->execute(['user_id' => $user_id, 'receiver_id' => $receiver_id]);

header('Content-Type: application/json');
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

