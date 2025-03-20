document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".like-btn").forEach(button => {
        button.addEventListener("click", function () {
            let postId = this.getAttribute("data-post-id");
            let likeCountElem = this.querySelector(".like-count");

            fetch("acchandlers/like_post.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "post_id=" + postId
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "liked") {
                        likeCountElem.textContent = parseInt(likeCountElem.textContent) + 1;
                    } else if (data.status === "unliked") {
                        likeCountElem.textContent = parseInt(likeCountElem.textContent) - 1;
                    }
                })
                .catch(error => console.error("Error:", error));
        });
    });
});
