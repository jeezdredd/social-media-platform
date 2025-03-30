<?php

function getModerationRequiests($pdo) {
    $stmt = $pdo->prepare("
    SELECT 
        moderation.*, 
        posts.content, 
        author.username AS author, 
        complainer.username AS complainer
    FROM moderation
    JOIN posts ON moderation.post_id = posts.id
    JOIN users AS author ON posts.user_id = author.id
    JOIN users AS complainer ON moderation.user_id = complainer.id
        ");
    $stmt->execute();
    $moderations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $moderations;
}