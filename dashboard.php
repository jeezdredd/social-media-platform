<?php
require_once "auth/auth_check.php";
session_start();

require_once "db/database.php";

$stmt = $pdo->prepare("SELECT username, profile_pic FROM users WHERE id = ?");
$stmt->execute([$_SESSION["user_id"]]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
}

$profilePic = $user['profile_pic'] ?: 'upload/default.jpg';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Page</title>
    <link rel="stylesheet" href="styles/dashboard.css">
</head>
<body class="profile-page">



<div class="container">
    <h2>Welcome, <?php echo htmlspecialchars($user["username"]); ?>!</h2>


    <!-- Profile block -->
    <div class="profile-container">
        <img src="<?= htmlspecialchars($profilePic ?: 'upload/default.png') ?>" class="profile-pic" alt="Profile photo">
        <div class="profile-buttons">
            <button id="uploadPhotoBtn">Upload photo</button>
            <button id="editProfileBtn">Edit profile</button>
            <button id="logoutConfirmBtn">Logout</button>
        </div>
    </div>

    <p>You have logged in.</p>
    <a href="posts.php" class="btn btn-feed">Go to feed page</a>

    <div id="logoutConfirmModal" class="modal">
        <div class="modal-content">
            <h3>Are you sure you want to logout?</h3>
            <div class="modal-buttons">
                <button id="confirmLogout">Logout</button>
                <button id="cancelLogout">Abort</button>
            </div>
        </div>
    </div>

    <div id="editProfileModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Edit profile</h3>
            <form action="profile/update_profile.php" method="POST">
                <div class="edit__field">
                    <label for="username">New username:</label>
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

    <div id="uploadPhotoModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Upload new profile photo</h3>
            <form id="profilePicForm" enctype="multipart/form-data">
                <input style="margin-bottom: 10px;" type="file" id="profilePic" name="profilePic" required>
                <button class="dashboard__button" type="submit">Upload</button>
            </form>
            <p id="uploadMessage"></p>
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

<script src="js/profile_page.js"></script>

</body>
</html>
