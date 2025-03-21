document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".delete-post-btn").forEach(button => {
        button.addEventListener("click", function () {
            if (!confirm("Are you sure you want to delete this post?")) return;

            let postId = this.getAttribute("data-post-id");
            let postElement = document.querySelector(`.post[data-post-id="${postId}"]`);
            postElement.style.opacity = "0.5";

            // Container "undo" button
            let undoDiv = document.createElement("div");
            undoDiv.className = "undo-container";
            undoDiv.innerHTML = `<button class="undo-btn">Undo deletion</button>`;
            postElement.appendChild(undoDiv);

            // Undo 5 seconds
            let deletionTimer = setTimeout(() => {
                fetch("acchandlers/delete_post.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "post_id=" + postId
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            postElement.remove();
                        } else {
                            alert(data.message);
                            postElement.style.opacity = "1";
                            undoDiv.remove();
                        }
                    })
                    .catch(error => {
                        console.error("Error:", error);
                        postElement.style.opacity = "1";
                        undoDiv.remove();
                    });
            }, 5000);

            // Undo button
            undoDiv.querySelector(".undo-btn").addEventListener("click", function () {
                clearTimeout(deletionTimer);
                undoDiv.remove();
                postElement.style.opacity = "1";
            });
        });
    });
});

document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".delete-comment-btn").forEach(button => {
        button.addEventListener("click", function () {
            if (!confirm("Are you sure you want to delete this comment?")) return;

            let commentId = this.getAttribute("data-comment-id");
            let commentElement = document.querySelector(`.comment[data-comment-id="${commentId}"]`);
            commentElement.style.opacity = "0.5";

            let undoDiv = document.createElement("div");
            undoDiv.className = "undo-container";
            undoDiv.innerHTML = `<button class="undo-btn">Undo deletion</button>`;
            commentElement.appendChild(undoDiv);

            let deletionTimer = setTimeout(() => {
                fetch("acchandlers/delete_comment.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "comment_id=" + commentId
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            commentElement.remove();
                        } else {
                            alert(data.message);
                            commentElement.style.opacity = "1";
                            undoDiv.remove();
                        }
                    })
                    .catch(error => {
                        console.error("Error:", error);
                        commentElement.style.opacity = "1";
                        undoDiv.remove();
                    });
            }, 5000);

            undoDiv.querySelector(".undo-btn").addEventListener("click", function () {
                clearTimeout(deletionTimer);
                undoDiv.remove();
                commentElement.style.opacity = "1";
            });
        });
    });
});
