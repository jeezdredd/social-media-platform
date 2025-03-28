<?php
session_start();
require_once "../db/database.php";

// Get profile user ID from URL
$profile_user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$current_user_id = $_SESSION["user_id"];

// If no ID provided or same as current user, redirect to own profile
if ($profile_user_id == 0 || $profile_user_id == $current_user_id) {
    header("Location: ../dashboard.php");
    exit;
}

// Get user data
$stmt = $pdo->prepare("SELECT username, profile_pic FROM users WHERE id = ?");
$stmt->execute([$profile_user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// If user not found
if (!$user) {
    $_SESSION["error_message"] = "User not found";
    header("Location: ../posts.php");
    exit;
}

// Fix profile pic path
$profilePic = $user['profile_pic'] ? "../" . $user['profile_pic'] : "../upload/default.jpg";

// Check if following
$stmt = $pdo->prepare("SELECT * FROM followers WHERE follower_id = ? AND followed_id = ?");
$stmt->execute([$current_user_id, $profile_user_id]);
$isFollowing = $stmt->rowCount() > 0;

// Get follower and following counts
$stmt = $pdo->prepare("SELECT COUNT(*) FROM followers WHERE followed_id = ?");
$stmt->execute([$profile_user_id]);
$followersCount = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM followers WHERE follower_id = ?");
$stmt->execute([$profile_user_id]);
$followingCount = $stmt->fetchColumn();

// Get current user's favorites for comparison
$stmt = $pdo->prepare("SELECT post_id FROM favorites WHERE user_id = ?");
$stmt->execute([$current_user_id]);
$favorites = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Get user's posts
$sql = "SELECT posts.*, users.username, users.profile_pic
        FROM posts
        INNER JOIN users ON posts.user_id = users.id
        WHERE posts.user_id = :user_id
        ORDER BY posts.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':user_id', $profile_user_id, PDO::PARAM_INT);
$stmt->execute();

$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($user["username"]) ?>'s Profile</title>
    <link rel="stylesheet" href="../styles/dashboard.css">
    <link rel="stylesheet" href="../styles/loader.css">
    <link rel="stylesheet" href="../styles/styles.css">
    <link rel="stylesheet" href="../styles/comments.css">
    <link rel="stylesheet" href="../styles/undo.css">
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

        .follow-stats {
            display: flex;
            gap: 20px;
            margin-top: 10px;
            justify-content: center;
        }

        .stat-item {
            cursor: pointer;
            text-align: center;
        }

        .stat-item:hover {
            color: #1da1f2;
        }

        .stat-count {
            font-weight: bold;
            font-size: 18px;
        }

        .user-list {
            max-height: 300px;
            overflow-y: auto;
            margin-top: 15px;
        }

        .user-list-item {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .user-list-item img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 15px;
        }

        .user-list-item a {
            font-weight: bold;
            text-decoration: none;
            color: #333;
        }

        .user-list-item a:hover {
            color: #1da1f2;
        }

        .modal-content {
            width: 400px;
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
        <a href="../dashboard.php" class="btn">My Profile</a>
        <a href="../posts.php" class="btn">Feed</a>
        <a href="../chat.php" class="btn">Messages</a>
        <a href="../favorites.php" class="btn">Favorites</a>
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
            <button id="followBtn" class="follow-btn <?= $isFollowing ? 'following' : '' ?>" data-user-id="<?= $profile_user_id ?>">
                <?= $isFollowing ? 'Unfollow' : 'Follow' ?>
            </button>
        </div>
    </div>

    <!-- User's posts section -->
    <div class="feed-container">
        <h3><?= htmlspecialchars($user["username"]) ?>'s posts</h3>

        <!-- Posts list -->
        <div id="posts">
            <?php if (empty($posts)): ?>
                <p>This user hasn't published any posts yet.</p>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <div class="post" data-post-id="<?= $post['id'] ?>">
                        <div class="post-header">
                            <img src="../<?= htmlspecialchars($post['profile_pic'] ?: 'upload/default.jpg') ?>" class="avatar" alt="Profile picture">
                            <a href="user_profile.php?id=<?= $post['user_id'] ?>" class="username-link"><?= htmlspecialchars($post['username']) ?></a>
                        </div>
                        <p><?= htmlspecialchars($post['content']) ?></p>
                        <?php if (!empty($post['image'])): ?>
                            <img src="../<?= htmlspecialchars($post['image']) ?>" class="post-image" alt="Post picture">
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
                                    <img src="../<?= htmlspecialchars($comment['profile_pic'] ?: 'upload/default.jpg') ?>" class="comment-avatar" alt="Avatar">
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

    <!-- Followers/Following modal -->
    <div id="userListModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeUserListModal">&times;</span>
            <h3 id="userListTitle">Followers</h3>
            <div id="userList" class="user-list">

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
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const currentUserId = <?= json_encode($current_user_id) ?>;
        const profileUserId = <?= json_encode($profile_user_id) ?>;

        // Logout modal functionality
        const logoutBtn = document.getElementById('logoutConfirmBtn');
        const logoutModal = document.getElementById('logoutConfirmModal');
        const confirmLogout = document.getElementById('confirmLogout');
        const cancelLogout = document.getElementById('cancelLogout');

        logoutBtn.addEventListener('click', function() {
            logoutModal.style.display = 'block';
        });

        confirmLogout.addEventListener('click', function() {
            window.location.href = '../auth/logout.php';
        });

        cancelLogout.addEventListener('click', function() {
            logoutModal.style.display = 'none';
        });

        // User list modal functionality
        const followersBtn = document.getElementById('followers-stat');
        const followingBtn = document.getElementById('following-stat');
        const userListModal = document.getElementById('userListModal');
        const userListTitle = document.getElementById('userListTitle');
        const userList = document.getElementById('userList');
        const closeUserListModal = document.getElementById('closeUserListModal');

        followersBtn.addEventListener('click', function() {
            loadUserList('followers', profileUserId);
        });

        followingBtn.addEventListener('click', function() {
            loadUserList('following', profileUserId);
        });

        closeUserListModal.addEventListener('click', function() {
            userListModal.style.display = 'none';
        });

        // Close modal when clicking outside of it
        window.addEventListener('click', function(event) {
            if (event.target == userListModal) {
                userListModal.style.display = 'none';
            }
            if (event.target == logoutModal) {
                logoutModal.style.display = 'none';
            }
        });

        function loadUserList(type, userId) {
            userListTitle.textContent = type === 'followers' ? 'Followers' : 'Following';
            userList.innerHTML = '<p>Loading...</p>';
            userListModal.style.display = 'block';

            fetch(`../api/get_users.php?type=${type}&user_id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.users.length === 0) {
                            userList.innerHTML = '<p>No users found</p>';
                        } else {
                            userList.innerHTML = '';
                            data.users.forEach(user => {
                                const item = document.createElement('div');
                                item.className = 'user-list-item';
                                const profilePic = user.profile_pic ? user.profile_pic : 'upload/default.jpg';

                                item.innerHTML = `
                                    <img src="../${profilePic}" alt="Profile picture">
                                    <a href="${user.id === currentUserId ? '../dashboard.php' : 'user_profile.php?id=' + user.id}">
                                        ${user.username}
                                    </a>
                                `;
                                userList.appendChild(item);
                            });
                        }
                    } else {
                        userList.innerHTML = `<p>Error: ${data.message}</p>`;
                    }
                })
                .catch(error => {
                    userList.innerHTML = `<p>Error: ${error.message}</p>`;
                });
        }
    });

    setTimeout(() => {
        document.getElementById("loader").classList.add("hidden");
    }, 1500);
</script>

<script src="../js/profile_page.js"></script>
<script src="../js/likes.js"></script>
<script src="../js/dislikes.js"></script>
<script src="../js/favorites.js"></script>
<script src="../js/comment.js"></script>
<script src="../js/follow.js"></script>

</body>
</html>