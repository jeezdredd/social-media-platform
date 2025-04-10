<?php
http_response_code(404);
require_once __DIR__ . "/../db/database.php";
session_start();
$isLoggedIn = isset($_SESSION["user_id"]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found</title>
    <link rel="stylesheet" href="/dmuk-coursework/styles/errors.css">
    <style>
        .error-container {
            max-width: 500px;
            padding: 40px;
            text-align: center;
        }

        .error-code {
            font-size: 120px;
            font-weight: bold;
            color: #f44336;
            margin: 0;
            line-height: 1;
            text-shadow: 2px 2px 10px rgba(244, 67, 54, 0.2);
        }

        .error-title {
            font-size: 28px;
            margin: 10px 0 20px;
            color: #333;
        }

        .error-message {
            color: #666;
            margin-bottom: 30px;
            font-size: 16px;
            line-height: 1.5;
        }

        .error-image {
            width: 200px;
            margin-bottom: 30px;
        }

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
    <h1 class="error-code">404</h1>
    <h2 class="error-title">Oops! Page not found</h2>

    <img src="/dmuk-coursework/img/error-404.svg" alt="Page not found illustration" class="error-image"
         onerror="this.onerror=null; this.src='/dmuk-coursework/img/error-404.png';
                     if(this.src.includes('png')) this.style.display='none';">

    <p class="error-message">
        The page you're looking for doesn't exist or has been moved.
        Let's get you back on track!
    </p>

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