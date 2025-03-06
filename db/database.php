<?php
$host = 'localhost';
$dbname = 'webapp';
$username = 'root'; // или другой пользователь
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}
?>
