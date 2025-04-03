let socket;
let reconnectAttempts = 0;
const maxReconnectAttempts = 5;

function connectWebSocket() {
    const userId = document.body.getAttribute('data-user-id');
    if (!userId) return;

    socket = new WebSocket('ws://localhost:8080');

    socket.onopen = function() {
        console.log('WebSocket connected');
        reconnectAttempts = 0;

        // Send authentication message
        socket.send(JSON.stringify({
            type: 'connect',
            userId: userId
        }));
    };

    socket.onmessage = function(event) {
        const data = JSON.parse(event.data);

        if (data.type === 'notification') {
            handleNotification(data);
        }
    };

    socket.onclose = function() {
        console.log('WebSocket connection closed');

        // Attempt to reconnect with exponential backoff
        if (reconnectAttempts < maxReconnectAttempts) {
            const timeout = Math.min(1000 * Math.pow(2, reconnectAttempts), 30000);
            reconnectAttempts++;

            setTimeout(function() {
                console.log(`Attempting to reconnect (${reconnectAttempts}/${maxReconnectAttempts})...`);
                connectWebSocket();
            }, timeout);
        }
    };

    socket.onerror = function(error) {
        console.error('WebSocket error:', error);
    };
}

function handleNotification(data) {
    if (localStorage.getItem("notificationsEnabled") === "false") return;
    if (document.visibilityState === "visible") return;

    switch(data.notificationType) {
        case 'post':
            showNotification('New Post', `${data.username} published a new post!`);
            break;
        case 'comment':
            showNotification('New Comment', `${data.username} commented on your post!`);
            break;
        case 'like':
            showNotification('New Like', `${data.username} liked your post!`);
            break;
    }

    playSound();
}

function showNotification(title, body) {
    if (Notification.permission !== "granted") {
        Notification.requestPermission();
    }

    if (Notification.permission === "granted") {
        new Notification(title, {
            body: body,
            icon: 'img/icon.jpg'
        });
    }
}

function playSound() {
    var sound = document.getElementById("notifySound");
    if (sound) {
        sound.play();
    }
}

document.addEventListener("DOMContentLoaded", function() {
    // Request notification permission when page loads
    if (Notification.permission !== "granted" && Notification.permission !== "denied") {
        Notification.requestPermission();
    }

    // Initialize WebSocket connection
    connectWebSocket();

    // Set up notification toggle button
    let toggleBtn = document.getElementById("toggleNotifications");
    if (toggleBtn) {
        if (localStorage.getItem("notificationsEnabled") === null) {
            localStorage.setItem("notificationsEnabled", "true");
        }

        function updateToggleText() {
            let enabled = localStorage.getItem("notificationsEnabled");
            toggleBtn.textContent = (enabled === "true") ? "Mute notifications" : "Unmute notifications";
        }

        updateToggleText();

        toggleBtn.addEventListener("click", function() {
            let enabled = localStorage.getItem("notificationsEnabled");
            localStorage.setItem("notificationsEnabled", enabled === "true" ? "false" : "true");
            updateToggleText();
        });
    }
});