document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".comment-form").forEach(form => {
        form.addEventListener("submit", function (e) {
            e.preventDefault();
            let postId = this.getAttribute("data-post-id");
            let formData = new FormData(this);
            formData.append("post_id", postId);

            fetch("acchandlers/add_comment.php", {
                method: "POST",
                body: new URLSearchParams(formData)
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let deleteButtonHTML = "";
                        if (parseInt(data.comment.user_id) === parseInt(currentUserId)) {
                            deleteButtonHTML = `<button class="delete-comment-btn" data-comment-id="${data.comment.id}">Delete</button>`;
                        }

                        const parsedContent = parseMarkdownClient(data.comment.content);

                        let commentBlock = document.createElement("div");
                        commentBlock.className = "comment";
                        commentBlock.setAttribute('data-comment-id', data.comment.id);
                        commentBlock.innerHTML = `
                            <img src="${data.comment.profile_pic}" class="comment-avatar" alt="Avatar">
                            <div class="comment-content">
                                <strong>${data.comment.username}:</strong>
                                <div class="comment-text">${parsedContent}</div>
                                <div class="comment-date">${data.comment.created_at}</div>
                            </div>
                            ${deleteButtonHTML}
                        `;

                        let commentsContainer = document.getElementById("comments-" + postId);
                        commentsContainer.appendChild(commentBlock);
                        this.reset();

                        let newDeleteBtn = commentBlock.querySelector(".delete-comment-btn");
                        if (newDeleteBtn) {
                            newDeleteBtn.addEventListener("click", function () {
                                if (!confirm("Are you sure you want to delete this comment?")) return;

                                let commentId = this.getAttribute("data-comment-id");
                                commentBlock.style.opacity = "0.5";

                                let undoDiv = document.createElement("div");
                                undoDiv.className = "undo-container";
                                undoDiv.innerHTML = `<button class="undo-btn">Undo deletion</button>`;
                                commentBlock.appendChild(undoDiv);

                                let deletionTimer = setTimeout(() => {
                                    fetch("acchandlers/delete_comment.php", {
                                        method: "POST",
                                        headers: { "Content-Type": "application/x-www-form-urlencoded" },
                                        body: "comment_id=" + commentId
                                    })
                                        .then(response => response.json())
                                        .then(delData => {
                                            if (delData.success) {
                                                commentBlock.remove();
                                            } else {
                                                alert(delData.message);
                                                commentBlock.style.opacity = "1";
                                                undoDiv.remove();
                                            }
                                        })
                                        .catch(error => {
                                            console.error("Error:", error);
                                            commentBlock.style.opacity = "1";
                                            undoDiv.remove();
                                        });
                                }, 5000);

                                undoDiv.querySelector(".undo-btn").addEventListener("click", function () {
                                    clearTimeout(deletionTimer);
                                    undoDiv.remove();
                                    commentBlock.style.opacity = "1";
                                });
                            });
                        }
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => console.error("Error:", error));
        });
    });
});

const textArea = document.querySelector(".post-input__area>textarea");
const charCounter = document.querySelector(".post-input__area>.post-input__counter");
const publishBtn = document.querySelector(".post-actions>button");

if (textArea.value.length === 0) {
    publishBtn.disabled = true;
    publishBtn.classList.add("disabled");
}

textArea.addEventListener("input", function (e) {
    const currentLength = e.currentTarget.value.length;
    charCounter.textContent = currentLength + "/255";

    if (currentLength > 0 && currentLength <= 255) {
        publishBtn.disabled = false;
        publishBtn.classList.remove("disabled");
    } else {
        publishBtn.disabled = true;
        publishBtn.classList.add("disabled");
    }

});
