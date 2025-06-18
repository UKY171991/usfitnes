<?php
/**
 * Database Configuration for Pathology Lab
 * Environment-based configuration
 */

// Environment Detection
$is_local = (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || 
             strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false);

return [
    'host' => $is_local ? 'localhost' : 'localhost',
    'dbname' => $is_local ? 'fitness' : 'u902379465_fitness',
    'username' => $is_local ? 'root' : 'u902379465_fitness',
    'password' => $is_local ? '' : '&m0DCQT!Jn0',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];
?>
