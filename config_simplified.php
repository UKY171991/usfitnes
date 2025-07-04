<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Try remote database first, fallback to local SQLite if it fails
$configs = [
    // Remote database configuration
    [
        'type' => 'mysql',
        'host' => 'localhost',
        'dbname' => 'u902379465_fitness',
        'username' => 'u902379465_fitness',
        'password' => '0Rz>Sa6qOi7M'
    ],
    // Local SQLite fallback
    [
        'type' => 'sqlite',
        'path' => 'pathlab_local.db'
    ]
];

$pdo = null;
$dbConnected = false;

foreach ($configs as $config) {
    try {
        if ($config['type'] === 'mysql') {
            // Try MySQL connection
            $pdo = new PDO("mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8", 
                          $config['username'], $config['password']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $dbConnected = true;
            break;
        } elseif ($config['type'] === 'sqlite') {
            // Try SQLite connection
            $pdo = new PDO("sqlite:" . $config['path']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $dbConnected = true;
            break;
        }
    } catch(PDOException $e) {
        // Continue to next configuration
        continue;
    }
}

if (!$dbConnected) {
    die("Error: Could not connect to any database. Please check your configuration.");
}

// Create tables if they don't exist (simplified structure)
$sql = "
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    user_type VARCHAR(20) DEFAULT 'lab_technician',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS patients (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    patient_id VARCHAR(20) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    date_of_birth DATE NOT NULL,
    gender VARCHAR(10) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS test_categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    category_name VARCHAR(100) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tests (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    test_code VARCHAR(20) UNIQUE NOT NULL,
    test_name VARCHAR(200) NOT NULL,
    category_id INTEGER NOT NULL,
    price DECIMAL(10,2) DEFAULT 0.00,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS doctors (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    doctor_id VARCHAR(20) UNIQUE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    specialization VARCHAR(100) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS test_orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id VARCHAR(20) UNIQUE NOT NULL,
    patient_id INTEGER NOT NULL,
    doctor_id INTEGER,
    order_date DATE NOT NULL,
    status VARCHAR(20) DEFAULT 'Pending',
    total_amount DECIMAL(10,2) DEFAULT 0.00,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
";

try {
    // Execute table creation
    $statements = array_filter(explode(';', $sql));
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
    
    // Create default admin user if it doesn't exist
    $checkAdmin = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = 'admin'");
    $checkAdmin->execute();
    
    if ($checkAdmin->fetchColumn() == 0) {
        $adminPassword = password_hash('password', PASSWORD_DEFAULT);
        $insertAdmin = $pdo->prepare("
            INSERT INTO users (username, password, email, full_name, user_type) 
            VALUES ('admin', ?, 'admin@pathlabpro.com', 'System Administrator', 'admin')
        ");
        $insertAdmin->execute([$adminPassword]);
    }
    
    // Insert sample data if empty
    $checkData = $pdo->prepare("SELECT COUNT(*) FROM test_categories");
    $checkData->execute();
    
    if ($checkData->fetchColumn() == 0) {
        // Sample categories
        $categories = [
            'Hematology',
            'Biochemistry', 
            'Microbiology',
            'Pathology'
        ];
        
        $insertCategory = $pdo->prepare("INSERT INTO test_categories (category_name) VALUES (?)");
        foreach ($categories as $category) {
            $insertCategory->execute([$category]);
        }
        
        // Sample tests
        $tests = [
            ['CBC001', 'Complete Blood Count', 1, 25.00],
            ['GLU001', 'Blood Glucose', 2, 15.00],
            ['LIP001', 'Lipid Profile', 2, 35.00],
            ['URI001', 'Urine Analysis', 3, 20.00]
        ];
        
        $insertTest = $pdo->prepare("INSERT INTO tests (test_code, test_name, category_id, price) VALUES (?, ?, ?, ?)");
        foreach ($tests as $test) {
            $insertTest->execute($test);
        }
        
        // Sample doctors
        $doctors = [
            ['DOC001', 'Dr. John', 'Smith', '123-456-7890', 'john@clinic.com', 'Pathology'],
            ['DOC002', 'Dr. Jane', 'Doe', '098-765-4321', 'jane@hospital.com', 'Internal Medicine']
        ];
        
        $insertDoctor = $pdo->prepare("INSERT INTO doctors (doctor_id, first_name, last_name, phone, email, specialization) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($doctors as $doctor) {
            $insertDoctor->execute($doctor);
        }
        
        // Sample patients
        $patients = [
            ['PAT001', 'John Doe', '555-123-4567', 'john.doe@email.com', '1985-06-15', 'Male'],
            ['PAT002', 'Jane Smith', '555-987-6543', 'jane.smith@email.com', '1990-12-03', 'Female']
        ];
        
        $insertPatient = $pdo->prepare("INSERT INTO patients (patient_id, full_name, phone, email, date_of_birth, gender) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($patients as $patient) {
            $insertPatient->execute($patient);
        }
    }
    
} catch(PDOException $e) {
    error_log("Database setup error: " . $e->getMessage());
}

return $pdo;
?>
