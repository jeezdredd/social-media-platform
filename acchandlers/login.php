<?php
global $pdo;
require_once '../db/database.php';

session_set_cookie_params([
    'lifetime' => 3600,
    'path' => '/',
    'domain' => '',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST["password"]);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["success" => false, "message" => "Некорректный email!"]);
        exit;
    }

    if (empty($email) || empty($password)) {
        echo json_encode(["success" => false, "message" => "Заполните все поля!"]);
        exit;
    }

    // Используем PDO
    $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user["password"])) {
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["username"] = $user["username"];
        echo json_encode(["success" => true, "message" => "Вход выполнен!", "redirect" => "dashboard.php"]);
        exit;
    }

    echo json_encode(["success" => false, "message" => "Неверный email или пароль."]);
}

