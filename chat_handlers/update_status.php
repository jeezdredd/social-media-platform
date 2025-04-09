<?php
// Start session first
session_start();
// Use absolute paths with __DIR__
require_once __DIR__ . '/../auth/auth_check.php';
require_once __DIR__ . '/../db/database.php';

// Set proper content type
header('Content-Type: application/json');

try {
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("UPDATE users SET status = 'online', last_seen = NOW() WHERE id = ?");
    $stmt->execute([$user_id]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}