# Chat Functionality API

## WebSocket Connection

- **WebSocket Endpoint**: `ws://{hostname}:8080` or `wss://{hostname}:8080` (secure)
- **Connection Authentication**: Send a connection message after establishing WebSocket connection

## WebSocket Message Types

### Connection
```json
{
  "type": "connect",
  "userId": 123
}
```

### Send Message
```json
{
  "type": "message",
  "senderId": 123,
  "receiverId": 456,
  "text": "Hello, how are you?"
}
```

### Message Delivery Receipt
```json
{
  "type": "message_delivered",
  "receiverId": 456
}
```

### Message Read Receipt
```json
{
  "type": "message_read",
  "senderId": 456,
  "receiverId": 123
}
```

## REST API Endpoints

### Load Messages
- **Endpoint**: `chat_handlers/load_messages.php`
- **Method**: GET
- **Parameters**: `receiver_id`
- **Description**: Fetches message history between current user and specified receiver
- **Response**: Array of message objects
```json
[
  {
    "id": 123,
    "sender_id": 456,
    "receiver_id": 789,
    "content": "Message text",
    "created_at": "2023-01-01 12:30:45",
    "is_read": 1
  }
]
```

### Send Message
- **Endpoint**: `chat_handlers/send_message.php`
- **Method**: POST
- **Parameters**: `receiver_id`, `message`
- **Description**: Sends a message to a specific user
- **Response**:
```json
{
  "success": true,
  "message_id": 123
}
```

### Mark Messages as Read
- **Endpoint**: `chat_handlers/mark_read.php`
- **Method**: POST
- **Parameters**: `sender_id`
- **Description**: Marks all messages from sender as read
- **Response**:
```json
{
  "success": true,
  "count": 5
}
```

### Update User Status
- **Endpoint**: `chat_handlers/update_status.php`
- **Method**: GET
- **Description**: Updates the current user's online status and last seen timestamp
- **Response**: No content

### Get Users List
- **Endpoint**: `chat_handlers/get_users.php`
- **Method**: GET
- **Description**: Retrieves list of users with unread message counts
- **Response**: Array of user objects
```json
[
  {
    "id": 123,
    "username": "username",
    "status": "online",
    "profile_pic": "upload/profile.jpg",
    "last_seen": "2023-01-01 12:30:45",
    "unread_count": 3
  }
]
```

## Message Status Codes

- **Sent**: Message sent to server but not yet delivered
- **Delivered**: Message delivered to recipient's server but not read
- **Read**: Message has been read by recipient