function showNotification(title, body) {
    if (localStorage.getItem("notificationsEnabled") === "false") return;
    if (document.visibilityState === "visible") return;

    if (Notification.permission !== "granted") {
        Notification.requestPermission();
    }

    if (Notification.permission === "granted") {
        new Notification(title, {
            body: body,
            icon: 'img/icon.jpg'
        });
    }
}

function playSound() {
    if (localStorage.getItem("notificationsEnabled") === "false") return;
    if (document.visibilityState === "visible") return;
    var sound = document.getElementById("notifySound");
    if (sound) {
        sound.play();
    }
}

document.addEventListener("DOMContentLoaded", function () {
    let maxPostId = Math.max(...Array.from(document.querySelectorAll(".post")).map(post => parseInt(post.getAttribute("data-post-id")) || 0), 0);
    localStorage.setItem("lastPostId", localStorage.getItem("lastPostId") || maxPostId);
    localStorage.setItem("lastCommentId", localStorage.getItem("lastCommentId") || 0);
    localStorage.setItem("lastLikeId", localStorage.getItem("lastLikeId") || 0);

    function checkNotifications() {
        let lastPostId = localStorage.getItem("lastPostId");
        let lastCommentId = localStorage.getItem("lastCommentId");
        let lastLikeId = localStorage.getItem("lastLikeId");

        let params = new URLSearchParams({
            lastPostId: lastPostId,
            lastCommentId: lastCommentId,
            lastLikeId: lastLikeId
        });

        fetch("acchandlers/check_notifications.php?" + params.toString())
            .then(response => response.json())
            .then(data => {
                if (data.new_posts > 0) {
                    showNotification("New post", "Someone published a new post!");
                    playSound();
                    localStorage.setItem("lastPostId", data.currentMaxPostId);
                }
                if (data.new_comments > 0) {
                    data.new_comments_data.forEach(comment => {
                        showNotification("New comment", `${comment.username} commented on a post!`);
                    });
                    playSound();
                    localStorage.setItem("lastCommentId", data.currentMaxCommentId);
                }
                if (data.new_likes > 0) {
                    data.new_likes_data.forEach(like => {
                        showNotification("New like", `${like.username} liked a post!`);
                    });
                    playSound();
                    localStorage.setItem("lastLikeId", data.currentMaxLikeId);
                }
            })
            .catch(error => console.error("Error checking notifications:", error));
    }

    setInterval(checkNotifications, 5000);
});
