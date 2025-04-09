# Database Schema

This document describes the database structure for the Social Media Web Application.

## Tables Overview

The application uses the following main tables:

- `users` - User account information
- `posts` - User posts and shared content
- `comments` - Comments on posts
- `likes` - Post likes
- `dislikes` - Post dislikes
- `favorites` - User's favorite posts
- `followers` - User follow relationships
- `messages` - Private messages between users
- `complaints` - Content moderation reports

## Schema Details

### users

Stores user account information.

| Column       | Type         | Description                      |
|--------------|--------------|----------------------------------|
| id           | INT          | Primary key, auto-increment      |
| username     | VARCHAR(255) | User's display name              |
| email        | VARCHAR(255) | User's email address (unique)    |
| password     | VARCHAR(255) | Hashed password                  |
| profile_pic  | VARCHAR(255) | Path to profile picture          |
| status       | ENUM         | 'online', 'offline'              |
| last_seen    | DATETIME     | Last activity timestamp          |
| is_admin     | BOOLEAN      | Administrator flag               |
| created_at   | TIMESTAMP    | Account creation time            |

### posts

Stores user posts.

| Column           | Type         | Description                      |
|------------------|--------------|----------------------------------|
| id               | INT          | Primary key, auto-increment      |
| user_id          | INT          | Foreign key to users.id          |
| content          | TEXT         | Post content                     |
| image            | VARCHAR(255) | Path to attached image           |
| created_at       | TIMESTAMP    | Post creation time               |
| is_share         | BOOLEAN      | Whether this is a shared post    |
| original_post_id | INT          | ID of original post if shared    |
| share_comment    | TEXT         | Comment when sharing a post      |
| is_pinned        | BOOLEAN      | Whether post is pinned to profile|

### messages

Stores private messages between users.

| Column      | Type       | Description                           |
|-------------|------------|---------------------------------------|
| id          | INT        | Primary key, auto-increment           |
| sender_id   | INT        | Foreign key to users.id (sender)      |
| receiver_id | INT        | Foreign key to users.id (receiver)    |
| content     | TEXT       | Message content                       |
| is_read     | BOOLEAN    | Whether message has been read         |
| sent_at     | TIMESTAMP  | Message sent time                     |
| delivered_at| TIMESTAMP  | Message delivery time                 |
| read_at     | TIMESTAMP  | Message read time                     |

### complaints

Stores user reports of inappropriate content.

| Column      | Type       | Description                           |
|-------------|------------|---------------------------------------|
| id          | INT        | Primary key, auto-increment           |
| user_id     | INT        | User who reported the content         |
| post_id     | INT        | Post that was reported                |
| reason      | TEXT       | Reason for the complaint              |
| status      | ENUM       | 'pending', 'accepted', 'rejected'     |
| created_at  | TIMESTAMP  | When the complaint was submitted      |

## Relationships

- A user can have many posts, comments, likes, and favorites
- A post can have many comments, likes, and dislikes
- A user can follow many users and be followed by many users
- A user can send and receive many messages