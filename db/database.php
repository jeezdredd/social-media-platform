<?php
$host = 'localhost'; //host of the database (in our case local database MariaDB via XAMMP)
$dbname = 'webapp'; //name of the database
$username = 'root'; //username with the access to database
$password = ''; //password of the user

$conn = new mysqli($host, $username, $password, $dbname); //establishing connection to the database

if ($conn->connect_error) {
    die("Database connection error: " . $conn->connect_error); //in case of connection faults, show the error messsage
}
?>
