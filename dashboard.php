<?php
global $conn;
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit;
}

require_once "db/database.php";

// Получаем данные пользователя
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body>
<div class="container">
    <h2>Welcome, <?php echo htmlspecialchars($user["username"]); ?>!</h2>
    <p>You have signed in.</p>
    <a href="/dmuk-coursework/acchandlers/logout.php" class="btn">Logout</a>
</div>
</body>
</html>
