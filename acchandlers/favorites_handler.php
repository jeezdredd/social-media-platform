<?php
session_start();
require_once "../db/database.php";

if (!isset($_SESSION['user_id'])) {
    die("Error: User not authorised.");
}

$user_id = $_SESSION['user_id'];
$post_id = $_POST['post_id'] ?? 0;

if ($post_id > 0) {
    // Check if the post exists in favourites
    $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND post_id = ?");
    $stmt->execute([$user_id, $post_id]);
    $favorite = $stmt->fetch();

    if ($favorite) {
        // Delete
        $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND post_id = ?");
        $stmt->execute([$user_id, $post_id]);
        echo "removed";
    } else {
        // Add
        $stmt = $pdo->prepare("INSERT INTO favorites (user_id, post_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $post_id]);
        echo "added";
    }
} else {
    echo "Error: incorrect post ID.";
}

