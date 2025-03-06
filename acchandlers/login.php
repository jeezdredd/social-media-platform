<?php
global $db;
session_start(); // Начинаем сессию для хранения данных пользователя
include '../db/database.php'; // Подключаем базу данных

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        // Поиск пользователя по имени
        $stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Пароль верный, сохраняем данные пользователя в сессии
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            echo "Вход выполнен успешно!";
            // В будущем можно добавить перенаправление, например:
            // header('Location: ../feed.php');
        } else {
            echo "Неверное имя пользователя или пароль.";
        }
    } catch (PDOException $e) {
        echo "Ошибка входа: " . $e->getMessage();
    }
}
?>