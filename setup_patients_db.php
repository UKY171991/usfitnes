<?php
require_once 'config.php';

echo "Starting database setup for patients table...\n";

// First, check if patients table exists and drop it if it has issues
$check_query = "SHOW TABLES LIKE 'patients'";
$result = mysqli_query($conn, $check_query);

if (mysqli_num_rows($result) > 0) {
    echo "Patients table exists. Checking structure...\n";
    
    // Check for problematic columns or indexes
    $desc_query = "DESCRIBE patients";
    $desc_result = mysqli_query($conn, $desc_query);
    
    $has_order_number = false;
    while ($row = mysqli_fetch_assoc($desc_result)) {
        if ($row['Field'] === 'order_number') {
            $has_order_number = true;
            break;
        }
    }
    
    if ($has_order_number) {
        echo "Found problematic order_number column. Dropping and recreating table...\n";
        mysqli_query($conn, "DROP TABLE IF EXISTS patients");
        echo "Old patients table dropped.\n";
    }
} else {
    echo "Patients table does not exist.\n";
}

// Create the patients table with correct structure
$create_sql = "
CREATE TABLE IF NOT EXISTS `patients` (
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
    echo "Patients table created successfully!\n";
} else {
    echo "Error creating patients table: " . mysqli_error($conn) . "\n";
    exit;
}

// Insert sample data
$sample_patients = [
    [
        'first_name' => 'John',
        'last_name' => 'Doe', 
        'phone' => '+1234567890',
        'email' => 'john.doe@email.com',
        'date_of_birth' => '1985-06-15',
        'gender' => 'male'
    ],
    [
        'first_name' => 'Jane',
        'last_name' => 'Smith',
        'phone' => '+1234567891', 
        'email' => 'jane.smith@email.com',
        'date_of_birth' => '1990-03-22',
        'gender' => 'female'
    ],
    [
        'first_name' => 'Robert',
        'last_name' => 'Johnson',
        'phone' => '+1234567892',
        'email' => 'robert.j@email.com', 
        'date_of_birth' => '1978-11-08',
        'gender' => 'male'
    ]
];

echo "Inserting sample data...\n";

$insert_sql = "INSERT INTO patients (first_name, last_name, phone, email, date_of_birth, gender, status, created_at) 
               VALUES (?, ?, ?, ?, ?, ?, 'active', NOW())";

$stmt = mysqli_prepare($conn, $insert_sql);

foreach ($sample_patients as $patient) {
    mysqli_stmt_bind_param($stmt, 'ssssss', 
        $patient['first_name'], 
        $patient['last_name'], 
        $patient['phone'], 
        $patient['email'], 
        $patient['date_of_birth'], 
        $patient['gender']
    );
    
    if (mysqli_stmt_execute($stmt)) {
        echo "Inserted: {$patient['first_name']} {$patient['last_name']}\n";
    } else {
        echo "Error inserting {$patient['first_name']}: " . mysqli_error($conn) . "\n";
    }
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

echo "Database setup completed successfully!\n";
echo "You can now use the patients management system.\n";
?>
