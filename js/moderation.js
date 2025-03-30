const moderationRequests = Array.from(document.querySelectorAll('.moderation__item'));

moderationRequests.forEach((request) => {
    request.addEventListener('click', async (event) => {
        if (!event.target.classList.contains('item__btn')) return;
        const complaintId = request.getAttribute('data-id');
        const postId = request.getAttribute('data-post-id');

        const response = await fetch('acchandlers/resolve_complaint.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                complaint_id: complaintId,
                post_id: postId,
                is_accepted: event.target.classList.contains('item__btn--approve')
            })
        });
        const result = await response.json();
        if (result.status !== 'success') {
            return;
        }
        
        request.remove();
    });
});