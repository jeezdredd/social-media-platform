<?php
require_once "auth/auth_check.php";
session_start();
require_once '../db/database.php';


$stmt = $pdo->prepare("UPDATE users SET status = 'online' WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
echo "OK";

