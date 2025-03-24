<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login page</title>
    <link rel="stylesheet" href="styles/login.css">
</head>
<body>
<div class="container">
    <h2>Login</h2>
    <input type="email" id="email" placeholder="Email">
    <input type="password" id="password" placeholder="Password">
    <button onclick="login()">Login</button>
    <p id="error-message"></p>
    <a href="register.php">Not a member? Sign up</a>
</div>

<script src="js/login.js"></script>
</body>
</html>

