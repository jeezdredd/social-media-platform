# API Documentation

## Authentication API

The Authentication API handles user registration, login, session management, and authentication checks across the application.

### Endpoints

#### Register User
- **URL**: `/acchandlers/register.php`
- **Method**: POST
- **Description**: Creates a new user account
- **Request Body**:
  ```json
  {
    "username": "string", 
    "email": "string",
    "password": "string"
  }
  ```
- **Response**:
    - Success (200):
      ```json
      {
        "success": true,
        "message": "User registered successfully"
      }
      ```
    - Error (400):
      ```json
      {
        "success": false,
        "message": "Error message explaining the issue"
      }
      ```

#### User Login
- **URL**: `/acchandlers/login.php`
- **Method**: POST
- **Description**: Authenticates a user and creates a session
- **Request Body**:
  ```json
  {
    "email": "string",
    "password": "string"
  }
  ```
- **Response**:
    - Success (200):
      ```json
      {
        "success": true,
        "user_id": 123,
        "username": "string"
      }
      ```
    - Error (401):
      ```json
      {
        "success": false,
        "message": "Invalid credentials"
      }
      ```

#### Logout
- **URL**: `/auth/logout.php`
- **Method**: GET
- **Description**: Destroys the current user session
- **Response**: Redirects to login page

#### Check Authentication Status
- **URL**: `/auth/auth_check.php`
- **Method**: Used internally
- **Description**: Verifies if a user is authenticated before allowing access to protected pages
- **Behavior**: Redirects to login page if user is not authenticated or suspicious activity is detected (e.g., session hijacking)

### Authentication Flow

1. User registers with username, email, and password
2. User logs in with email and password
3. Upon successful login, a session is created containing user ID and other essential information
4. Protected pages include `auth_check.php` to verify authentication status
5. Users can logout to destroy their session

### Security Notes

- Passwords are hashed before storage
- Session data is used to maintain authentication state
- CSRF protection is implemented for sensitive operations
- Session timeout is implemented for security