document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.pin-post-btn').forEach(button => {
        button.addEventListener('click', function() {
            const postId = this.getAttribute('data-post-id');
            togglePinPost(postId, this);
        });
    });

    function togglePinPost(postId, button) {
        const formData = new FormData();
        formData.append('post_id', postId);

        fetch('acchandlers/pin_post.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update button text and status
                    if (data.is_pinned) {
                        button.innerHTML = '📌 Unpin';
                        button.classList.add('pinned');

                        // Move post to top
                        const post = button.closest('.post');
                        post.setAttribute('data-is-pinned', '1');
                        const postsContainer = document.getElementById('posts');
                        postsContainer.insertBefore(post, postsContainer.firstChild);
                    } else {
                        button.innerHTML = '📌 Pin';
                        button.classList.remove('pinned');
                        button.closest('.post').setAttribute('data-is-pinned', '0');

                        // Reload to restore proper order
                        window.location.reload();
                    }
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }
});