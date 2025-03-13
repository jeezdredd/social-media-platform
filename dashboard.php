<?php
global $pdo;
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit;
}

require_once "db/database.php"; // Подключаем PDO

// Получаем данные пользователя
$stmt = $pdo->prepare("SELECT username, profile_pic FROM users WHERE id = ?");
$stmt->execute([$_SESSION["user_id"]]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header("Location: login.html");
    exit;
}

// Определяем аватар (если нет, используем стандартное изображение)
$profilePic = $user['profile_pic'] ?: 'upload/default.jpg';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет</title>
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body>



<div class="container">
    <h2>Добро пожаловать, <?php echo htmlspecialchars($user["username"]); ?>!</h2>

    <!-- Блок профиля -->
    <div class="profile-container">
        <img src="<?= htmlspecialchars($profilePic ?: 'upload/default.png') ?>"
             class="profile-pic"
             alt="Фото профиля">
        <form id="profilePicForm" enctype="multipart/form-data">
            <input type="file" id="profilePic" name="profilePic">
            <button type="submit" class="upload-btn">Загрузить фото</button>
        </form>
        <p id="uploadMessage"></p>
    </div>

    <p>Вы вошли в систему.</p>
    <a href="acchandlers/logout.php" class="btn">Выйти</a>
    <a href="posts.php" class="btn">Перейти в ленту постов</a>
</div>

<script>
    // Загрузка аватара
    document.getElementById("profilePicForm").addEventListener("submit", async function(event) {
        event.preventDefault();
        let formData = new FormData(this);
        let response = await fetch("acchandlers/upload_profile.php", { method: "POST", body: formData });
        let result = await response.json();
        document.getElementById("uploadMessage").innerText = result.message;
        if (result.success) location.reload();
    });
</script>

</body>
</html>
