document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".dislike-btn").forEach(button => {
        button.addEventListener("click", function () {
            let postId = this.getAttribute("data-post-id");
            let dislikeCountElem = this.querySelector(".dislike-count");
            let likeBtn = document.querySelector(`.like-btn[data-post-id="${postId}"]`);
            let likeCountElem = likeBtn ? likeBtn.querySelector(".like-count") : null;

            fetch("acchandlers/dislike_post.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "post_id=" + postId
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "disliked") {
                        dislikeCountElem.textContent = parseInt(dislikeCountElem.textContent) + 1;
                        button.classList.add("active");

                        // Если был лайк, убираем его
                        if (likeCountElem && parseInt(likeCountElem.textContent) > 0) {
                            likeCountElem.textContent = parseInt(likeCountElem.textContent) - 1;
                        }
                        if (likeBtn) {
                            likeBtn.classList.remove("active"); // Гарантированно убираем подсветку лайка
                        }
                    } else if (data.status === "undisliked") {
                        dislikeCountElem.textContent = parseInt(dislikeCountElem.textContent) - 1;
                        button.classList.remove("active");
                    }
                })
                .catch(error => console.error("Error:", error));
        });
    });
});
