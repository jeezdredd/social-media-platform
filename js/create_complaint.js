const complainButtons = Array.from(document.querySelectorAll('.post__complain'));

complainButtons.forEach((button) => {
    button.addEventListener('click', async (event) => {
        event.preventDefault();
        const postId = button.getAttribute('data-post-id');
        const userId = button.getAttribute('data-user-id');
        const response = await fetch('acchandlers/create_complaint.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                post_id: postId,
                user_id: userId
            })
        });
        const result = await response.json();
        if (result.status !== 'success') {
            return;
        }
        const div = document.createElement('div');
        div.innerText = "Complaint is already sent!";
        button.replaceWith(div);
    });
})