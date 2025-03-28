<?php
session_start();
require_once "../db/database.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Not authenticated"]);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$follower_id = $_SESSION['user_id'];
$followed_id = isset($data['user_id']) ? (int)$data['user_id'] : 0;

if ($followed_id === 0 || $follower_id === $followed_id) {
    echo json_encode(["success" => false, "message" => "Invalid user ID"]);
    exit;
}

// Check if already following
$stmt = $pdo->prepare("SELECT * FROM followers WHERE follower_id = ? AND followed_id = ?");
$stmt->execute([$follower_id, $followed_id]);
$following = $stmt->rowCount() > 0;

if ($following) {
    // Unfollow
    $stmt = $pdo->prepare("DELETE FROM followers WHERE follower_id = ? AND followed_id = ?");
    $stmt->execute([$follower_id, $followed_id]);
    echo json_encode(["success" => true, "following" => false]);
} else {
    // Follow
    $stmt = $pdo->prepare("INSERT INTO followers (follower_id, followed_id, created_at) VALUES (?, ?, NOW())");
    $stmt->execute([$follower_id, $followed_id]);
    echo json_encode(["success" => true, "following" => true]);
}