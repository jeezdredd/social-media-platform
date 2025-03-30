<?php
global $pdo;
session_start();
require_once "../auth/auth_check.php";
require_once "../db/database.php";

$data = json_decode(file_get_contents('php://input'), true);
$complaint_id = $data["complaint_id"];
$post_id = $data["post_id"];
$is_accepted = $data["is_accepted"];

$stmtComplaint = $pdo->prepare("DELETE FROM moderation WHERE id = ?");
$stmtComplaint->execute([$complaint_id]);

if ($is_accepted) {
    $stmtPost = $pdo->prepare("DELETE FROM posts WHERE id = ?");
    $stmtPost->execute([$post_id]);
} else {
    echo json_encode([
        "status" => "success",
        "message" => "Complaint rejected and post will not be deleted.",
        "deleted" => false
    ]);
    exit;
}

echo json_encode([
    "status" => "success",
    "message" => "Complaint accepted and post deleted successfully.",
    "deleted" => true
]);

