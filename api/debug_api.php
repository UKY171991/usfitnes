<?php
/**
 * Debug Registration Flow
 * This file helps debug the registration process step by step
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set script timeout
set_time_limit(30);

echo json_encode([
    'success' => true,
    'message' => 'Debug endpoint working',
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => phpversion(),
    'max_execution_time' => ini_get('max_execution_time'),
    'memory_limit' => ini_get('memory_limit'),
    'session_status' => session_status(),
    'session_id' => session_id() ?: 'No session',
    'server_info' => [
        'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? 'Unknown',
        'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? 'Unknown',
        'SERVER_SOFTWARE' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'
    ]
]);
?>
