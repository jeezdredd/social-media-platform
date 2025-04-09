# Database Schema

This document describes the database structure for the Social Media Web Application.

## Tables Overview

The application uses the following main tables:

- `users` - User account information
- `posts` - User posts and shared content
- `comments` - Comments on posts
- `likes` - Post likes tracking
- `dislikes` - Post dislikes tracking
- `favorites` - User's favorite posts
- `followers` - User follow relationships
- `messages` - Private messages between users
- `moderation` - Content moderation system

## Schema Details

### users

Stores user account information.

| Column          | Type         | Description                      |
|-----------------|--------------|----------------------------------|
| id              | INT          | Primary key, auto-increment      |
| username        | VARCHAR(50)  | User's display name (unique)     |
| email           | VARCHAR(100) | User's email address (unique)    |
| password        | VARCHAR(255) | Bcrypt hashed password           |
| profile_picture | VARCHAR(255) | Legacy field for profile image   |
| profile_pic     | VARCHAR(255) | Path to profile picture          |
| status          | ENUM         | 'online', 'offline'              |
| last_seen       | DATETIME     | Last activity timestamp          |
| is_admin        | TINYINT(1)   | Administrator flag               |
| created_at      | TIMESTAMP    | Account creation time            |

### posts

Stores user posts and shared content.

| Column           | Type         | Description                       |
|------------------|--------------|-----------------------------------|
| id               | INT          | Primary key, auto-increment       |
| user_id          | INT          | Foreign key to users.id           |
| content          | TEXT         | Post content                      |
| image            | VARCHAR(255) | Path to attached image            |
| created_at       | TIMESTAMP    | Post creation time                |
| likes_count      | INT          | Cached count of post likes        |
| dislikes_count   | INT          | Cached count of post dislikes     |
| is_share         | TINYINT(1)   | Whether this is a shared post     |
| original_post_id | INT          | ID of original post if shared     |
| share_comment    | TEXT         | Comment when sharing a post       |
| shares_count     | INT          | Count of times post was shared    |
| is_pinned        | TINYINT(4)   | Whether post is pinned to profile |

### comments

Stores comments on posts.

| Column     | Type      | Description                  |
|------------|-----------|------------------------------|
| id         | INT       | Primary key, auto-increment  |
| post_id    | INT       | Foreign key to posts.id      |
| user_id    | INT       | Foreign key to users.id      |
| content    | TEXT      | Comment content              |
| created_at | TIMESTAMP | Comment creation time        |

### likes

Tracks user likes on posts.

| Column     | Type      | Description                           |
|------------|-----------|---------------------------------------|
| id         | INT       | Primary key, auto-increment           |
| user_id    | INT       | Foreign key to users.id               |
| post_id    | INT       | Foreign key to posts.id               |
| created_at | TIMESTAMP | Like creation time                    |

*Note: Has a unique constraint on (user_id, post_id) to prevent duplicate likes*

### dislikes

Tracks user dislikes on posts.

| Column     | Type      | Description                           |
|------------|-----------|---------------------------------------|
| id         | INT       | Primary key, auto-increment           |
| user_id    | INT       | Foreign key to users.id               |
| post_id    | INT       | Foreign key to posts.id               |

### favorites

Stores user's favorite posts.

| Column     | Type      | Description                           |
|------------|-----------|---------------------------------------|
| id         | INT       | Primary key, auto-increment           |
| user_id    | INT       | Foreign key to users.id               |
| post_id    | INT       | Foreign key to posts.id               |
| created_at | TIMESTAMP | When post was favorited               |

*Note: Has a unique constraint on (user_id, post_id) to prevent duplicates*

### followers

Tracks user follow relationships.

| Column      | Type      | Description                           |
|-------------|-----------|---------------------------------------|
| id          | INT       | Primary key, auto-increment           |
| follower_id | INT       | User who is following (users.id)      |
| followed_id | INT       | User being followed (users.id)        |
| created_at  | TIMESTAMP | When the follow relationship started  |

*Note: Has a unique constraint on (follower_id, followed_id) to prevent duplicates*

### messages

Stores private messages between users.

| Column      | Type         | Description                         |
|-------------|--------------|-------------------------------------|
| id          | INT          | Primary key, auto-increment         |
| sender_id   | INT          | Foreign key to users.id (sender)    |
| receiver_id | INT          | Foreign key to users.id (receiver)  |
| content     | TEXT         | Message content                     |
| created_at  | TIMESTAMP    | Message sent time                   |
| is_read     | TINYINT(1)   | Whether message has been read       |

### moderation

Stores information for content moderation.

| Column      | Type       | Description                           |
|-------------|------------|---------------------------------------|
| id          | INT        | Primary key, auto-increment           |
| post_id     | INT        | Post that was reported                |
| user_id     | INT        | User who reported the content         |

## Relationships

- A user can have many posts, comments, likes, and favorites
- A post can have many comments, likes, and dislikes
- A user can follow many users and be followed by many users
- A user can send and receive many messages
- All tables with foreign keys to users or posts have CASCADE delete rules

## Indexing Strategy

- Primary keys on all tables
- Foreign keys are indexed for performance
- Unique constraints prevent duplicate records where needed
- Composite unique indexes on relationship tables