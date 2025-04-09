# WebSocket Implementation

This document outlines the WebSocket implementation used for real-time features in the social media platform.

## Architecture Overview

The WebSocket implementation consists of:

1. **Node.js WebSocket Server**: Handles real-time messaging and notifications
2. **HTTP Notification API**: REST endpoints for triggering notifications
3. **Client WebSocket Integrations**: Chat and notification systems

## Server Implementation

### Core Components

- **WebSocket Server**: Running on port 8080 using `ws` library
- **HTTP Server**: Running on port 8081 for notification triggers
- **MySQL Connection Pool**: For database operations

### Connection Management

```javascript
// Server-side connection tracking
let clients = {};

wss.on("connection", (ws, req) => {
    let userId = null;
    
    ws.on("message", async (message) => {
        // Handle connection message
        if (data.type === "connect") {
            userId = data.userId;
            clients[userId] = ws;
        }
        // Other message handling...
    });
    
    ws.on("close", () => {
        if (userId) {
            delete clients[userId];
        }
    });
});
```

### Supported Message Types

| Type | Direction | Purpose |
|------|-----------|---------|
| `connect` | Client → Server | Authenticate and establish connection |
| `message` | Bidirectional | Send/receive chat messages |
| `message_delivered` | Server → Client | Confirm message delivery |
| `message_read` | Bidirectional | Mark messages as read |
| `notification` | Server → Client | System notifications (likes, comments, posts) |

## Notification System

### Notification Types

- **New Post**: When a user creates a new post (sent to followers)
- **New Comment**: When a user comments on a post (sent to post owner)
- **New Like**: When a user likes a post (sent to post owner)

### HTTP Endpoints

- **`/notify/post`**: Broadcast new post to followers
- **`/notify/comment`**: Notify post owner about new comment
- **`/notify/like`**: Notify post owner about new like

## Chat System

### Features

- Real-time message delivery
- Message status tracking (sent, delivered, read)
- Read receipts
- Message history persistence

### Message Flow

1. User sends message via client interface
2. Message stored in database
3. WebSocket delivers message to recipient if online
4. Delivery confirmation sent to sender
5. Read receipt sent when recipient views message

## Client Implementation

### Chat Client

```javascript
// Initialize WebSocket connection
const socketProtocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
const socketUrl = `${socketProtocol}//${window.location.hostname}:8080`;
socket = new WebSocket(socketUrl);

// Send authentication on connect
socket.onopen = () => {
    socket.send(JSON.stringify({
        type: "connect",
        userId: userId
    }));
};
```

### Notification Client

- Browser notifications for new activity
- Visual indicators in UI
- Sound alerts (configurable)
- Reconnection with exponential backoff

## Reconnection Strategy

The client automatically attempts to reconnect when the connection is lost:

```javascript
socket.onclose = function() {
    if (reconnectAttempts < maxReconnectAttempts) {
        const timeout = Math.min(1000 * Math.pow(2, reconnectAttempts), 30000);
        reconnectAttempts++;
        
        setTimeout(function() {
            connectWebSocket();
        }, timeout);
    }
};
```

## Security Considerations

- User authentication required for WebSocket connections
- Database access through connection pool with prepared statements
- CORS headers configured for HTTP endpoints
- No sensitive data transmitted in plain text

## Performance Optimization

- Connection pooling for database operations
- Message caching on client side
- Polling interval adjustment based on activity
- Optimistic UI updates for better perceived performance

## Deployment Considerations

- WebSocket server runs independently from main application
- Environment variables for configuration
- Logging for monitoring and debugging
- Error handling and fallback mechanisms
