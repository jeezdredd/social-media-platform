<?php
require_once "auth/auth_check.php";
?>

<style>
    /* General */
    * {
        box-sizing: border-box;
    }

    h1, h2, h3, p, ul, ol, li {
        margin: 0;
        padding: 0;
    }

    body {
        font-family: Arial, sans-serif;
        background-color: #f0f2f5;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    /* Nav */
    .navbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: white;
        padding: 15px;
        margin-top: 20px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
        border-radius: 10px;
    }

    .nav-left {
        display: flex;
        align-items: center;
        gap: 15px;
        flex-grow: 1;
    }

    .nav-left h2 {
        margin: 0;
    }

    .nav-right {
        display: flex;
        gap: 15px;
        margin-left: 10px;
    }

    .nav-right .btn {
        text-decoration: none;
        background: #1d9bf0;
        color: white;
        padding: 8px 15px;
        border-radius: 20px;
        font-weight: bold;
    }

    .nav-right .btn:hover {
        background: #0c7abf;
    }

    .nav-right .btn-danger {
        background: #e0245e;
    }

    .nav-right .btn-danger:hover {
        background: #c21d4b;
    }
</style>

<div class="navbar">
    <div class="nav-left">
        <h2>Favorites</h2>
    </div>
    <div class="nav-right">
        <a href="dashboard.php" class="btn">Profile</a>
        <a href="posts.php" class="btn">Posts</a>
        <a href="chat.php" class="btn">Messages</a>
        <a href="favorites.php" class="btn">Favorites</a>
        <a href="auth/logout.php" class="btn btn-danger">Logout</a>
    </div>
</div>