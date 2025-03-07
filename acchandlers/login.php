<?php
global $conn;
session_start();
//Requiring the database connection once.
require_once '../db/database.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if (empty($email) || empty($password)) {
        echo json_encode(["success" => false, "message" => "Fill in all fields!"]);
        exit;
    }

    // Проверяем пользователя в базе
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user["password"])) {
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            echo json_encode(["success" => true, "message" => "Login success!", "redirect" => "dashboard.php"]);
            exit;
        }
    }

    echo json_encode(["success" => false, "message" => "Incorrect email or password."]);
}
?>
