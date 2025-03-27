<?php
require_once "auth/auth_check.php";
session_start();
require_once 'db/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: errors/403.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT id, username, status, profile_pic, last_seen FROM users WHERE id != ?");
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
</head>
<body>
<input type="hidden" id="user-id" value="<?= $_SESSION['user_id'] ?? '' ?>">
<div class="chat-container">
    <?php
    function formatLastSeen($timestamp) {
        if (!$timestamp) return "Неизвестно";

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
        <h3>Выберите собеседника:</h3>
        <?php foreach ($users as $user): ?>
            <?php
            $lastSeen = isset($user['last_seen']) ? formatLastSeen($user['last_seen']) : "Unknown";
            $avatarPath = htmlspecialchars($user['profile_pic'] ?: 'upload/default.jpg');
            ?>
            <button class="user-item" data-user-id="<?= $user['id'] ?>">
            <span class="status <?= $user['status'] ?>">
                <?= $user['status'] === 'online' ? '🟢 Online' : '⚪' ?>
            </span>
                <img src="<?= $avatarPath ?>" alt="avatar" class="user-avatar">
                <?= htmlspecialchars($user['username']) ?>
                <br>
                <span class="last-seen"><?= $user['status'] === 'online' ? '' : "Last seen: $lastSeen" ?></span>
            </button>
        <?php endforeach; ?>
    </div>

    <div class="chat-box">
        <h3 id="chat-title">Chat</h3>
        <div id="messages"></div>
        <form id="chat-form">
            <input type="text" id="message-input" placeholder="Enter your message..." required>
            <button type="submit" id="send-button">Send</button>
        </form>
    </div>
    <a href="posts.php">To feed page</a>
</div>
<script src="js/chat.js" defer></script>
</body>
</html>