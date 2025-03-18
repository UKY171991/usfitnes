<?php

echo "Hello"; die;
// Database Connection
$servername = "localhost";
$username = "u902379465_fitness";
$password = "m|ETEq6jU0";
$dbname = "u902379465_fitness";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }
?>