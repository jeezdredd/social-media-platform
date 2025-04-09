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
    const chatForm = document.getElementById("chat-form");
    let selectedUserId = null;
    let selectedUserName = "";
    let messageCache = [];
    let isTyping = false;
    let typingTimeout = null;

    // User selection
    users.forEach(user => {
        user.addEventListener("click", () => {
            // Highlight selected user
            users.forEach(u => u.classList.remove("selected"));
            user.classList.add("selected");

            selectedUserId = Number(user.dataset.userId);
            selectedUserName = user.querySelector(".user-name").textContent.trim();
            chatTitle.textContent = `Chat with ${selectedUserName}`;
            messagesDiv.innerHTML = '<div class="loading-messages"><div class="spinner"></div><p>Loading messages...</p></div>';

            // Enable input field
            messageInput.disabled = false;

            // Reset cache when switching chats
            messageCache = [];
            fetchMessages();

            // Add active class to chat box
            document.querySelector(".chat-box").classList.add("active");

            // Focus on input field
            setTimeout(() => messageInput.focus(), 100);
        });
    });

    // Send message
    chatForm.addEventListener("submit", (e) => {
        e.preventDefault();
        sendMessage();
    });

    // Message input handling
    messageInput.addEventListener("input", () => {
        if (messageInput.value.trim() && !isTyping) {
            isTyping = true;
            document.getElementById("send-button").classList.add("active");
        } else if (!messageInput.value.trim() && isTyping) {
            isTyping = false;
            document.getElementById("send-button").classList.remove("active");
        }
    });

    // Enter to send, Shift+Enter for new line
    messageInput.addEventListener("keydown", (e) => {
        if (e.key === "Enter" && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    function fetchMessages() {
        if (!selectedUserId) return;

        fetch(`chat_handlers/load_messages.php?receiver_id=${selectedUserId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.text();
            })
            .then(text => {
                if (!text.trim()) {
                    throw new Error('Server returned empty response');
                }

                try {
                    return JSON.parse(text);
                } catch (error) {
                    throw new Error('Server returned invalid JSON');
                }
            })
            .then(messages => {
                if (messages.error) {
                    throw new Error(messages.error);
                }

                const hasNewMessages = messagesHaveChanged(messages);
                if (hasNewMessages) {
                    updateMessageDisplay(messages);
                    messageCache = [...messages];
                    messagesDiv.scrollTop = messagesDiv.scrollHeight;
                }
            })
            .catch(error => {
                messagesDiv.innerHTML = `<div class="error-message"><i class="error-icon">⚠️</i> Error loading messages: ${error.message}</div>`;
            });
    }

    function messagesHaveChanged(newMessages) {
        // First load or different message count
        if (messageCache.length === 0 || messageCache.length !== newMessages.length) {
            return true;
        }

        // Compare last message
        const lastNewMsg = newMessages[newMessages.length - 1];
        const lastCachedMsg = messageCache[messageCache.length - 1];

        return lastNewMsg.content !== lastCachedMsg.content ||
            lastNewMsg.created_at !== lastCachedMsg.created_at;
    }

    function updateMessageDisplay(messages) {
        if (messages.length === 0) {
            messagesDiv.innerHTML = '<div class="no-messages"><i class="message-icon">💬</i><p>No messages yet. Start a conversation!</p></div>';
            return;
        }

        // Group messages by date
        const groupedMessages = groupMessagesByDate(messages);
        let html = '';

        // Render messages grouped by date
        Object.keys(groupedMessages).forEach(date => {
            html += `<div class="date-separator">${date}</div>`;

            // Consecutive messages grouping
            let currentSender = null;
            let messageGroup = '';

            groupedMessages[date].forEach((msg, index) => {
                const isCurrentUser = msg.sender_id == userId;
                const messageClass = isCurrentUser ? "sent" : "received";
                const nextMsg = groupedMessages[date][index + 1];
                const isSameSender = nextMsg && nextMsg.sender_id === msg.sender_id;

                // Format time
                const messageTime = new Date(msg.created_at).toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit'
                });

                // Start new group if sender changes
                if (currentSender !== msg.sender_id) {
                    if (currentSender !== null) {
                        html += `<div class="message-group ${currentSender == userId ? 'sent-group' : 'received-group'}">${messageGroup}</div>`;
                        messageGroup = '';
                    }
                    currentSender = msg.sender_id;
                }

                // Add message to group
                messageGroup += `
                    <div class="message ${messageClass} ${!isSameSender ? 'last-in-group' : ''}">
                        ${msg.content}
                        <small class="message-time">${messageTime}</small>
                    </div>
                `;

                // End of messages or sender changes
                if (!isSameSender || index === groupedMessages[date].length - 1) {
                    html += `<div class="message-group ${currentSender == userId ? 'sent-group' : 'received-group'}">${messageGroup}</div>`;
                    messageGroup = '';
                    currentSender = nextMsg ? nextMsg.sender_id : null;
                }
            });
        });

        messagesDiv.innerHTML = html;
    }

    function groupMessagesByDate(messages) {
        const groups = {};

        messages.forEach(msg => {
            const date = new Date(msg.created_at).toLocaleDateString(undefined, {
                weekday: 'long',
                month: 'long',
                day: 'numeric'
            });

            if (!groups[date]) {
                groups[date] = [];
            }

            groups[date].push(msg);
        });

        return groups;
    }

    function sendMessage() {
        const messageText = messageInput.value.trim();
        if (!selectedUserId || !messageText) return;

        messageInput.value = "";
        messageInput.focus();
        document.getElementById("send-button").classList.remove("active");
        isTyping = false;

        // Optimistic UI update
        const now = new Date();
        const timeString = now.toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'});

        // Find existing group or create new one
        let lastGroup = messagesDiv.querySelector('.sent-group:last-child');
        if (lastGroup) {
            lastGroup.insertAdjacentHTML('beforeend', `
                <div class="message sent">
                    ${messageText}
                    <small class="message-time">${timeString}</small>
                </div>
            `);
        } else {
            messagesDiv.insertAdjacentHTML('beforeend', `
                <div class="message-group sent-group">
                    <div class="message sent last-in-group">
                        ${messageText}
                        <small class="message-time">${timeString}</small>
                    </div>
                </div>
            `);
        }

        messagesDiv.scrollTop = messagesDiv.scrollHeight;

        fetch("chat_handlers/send_message.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `receiver_id=${selectedUserId}&message=${encodeURIComponent(messageText)}`
        })
            .then(response => {
                if (!response.ok) throw new Error("Network response error");
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Update messages to get the saved message with proper timestamp
                    fetchMessages();
                } else if (data.error) {
                    throw new Error(data.error);
                }
            })
            .catch(error => {
                console.error("Error sending message:", error);
                messageInput.value = messageText; // Return the text if send failed
            });
    }

    // Polling with exponential backoff
    let pollingInterval = 2000; // Start with 2 seconds
    const maxPollingInterval = 10000; // Max 10 seconds

    function pollMessages() {
        if (selectedUserId) {
            fetchMessages();

            // If no new messages, gradually increase polling interval
            if (messageCache.length > 0) {
                pollingInterval = 2000; // Reset to 2 seconds if active chat
            } else {
                pollingInterval = Math.min(pollingInterval * 1.5, maxPollingInterval);
            }
        }

        setTimeout(pollMessages, pollingInterval);
    }

    // Start polling
    pollMessages();

    // Update status
    setInterval(() => {
        fetch("chat_handlers/update_status.php").catch(error => {
            console.error("Error updating status:", error);
        });
    }, 15000);

    // Add active class when typing in message input
    messageInput.addEventListener("focus", () => {
        if (selectedUserId) {
            chatForm.classList.add("active");
        }
    });

    messageInput.addEventListener("blur", () => {
        if (!messageInput.value.trim()) {
            chatForm.classList.remove("active");
        }
    });
});