# Post Functionality

## Create Post
- **URL**: `/acchandlers/post.php`
- **Method**: POST
- **Authentication**: Required
- **Form Data**:
    - `content`: string (required) - Text content of the post
    - `image`: file (optional) - Image file to attach to the post
- **Response**:
    - Success:
        - Redirects to `posts.php` with session message: "Post published!"
        - The post data is saved to the database and a notification is sent via WebSocket
    - Error:
        - Redirects to `posts.php` with error message in session
        - Possible errors include empty content or image upload issues
- **Description**: Creates a new post with text content and optional image attachment, notifies followers about the new post

Unlike the other endpoints which return JSON responses directly, this endpoint uses redirects with session messages to communicate the result to the user.

## Delete Post
- **URL**: `/acchandlers/delete_post.php`
- **Method**: POST
- **Authentication**: Required
- **Request Body**:
  ```json
  {
    "post_id": "number"
  }
  ```
- **Response**:
    - Success (200):
      ```json
      {
        "success": true,
        "message": "Post successfully deleted."
      }
      ```
    - Error (400/403):
      ```json
      {
        "success": false,
        "message": "Error message explaining the issue"
      }
      ```
- **Description**: Deletes a post (only available to the post owner and admin)

## Like Post
- **URL**: `/acchandlers/like_post.php`
- **Method**: POST
- **Authentication**: Required
- **Request Body**:
  ```json
  {
    "post_id": "number"
  }
  ```
- **Response**:
    - Success (200):
      ```json
      {
        "status": "liked" // or "unliked" if removing like
      }
      ```
    - Error (400):
      ```json
      {
        "status": "error",
        "message": "Error message"
      }
      ```
- **Description**: Adds or removes a like from a post. Automatically removes a dislike if present.

## Dislike Post
- **URL**: `/acchandlers/dislike_post.php`
- **Method**: POST
- **Authentication**: Required
- **Request Body**:
  ```json
  {
    "post_id": "number"
  }
  ```
- **Response**:
    - Success (200):
      ```json
      {
        "status": "disliked" // or "undisliked" when removing
      }
      ```
    - Error (400):
      ```json
      {
        "status": "error",
        "message": "Error message"
      }
      ```
- **Description**: Adds or removes a dislike from a post. Automatically removes a like if present.

## Add to Favorites
- **URL**: `/acchandlers/favorite_post.php`
- **Method**: POST
- **Authentication**: Required
- **Request Body**:
  ```json
  {
    "post_id": "number"
  }
  ```
- **Response**:
    - Success (200):
      ```json
      {
        "success": true,
        "action": "added", // or "removed"
        "message": "Post added to favorites" // or "Post removed from favorites"
      }
      ```
    - Error (400):
      ```json
      {
        "success": false,
        "message": "Error message"
      }
      ```
- **Description**: Adds or removes a post from user's favorites

## Share Post
- **URL**: `/acchandlers/share_post.php`
- **Method**: POST
- **Authentication**: Required
- **Request Body**:
  ```json
  {
    "post_id": "number",
    "comment": "string" // optional comment on the shared post
  }
  ```
- **Response**:
    - Success (200):
      ```json
      {
        "success": true,
        "message": "Post shared successfully"
      }
      ```
    - Error (400/500):
      ```json
      {
        "error": "Error message"
      }
      ```
- **Description**: Creates a new post that references the original post, incrementing the share count

## Pin Post to Profile
- **URL**: `/acchandlers/pin_post.php`
- **Method**: POST
- **Authentication**: Required
- **Request Body**:
  ```json
  {
    "post_id": "number"
  }
  ```
- **Response**:
    - Success (200):
      ```json
      {
        "success": true,
        "is_pinned": true, // or false if unpinned
        "message": "Post pinned successfully" // or "Post unpinned successfully"
      }
      ```
    - Error (400/403):
      ```json
      {
        "success": false,
        "message": "Error message"
      }
      ```
- **Description**: Pins or unpins a post to user's profile. Only one post can be pinned at a time.

## Get Favorite Posts
- **URL**: `/favorites.php`
- **Method**: GET
- **Authentication**: Required
- **Description**: Retrieves user's favorite and liked posts
- **Response**: Renders HTML page displaying favorite and liked posts