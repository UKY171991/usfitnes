<?php
// db_connect.php
$host = 'localhost'; // Adjust as needed
$dbname = 'u902379465_fitness'; // Your database name
$username = 'u902379465_fitness'; // Your MySQL username
$password = 'yI5$I$zdC>R'; // Your MySQL password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>