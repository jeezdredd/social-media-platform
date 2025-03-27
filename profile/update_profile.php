<?php
require_once "auth/auth_check.php";
session_start();
require_once "../db/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$username = trim($_POST["username"] ?? "");
$password = trim($_POST["password"] ?? "");

if (empty($username)) {
    $_SESSION["update_error"] = "Name cannot be empty.";
    header("Location: ../dashboard.php");
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE users SET username = ? WHERE id = ?");
    $stmt->execute([$username, $user_id]);

    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashedPassword, $user_id]);
    }

    $_SESSION["update_success"] = "Username or password updated!";
} catch (PDOException $e) {
    $_SESSION["update_error"] = "Error: " . $e->getMessage();
}

header("Location: ../dashboard.php");
exit;

