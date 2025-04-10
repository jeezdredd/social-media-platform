<?php
require_once "utils/error_handler.php";
require_once "auth/auth_check.php";
require_once "db/database.php";
require_once "utils/markdown.php";

// Get user data
$stmt = $pdo->prepare("SELECT username, profile_pic FROM users WHERE id = ?");
$stmt->execute([$_SESSION["user_id"]]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
}

if (isset($_GET['user_id']) && !userExists($_GET['user_id'])) {
    handle_404();
}

$profilePic = $user['profile_pic'] ?: 'upload/default.jpg';


// User ID
$user_id = $_SESSION['user_id'];

// Get user likes and dislikes
$stmtUserLikes = $pdo->prepare("SELECT post_id FROM likes WHERE user_id = ?");
$stmtUserLikes->execute([$user_id]);
$userLikes = $stmtUserLikes->fetchAll(PDO::FETCH_COLUMN);

$stmtUserDislikes = $pdo->prepare("SELECT post_id FROM dislikes WHERE user_id = ?");
$stmtUserDislikes->execute([$user_id]);
$userDislikes = $stmtUserDislikes->fetchAll(PDO::FETCH_COLUMN);

$stmt = $pdo->prepare("SELECT post_id FROM favorites WHERE user_id = ?");
$stmt->execute([$user_id]);
$favorites = $stmt->fetchAll(PDO::FETCH_COLUMN);

$stmt = $pdo->prepare("SELECT COUNT(*) FROM followers WHERE followed_id = ?");
$stmt->execute([$_SESSION["user_id"]]);
$followersCount = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM followers WHERE follower_id = ?");
$stmt->execute([$_SESSION["user_id"]]);
$followingCount = $stmt->fetchColumn();

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$sql = "SELECT posts.*, users.username, users.profile_pic 
        FROM posts 
        INNER JOIN users ON posts.user_id = users.id 
        WHERE posts.user_id = :user_id
        ORDER BY posts.is_pinned DESC, posts.created_at DESC";

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
    <link rel="stylesheet" href="styles/advanced-editor.css">

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
            margin-left: 0;
        }
    </style>
</head>
<body class="profile-page">

<div class="loader-container" id="loader">
    <div class="loader">
        <div></div>
        <div></div>
        <div></div>
    </div>
</div>

<!-- Top navbar -->
<div class="navbar">
    <div class="nav-left">
        <h2>Profile</h2>
    </div>
    <div class="nav-right">
        <a href="chat.php" class="btn">Messages</a>
        <a href="posts.php" class="btn">Feed</a>
        <a href="favorites.php" class="btn">Favorites</a>
        <button id="logoutConfirmBtn" class="btn">Logout</button>
    </div>
</div>

