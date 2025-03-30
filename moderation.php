<?php
require_once "auth/auth_check.php";
require_once "db/database.php";
require_once "db/queries/get_user.php";
require "db/queries/get_moderations.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];
$user = getUser($user_id, $pdo);
if ($user["is_admin"] == 0) {
    header("Location: posts.php");
    exit;
}

$moderations = getModerationRequiests($pdo);


// echo json_encode($moderations, JSON_UNESCAPED_UNICODE);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/moderation.css">
    <title>Moderation</title>
</head>
<body>
    <div class="moderation">
        <a class="moderation__back" href="posts.php">Back</a>
        <h2 class="moderation__title">Moderation</h2>
        <div class="moderation__list">
            <?php foreach ($moderations as $complaint): ?>
                <div class="moderation__item" data-id="<?= $complaint["id"] ?>" data-post-id="<?= $complaint["post_id"] ?>">
                    <div class="item__title">
                        <div class="item__person">
                            Post author: <strong><?= $complaint["author"] ?></strong>
                        </div>
                        <div class="item__person">
                            Complainer: <strong><?= $complaint["complainer"] ?></strong>
                        </div>
                    </div>
                    <div class="item__content">
                        <?= $complaint["content"] ?>
                    </div>
                    <div class="item__controls">
                        <button class="item__btn item__btn--approve">Accept</button>
                        <button class="item__btn item__btn--reject">Reject</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="js/moderation.js"></script>
</body>
</html>