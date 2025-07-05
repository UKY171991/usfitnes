# SQL Commands to Fix Database Schema

## Option 1: Run this PHP migration script
Visit: `https://usfitnes.com/migrate_otp.php`

## Option 2: Run these SQL commands manually in your database

### 1. Create email_verifications table:
```sql
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 2. Add email_verified column to users table:
```sql
ALTER TABLE `users` ADD COLUMN `email_verified` TINYINT(1) NOT NULL DEFAULT 0 AFTER `email`;
```

### 3. Add created_at column to users table:
```sql
ALTER TABLE `users` ADD COLUMN `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `email_verified`;
```

### 4. Add updated_at column to users table:
```sql
ALTER TABLE `users` ADD COLUMN `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;
```

## How to execute:

### Method 1: cPanel/phpMyAdmin
1. Log into your hosting control panel
2. Go to phpMyAdmin
3. Select your database
4. Go to "SQL" tab
5. Copy and paste each command above one by one
6. Click "Go" for each command

### Method 2: Command Line
If you have SSH access:
```bash
mysql -u username -p database_name < simple_migration.sql
```

### Method 3: Use the migration script
Just visit: `https://usfitnes.com/migrate_otp.php`

## After running the migration:
1. The registration page should work without errors
2. You can test OTP registration functionality
3. Email verification will be fully functional

## Troubleshooting:
- If you get "column already exists" errors, that's normal - it means the column was already added
- Make sure your database user has ALTER privileges
- If using shared hosting, some hosts restrict ALTER commands - use the PHP migration script instead