<div class="container">

    <!-- Profile block -->
    <div class="profile-header">
        <div class="profile-left">
            <img src="<?= htmlspecialchars($profilePic) ?>" class="profile-pic" alt="Profile photo">
            <div class="profile-info">
                <h2><?= htmlspecialchars($user["username"]) ?></h2>
                <div class="follow-stats">
                    <div class="stat-item" id="followers-stat">
                        <div class="stat-count"><?= $followersCount ?></div>
                        <div>Followers</div>
                    </div>
                    <div class="stat-item" id="following-stat">
                        <div class="stat-count"><?= $followingCount ?></div>
                        <div>Following</div>
                    </div>
                </div>
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
                <p>You haven't posted anything yet.</p>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <div class="post" data-post-id="<?= $post['id'] ?>" data-is-pinned="<?= $post['is_pinned'] ?>">
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
                                    <button class="pin-post-btn <?= $post['is_pinned'] ? 'pinned' : '' ?>" data-post-id="<?= $post['id'] ?>">
                                        <?= $post['is_pinned'] ? '📌 Unpin' : '📌 Pin' ?>
                                    </button>
                                    <button class="delete-post-btn" data-post-id="<?= $post['id'] ?>">Delete post</button>
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
                                    <p class="original-post-content"><?= parseMarkdown(htmlspecialchars($originalPost['content'] ?? '')) ?></p>
                                    <?php if (!empty($originalPost['image'])): ?>
                                        <img src="<?= htmlspecialchars($originalPost['image']) ?>" class="post-image" alt="Post image">
                                    <?php endif; ?>
                                    <div class="post-date"><?= $originalPost['created_at'] ?? '' ?></div>
                                </div>
                            </div>
                        <?php else: ?>

                            <!-- Regular post display -->
                            <div class="post-header">
                                <div class="post-header__left">
                                    <img src="<?= htmlspecialchars($post['profile_pic'] ?: 'upload/default.jpg') ?>" class="avatar" alt="Profile picture">
                                    <a href="profile/user_profile.php?id=<?= $post['user_id'] ?>" class="username-link"><?= htmlspecialchars($post['username']) ?></a>
                                </div>
                                <div class="post-header__right">
                                    <button class="pin-post-btn <?= $post['is_pinned'] ? 'pinned' : '' ?>" data-post-id="<?= $post['id'] ?>">
                                        <?= $post['is_pinned'] ? '📌 Unpin' : '📌 Pin' ?>
                                    </button>
                                    <button class="delete-post-btn" data-post-id="<?= $post['id'] ?>">Delete post</button>
                                </div>
                            </div>
                            <p><?= parseMarkdown(htmlspecialchars($post['content'])) ?></p>
                            <?php if (!empty($post['image'])): ?>
                                <img src="<?= htmlspecialchars($post['image']) ?>" class="post-image" alt="Post picture">
                            <?php endif; ?>
                            <div class="post-date"><?= $post['created_at'] ?></div>
                        <?php endif; ?>

                        <!-- Post actions -->
                        <button class="like-btn <?= in_array($post['id'], $userLikes ?? []) ? "active" : ""; ?>" data-post-id="<?= $post['id'] ?>">
                            ❤️ <span class="like-count"><?= $post['likes_count'] ?? 0 ?></span>
                        </button>

                        <button class="dislike-btn <?= in_array($post['id'], $userDislikes ?? []) ? "active" : ""; ?>" data-post-id="<?= $post['id'] ?>">
                            👎 <span class="dislike-count"><?= $post['dislikes_count'] ?? 0 ?></span>
                        </button>

                        <button class="share-btn" data-post-id="<?= $post['id'] ?>">
                            🔄 Share
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
                                        <div class="comment-text"><?= parseMarkdown(htmlspecialchars($comment['content'])) ?></div>
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

    <!-- Followers/Following modal -->
    <div id="userListModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeUserListModal">&times;</span>
            <h3 id="userListTitle">Followers</h3>
            <div id="userList" class="user-list">
                <!-- Users will be loaded here via JavaScript -->
            </div>
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

<div id="externalLinkModal" class="modal">
    <div class="modal-content">
        <h3>External Link Warning</h3>
        <p>You are about to visit external URL:</p>
        <br>
        <div class="external-url-display"></div>
        <br>
        <p>Do you want to proceed?</p>
        <div class="modal-buttons">
            <button id="openExternalLink" class="primary-btn">Continue</button>
            <button id="cancelExternalLink" class="cancel-btn">Cancel</button>
        </div>
    </div>
</div>

<script>
    const currentUserId = <?= json_encode($_SESSION['user_id']) ?>;
    setTimeout(() => {
        document.getElementById("loader").classList.add("hidden");
    }, 1500);
</script>

<script src="js/profile_page.js"></script>
<script src="js/modal.js"></script>
<script src="js/likes.js"></script>
<script src="js/dislikes.js"></script>
<script src="js/favorites.js"></script>
<script src="js/comment.js"></script>
<script src="js/share.js"></script>
<script src="js/delete.js"></script>
<script src="js/advanced-editor.js"></script>
<script src="js/external-links.js"></script>
<script src="js/pin.js"></script>

</body>
</html>