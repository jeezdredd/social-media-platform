<?php
global $pdo;
session_start();
require_once '../db/database.php';

if (!isset($_SESSION['user_id'])) {
    die("Ошибка: пользователь не авторизован.");
}

$stmt = $pdo->prepare("UPDATE users SET status = 'online' WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
echo "OK";

