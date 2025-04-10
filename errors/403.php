<?php
http_response_code(403);
require_once __DIR__ . "/../db/database.php";
session_start();
$isLoggedIn = isset($_SESSION["user_id"]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Access Forbidden</title>
    <link rel="stylesheet" href="../styles/errors.css">
    <style>
        .buttons {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .primary-btn {
            background: #4285f4;
            color: white;
        }

        .primary-btn:hover {
            background: #2b6ed9;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(66, 133, 244, 0.3);
        }

        .secondary-btn {
            background: #f1f1f1;
            color: #333;
        }

        .secondary-btn:hover {
            background: #e3e3e3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
<div class="error-container">
    <h1>403</h1>
    <p>Access Forbidden</p>
    <p>Sorry, you don't have permission to access this page.</p>
    <div class="buttons">
        <?php if ($isLoggedIn): ?>
            <a href="/dmuk-coursework/posts.php" class="btn primary-btn">Go to Feed</a>
            <a href="/dmuk-coursework/dashboard.php" class="btn secondary-btn">My Profile</a>
            <a href="/dmuk-coursework/chat.php" class="btn secondary-btn">Messages</a>
        <?php else: ?>
            <a href="/dmuk-coursework/login.php" class="btn secondary-btn">Login</a>
            <a href="/dmuk-coursework/register.php" class="btn secondary-btn">Register</a>
        <?php endif; ?>
    </div>
</div>
</body>
</html>