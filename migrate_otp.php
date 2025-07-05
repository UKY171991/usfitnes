<?php
/**
 * Database migration for email verification system
 * Run this file to create the required table for OTP verification
 */

require_once 'config.php';

try {
    // Create email_verifications table
    $sql = "
    CREATE TABLE IF NOT EXISTS email_verifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL UNIQUE,
        otp VARCHAR(10) NOT NULL,
        firstname VARCHAR(100) NOT NULL,
        lastname VARCHAR(100) NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        expires_at DATETIME NOT NULL,
        created_at DATETIME NOT NULL,
        attempts INT DEFAULT 0,
        INDEX idx_email (email),
        INDEX idx_otp (otp),
        INDEX idx_expires_at (expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    $pdo->exec($sql);
    
    // Add email_verified column to users table if it doesn't exist
    $sql = "
    ALTER TABLE users 
    ADD COLUMN IF NOT EXISTS email_verified TINYINT(1) DEFAULT 0 AFTER email
    ";
    
    $pdo->exec($sql);
    
    // Clean up expired verification records (optional - can be run periodically)
    $cleanupSql = "
    DELETE FROM email_verifications 
    WHERE expires_at < NOW() OR created_at < DATE_SUB(NOW(), INTERVAL 1 DAY)
    ";
    
    $pdo->exec($cleanupSql);
    
    echo json_encode([
        'success' => true,
        'message' => 'Database migration completed successfully',
        'tables' => [
            'email_verifications' => 'Created/Updated',
            'users' => 'Added email_verified column'
        ]
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Migration failed: ' . $e->getMessage()
    ]);
}
?>
