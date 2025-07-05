<?php
/**
 * Database migration for email verification system
 * Run this file to create the required table for OTP verification
 */

header('Content-Type: application/json');
require_once 'config.php';

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // 1. Create email_verifications table
    $sql = "
    CREATE TABLE IF NOT EXISTS `email_verifications` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `email` VARCHAR(255) NOT NULL,
        `otp` VARCHAR(10) NOT NULL,
        `firstname` VARCHAR(100) NOT NULL,
        `lastname` VARCHAR(100) NOT NULL,
        `password_hash` VARCHAR(255) NOT NULL,
        `expires_at` DATETIME NOT NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `attempts` INT(11) NOT NULL DEFAULT 0,
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_email` (`email`),
        KEY `idx_email` (`email`),
        KEY `idx_otp` (`otp`),
        KEY `idx_expires_at` (`expires_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    $pdo->exec($sql);
    
    // 2. Check if email_verified column exists in users table
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE table_name = 'users' 
        AND table_schema = DATABASE() 
        AND column_name = 'email_verified'
    ");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] == 0) {
        $sql = "ALTER TABLE `users` ADD COLUMN `email_verified` TINYINT(1) NOT NULL DEFAULT 0 AFTER `email`";
        $pdo->exec($sql);
    }
    
    // 3. Check if created_at column exists in users table
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE table_name = 'users' 
        AND table_schema = DATABASE() 
        AND column_name = 'created_at'
    ");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] == 0) {
        $sql = "ALTER TABLE `users` ADD COLUMN `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `email_verified`";
        $pdo->exec($sql);
    }
    
    // 4. Check if updated_at column exists in users table
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE table_name = 'users' 
        AND table_schema = DATABASE() 
        AND column_name = 'updated_at'
    ");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['count'] == 0) {
        $sql = "ALTER TABLE `users` ADD COLUMN `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`";
        $pdo->exec($sql);
    }
    
    // 5. Clean up expired verification records
    $cleanupSql = "
    DELETE FROM `email_verifications` 
    WHERE `expires_at` < NOW() OR `created_at` < DATE_SUB(NOW(), INTERVAL 1 DAY)
    ";
    $pdo->exec($cleanupSql);
    
    // Commit transaction
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Database migration completed successfully',
        'tables' => [
            'email_verifications' => 'Created/Updated',
            'users' => 'Added email_verified, created_at, updated_at columns'
        ]
    ]);
    
} catch (PDOException $e) {
    // Rollback transaction on error
    $pdo->rollback();
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Migration failed: ' . $e->getMessage(),
        'error_code' => $e->getCode()
    ]);
}
?>
