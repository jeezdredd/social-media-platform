<?php
global $pdo;
session_start();
require_once '../db/database.php';

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("UPDATE users SET status = 'offline', last_seen = NOW() WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);

    session_destroy();
}
header("Location: ../login.php");
exit;
