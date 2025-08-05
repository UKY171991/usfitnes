<?php
// Check if patients table exists and create it if it doesn't
require_once 'config.php';

try {
    // Check if table exists
    $result = $pdo->query("SHOW TABLES LIKE 'patients'");
    
    if ($result->rowCount() == 0) {
        echo "Creating patients table...\n";
        
        $sql = "CREATE TABLE `patients` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `patient_id` varchar(20) DEFAULT NULL,
            `name` varchar(255) NOT NULL,
            `phone` varchar(20) DEFAULT NULL,
            `email` varchar(255) DEFAULT NULL,
            `date_of_birth` date DEFAULT NULL,
            `gender` enum('male','female','other') DEFAULT NULL,
            `address` text DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `patient_id` (`patient_id`),
            KEY `name` (`name`),
            KEY `phone` (`phone`),
            KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        $pdo->exec($sql);
        echo "Patients table created successfully!\n";
        
        // Insert some sample data
        $samplePatients = [
            ['PAT000001', 'John Doe', '(555) 123-4567', 'john.doe@email.com', '1985-03-15', 'male', '123 Main St, City, State'],
            ['PAT000002', 'Jane Smith', '(555) 987-6543', 'jane.smith@email.com', '1990-07-22', 'female', '456 Oak Ave, City, State'],
            ['PAT000003', 'Robert Johnson', '(555) 555-0123', 'r.johnson@email.com', '1975-12-08', 'male', '789 Pine Rd, City, State'],
            ['PAT000004', 'Emily Davis', '(555) 444-7890', 'emily.davis@email.com', '1988-09-30', 'female', '321 Elm St, City, State'],
            ['PAT000005', 'Michael Brown', '(555) 333-2468', 'michael.brown@email.com', '1992-05-17', 'male', '654 Maple Dr, City, State']
        ];
        
        $insertSql = "INSERT INTO patients (patient_id, name, phone, email, date_of_birth, gender, address, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $pdo->prepare($insertSql);
        
        foreach ($samplePatients as $patient) {
            $stmt->execute($patient);
        }
        
        echo "Sample patient data inserted successfully!\n";
        
    } else {
        echo "Patients table already exists.\n";
        
        // Check if we need to add missing columns
        $columns = $pdo->query("DESCRIBE patients")->fetchAll(PDO::FETCH_COLUMN);
        
        if (!in_array('patient_id', $columns)) {
            echo "Adding patient_id column...\n";
            $pdo->exec("ALTER TABLE patients ADD COLUMN patient_id varchar(20) DEFAULT NULL AFTER id");
            $pdo->exec("ALTER TABLE patients ADD UNIQUE KEY patient_id (patient_id)");
            
            // Generate patient IDs for existing records
            $patients = $pdo->query("SELECT id FROM patients WHERE patient_id IS NULL")->fetchAll(PDO::FETCH_ASSOC);
            $updateStmt = $pdo->prepare("UPDATE patients SET patient_id = ? WHERE id = ?");
            
            foreach ($patients as $patient) {
                $patientId = 'PAT' . str_pad($patient['id'], 6, '0', STR_PAD_LEFT);
                $updateStmt->execute([$patientId, $patient['id']]);
            }
            
            echo "Patient IDs generated successfully!\n";
        }
        
        if (!in_array('updated_at', $columns)) {
            echo "Adding updated_at column...\n";
            $pdo->exec("ALTER TABLE patients ADD COLUMN updated_at timestamp NULL DEFAULT NULL");
        }
    }
    
    echo "Database check completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
