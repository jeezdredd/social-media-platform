<?php
global $pdo;
session_start();
require_once "db/database.php";

// Проверка аутентификации
if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit;
}

$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;

// Очищаем сообщения после вывода
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Получаем информацию о пользователе
$stmtUser = $pdo->prepare("SELECT username, profile_pic FROM users WHERE id = ?");
$stmtUser->execute([$_SESSION["user_id"]]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);

// Получаем список постов
$stmt = $pdo->query("SELECT posts.*, users.username, users.profile_pic FROM posts 
                     JOIN users ON posts.user_id = users.id 
                     ORDER BY posts.created_at DESC");
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Лента постов</title>
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body>

<!-- Верхний навигационный блок -->
<div class="navbar">
    <div class="nav-left">
        <h2>Feed</h2>
    </div>
    <div class="nav-right">
        <a href="dashboard.php" class="btn">Profile</a>
        <a href="#" class="btn">Messages</a>
        <a href="#" class="btn">Settings</a>
        <a href="acchandlers/logout.php" class="btn btn-danger">Logout</a>
    </div>
</div>

<!-- Форма публикации поста -->
<div class="feed-container">
    <div class="post-form">
        <img src="<?= htmlspecialchars($user['profile_pic'] ?: 'upload/default.jpg') ?>" class="avatar" alt="Аватар">
        <form action="acchandlers/post.php" method="POST" enctype="multipart/form-data" class="post-input">
            <textarea name="content" placeholder="Whats on your mind?" required></textarea>
            <div class="post-actions">
                <input type="file" name="image">
                <button type="submit">Publish</button>
            </div>
        </form>
    </div>

    <div id="posts">
        <?php foreach ($posts as $post): ?>
            <div class="post">
                <div class="post-header">
                    <img src="<?= htmlspecialchars($post['profile_pic'] ?: 'upload/default.jpg') ?>" class="avatar" alt="Profile picture">
                    <p><?= htmlspecialchars($post['username']) ?></p>
                </div>
                <p><?= htmlspecialchars($post['content']) ?></p>
                <?php if (!empty($post['image'])): ?>
                    <img src="<?= htmlspecialchars($post['image']) ?>" class="post-image" alt="Post picture">
                <?php endif; ?>
                <div class="post-date"><?= $post['created_at'] ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>

<?php if ($success_message): ?>
    <div class="alert success"><?= htmlspecialchars($success_message) ?></div>
<?php endif; ?>

<?php if ($error_message): ?>
    <div class="alert error"><?= htmlspecialchars($error_message) ?></div>
<?php endif; ?>