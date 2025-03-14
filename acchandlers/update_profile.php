<?php
global $pdo;
session_start();
require_once "../db/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.html");
    exit;
}

$user_id = $_SESSION["user_id"];
$username = trim($_POST["username"] ?? "");
$password = trim($_POST["password"] ?? "");

if (empty($username)) {
    $_SESSION["update_error"] = "Имя пользователя не может быть пустым.";
    header("Location: ../dashboard.php");
    exit;
}

try {
    // Обновляем имя пользователя
    $stmt = $pdo->prepare("UPDATE users SET username = ? WHERE id = ?");
    $stmt->execute([$username, $user_id]);

    // Обновляем пароль, если он введен
    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashedPassword, $user_id]);
    }

    $_SESSION["update_success"] = "Данные обновлены!";
} catch (PDOException $e) {
    $_SESSION["update_error"] = "Ошибка: " . $e->getMessage();
}

header("Location: ../dashboard.php");
exit;

