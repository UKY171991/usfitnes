<?php
echo "Testing database connection...\n";

// Test the config file
if (file_exists('config.php')) {
    echo "Config file found.\n";
    
    // Include and test connection
    try {
        require_once 'config.php';
        
        if (isset($conn) && $conn) {
            echo "Database connection successful!\n";
            
            // Test a simple query
            $result = mysqli_query($conn, "SELECT 1 as test");
            if ($result) {
                echo "Database query test successful!\n";
                
                // Check if patients table exists
                $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'patients'");
                if (mysqli_num_rows($table_check) > 0) {
                    echo "Patients table exists.\n";
                    
                    // Count patients
                    $count_result = mysqli_query($conn, "SELECT COUNT(*) as count FROM patients");
                    $count_row = mysqli_fetch_assoc($count_result);
                    echo "Number of patients: " . $count_row['count'] . "\n";
                } else {
                    echo "Patients table does not exist.\n";
                }
            } else {
                echo "Database query failed: " . mysqli_error($conn) . "\n";
            }
        } else {
            echo "Database connection failed.\n";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "Config file not found.\n";
}
?>
