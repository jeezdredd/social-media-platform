document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".favorite-btn").forEach(button => {
        button.addEventListener("click", function () {
            let postId = this.getAttribute("data-id");
            let btn = this;

            fetch("acchandlers/favorites_handler.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "post_id=" + postId
            })
                .then(response => response.text())
                .then(data => {
                    if (data === "added") {
                        btn.textContent = "✅ In favorites";
                    } else if (data === "removed") {
                        btn.textContent = "⭐ Add to favorites";
                    }
                })
                .catch(error => console.error("Error:", error));
        });
    });
});

document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".remove-favorite").forEach(button => {
        button.addEventListener("click", function() {
            let postId = this.getAttribute("data-id");
            let postElement = this.closest(".post");

            fetch("acchandlers/favorites_handler.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `post_id=${postId}`
            })
                .then(response => response.text())
                .then(data => {
                    if (data === "removed") {
                        postElement.remove();
                    }
                })
                .catch(error => console.error("Error:", error));
        });
    });
});
