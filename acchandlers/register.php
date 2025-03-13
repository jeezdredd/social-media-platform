<?php
global $pdo;
require_once '../db/database.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = htmlspecialchars(trim($_POST["username"]));
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST["password"]);

    if (empty($username) || empty($email) || empty($password)) {
        echo json_encode(["success" => false, "message" => "Заполните все поля!"]);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["success" => false, "message" => "Некорректный email!"]);
        exit;
    }

    try {
        // Проверка на существующий email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            echo json_encode(["success" => false, "message" => "Email уже зарегистрирован!"]);
            exit;
        }

        // Проверка на существующий username
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            echo json_encode(["success" => false, "message" => "Имя пользователя занято!"]);
            exit;
        }

        // Хешируем пароль
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Записываем в БД
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $hashedPassword]);

        echo json_encode(["success" => true, "message" => "Регистрация успешна!", "redirect" => "login.html"]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Ошибка регистрации: " . $e->getMessage()]);
    }
}
?>
