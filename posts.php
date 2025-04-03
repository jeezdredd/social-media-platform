<?php
require_once "auth/auth_check.php";
require_once "db/database.php";
require_once "db/queries/get_user.php";
require_once "db/queries/complaints/get_my_complaints.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

$stmtUser = $pdo->prepare("SELECT id, username, profile_pic, is_admin FROM users WHERE id = ?");
$stmtUser->execute([$_SESSION["user_id"]]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);

$complaints = getMyComplaints($pdo);

$limit = $_GET['offset'] ?? 7;

$stmtUserLikes = $pdo->prepare("SELECT post_id FROM likes WHERE user_id = ?");
$stmtUserLikes->execute([$user_id]);
$userLikes = $stmtUserLikes->fetchAll(PDO::FETCH_COLUMN);

$stmtUserDislikes = $pdo->prepare("SELECT post_id FROM dislikes WHERE user_id = ?");
$stmtUserDislikes->execute([$user_id]);
$userDislikes = $stmtUserDislikes->fetchAll(PDO::FETCH_COLUMN);

$totalSql = "SELECT COUNT(*) FROM posts";
$stmt = $pdo->query($totalSql);
$totalPosts = $stmt->fetchColumn();

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
        <link rel="stylesheet" href="styles/loader.css">
    </head>
    <body data-user-id="<?= $_SESSION['user_id'] ?>">

    <audio id="notifySound" src="sounds/notify.mp3" preload="auto"></audio>

    <div class="loader-container" id="loader">
        <div class="loader">
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>

    <!-- upper nav bar -->
    <div class="navbar">
        <div class="nav-left">
            <h2>Feed</h2>
        </div>
        <div class="nav-right">
            <a href="dashboard.php" class="btn">Profile</a>
            <a href="chat.php" class="btn">Messages</a>
            <a href="favorites.php" class="btn">Favorites</a>
            <?= $user['is_admin'] != 0 ? '<a href="moderation.php" class="btn">Moderation</a>' : ''; ?>
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
            <?= $user['is_admin'] != 0 ? '<a href="moderation.php" class="btn">Moderation</a>' : ''; ?>
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
                        <?php if (isset($post['is_share']) && $post['is_share'] == 1 && isset($post['original_post_id'])): ?>
                            <!-- Regular post header showing who shared -->
                            <div class="post-header">
                                <div class="post-header__left">
                                    <img src="<?= htmlspecialchars($post['profile_pic'] ?: 'upload/default.jpg') ?>" class="avatar" alt="Profile picture">
                                    <a href="profile/user_profile.php?id=<?= $post['user_id'] ?>" class="username-link">
                                        <?= htmlspecialchars($post['username']) ?>
                                        <span class="repost-label">reposted</span>
                                    </a>
                                </div>
                                <div class="post-header__right">
                                    <?php if ($post['user_id'] == $_SESSION["user_id"]): ?>
                                        <button class="delete-post-btn" data-post-id="<?= $post['id'] ?>">Delete post</button>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Share comment from reposter -->
                            <?php if (isset($post['share_comment']) && !empty($post['share_comment'])): ?>
                                <div class="user-share-comment"><?= htmlspecialchars($post['share_comment']) ?></div>
                            <?php endif; ?>

                            <!-- Original post in a box -->
                            <div class="repost-container">
                                <?php
                                $stmtOriginal = $pdo->prepare("SELECT posts.*, users.username, users.profile_pic FROM posts
              INNER JOIN users ON posts.user_id = users.id
              WHERE posts.id = ?");
                                $stmtOriginal->execute([$post['original_post_id']]);
                                $originalPost = $stmtOriginal->fetch(PDO::FETCH_ASSOC);
                                ?>
                                <div class="original-post">
                                    <div class="original-post-header">
                                        <img src="<?= htmlspecialchars($originalPost['profile_pic'] ?? 'upload/default.jpg') ?>" class="avatar" alt="Profile picture">
                                        <a href="profile/user_profile.php?id=<?= $originalPost['user_id'] ?? '#' ?>" class="username-link">
                                            <?= htmlspecialchars($originalPost['username'] ?? 'Unknown user') ?>
                                        </a>
                                    </div>
                                    <p class="original-post-content"><?= htmlspecialchars($originalPost['content'] ?? '') ?></p>
                                    <?php if (!empty($originalPost['image'])): ?>
                                        <img src="<?= htmlspecialchars($originalPost['image']) ?>" class="post-image" alt="Post image">
                                    <?php endif; ?>
                                    <div class="post-date"><?= $originalPost['created_at'] ?? '' ?></div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="post-header">
                                <div class="post-header__left">
                                    <img src="<?= htmlspecialchars($post['profile_pic'] ?: 'upload/default.jpg') ?>" class="avatar" alt="Profile picture">
                                    <a href="profile/user_profile.php?id=<?= $post['user_id'] ?>" class="username-link"><?= htmlspecialchars($post['username']) ?></a>
                                </div>
                                <div class="post-header__right">
                                    <?php if ($post['user_id'] == $_SESSION["user_id"]): ?>
                                        <button class="delete-post-btn" data-post-id="<?= $post['id'] ?>">Delete post</button>
                                    <?php endif; ?>
                                    <?php if ($user['id'] != $post['user_id']): ?>
                                        <?php if (in_array($post['id'], $complaints)): ?>
                                            <div>Complaint is already sent!</div>
                                        <?php else: ?>
                                            <button
                                                    class="post__complain"
                                                    data-post-id="<?= $post['id'] ?>"
                                                    data-user-id="<?= $user['id'] ?>"
                                            >
                                                Complain
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <p><?= htmlspecialchars($post['content']) ?></p>
                            <?php if (!empty($post['image'])): ?>
                                <img src="<?= htmlspecialchars($post['image']) ?>" class="post-image" alt="Post picture">
                            <?php endif; ?>
                            <div class="post-date"><?= $post['created_at'] ?></div>
                        <?php endif; ?>

                        <!-- likes button -->
                        <button class="like-btn <?= in_array($post['id'], $userLikes) ? "active" : ""; ?>" data-post-id="<?= $post['id'] ?>">
                            ❤️ <span class="like-count"><?= $post['likes_count'] ?></span>
                        </button>

                        <!-- dislike button -->
                        <button class="dislike-btn <?= in_array($post['id'], $userDislikes) ? "active" : ""; ?>" data-post-id="<?= $post['id'] ?>">
                            👎 <span class="dislike-count"><?= $post['dislikes_count'] ?></span>
                        </button>

                        <!-- share button -->
                        <button class="share-btn" data-post-id="<?= $post['id'] ?>">
                            🔄 Share
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

    <script src="js/likes.js"></script>
    <script src="js/dislikes.js"></script>
    <script src="js/favorites.js"></script>
    <script src="js/comment.js"></script>
    <script src="js/notification.js"></script>
    <script src="js/delete.js"></script>
    <script src="js/mobile_menu.js"></script>
    <script src="js/share.js"></script>
    <script src="js/create_complaint.js"></script>

    <script>
        setTimeout(() => {
            document.getElementById("loader").classList.add("hidden");
        }, 1500);
    </script>

    <script>
        const moreBtn = document.querySelector(".more");
        const currentLimit = moreBtn.dataset.limit;
        moreBtn.addEventListener("click", () => {
            window.location.href = `posts.php?offset=${parseInt(currentLimit) + 7}`;
        })
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