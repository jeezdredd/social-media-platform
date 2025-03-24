function showNotification(title, body) {
    // Check if notifications turned on (default: yes)
    if (localStorage.getItem("notificationsEnabled") === "false") return;

    // If page active, no new notifications pushed
    if (document.visibilityState === "visible") return;

    // Request notification access (in browser)
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

// Sound function
function playSound() {
    if (localStorage.getItem("notificationsEnabled") === "false") return;

    if (document.visibilityState === "visible") return;
    var sound = document.getElementById("notifySound");
    if (sound) {
        sound.play();
    }
}

// Initialise variables retrieved from json
document.addEventListener("DOMContentLoaded", function () {
    // localStorage
    let postElements = document.querySelectorAll(".post");
    let maxPostId = 0;
    postElements.forEach(post => {
        let id = parseInt(post.getAttribute("data-post-id"));
        if (id > maxPostId) maxPostId = id;
    });
    if (!localStorage.getItem("lastPostId")) {
        localStorage.setItem("lastPostId", maxPostId);
    }
    if (!localStorage.getItem("lastCommentId")) {
        localStorage.setItem("lastCommentId", 0);
    }

    // checkNotifications function
    function checkNotifications() {
        let lastPostId = localStorage.getItem("lastPostId");
        let lastCommentId = localStorage.getItem("lastCommentId");

        let params = new URLSearchParams({
            lastPostId: lastPostId,
            lastCommentId: lastCommentId
        });

        fetch("notification/check_notifications.php?" + params.toString())
            .then(response => response.json())
            .then(data => {
                if (data.new_posts > 0) {
                    showNotification("New post.", "There is new post from user!");
                    playSound();
                    // update lastPostId
                    localStorage.setItem("lastPostId", data.currentMaxPostId);
                }
                if (data.new_comments > 0) {
                    showNotification("New comment.", "New comment on post!");
                    playSound();
                    localStorage.setItem("lastCommentId", data.currentMaxCommentId);
                }
            })
            .catch(error => console.error("Error checking notifications:", error));
    }

    setInterval(checkNotifications, 5000);
});