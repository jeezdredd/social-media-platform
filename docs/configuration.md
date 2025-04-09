# Configuration Guide

This guide covers the configuration options for the Social Media Web Application.

## Database Configuration

The database connection is configured in `db/database.php`. You need to update this file with your MySQL credentials:

```php
$host = 'localhost';      // Your database host
$dbname = 'webapp';       // Your database name
$username = 'root';       // Your database username
$password = '';           // Your database password

$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    throw new PDOException($e->getMessage(), (int)$e->getCode());
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


