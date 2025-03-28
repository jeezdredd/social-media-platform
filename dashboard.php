<?php
require_once "auth/auth_check.php";
require_once "db/database.php";

// Get user data
$stmt = $pdo->prepare("SELECT username, profile_pic FROM users WHERE id = ?");
$stmt->execute([$_SESSION["user_id"]]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
}

$profilePic = $user['profile_pic'] ?: 'upload/default.jpg';

// Get user's own posts
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT post_id FROM favorites WHERE user_id = ?");
$stmt->execute([$user_id]);
$favorites = $stmt->fetchAll(PDO::FETCH_COLUMN);

$sql = "SELECT posts.*, users.username, users.profile_pic 
        FROM posts 
        INNER JOIN users ON posts.user_id = users.id 
        WHERE posts.user_id = :user_id
        ORDER BY posts.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();

$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Page</title>
    <link rel="stylesheet" href="styles/dashboard.css">
    <link rel="stylesheet" href="styles/loader.css">
    <link rel="stylesheet" href="styles/styles.css">
    <link rel="stylesheet" href="styles/comments.css">
    <link rel="stylesheet" href="styles/undo.css">
    <style>
        .container {
            height: auto;
            min-height: 100vh;
            padding: 20px 0;
        }

        .feed-container {
            width: 100%;
            max-width: 600px;
            margin-top: 30px;
        }
    </style>
</head>
<body class="profile-page">

<!--<div class="loader-container" id="loader">-->
<!--    <div class="loader">-->
<!--        <div></div>-->
<!--        <div></div>-->
<!--        <div></div>-->
<!--    </div>-->
<!--</div>-->

<!-- Top navbar -->
<div class="navbar">
    <div class="nav-left">
        <h2>Profile</h2>
    </div>
    <div class="nav-right">
        <a href="chat.php" class="btn">Messages</a>
        <a href="posts.php" class="btn">Feed</a>
        <a href="chat.php" class="btn">Messages</a>
        <a href="favorites.php" class="btn">Favorites</a>
        <button id="logoutConfirmBtn" class="btn">Logout</button>
    </div>
</div>

