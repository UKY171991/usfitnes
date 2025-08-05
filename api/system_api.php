<?php
/**
 * System API for PathLab Pro
 * Provides system status and health checks
 */

// Prevent direct access
if (!defined('SECURE_ACCESS')) {
    define('SECURE_ACCESS', true);
}

// Include database configuration
require_once '../config.php';

// Set JSON header
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'status':
            echo json_encode(getSystemStatus());
            break;
            
        case 'health':
            echo json_encode(getSystemHealth());
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'System API Error: ' . $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

function getSystemStatus() {
    try {
        // Try to connect to database
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
            DB_USER,
            DB_PASS,
            [PDO::ATTR_TIMEOUT => 3]
        );
        
        $dbStatus = 'online';
    } catch (Exception $e) {
        $dbStatus = 'offline';
    }
    
    // Check disk space (basic check)
    $diskFree = disk_free_space('.');
    $diskTotal = disk_total_space('.');
    $diskUsage = $diskTotal > 0 ? (($diskTotal - $diskFree) / $diskTotal) * 100 : 0;
    
    return [
        'success' => true,
        'data' => [
            'status' => $dbStatus === 'online' ? 'online' : 'warning',
            'database' => $dbStatus,
            'disk_usage' => round($diskUsage, 2),
            'php_version' => PHP_VERSION,
            'server_time' => date('Y-m-d H:i:s'),
            'uptime' => getServerUptime()
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ];
}

function getSystemHealth() {
    $health = [
        'database' => checkDatabaseHealth(),
        'files' => checkFileSystem(),
        'memory' => checkMemoryUsage(),
        'overall' => 'healthy'
    ];
    
    // Determine overall health
    $issues = 0;
    foreach ($health as $key => $status) {
        if ($key !== 'overall' && in_array($status, ['warning', 'error', 'critical'])) {
            $issues++;
        }
    }
    
    if ($issues >= 3) {
        $health['overall'] = 'critical';
    } elseif ($issues >= 2) {
        $health['overall'] = 'warning';
    } elseif ($issues >= 1) {
        $health['overall'] = 'fair';
    }
    
    return [
        'success' => true,
        'data' => $health,
        'timestamp' => date('Y-m-d H:i:s')
    ];
}

function checkDatabaseHealth() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
            DB_USER,
            DB_PASS,
            [PDO::ATTR_TIMEOUT => 3]
        );
        
        // Test a simple query
        $pdo->query("SELECT 1")->fetch();
        return 'healthy';
        
    } catch (Exception $e) {
        return 'error';
    }
}

function checkFileSystem() {
    $diskFree = disk_free_space('.');
    $diskTotal = disk_total_space('.');
    
    if ($diskTotal === false || $diskFree === false) {
        return 'unknown';
    }
    
    $usage = (($diskTotal - $diskFree) / $diskTotal) * 100;
    
    if ($usage > 90) {
        return 'critical';
    } elseif ($usage > 80) {
        return 'warning';
    } else {
        return 'healthy';
    }
}

function checkMemoryUsage() {
    $memoryUsage = memory_get_usage(true);
    $memoryLimit = ini_get('memory_limit');
    
    if ($memoryLimit === '-1') {
        return 'healthy'; // No limit
    }
    
    // Convert memory limit to bytes
    $memoryLimitBytes = convertToBytes($memoryLimit);
    if ($memoryLimitBytes <= 0) {
        return 'unknown';
    }
    
    $usage = ($memoryUsage / $memoryLimitBytes) * 100;
    
    if ($usage > 90) {
        return 'critical';
    } elseif ($usage > 75) {
        return 'warning';
    } else {
        return 'healthy';
    }
}

function convertToBytes($value) {
    $unit = strtolower(substr($value, -1));
    $value = (int) $value;
    
    switch ($unit) {
        case 'g':
            $value *= 1024;
        case 'm':
            $value *= 1024;
        case 'k':
            $value *= 1024;
    }
    
    return $value;
}

function getServerUptime() {
    if (PHP_OS_FAMILY === 'Windows') {
        return 'N/A (Windows)';
    }
    
    if (function_exists('sys_getloadavg')) {
        $uptime = @file_get_contents('/proc/uptime');
        if ($uptime !== false) {
            $uptime = explode(' ', $uptime)[0];
            $days = floor($uptime / 86400);
            $hours = floor(($uptime % 86400) / 3600);
            $minutes = floor(($uptime % 3600) / 60);
            
            return "{$days}d {$hours}h {$minutes}m";
        }
    }
    
    return 'N/A';
}
?>
