# System Architecture

## Overview
This social media platform uses a classic LAMP stack architecture enhanced with WebSockets for real-time functionality. The application follows a client-server model where PHP handles server-side processing, JavaScript manages client-side interactions, and WebSockets enable real-time notifications and chat.

## Technology Stack
- **Frontend**: HTML5, CSS3, JavaScript (vanilla JS with AJAX)
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Real-time Communication**: Node.js WebSocket server
- **Web Server**: Apache/Nginx

## Component Architecture

### Client Layer
- **Browser UI**: Responsive design for desktop and mobile devices
- **JavaScript Controllers**: Handle user interactions and AJAX calls
- **WebSocket Client**: Maintains persistent connection for real-time updates

### Server Layer
- **PHP Request Handlers**: Process HTTP requests in the `acchandlers` directory
- **Authentication System**: Manages user sessions and permissions
- **Business Logic**: Implements core functionality (posts, comments, likes)
- **WebSocket Server**: Node.js server for real-time communication

### Database Layer
- **MySQL Database**: Stores all application data
- **Data Models**: Structured tables with relationships (users, posts, comments)
- **Indexes**: Optimized for common queries and search operations

## Key Components

### Authentication System
- Session-based authentication
- Password hashing for security
- Role-based access control (regular users vs. moderators)

### Post Management
- Create, read, update, delete (CRUD) operations for posts
- Media handling for images
- Post engagement tracking (views, likes, dislikes)

### Interaction Engine
- Like/dislike system with toggle functionality
- Comment threading
- Sharing/reposting mechanism
- Favorites and pinned posts

### Chat System
- Private messaging between users
- Real-time message delivery via WebSockets
- Read receipts and online status indicators
- Message history storage and retrieval

### Notification System
- Real-time push notifications via WebSockets
- Event tracking for social interactions (likes, comments, mentions)

### Moderation System
- Content reporting workflow
- Moderation queue for reported content
- Admin review interface
- Content policy enforcement

## Data Flow

### Post Creation Flow
1. User submits post content and optional image
2. Server validates input and stores in database
3. WebSocket server notifies followers about new post
4. UI updates to display the new post

### Chat Message Flow
1. User sends message via UI
2. Message is sent to server via AJAX
3. Server stores message and triggers WebSocket event
4. Recipient receives real-time notification
5. Message appears in recipient's chat interface

## Security Architecture
- **Authentication**: Session-based with secure cookies
- **Input Validation**: Server-side validation for all user inputs
- **CSRF Protection**: Token validation for sensitive operations
- **Password Security**: Bcrypt hashing with appropriate work factor
- **SQL Injection Prevention**: Prepared statements for database queries
- **XSS Prevention**: Output escaping and content sanitization

## Scalability Considerations
- Database connection pooling
- Caching layer for frequently accessed data
- WebSocket clustering for high availability
- Horizontal scaling potential with load balancing

## Integration Points
- WebSocket server integration for real-time features
- Potential for third-party authentication (future enhancement)
- Media storage and CDN integration (future enhancement)

## Deployment Architecture
- Production environment on XAMPP or similar LAMP stack
- WebSocket server runs independently using Node.js for real-time features (notifications, chat)
- Database backups and recovery procedures
- Logging and monitoring infrastructure
