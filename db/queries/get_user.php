<?php

function getUser($userId, $pdo) {
    $stmtUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmtUser->execute([$_SESSION["user_id"]]);
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

    return $user;
}
