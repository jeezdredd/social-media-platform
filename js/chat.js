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
    let socket = null;
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

    function initWebSocket() {
        const socketProtocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
        const socketUrl = `${socketProtocol}//${window.location.hostname}:8080`;

        socket = new WebSocket(socketUrl);

        socket.onopen = () => {
            console.log("WebSocket connected");
            // Send user ID when connected
            socket.send(JSON.stringify({
                type: "connect",
                userId: userId
            }));
        };

        socket.onmessage = (event) => {
            const data = JSON.parse(event.data);

            if (data.type === "message" && data.senderId == selectedUserId) {
                // New message from currently selected user
                fetchMessages();

                // Send read receipt
                socket.send(JSON.stringify({
                    type: "message_read",
                    senderId: data.senderId,
                    receiverId: userId
                }));
            }
            else if (data.type === "message_delivered" && data.receiverId == selectedUserId) {
                // Update message status to delivered (but not read)
                document.querySelectorAll('.message.sent .status-sent').forEach(el => {
                    el.classList.remove('status-sent');
                    el.classList.add('status-delivered');
                });
            }
            else if (data.type === "message_read" && data.senderId == selectedUserId) {
                // Update all delivered messages to read status
                document.querySelectorAll('.message.sent .status-delivered').forEach(el => {
                    el.classList.remove('status-delivered');
                    el.classList.add('status-read');
                });
            }
        };

        socket.onclose = () => {
            console.log("WebSocket disconnected");
            // Try to reconnect after 3 seconds
            setTimeout(initWebSocket, 3000);
        };
    }

    initWebSocket();

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

                    markMessagesAsRead(selectedUserId);
                }
            })
            .catch(error => {
                messagesDiv.innerHTML = `<div class="error-message"><i class="error-icon">⚠️</i> Error loading messages: ${error.message}</div>`;
            });
    }

    function updateUserList() {
        fetch("chat_handlers/get_users.php")
            .then(response => response.json())
            .then(users => {
                // Iterate through users and update unread badges
                users.forEach(user => {
                    const userItem = document.querySelector(`.user-item[data-user-id="${user.id}"]`);
                    if (userItem) {
                        // Find or create the unread badge
                        let badge = userItem.querySelector('.unread-badge');

                        if (user.unread_count > 0) {
                            if (!badge) {
                                badge = document.createElement('span');
                                badge.className = 'unread-badge';
                                userItem.querySelector('.user-header').appendChild(badge);
                            }
                            badge.textContent = user.unread_count;
                        } else if (badge) {
                            badge.remove();
                        }
                    }
                });
            })
            .catch(error => console.error("Error updating user list:", error));
    }

    function markMessagesAsRead(senderId) {
        // Only mark as read if we're looking at this conversation
        if (!senderId) return;

        fetch("chat_handlers/mark_read.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `sender_id=${senderId}`
        })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.count > 0) {
                    updateUserList();
                }
            })
            .catch(error => console.error("Error marking messages as read:", error));
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

                // Status indicator (only for sent messages)
                let statusIndicator = '';
                if (isCurrentUser) {
                    statusIndicator = `<span class="message-status ${msg.is_read ? 'status-read' : 'status-delivered'}"></span>`;
                }

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
                    <small class="message-time">${messageTime}${statusIndicator}</small>
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
                <small class="message-time">${timeString}<span class="message-status status-sent"></span></small>
            </div>
        `);
        } else {
            messagesDiv.insertAdjacentHTML('beforeend', `
            <div class="message-group sent-group">
                <div class="message sent last-in-group">
                    ${messageText}
                    <small class="message-time">${timeString}<span class="message-status status-sent"></span></small>
                </div>
            </div>
        `);
        }

        messagesDiv.scrollTop = messagesDiv.scrollHeight;

        // Send message to server
        fetch("chat_handlers/send_message.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `receiver_id=${selectedUserId}&message=${encodeURIComponent(messageText)}`
        })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    throw new Error(data.error);
                }

                // Update to delivered status after successful send
                const sentMsgStatus = messagesDiv.querySelector('.message:last-child .status-sent');
                if (sentMsgStatus) {
                    sentMsgStatus.classList.remove('status-sent');
                    sentMsgStatus.classList.add('status-delivered');
                }

                // Send via WebSocket if connected
                if (socket && socket.readyState === WebSocket.OPEN) {
                    socket.send(JSON.stringify({
                        type: "message",
                        senderId: userId,
                        receiverId: selectedUserId,
                        text: messageText
                    }));
                }

                fetchMessages(); // Refresh messages
            })
            .catch(error => {
                console.error("Error sending message:", error);
                messagesDiv.insertAdjacentHTML('beforeend', `
            <div class="error-message">
                <span class="error-icon">⚠️</span> Failed to send message. Please try again.
            </div>
        `);
            });
    }

    // Polling with exponential backoff
    let pollingInterval = 2000; // Start with 2 seconds
    const maxPollingInterval = 10000; // Max 10 seconds

    function pollMessages() {
        if (selectedUserId) {
            fetchMessages();

            if (messageCache.length > 0) {
                pollingInterval = 2000;
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