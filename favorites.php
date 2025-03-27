<?php
require_once "auth/auth_check.php";
require_once "db/database.php";


$user_id = $_SESSION['user_id'];

// Retrieve favorite posts
$stmt = $pdo->prepare("
    SELECT posts.*, DATE_FORMAT(posts.created_at, '%d.%m.%Y %H:%i') AS formatted_date 
    FROM posts 
    JOIN favorites ON posts.id = favorites.post_id 
    WHERE favorites.user_id = ?
");
$stmt->execute([$user_id]);
$favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Retrieve liked posts
$stmt = $pdo->prepare("
    SELECT posts.*, DATE_FORMAT(posts.created_at, '%d.%m.%Y %H:%i') AS formatted_date 
    FROM posts 
    JOIN likes ON posts.id = likes.post_id 
    WHERE likes.user_id = ?
");
$stmt->execute([$user_id]);
$liked_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favorite</title>
    <link rel="stylesheet" href="styles/favorites.css">
</head>
<body>
<?php require_once "page_elements/header.php"; ?>

<h2 style="margin-top: 20px; margin-bottom: 20px;">⭐ Favorite posts</h2>
<div class="posts-container">
    <?php if (empty($favorites)): ?>
        <p>You did not add any posts as favorite.</p>
    <?php else: ?>
        <?php foreach ($favorites as $post): ?>
            <div class="post">
                <?php if (!empty($post['image'])): ?>
                    <img src="<?= htmlspecialchars($post['image']); ?>" alt="Post image"
                         style="max-width: 100%; max-height: 400px; object-fit: cover; border-radius: 10px; display: block; margin: 0 auto;">
                <?php else: ?>
                    <p style="color: gray; font-style: italic;">This post doesn’t have an image attached.</p>
                <?php endif; ?>
                <p style="margin-top: 10px; font-size: 0.9em; color: gray;">📅 <b>Published:</b> <?= htmlspecialchars($post['formatted_date']) ?></p>
                <p style="margin-top: 10px; font-size: 0.9em; color: gray;"><b>📝 Content:</b> <?= htmlspecialchars($post['content']) ?></p>
                <button class="remove-favorite" data-id="<?= $post['id']; ?>">Delete</button>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<h2 style="margin-top: 20px; margin-bottom: 20px;">❤️ Liked posts</h2>
<div class="posts-container">
    <?php if (empty($liked_posts)): ?>
        <p>You did not like any posts.</p>
    <?php else: ?>
        <?php foreach ($liked_posts as $post): ?>
            <div class="post">
                <?php if (!empty($post['image'])): ?>
                    <img src="<?= htmlspecialchars($post['image']); ?>" alt="Post image"
                         style="max-width: 100%; max-height: 400px; object-fit: cover; border-radius: 10px; display: block; margin: 0 auto;">
                <?php else: ?>
                    <p style="color: gray; font-style: italic;">This post doesn’t have an image attached.</p>
                <?php endif; ?>
                <p style="margin-top: 10px; font-size: 0.9em; color: gray;">📅 <b>Published:</b> <?= htmlspecialchars($post['formatted_date']) ?></p>
                <p style="margin-top: 10px; font-size: 0.9em; color: gray;"><b>📝 Content:</b> <?= htmlspecialchars($post['content']) ?></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script src="js/favorites.js"></script>
</body>
</html>
