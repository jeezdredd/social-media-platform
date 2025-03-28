<?php
session_start();
require_once "../db/database.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Not authenticated"]);
    exit;
}

$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$type = isset($_GET['type']) ? $_GET['type'] : '';

if ($user_id === 0) {
    echo json_encode(["success" => false, "message" => "Invalid user ID"]);
    exit;
}

if (!in_array($type, ['followers', 'following'])) {
    echo json_encode(["success" => false, "message" => "Invalid type"]);
    exit;
}

try {
    if ($type === 'followers') {
        // Get users who follow the specified user
        $sql = "SELECT u.id, u.username, u.profile_pic 
                FROM users u 
                JOIN followers f ON u.id = f.follower_id 
                WHERE f.followed_id = ?
                ORDER BY u.username";
    } else {
        // Get users followed by the specified user
        $sql = "SELECT u.id, u.username, u.profile_pic 
                FROM users u 
                JOIN followers f ON u.id = f.followed_id 
                WHERE f.follower_id = ?
                ORDER BY u.username";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "users" => $users]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}