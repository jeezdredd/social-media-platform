# Installation Guide

This guide will walk you through the process of setting up the web application on your server.

## Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Node.js 14.x or higher (for WebSocket server)
- Apache/Nginx web server (In my case XAMPP)

## Step 1: Clone the Repository

```bash
git clone https://github.com/yourusername/social-media-app.git
cd social-media-app
```

## Step 2: Set Up the Database

1. Create a new MySQL database:

```sql
CREATE DATABASE webapp;
```

2. Import the database schema:

```bash
mysql -u root -p webapp < db/schema.sql
```

## Step 3: Configure Environment Variables

1. Create a `.env` file for the WebSocket server:

```
DB_HOST=localhost
DB_USER=root
DB_PASSWORD=
DB_NAME=webapp
PORT=8080
HTTP_PORT=8081
```

2. Update database connection in `db/database.php` with your credentials.

## Step 4: Install WebSocket Server Dependencies

```bash
cd websocket-server
npm install
```

## Step 5: Set Up Web Server

Configure Apache/Nginx to point to the project directory.

### Apache Example (.htaccess is already included):

```apache
<VirtualHost *:80>
    ServerName socialmedia.local
    DocumentRoot /path/to/social-media-app
    
    <Directory /path/to/social-media-app>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

## Step 6: Start the WebSocket Server

```bash
cd websocket-server
node server.js
```

## Step 7: Access the Application

Navigate to http://localhost or your configured domain in your web browser.

## Troubleshooting

If you encounter any issues during installation, check the following:

- Ensure all PHP extensions are enabled (mysqli, pdo, etc.)
- Verify database credentials are correct
- Confirm WebSocket server is running
- Check web server logs for any PHP errors

