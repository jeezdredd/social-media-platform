document.addEventListener('DOMContentLoaded', function() {
    const followBtn = document.getElementById('followBtn');

    if (followBtn) {
        followBtn.addEventListener('click', function() {
            const userId = this.dataset.userId;
            const isFollowing = this.classList.contains('following');

            fetch('../api/toggle_follow.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ user_id: userId })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.classList.toggle('following');
                        this.textContent = this.classList.contains('following') ? 'Unfollow' : 'Follow';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        });
    }
});