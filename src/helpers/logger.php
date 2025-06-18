<?php
/**
 * Logger Class
 * Handles application logging with different levels
 */

class Logger {
    
    private static $logFile = null;
    
    /**
     * Initialize logger
     */
    public static function init() {
        if (!file_exists(LOG_PATH)) {
            mkdir(LOG_PATH, 0755, true);
        }
        self::$logFile = LOG_PATH . 'app.log';
    }
    
    /**
     * Log message with level
     */
    public static function log($message, $level = LOG_LEVEL_INFO, $context = []) {
        if (self::$logFile === null) {
            self::init();
        }
        
        // Check if we should log this level
        if (!self::shouldLog($level)) {
            return;
        }
        
        $timestamp = date(DATETIME_FORMAT);
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
        $logLine = "[$timestamp] [$level] $message$contextStr" . PHP_EOL;
        
        file_put_contents(self::$logFile, $logLine, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Log error message
     */
    public static function error($message, $context = []) {
        self::log($message, LOG_LEVEL_ERROR, $context);
    }
    
    /**
     * Log warning message
     */
    public static function warning($message, $context = []) {
        self::log($message, LOG_LEVEL_WARNING, $context);
    }
    
    /**
     * Log info message
     */
    public static function info($message, $context = []) {
        self::log($message, LOG_LEVEL_INFO, $context);
    }
    
    /**
     * Log debug message
     */
    public static function debug($message, $context = []) {
        self::log($message, LOG_LEVEL_DEBUG, $context);
    }
    
    /**
     * Log payment activity
     */
    public static function payment($message, $context = []) {
        $paymentLogFile = LOG_PATH . 'payment.log';
        $timestamp = date(DATETIME_FORMAT);
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
        $logLine = "[$timestamp] [PAYMENT] $message$contextStr" . PHP_EOL;
        
        file_put_contents($paymentLogFile, $logLine, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Log user activity
     */
    public static function activity($userId, $action, $details = [], $ipAddress = null) {
        $ipAddress = $ipAddress ?: Security::getClientIP();
        $context = [
            'user_id' => $userId,
            'ip_address' => $ipAddress,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'details' => $details
        ];
        
        self::log("User Activity: $action", LOG_LEVEL_INFO, $context);
        
        // Also log to database if possible
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("
                INSERT INTO audit_logs (user_id, action, ip_address, user_agent, new_values, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $userId,
                $action,
                $ipAddress,
                $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                json_encode($details)
            ]);
        } catch (Exception $e) {
            self::error("Failed to log activity to database: " . $e->getMessage());
        }
    }
    
    /**
     * Log database operations
     */
    public static function database($operation, $table, $recordId = null, $context = []) {
        $message = "Database $operation on table '$table'";
        if ($recordId) {
            $message .= " (ID: $recordId)";
        }
        
        self::log($message, LOG_LEVEL_DEBUG, $context);
    }
    
    /**
     * Log API calls
     */
    public static function api($endpoint, $method, $response, $context = []) {
        $message = "API Call: $method $endpoint";
        $context['response'] = $response;
        
        self::log($message, LOG_LEVEL_INFO, $context);
    }
    
    /**
     * Log security events
     */
    public static function security($event, $context = []) {
        $securityLogFile = LOG_PATH . 'security.log';
        $timestamp = date(DATETIME_FORMAT);
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
        $logLine = "[$timestamp] [SECURITY] $event$contextStr" . PHP_EOL;
        
        file_put_contents($securityLogFile, $logLine, FILE_APPEND | LOCK_EX);
        
        // Also log as error in main log
        self::error("Security Event: $event", $context);
    }
    
    /**
     * Check if we should log this level
     */
    private static function shouldLog($level) {
        $levels = [
            LOG_LEVEL_DEBUG => 4,
            LOG_LEVEL_INFO => 3,
            LOG_LEVEL_WARNING => 2,
            LOG_LEVEL_ERROR => 1
        ];
        
        $currentLevel = LOG_LEVEL;
        return $levels[$level] <= $levels[$currentLevel];
    }
    
    /**
     * Rotate log files
     */
    public static function rotate($maxSize = 10485760) { // 10MB default
        if (self::$logFile === null) {
            self::init();
        }
        
        if (file_exists(self::$logFile) && filesize(self::$logFile) > $maxSize) {
            $backupFile = self::$logFile . '.' . date('Y-m-d-H-i-s');
            rename(self::$logFile, $backupFile);
            
            // Compress old log file
            if (function_exists('gzencode')) {
                $data = file_get_contents($backupFile);
                file_put_contents($backupFile . '.gz', gzencode($data));
                unlink($backupFile);
            }
        }
    }
    
    /**
     * Clean old log files
     */
    public static function cleanup($daysToKeep = 30) {
        $files = glob(LOG_PATH . '*.log.*');
        $cutoff = time() - ($daysToKeep * 24 * 60 * 60);
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
            }
        }
    }
    
    /**
     * Get log contents
     */
    public static function getLogContents($lines = 100, $logType = 'app') {
        $logFile = LOG_PATH . $logType . '.log';
        
        if (!file_exists($logFile)) {
            return [];
        }
        
        $content = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        return array_slice($content, -$lines);
    }
}
?>
