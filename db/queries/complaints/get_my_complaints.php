<?php

function getMyComplaints($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM moderation WHERE user_id = ?");
    $stmt->execute([$_SESSION["user_id"]]);
    $complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return array_column($complaints, 'post_id');
}