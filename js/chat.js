document.addEventListener("DOMContentLoaded", () => {
    const userIdElement = document.getElementById("user-id");
    if (!userIdElement) {
        console.error("Error: element #user-id fetch fault!");
        return;
    }

    const userId = Number(userIdElement.value);
    const users = document.querySelectorAll(".user-item");
    const chatTitle = document.getElementById("chat-title");
    const messagesDiv = document.getElementById("messages");
    const messageInput = document.getElementById("message-input");
    let selectedUserId = null;
    let selectedUserName = "";

    users.forEach(user => {
        user.addEventListener("click", () => {
            selectedUserId = Number(user.dataset.userId);
            selectedUserName = user.textContent.trim();
            chatTitle.textContent = `Chat with ${selectedUserName}`;
            messagesDiv.innerHTML = "<p>Loading messages history...</p>";

            fetch(`chat_handlers/load_messages.php?receiver_id=${selectedUserId}`)
                .then(response => response.json())
                .then(messages => {
                    messagesDiv.innerHTML = "";
                    messages.forEach(msg => {
                        const sender = msg.sender_id === userId ? "Вы" : selectedUserName;
                        messagesDiv.innerHTML += `<p><strong>${sender}:</strong> ${msg.content} <small>(${msg.created_at})</small></p>`;
                    });
                });
        });
    });

    document.getElementById("chat-form").addEventListener("submit", (e) => {
        e.preventDefault();
        sendMessage();
    });

    function sendMessage() {
        const messageText = messageInput.value.trim();
        if (!selectedUserId || !messageText) return;

        messageInput.value = "";

        fetch("chat_handlers/send_message.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `receiver_id=${selectedUserId}&message=${encodeURIComponent(messageText)}`
        }).then(response => response.json())
            .then(data => {
                if (data.success) {
                    messagesDiv.innerHTML += `<p><strong>You:</strong> ${messageText}</p>`;
                    messagesDiv.scrollTop = messagesDiv.scrollHeight;
                }
            }).catch(error => console.error("Error in sending message:", error));
    }

    setInterval(() => {
        if (selectedUserId) {
            fetch(`chat_handlers/load_messages.php?receiver_id=${selectedUserId}`)
                .then(response => response.json())
                .then(messages => {
                    messagesDiv.innerHTML = "";
                    messages.forEach(msg => {
                        const sender = msg.sender_id === userId ? "You" : selectedUserName;
                        messagesDiv.innerHTML += `<p><strong>${sender}:</strong> ${msg.content} <small>(${msg.created_at})</small></p>`;
                    });
                }).catch(error => console.error("Error fetching chat:", error));
        }
    }, 2000);

    setInterval(() => {
        fetch("chat_handlers/update_status.php")
            .catch(error => console.error("Error fetching status:", error));
    }, 15000);
});
