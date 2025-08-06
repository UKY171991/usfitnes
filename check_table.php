<?php
require_once 'config.php';

// Check if patients table exists
$result = mysqli_query($conn, "SHOW TABLES LIKE 'patients'");
if (mysqli_num_rows($result) > 0) {
    echo "Patients table exists\n";
    
    // Show table structure
    $result = mysqli_query($conn, "DESCRIBE patients");
    while ($row = mysqli_fetch_assoc($result)) {
        echo $row['Field'] . ' - ' . $row['Type'] . ' - ' . $row['Key'] . "\n";
    }
} else {
    echo "Patients table does not exist\n";
    
    // Create the patients table
    $create_sql = "
    CREATE TABLE `patients` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `first_name` varchar(100) NOT NULL,
      `last_name` varchar(100) NOT NULL,
      `phone` varchar(20) NOT NULL,
      `email` varchar(255) DEFAULT NULL,
      `date_of_birth` date DEFAULT NULL,
      `gender` enum('male','female') DEFAULT NULL,
      `status` enum('active','inactive','deleted') NOT NULL DEFAULT 'active',
      `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `phone_unique` (`phone`),
      KEY `idx_status` (`status`),
      KEY `idx_name` (`first_name`, `last_name`),
      KEY `idx_created_at` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    if (mysqli_query($conn, $create_sql)) {
        echo "Patients table created successfully\n";
    } else {
        echo "Error creating table: " . mysqli_error($conn) . "\n";
    }
}

mysqli_close($conn);
?>
