<?php
/**
 * PathLab Pro - Secure Admin Access Control
 * This file provides additional security for admin-only operations
 */

class SecureAdminAccess {
    
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        require_once '../config.php';
        global $pdo;
        $this->pdo = $pdo;
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Check if current user has admin access
     */
    public function checkAdminAccess($redirectOnFail = true) {
        session_start();
        
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            if ($redirectOnFail) {
                $this->denyAccess('Not logged in', 'login_required');
            }
            return false;
        }
        
        // Check if user is admin
        if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
            if ($redirectOnFail) {
                $this->denyAccess('Insufficient privileges', 'admin_required');
            }
            return false;
        }
        
        // Additional security: Verify admin status in database
        try {
            $stmt = $this->pdo->prepare("SELECT user_type, status FROM users WHERE id = ? AND user_type = 'admin'");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            
            if (!$user) {
                if ($redirectOnFail) {
                    $this->denyAccess('Admin verification failed', 'verification_failed');
                }
                return false;
            }
            
        } catch (Exception $e) {
            if ($redirectOnFail) {
                $this->denyAccess('Database verification error', 'db_error');
            }
            return false;
        }
        
        // Log successful admin access
        $this->logAdminAccess();
        
        return true;
    }
    
    /**
     * Deny access with appropriate error page
     */
    private function denyAccess($reason, $type = 'general') {
        http_response_code(403);
        
        // Log the access attempt
        $this->logAccessAttempt($reason, $type);
        
        $errorPages = [
            'login_required' => $this->getLoginRequiredPage(),
            'admin_required' => $this->getAdminRequiredPage(),
            'verification_failed' => $this->getVerificationFailedPage(),
            'db_error' => $this->getDatabaseErrorPage(),
            'general' => $this->getGeneralDeniedPage()
        ];
        
        die($errorPages[$type] ?? $errorPages['general']);
    }
    
    /**
     * Log admin access attempts
     */
    private function logAccessAttempt($reason, $type) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO admin_access_logs (
                    user_id, ip_address, user_agent, access_type, 
                    reason, request_uri, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $_SESSION['user_id'] ?? null,
                $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                $type,
                $reason,
                $_SERVER['REQUEST_URI'] ?? 'unknown'
            ]);
        } catch (Exception $e) {
            // Log to file if database logging fails
            error_log("Admin access log failed: " . $e->getMessage());
        }
    }
    
    /**
     * Log successful admin access
     */
    private function logAdminAccess() {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO admin_access_logs (
                    user_id, ip_address, user_agent, access_type, 
                    reason, request_uri, created_at
                ) VALUES (?, ?, ?, 'success', 'Admin access granted', ?, NOW())
            ");
            
            $stmt->execute([
                $_SESSION['user_id'],
                $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                $_SERVER['REQUEST_URI'] ?? 'unknown'
            ]);
        } catch (Exception $e) {
            // Create the table if it doesn't exist
            $this->createAdminLogsTable();
        }
    }
    
    /**
     * Create admin logs table if it doesn't exist
     */
    private function createAdminLogsTable() {
        try {
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS admin_access_logs (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NULL,
                    ip_address VARCHAR(45) NOT NULL,
                    user_agent TEXT,
                    access_type VARCHAR(50) NOT NULL,
                    reason VARCHAR(255) NOT NULL,
                    request_uri VARCHAR(500),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_user_id (user_id),
                    INDEX idx_created_at (created_at),
                    INDEX idx_access_type (access_type)
                )
            ");
        } catch (Exception $e) {
            error_log("Failed to create admin_access_logs table: " . $e->getMessage());
        }
    }
    
    /**
     * Get login required error page
     */
    private function getLoginRequiredPage() {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <title>Login Required - PathLab Pro</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f8f9fa; }
                .error-container { max-width: 500px; margin: 0 auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
                .error-icon { font-size: 4rem; color: #007bff; margin-bottom: 20px; }
                h1 { color: #007bff; margin-bottom: 20px; }
                p { color: #6c757d; margin-bottom: 30px; }
                .btn { background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; }
                .info { background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px; margin: 20px 0; color: #0c5460; }
            </style>
        </head>
        <body>
            <div class="error-container">
                <div class="error-icon">🔐</div>
                <h1>Authentication Required</h1>
                <p>You must be logged in to access this administrative resource.</p>
                <div class="info">
                    Please log in with your administrator credentials to continue.
                </div>
                <a href="../login.php" class="btn">Login</a>
            </div>
        </body>
        </html>';
    }
    
    /**
     * Get admin required error page
     */
    private function getAdminRequiredPage() {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <title>Administrator Access Required - PathLab Pro</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f8f9fa; }
                .error-container { max-width: 500px; margin: 0 auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
                .error-icon { font-size: 4rem; color: #dc3545; margin-bottom: 20px; }
                h1 { color: #dc3545; margin-bottom: 20px; }
                p { color: #6c757d; margin-bottom: 30px; }
                .btn { background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 5px; }
                .btn-secondary { background: #6c757d; }
                .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0; color: #856404; }
            </style>
        </head>
        <body>
            <div class="error-container">
                <div class="error-icon">⚠️</div>
                <h1>Administrator Access Required</h1>
                <p>This resource requires administrator privileges.</p>
                <div class="warning">
                    <strong>Access Denied:</strong> Your current account does not have sufficient privileges to access this administrative tool.
                </div>
                <a href="../dashboard.php" class="btn-secondary btn">Return to Dashboard</a>
                <a href="../login.php" class="btn">Login as Administrator</a>
            </div>
        </body>
        </html>';
    }
    
    /**
     * Get verification failed error page
     */
    private function getVerificationFailedPage() {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <title>Verification Failed - PathLab Pro</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f8f9fa; }
                .error-container { max-width: 500px; margin: 0 auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
                .error-icon { font-size: 4rem; color: #dc3545; margin-bottom: 20px; }
                h1 { color: #dc3545; margin-bottom: 20px; }
                p { color: #6c757d; margin-bottom: 30px; }
                .btn { background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; }
                .error { background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 20px 0; color: #721c24; }
            </style>
        </head>
        <body>
            <div class="error-container">
                <div class="error-icon">🚫</div>
                <h1>Access Verification Failed</h1>
                <p>Unable to verify your administrator privileges.</p>
                <div class="error">
                    <strong>Security Notice:</strong> Your session may have expired or your account privileges may have been modified. Please log in again.
                </div>
                <a href="../login.php" class="btn">Re-authenticate</a>
            </div>
        </body>
        </html>';
    }
    
    /**
     * Get database error page
     */
    private function getDatabaseErrorPage() {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <title>System Error - PathLab Pro</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f8f9fa; }
                .error-container { max-width: 500px; margin: 0 auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
                .error-icon { font-size: 4rem; color: #dc3545; margin-bottom: 20px; }
                h1 { color: #dc3545; margin-bottom: 20px; }
                p { color: #6c757d; margin-bottom: 30px; }
                .btn { background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; }
                .error { background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 20px 0; color: #721c24; }
            </style>
        </head>
        <body>
            <div class="error-container">
                <div class="error-icon">💥</div>
                <h1>System Error</h1>
                <p>A system error occurred while verifying your access.</p>
                <div class="error">
                    <strong>Technical Issue:</strong> Unable to connect to the authentication database. Please contact your system administrator.
                </div>
                <a href="../dashboard.php" class="btn">Return to Dashboard</a>
            </div>
        </body>
        </html>';
    }
    
    /**
     * Get general denied page
     */
    private function getGeneralDeniedPage() {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <title>Access Denied - PathLab Pro</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f8f9fa; }
                .error-container { max-width: 500px; margin: 0 auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
                .error-icon { font-size: 4rem; color: #dc3545; margin-bottom: 20px; }
                h1 { color: #dc3545; margin-bottom: 20px; }
                p { color: #6c757d; margin-bottom: 30px; }
                .btn { background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; }
            </style>
        </head>
        <body>
            <div class="error-container">
                <div class="error-icon">🔒</div>
                <h1>Access Denied</h1>
                <p>You do not have permission to access this resource.</p>
                <a href="../dashboard.php" class="btn">Return to Dashboard</a>
            </div>
        </body>
        </html>';
    }
}

// Usage: Include this file and call SecureAdminAccess::getInstance()->checkAdminAccess();
?>