<?php
global $pdo;
session_start();
require_once "../auth/auth_check.php";
require_once "../db/database.php";

$data = json_decode(file_get_contents('php://input'), true);
$post_id = $data["post_id"];
$user_id = $data["user_id"];
$stmt = $pdo->prepare("INSERT INTO moderation (user_id, post_id) VALUES (?, ?)");
$stmt->execute([$user_id, $post_id]);

echo json_encode([
    "status" => "success",
    "message" => "Complaint created successfully."
]);
exit;