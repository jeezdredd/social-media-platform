<?php
require_once '../db/database.php';


session_set_cookie_params([
    'lifetime' => 3600,
    'path' => '/',
    'domain' => '',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);

session_start();

if (isset($_SESSION["user_id"])) {
    header("Location: chat.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
        $password = trim($_POST["password"]);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(["success" => false, "message" => "Incorrect email!"], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (empty($email) || empty($password)) {
            echo json_encode(["success" => false, "message" => "Fill in all fields!"], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user["password"])) {
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];

            // Обновляем статус пользователя на "онлайн"
            $stmt = $pdo->prepare("UPDATE users SET status = 'online' WHERE id = ?");
            $stmt->execute([$user["id"]]);

            echo json_encode(["success" => true, "message" => "Successful login!", "redirect" => "dashboard.php"], JSON_UNESCAPED_UNICODE);
            exit;
        }

        echo json_encode(["success" => false, "message" => "Incorrect email or password!"], JSON_UNESCAPED_UNICODE);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()], JSON_UNESCAPED_UNICODE);
        exit;
    }
}