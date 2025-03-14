<?php
// Database Connection
$servername = "localhost";
$username = "u820431346_account";
$password = "o0V5DnDLqbR*";
$dbname = "u820431346_account";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }
?>