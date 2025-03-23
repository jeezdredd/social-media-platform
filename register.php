<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration page</title>
    <link rel="stylesheet" href="styles/register.css">
</head>
<body>
<div class="container">
    <h2>Register form</h2>
    <input type="text" id="username" placeholder="Username">
    <input type="email" id="email" placeholder="Email">
    <input type="password" id="password" placeholder="Password">
    <button onclick="register()">Register</button>
    <p id="error-message"></p>
    <a href="login.php">Already a member? Sign in</a>
</div>

<script src="js/register.js"></script>
</body>
</html>
