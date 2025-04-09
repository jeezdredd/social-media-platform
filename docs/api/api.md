# API Endpoints Classification overview

## Authentication
- **Login**: `login.php` and `auth/login.php`
    - Handles user login with email and password
- **Register**: `register.php` and `auth/register.php`
    - User registration with username, email, and password
- **Logout**: `auth/logout.php`
    - Ends user session

## Chat Functionality
- **Load Messages**: `chat_handlers/load_messages.php`
    - Parameters: `receiver_id` (ID of user to load messages with)
    - Returns message history with a specific user
- **Send Message**: `chat_handlers/send_message.php`
    - Input: `receiver_id`, `message`
    - Sends a new message and returns confirmation with message ID
- **Mark Read**: `chat_handlers/mark_read.php`
    - Input: `sender_id`
    - Marks messages from a specific sender as read
- **Update Status**: `chat_handlers/update_status.php`
    - Updates user's online status
- **Get Users**: `chat_handlers/get_users.php`
    - Returns list of users with unread message counts

## Post Functionality
- **Create Post**: `acchandlers/post.php`
    - Creates a new post with content and optional image
- **Delete Post**: (AJAX endpoint in `js/delete.js`)
    - Input: `post_id`
    - Deletes a specific post
- **Like Post**: (AJAX endpoint in `js/likes.js`)
    - Input: `post_id`
    - Adds/removes a like from a post
- **Dislike Post**: (AJAX endpoint in `js/dislikes.js`)
    - Input: `post_id`
    - Adds/removes a dislike from a post
- **Add to Favorites**: (AJAX endpoint in `js/favorites.js`)
    - Input: `post_id`
    - Adds/removes a post from favorites
- **Share Post**: (AJAX endpoint in `js/share.js`)
    - Input: `post_id`, optional `comment`
    - Shares/reposts content to user's feed
- **Pin Post**: (AJAX endpoint in `js/pin.js`)
    - Input: `post_id`
    - Pins/unpins post to user profile

## Comment Functionality
- **Add Comment**: (AJAX endpoint in `js/comment.js`)
    - Input: `post_id`, `content`
    - Adds a comment to a post
- **Delete Comment**: (AJAX endpoint in `js/delete.js`)
    - Input: `comment_id`
    - Deletes a specific comment

## Moderation
- **Create Complaint**: (AJAX endpoint in `js/create_complaint.js`)
    - Input: `post_id`
    - Reports a post for moderation
- **Approve/Reject Complaint**: (AJAX endpoint in `js/moderation.js`)
    - Input: Post and complaint identifiers
    - Moderator decision on reported content

## Profile Management
- **Update Profile**: `profile/update_profile.php`
    - Updates username and/or password
- **Upload Profile Picture**: (AJAX endpoint used in dashboard.php)
    - Updates user's profile picture

## Notification System
- **WebSocket Notifications**:
    - Uses `websocket-server/server.js` for real-time notifications
    - HTTP endpoints for post, comment, and like notifications

## Search
- **Search Posts and Users**: `posts.php`
    - Parameter: `search`
    - Searches posts and usernames based on keywords
