# Comment API Documentation

The Comment API handles the creation, retrieval, and deletion of comments on posts.

## Endpoints

### Add Comment

- **URL**: `/acchandlers/add_comment.php`
- **Method**: POST
- **Description**: Creates a new comment on a post
- **Request Body**:
  ```json
  {
    "post_id": "number",
    "content": "string"
  }
  ```
- **Response**:
    - Success (200):
      ```json
      {
        "success": true,
        "comment": {
          "id": 123,
          "user_id": 456,
          "username": "username",
          "profile_pic": "upload/profile123.jpg",
          "content": "Comment text",
          "created_at": "2023-04-15 14:32:00"
        }
      }
      ```
    - Error (400):
      ```json
      {
        "success": false,
        "message": "Error message explaining the issue"
      }
      ```

### Delete Comment

- **URL**: `/acchandlers/delete_comment.php`
- **Method**: POST
- **Description**: Deletes a comment (only available to comment author)
- **Request Body**:
  ```json
  {
    "comment_id": "number"
  }
  ```
- **Response**:
    - Success (200):
      ```json
      {
        "success": true,
        "message": "Comment deleted successfully"
      }
      ```
    - Error (400/403):
      ```json
      {
        "success": false,
        "message": "Error message explaining the issue"
      }
      ```

### Get Post Comments

- **URL**: (Embedded in post rendering)
- **Method**: Internal query
- **Description**: Retrieves comments for a specific post
- **Response**: Comments are rendered within the post HTML structure

## Comment Object Structure

Comments typically include the following information:

```json
{
  "id": 123,
  "post_id": 789,
  "user_id": 456,
  "username": "username",
  "profile_pic": "upload/profile456.jpg",
  "content": "Comment content text",
  "created_at": "2023-04-15 14:32:00"
}
```

## Markdown Support

Comments support Markdown formatting, which is parsed when displayed to users. The Markdown parsing is handled by the server-side `parseMarkdown()` function.