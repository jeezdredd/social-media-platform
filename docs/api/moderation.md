# Moderation API

The Moderation API enables administrators to review and resolve user complaints about potentially inappropriate content.

## Access Control

- Access to moderation features is restricted to users with admin privileges (`is_admin = 1`)
- Non-admin users attempting to access moderation pages are redirected to the main posts page

## Endpoints

### View Moderation Requests
- **URL**: `/moderation.php`
- **Method**: GET
- **Description**: Displays a list of pending moderation requests (complaints)
- **Access**: Admin only
- **Response**: Renders HTML page with complaint details:
    - Post author information
    - Complainer information
    - Post content
    - Action buttons (Accept/Reject)

### Submit Complaint
- **URL**: `/acchandlers/create_complaint.php`
- **Method**: POST
- **Description**: Submits a new complaint about a post
- **Request Body**:
  ```json
  {
    "post_id": "number",
    "user_id": "number"
  }
  ```
- **Response**:
    - Success (200):
      ```json
      {
        "success": true,
        "message": "Complaint submitted successfully"
      }
      ```
    - Error (400/403):
      ```json
      {
        "success": false,
        "message": "Error message explaining the issue"
      }
      ```

### Accept Complaint
- **URL**: `/acchandlers/accept_complaint.php`
- **Method**: POST
- **Description**: Approves a complaint and removes the reported post
- **Access**: Admin only
- **Request Body**:
  ```json
  {
    "id": "number",     // Complaint ID
    "post_id": "number" // Post ID to be removed
  }
  ```
- **Response**:
    - Success (200):
      ```json
      {
        "success": true,
        "message": "Complaint accepted and post removed"
      }
      ```
    - Error (400/403):
      ```json
      {
        "success": false,
        "message": "Error message explaining the issue" 
      }
      ```

### Reject Complaint
- **URL**: `/acchandlers/reject_complaint.php`
- **Method**: POST
- **Description**: Rejects a complaint and keeps the reported post
- **Access**: Admin only
- **Request Body**:
  ```json
  {
    "id": "number" // Complaint ID
  }
  ```
- **Response**:
    - Success (200):
      ```json
      {
        "success": true,
        "message": "Complaint rejected"
      }
      ```
    - Error (400/403):
      ```json
      {
        "success": false,
        "message": "Error message explaining the issue"
      }
      ```

## Data Structure

Complaints have the following structure:

```json
{
  "id": 123,
  "user_id": 456,      // User who submitted the complaint
  "post_id": 789,      // Post that was reported
  "author": "username", // Username of post author
  "complainer": "reporterusername", // Username of user who reported
  "content": "Post content text", // Content of the reported post
  "status": "pending", // Status: "pending", "accepted", or "rejected"
  "created_at": "2023-04-15 14:30:00"
}
```

## Workflow

1. Regular users submit complaints about posts they find inappropriate
2. Admin users access the moderation panel to review pending complaints
3. For each complaint, admins can:
    - Accept the complaint and remove the post
    - Reject the complaint and keep the post
4. Users can track the status of their complaints

## Implementation Notes

- The moderation system uses JavaScript to handle Accept/Reject actions
- The moderation interface displays only pending complaints