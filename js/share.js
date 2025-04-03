document.addEventListener('DOMContentLoaded', function() {
    const shareButtons = document.querySelectorAll('.share-btn');

    // Add click event listener to each share button
    shareButtons.forEach(button => {
        button.addEventListener('click', function() {
            const postId = this.dataset.postId;

            // Create a modal for sharing
            const modal = document.createElement('div');
            modal.classList.add('share-modal');
            modal.innerHTML = `
                <div class="share-modal-content">
                    <span class="share-modal-close">&times;</span>
                    <h3>Share this post</h3>
                    <textarea placeholder="Add a comment with your share (optional)" id="share-comment"></textarea>
                    <button id="confirm-share" data-post-id="${postId}">Share</button>
                </div>
            `;

            // Append modal to body
            document.body.appendChild(modal);

            // Show modal
            setTimeout(() => {
                modal.style.display = 'flex';
            }, 10);

            // Set up close button
            const closeButton = modal.querySelector('.share-modal-close');
            closeButton.addEventListener('click', function() {
                modal.style.opacity = '0';
                setTimeout(() => {
                    document.body.removeChild(modal);
                }, 300);
            });

            // Close modal when clicking outside
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.style.opacity = '0';
                    setTimeout(() => {
                        document.body.removeChild(modal);
                    }, 300);
                }
            });

            // Set up share confirmation button
            const confirmButton = modal.querySelector('#confirm-share');
            confirmButton.addEventListener('click', function() {
                const comment = document.getElementById('share-comment').value;
                sharePost(postId, comment, modal);
            });
        });
    });

    // Function to handle the share post action
    function sharePost(postId, comment, modal) {
        // Create form data
        const formData = new FormData();
        formData.append('post_id', postId);
        formData.append('comment', comment);

        // Show loading state
        const confirmButton = modal.querySelector('#confirm-share');
        confirmButton.textContent = 'Sharing...';
        confirmButton.disabled = true;

        // Send share request to server
        fetch('acchandlers/share_post.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove modal with animation
                    modal.style.opacity = '0';
                    setTimeout(() => {
                        document.body.removeChild(modal);
                    }, 300);

                    // Show success message
                    const alert = document.createElement('div');
                    alert.classList.add('alert', 'success');
                    alert.textContent = 'Post shared successfully!';
                    document.body.appendChild(alert);

                    // Remove alert after 3 seconds
                    setTimeout(() => {
                        alert.style.opacity = '0';
                        setTimeout(() => {
                            document.body.removeChild(alert);
                            // Reload page to show the shared post
                            window.location.reload();
                        }, 300);
                    }, 2000);
                } else {
                    console.error('Error sharing post:', data.error);
                    confirmButton.textContent = 'Share';
                    confirmButton.disabled = false;

                    const errorMsg = document.createElement('p');
                    errorMsg.classList.add('error-message');
                    errorMsg.textContent = 'Error: ' + data.error;
                    modal.querySelector('.share-modal-content').appendChild(errorMsg);

                    setTimeout(() => {
                        errorMsg.remove();
                    }, 3000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                confirmButton.textContent = 'Share';
                confirmButton.disabled = false;

                const errorMsg = document.createElement('p');
                errorMsg.classList.add('error-message');
                errorMsg.textContent = 'An error occurred while sharing the post.';
                modal.querySelector('.share-modal-content').appendChild(errorMsg);

                setTimeout(() => {
                    errorMsg.remove();
                }, 3000);
            });
    }

    // Add required CSS to the page
    const styleSheet = document.createElement('style');
    styleSheet.textContent = `
        .share-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .share-modal[style*="display: flex"] {
            opacity: 1;
        }
        
        .share-modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            max-width: 500px;
            width: 90%;
            position: relative;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .share-modal-close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 24px;
            cursor: pointer;
        }
        
        .share-modal h3 {
            margin-top: 0;
            margin-bottom: 15px;
        }
        
        .share-modal textarea {
            width: 100%;
            height: 100px;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: none;
            font-family: inherit;
        }
        
        .share-modal button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        
        .share-modal button:hover {
            background-color: #45a049;
        }
        
        .share-modal button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }
        
        .error-message {
            color: #f44336;
            margin: 10px 0 0;
        }
        
        .alert {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            padding: 10px 20px;
            border-radius: 4px;
            color: white;
            z-index: 1001;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            transition: opacity 0.3s ease;
        }
        
        .alert.success {
            background-color: #4CAF50;
        }
        
        .alert.error {
            background-color: #f44336;
        }
    `;
    document.head.appendChild(styleSheet);
});