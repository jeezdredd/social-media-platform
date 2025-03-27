<?php
require_once "auth/auth_check.php";
session_start();
require_once '../db/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "You are not authorised."]);
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_FILES['profilePic']) && $_FILES['profilePic']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = "../upload/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $imageName = uniqid() . "_" . basename($_FILES["profilePic"]["name"]);
    $imagePath = $uploadDir . $imageName;

    if (move_uploaded_file($_FILES["profilePic"]["tmp_name"], $imagePath)) {
        $imagePath = "upload/" . $imageName;

        $stmt = $pdo->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
        $stmt->execute([$imagePath, $user_id]);

        echo json_encode(["success" => true, "message" => "Profile photo updated!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Error uploading photos."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "You have to select file."]);
}

