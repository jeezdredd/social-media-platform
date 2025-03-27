<?php
require_once "auth/auth_check.php";
session_start();
require_once "db/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

$stmtUser = $pdo->prepare("SELECT username, profile_pic FROM users WHERE id = ?");
$stmtUser->execute([$_SESSION["user_id"]]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);

$limit = $_GET['offset'] ?? 7;

$totalSql = "SELECT COUNT(*) FROM posts";
$stmt = $pdo->query($totalSql);
$totalPosts = $stmt->fetchColumn();

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT post_id FROM favorites WHERE user_id = ?");
$stmt->execute([$user_id]);
$favorites = $stmt->fetchAll(PDO::FETCH_COLUMN);

$sql = "SELECT posts.*, users.username, users.profile_pic 
        FROM posts 
        INNER JOIN users ON posts.user_id = users.id 
        ORDER BY posts.created_at DESC 
        LIMIT :limit OFFSET 0";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
$stmt->execute();

$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Feed page</title>
        <link rel="stylesheet" href="styles/styles.css">
        <link rel="stylesheet" href="styles/comments.css">
        <link rel="stylesheet" href="img/icon.jpg">
        <link rel="stylesheet" href="styles/undo.css">
    </head>
    <body>
    <script>
        const currentUserId = <?= json_encode($_SESSION['user_id']) ?>;
        console.log("Current user id:", currentUserId);
    </script>

    <audio id="notifySound" src="sounds/notify.mp3" preload="auto"></audio>

    <!-- upper nav bar -->
    <div class="navbar">
        <div class="nav-left">
            <h2>Feed</h2>
        </div>
        <div class="nav-right">
            <a href="dashboard.php" class="btn">Profile</a>
            <a href="chat.php" class="btn">Messages</a>
            <a href="favorites.php" class="btn">Favorites</a>
            <button id="toggleNotifications" class="btn">Mute notifications</button>
            <a href="auth/logout.php" class="btn btn-danger">Logout</a>
        </div>
    </div>

    <div class="burger">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M5 12H20" stroke="#000000" stroke-width="2" stroke-linecap="round"/>
            <path d="M5 17H20" stroke="#000000" stroke-width="2" stroke-linecap="round"/>
            <path d="M5 7H20" stroke="#000000" stroke-width="2" stroke-linecap="round"/>
        </svg>
    </div>

    <div class="navbar--mobile">
        <div class="navbar__close">
            <svg width="32px" height="32px" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg"
                 aria-hidden="true" role="img"
                 class="iconify iconify--emojione" preserveAspectRatio="xMidYMid meet">
                <path fill="#ff5a79"
                      d="M62 10.6L53.4 2L32 23.4L10.6 2L2 10.6L23.4 32L2 53.4l8.6 8.6L32 40.6L53.4 62l8.6-8.6L40.6 32z"></path>
            </svg>
        </div>
        <div class="navbar__list">
            <a href="dashboard.php" class="btn">Profile</a>
            <a href="chat.php" class="btn">Messages</a>
            <a href="#" class="btn">Settings</a>
            <button id="toggleNotifications" class="btn">Mute notifications</button>
            <a href="auth/logout.php" class="btn btn-danger">Logout</a>
        </div>
    </div>

    <!-- publish post form -->
    <div class="feed-container">
        <div class="post-form">
            <img src="<?= htmlspecialchars($user['profile_pic'] ?: 'upload/default.jpg') ?>" class="avatar"
                 alt="avatar">
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
                    <div class="post" data-post-id="<?= $post['id'] ?>">
                        <div class="post-header">
                            <img src="<?= htmlspecialchars($post['profile_pic'] ?: 'upload/default.jpg') ?>"
                                 class="avatar"
                                 alt="Profile picture">
                            <p><?= htmlspecialchars($post['username']) ?></p>
                            <?php if ($post['user_id'] == $_SESSION["user_id"]): ?>
                                <button class="delete-post-btn" data-post-id="<?= $post['id'] ?>">Delete post</button>
                            <?php endif; ?>
                        </div>
                        <p><?= htmlspecialchars($post['content']) ?></p>
                        <?php if (!empty($post['image'])): ?>
                            <img src="<?= htmlspecialchars($post['image']) ?>" class="post-image" alt="Post picture">
                        <?php endif; ?>
                        <div class="post-date"><?= $post['created_at'] ?></div>

                        <!-- likes button -->
                        <button class="like-btn" data-post-id="<?= $post['id'] ?>">
                            ❤️ <span class="like-count"><?= $post['likes_count'] ?></span>
                        </button>

                        <!-- dislike button -->
                        <button class="dislike-btn" data-post-id="<?= $post['id'] ?>">
                            👎 <span class="dislike-count"><?= $post['dislikes_count'] ?></span>
                        </button>

                        <!-- favorites button -->
                        <button class="favorite-btn" data-id="<?= $post['id']; ?>">
                            <?= in_array($post['id'], $favorites) ? '✅ In favorites' : '⭐ Add to favorites'; ?>
                        </button>

                        <!-- comments section -->
                        <div class="comments" id="comments-<?= $post['id'] ?>">
                            <?php
                            $stmtComments = $pdo->prepare("SELECT c.*, u.username, u.profile_pic FROM comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = ? ORDER BY c.created_at ASC");
                            $stmtComments->execute([$post['id']]);
                            $comments = $stmtComments->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($comments as $comment):
                                ?>
                                <div class="comment" data-comment-id="<?= $comment['id'] ?>">
                                    <img src="<?= htmlspecialchars($comment['profile_pic'] ?: 'upload/default.jpg') ?>"
                                         class="comment-avatar" alt="Avatar">
                                    <div class="comment-content">
                                        <strong><?= htmlspecialchars($comment['username']) ?>:</strong>
                                        <span><?= htmlspecialchars($comment['content']) ?></span>
                                        <div class="comment-date"><?= $comment['created_at'] ?></div>
                                    </div>
                                    <?php if ($comment['user_id'] == $_SESSION["user_id"]): ?>
                                        <button class="delete-comment-btn" data-comment-id="<?= $comment['id'] ?>">
                                            Delete
                                        </button>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- add comments form -->
                        <form class="comment-form" data-post-id="<?= $post['id'] ?>">
                            <input type="text" name="content" placeholder="Add a comment..." required>
                            <button type="submit">Comment</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>

        <?php
        if ($totalPosts >= $limit) {
            echo '<button class="more" data-limit="' . $limit . '">Load more</button>';
        }
        ?>

    </div>

    <script src="js/likes.js"></script>
    <script src="js/dislikes.js"></script>
    <script src="js/favorites.js"></script>
    <script src="js/comment.js"></script>
    <script src="js/notification.js"></script>
    <script src="js/delete.js"></script>
    <script src="js/mobile_menu.js"></script>

    <script>
        const moreBtn = document.querySelector(".more");
        const currentLimit = moreBtn.dataset.limit;
        moreBtn.addEventListener("click", () => {
            window.location.href = `posts.php?offset=${parseInt(currentLimit) + 7}`;
        })
    </script>

    <!-- posts page script for notification mute button  -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {

            if (localStorage.getItem("notificationsEnabled") === null) {
                localStorage.setItem("notificationsEnabled", "true");
            }
            let toggleBtn = document.getElementById("toggleNotifications");

            function updateToggleText() {
                let enabled = localStorage.getItem("notificationsEnabled");
                toggleBtn.textContent = (enabled === "true") ? "Mute notifications" : "Unmute notifications";
            }

            updateToggleText();

            toggleBtn.addEventListener("click", function () {
                let enabled = localStorage.getItem("notificationsEnabled");
                if (enabled === "true") {
                    localStorage.setItem("notificationsEnabled", "false");
                } else {
                    localStorage.setItem("notificationsEnabled", "true");
                }
                updateToggleText();
            });
        });
    </script>

    </body>
    </html>

    <!-- misc -->
<?php if ($success_message): ?>
    <div class="alert success"><?= htmlspecialchars($success_message) ?></div>
<?php endif; ?>

<?php if ($error_message): ?>
    <div class="alert error"><?= htmlspecialchars($error_message) ?></div>
<?php endif; ?>