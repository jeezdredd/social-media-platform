<?php

function sendHttpNotification($endpoint, $data) {
    $url = "http://localhost:8081/notify/" . $endpoint;

    $options = [
        'http' => [
            'header'  => "Content-type: application/json",
            'method'  => 'POST',
            'content' => json_encode($data)
        ]
    ];

    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result === FALSE) {
        error_log("Failed to send notification to endpoint: $endpoint");
        return false;
    }

    return json_decode($result, true);
}

function notifyNewPost($postId, $authorId, $username) {
    return sendHttpNotification('post', [
        "postId" => $postId,
        "authorId" => $authorId,
        "username" => $username
    ]);
}

function notifyNewComment($postOwnerId, $commenterId, $username) {
    return sendHttpNotification('comment', [
        "postOwnerId" => $postOwnerId,
        "commenterId" => $commenterId,
        "username" => $username
    ]);
}

function notifyNewLike($postOwnerId, $likerId, $username) {
    return sendHttpNotification('like', [
        "postOwnerId" => $postOwnerId,
        "likerId" => $likerId,
        "username" => $username
    ]);
}