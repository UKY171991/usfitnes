<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Local Database configuration for development
// $host = 'localhost';
// $dbname = 'pathlab_pro';
// $username = 'root';
// $password = '';

$host = 'localhost';
$dbname = 'u902379465_fitness';
$username = 'u902379465_fitness';
$password = '!f#gGC^VKs0';

try {
    // First create database if it doesn't exist
    $pdo_temp = new PDO("mysql:host=$host", $username, $password);
    $pdo_temp->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo_temp->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    $pdo_temp = null;
    
    // Connect to the database using PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Also create MySQLi connection for backward compatibility
    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("MySQLi Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8");
    
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$sql = "
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `user_type` enum('admin','member','trainer') DEFAULT 'member',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `classes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `trainer_id` int(11) DEFAULT NULL,
  `schedule` datetime NOT NULL,
  `duration_minutes` int(11) NOT NULL,
  `max_capacity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `trainer_id` (`trainer_id`),
  CONSTRAINT `classes_ibfk_1` FOREIGN KEY (`trainer_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `booking_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `class_id` (`class_id`),
  KEY `member_id` (`member_id`),
  CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
";

try {
    $pdo->exec($sql);
} catch(PDOException $e) {
    die("Error creating tables: " . $e->getMessage());
}

?>
