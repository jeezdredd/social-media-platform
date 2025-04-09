# Configuration Guide

This guide covers the configuration options for the Social Media Web Application.

## Database Configuration

The database connection is configured in `db/database.php`. You need to update this file with your MySQL credentials:

```php
mb_internal_encoding('UTF-8');
header('Content-Type: text/html; charset=utf-8');

$host = 'localhost'; //default host
$dbname = 'your_database_name'; //replace with your database name
$username = 'username'; //replace with your database username
$password = 'password'; //replace with your database password

// Establish variable $pdo for universal connection.
// Additional requirements: set unicode format to utf8mb4 and collation to utf8mb4_unicode_ci.
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ]);
} catch (PDOException $e) {
    header("Location: errors/500.php");
    die("Database connection error: " . $e->getMessage());
}
```

## WebSocket Server Configuration

The WebSocket server is configured through the `.env` file in the `websocket-server` directory:

```
DB_HOST=localhost     # Database host
DB_USER=root          # Database username
DB_PASSWORD=          # Database password
DB_NAME=webapp        # Database name
PORT=8080             # WebSocket server port
HTTP_PORT=8081        # HTTP server port for WebSocket
```

### WebSocket Server Options

- `PORT`: The port on which the WebSocket server listens for connections
- `HTTP_PORT`: The port for the HTTP server that manages WebSocket connections
- Database credentials must match those in `db/database.php`

## File Upload Settings

File upload settings are defined in the application. The default configuration includes:

- Maximum upload file size: 5MB
- Allowed file types: jpg, jpeg, png, gif
- Upload directory: `upload/`

You can modify these settings in the relevant PHP files.

## Session Configuration

Session settings are configured in PHP. The default timeout is 24 hours. To change session settings, modify your `php.ini` file or use `ini_set()` in your PHP code.


