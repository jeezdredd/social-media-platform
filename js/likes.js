document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".like-btn").forEach(button => {
        button.addEventListener("click", function () {
            let postId = this.getAttribute("data-post-id");
            let likeCountElem = this.querySelector(".like-count");
            let dislikeBtn = document.querySelector(`.dislike-btn[data-post-id="${postId}"]`);
            let dislikeCountElem = dislikeBtn ? dislikeBtn.querySelector(".dislike-count") : null;

            fetch("acchandlers/like_post.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "post_id=" + postId
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "liked") {
                        likeCountElem.textContent = parseInt(likeCountElem.textContent) + 1;
                        button.classList.add("active");

                        // Если был дизлайк, убираем его
                        if (dislikeCountElem && parseInt(dislikeCountElem.textContent) > 0) {
                            dislikeCountElem.textContent = parseInt(dislikeCountElem.textContent) - 1;
                        }
                        if (dislikeBtn) {
                            dislikeBtn.classList.remove("active"); // Гарантированно убираем подсветку дизлайка
                        }
                    } else if (data.status === "unliked") {
                        likeCountElem.textContent = parseInt(likeCountElem.textContent) - 1;
                        button.classList.remove("active");
                    }
                })
                .catch(error => console.error("Error:", error));
        });
    });
});
