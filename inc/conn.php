<?php
$servername = "localhost";   // Replace if using another host
$username = "u902379465_fitness";          // Replace with your DB username
$password = "^ytIkNdD=1K";              // Replace with your DB password
$dbname = "u902379465_fitness";    // Replace with your database name

// Create connection
$con = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
