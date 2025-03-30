<?php
session_start();
require_once "auth/auth_check.php";
require_once 'db/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: errors/403.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT id, username, status, profile_pic, last_seen FROM users WHERE id != ? ORDER BY status DESC, username ASC");
$stmt->execute([$user_id]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>
    <link rel="stylesheet" href="styles/chat.css">
    <link rel="stylesheet" href="styles/loader.css">
</head>
<body>

<div class="loader-container" id="loader">
    <div class="loader">
        <div></div>
        <div></div>
        <div></div>
    </div>
</div>

<input type="hidden" id="user-id" value="<?= $_SESSION['user_id'] ?? '' ?>">
<div class="chat-container">
    <?php
    function formatLastSeen($timestamp)
    {
        if (!$timestamp) return "Unknown";

        $time = strtotime($timestamp);
        $now = time();
        $today = strtotime("today");
        $yesterday = strtotime("yesterday");

        if ($time >= $today) {
            return "Today at " . date("H:i", $time);
        } elseif ($time >= $yesterday) {
            return "Yesterday at " . date("H:i", $time);
        } else {
            return date("d.m.Y at H:i", $time);
        }
    }
    ?>

    <div class="user-list">
        <h3>Choose message receiver:</h3>
        <?php foreach ($users as $user): ?>
            <?php
            $lastSeen = isset($user['last_seen']) ? formatLastSeen($user['last_seen']) : "Unknown";
            $avatarPath = htmlspecialchars($user['profile_pic'] ?: 'upload/default.jpg');
            ?>
            <button class="user-item" data-user-id="<?= $user['id'] ?>">
                <div class="user-header">
                    <img src="<?= $avatarPath ?>" alt="avatar" class="user-avatar">
                    <span class="user-name"><?= htmlspecialchars($user['username']) ?></span>
                </div>
                <span class="status <?= $user['status'] ?>">
                    <?= $user['status'] === 'online' ? '🟢 Online now' : '⚪ Offline' ?>
                </span>
                <?php if ($user['status'] !== 'online'): ?>
                    <span class="last-seen">Last seen: <?= $lastSeen ?></span>
                <?php endif; ?>
            </button>
        <?php endforeach; ?>
    </div>

    <div class="chat-box">
        <h3 id="chat-title">Select a user to start chatting</h3>
        <div id="messages">
            <div class="no-messages">
                <i class="message-icon">💬</i>
                <p>Select a user to start a conversation</p>
            </div>
        </div>
        <form id="chat-form">
            <input type="text" id="message-input" placeholder="Type a message..." required disabled>
            <button type="submit" id="send-button"></button>
        </form>
    </div>
</div>

<a href="posts.php" class="nav-link">← Back to feed</a>

<script src="js/chat.js" defer></script>
<script>
    setTimeout(() => {
        document.getElementById("loader").classList.add("hidden");
    }, 1000);
</script>
</body>
</html>