<div class="container">
<!--    <h2>Welcome, --><?php //echo htmlspecialchars($user["username"]); ?><!--!</h2>-->

    <!-- Profile block -->
    <div class="profile-header">
        <div class="profile-left">
            <img src="<?= htmlspecialchars($profilePic) ?>" class="profile-pic" alt="Profile photo">
            <div class="profile-info">
                <h2><?php echo htmlspecialchars($user["username"]); ?></h2>
            </div>
        </div>
        <div class="profile-buttons">
            <button id="editProfileBtn">Edit profile</button>
        </div>
    </div>

    <!-- User's posts section -->
    <div class="feed-container">
        <h3>Your posts</h3>

        <!-- Post form -->
        <div class="post-form">
            <img src="<?= htmlspecialchars($profilePic) ?>" class="avatar" alt="avatar">
            <form action="acchandlers/post.php" method="POST" enctype="multipart/form-data" class="post-input">
                <textarea name="content" placeholder="What's on your mind?" required></textarea>
                <div class="post-actions">
                    <input type="file" name="image">
                    <button type="submit">Publish</button>
                </div>
            </form>
        </div>

        <!-- Posts list -->
        <div id="posts">
            <?php if (empty($posts)): ?>
                <p>You haven't published any posts yet.</p>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <div class="post" data-post-id="<?= $post['id'] ?>">
                        <div class="post-header">
                            <img src="<?= htmlspecialchars($post['profile_pic'] ?: 'upload/default.jpg') ?>" class="avatar" alt="Profile picture">
                            <p><?= htmlspecialchars($post['username']) ?></p>
                            <button class="delete-post-btn" data-post-id="<?= $post['id'] ?>">Delete post</button>
                        </div>
                        <p><?= htmlspecialchars($post['content']) ?></p>
                        <?php if (!empty($post['image'])): ?>
                            <img src="<?= htmlspecialchars($post['image']) ?>" class="post-image" alt="Post picture">
                        <?php endif; ?>
                        <div class="post-date"><?= $post['created_at'] ?></div>

                        <!-- Post actions -->
                        <button class="like-btn" data-post-id="<?= $post['id'] ?>">
                            ❤️ <span class="like-count"><?= $post['likes_count'] ?></span>
                        </button>
                        <button class="dislike-btn" data-post-id="<?= $post['id'] ?>">
                            👎 <span class="dislike-count"><?= $post['dislikes_count'] ?></span>
                        </button>
                        <button class="favorite-btn" data-id="<?= $post['id']; ?>">
                            <?= in_array($post['id'], $favorites) ? '✅ In favorites' : '⭐ Add to favorites'; ?>
                        </button>

                        <!-- Comments section -->
                        <div class="comments" id="comments-<?= $post['id'] ?>">
                            <?php
                            $stmtComments = $pdo->prepare("SELECT c.*, u.username, u.profile_pic FROM comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = ? ORDER BY c.created_at ASC");
                            $stmtComments->execute([$post['id']]);
                            $comments = $stmtComments->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($comments as $comment):
                                ?>
                                <div class="comment" data-comment-id="<?= $comment['id'] ?>">
                                    <img src="<?= htmlspecialchars($comment['profile_pic'] ?: 'upload/default.jpg') ?>" class="comment-avatar" alt="Avatar">
                                    <div class="comment-content">
                                        <strong><?= htmlspecialchars($comment['username']) ?>:</strong>
                                        <span><?= htmlspecialchars($comment['content']) ?></span>
                                        <div class="comment-date"><?= $comment['created_at'] ?></div>
                                    </div>
                                    <?php if ($comment['user_id'] == $_SESSION["user_id"]): ?>
                                        <button class="delete-comment-btn" data-comment-id="<?= $comment['id'] ?>">Delete</button>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Add comment form -->
                        <form class="comment-form" data-post-id="<?= $post['id'] ?>">
                            <input type="text" name="content" placeholder="Add a comment..." required>
                            <button type="submit">Comment</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Logout confirmation modal -->
    <div id="logoutConfirmModal" class="modal">
        <div class="modal-content">
            <h3>Are you sure you want to logout?</h3>
            <div class="modal-buttons">
                <button id="confirmLogout">Logout</button>
                <button id="cancelLogout">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Edit profile modal -->
    <div id="editProfileModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Edit profile</h3>

            <!-- Toggle buttons -->
            <div class="toggle-buttons">
                <button id="photoToggleBtn" class="toggle-btn active">Change Photo</button>
                <button id="infoToggleBtn" class="toggle-btn">Change Info</button>
            </div>

            <!-- Profile picture section -->
            <div id="photoSection" class="modal-section active">
                <div class="profile-pic-section">
                    <img src="<?= htmlspecialchars($profilePic) ?>" class="edit-profile-pic" alt="Profile photo">
                    <form id="profilePicForm" enctype="multipart/form-data">
                        <input type="file" id="profilePic" name="profilePic" class="file-input">
                        <button type="submit" class="dashboard__button">Upload new photo</button>
                    </form>
                    <p id="uploadMessage"></p>
                </div>
            </div>

            <!-- Profile info section -->
            <div id="infoSection" class="modal-section">
                <form action="profile/update_profile.php" method="POST">
                    <div class="edit__field">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" value="<?= htmlspecialchars($user["username"]) ?>" required>
                    </div>

                    <div class="edit__field">
                        <label for="password">New password:</label>
                        <input type="password" id="password" name="password">
                    </div>

                    <button class="dashboard__button" type="submit">Save changes</button>
                </form>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION["update_success"])): ?>
        <p class="success-message"><?= $_SESSION["update_success"] ?></p>
        <?php unset($_SESSION["update_success"]); ?>
    <?php elseif (isset($_SESSION["update_error"])): ?>
        <p class="error-message"><?= $_SESSION["update_error"] ?></p>
        <?php unset($_SESSION["update_error"]); ?>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const currentUserId = <?= json_encode($_SESSION['user_id']) ?>;
    });

    setTimeout(() => {
        document.getElementById("loader").classList.add("hidden");
    }, 1500);
</script>

<script src="js/profile_page.js"></script>
<script src="js/likes.js"></script>
<script src="js/dislikes.js"></script>
<script src="js/favorites.js"></script>
<script src="js/comment.js"></script>
<script src="js/delete.js"></script>

</body>
</html>