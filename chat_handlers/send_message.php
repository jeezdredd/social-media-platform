<?php
global $pdo;
session_start();
require_once __DIR__ . '/../db/database.php';

if (!isset($_SESSION['user_id'])) {
    die("Ошибка: пользователь не авторизован.");
}

$user_id = $_SESSION['user_id'];

if (!isset($_POST['receiver_id'], $_POST['message']) || empty($_POST['message'])) {
    die("Ошибка: пустое сообщение.");
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

echo "OK";
?>
