<?php
require_once '../config.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

try {
    // Check if database connection exists
    if (!isset($pdo) || !$pdo instanceof PDO) {
        // Try to create connection
        require_once '../config.php';
        if (!isset($pdo) || !$pdo instanceof PDO) {
            echo json_encode([
                'draw' => intval($_POST['draw'] ?? 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Database connection not available'
            ]);
            exit;
        }
    }
    
    // Ensure patients table exists with sample data for testing
    $pdo->exec("CREATE TABLE IF NOT EXISTS patients (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        phone VARCHAR(20),
        email VARCHAR(100),
        date_of_birth DATE,
        gender ENUM('male', 'female', 'other'),
        address TEXT,
        emergency_contact VARCHAR(200),
        medical_history TEXT,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    // Add sample data if table is empty
    $count = $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();
    if ($count == 0) {
        $pdo->exec("INSERT INTO patients (first_name, last_name, phone, email, date_of_birth, gender, address, status) VALUES
            ('John', 'Doe', '+1234567890', 'john.doe@email.com', '1985-03-15', 'male', '123 Main St, City', 'active'),
            ('Jane', 'Smith', '+0987654321', 'jane.smith@email.com', '1990-07-22', 'female', '456 Oak Ave, Town', 'active'),
            ('Mike', 'Johnson', '+5555555555', 'mike.j@email.com', '1978-11-08', 'male', '789 Pine Rd, Village', 'inactive'),
            ('Sarah', 'Wilson', '+4444444444', 'sarah.w@email.com', '1992-02-14', 'female', '321 Elm St, City', 'active')
        ");
    }
    
    // DataTables parameters
    $draw = intval($_POST['draw'] ?? 1);
    $start = intval($_POST['start'] ?? 0);
    $length = intval($_POST['length'] ?? 10);
    $search = $_POST['search']['value'] ?? '';
    $orderColumn = $_POST['order'][0]['column'] ?? 0;
    $orderDir = $_POST['order'][0]['dir'] ?? 'desc';
    
    // Column mapping
    $columns = ['id', 'full_name', 'phone', 'email', 'age', 'gender', 'status', 'actions'];
    $orderBy = $columns[$orderColumn] ?? 'id';
    
    // Base query
    $whereClause = "WHERE 1=1";
    $params = [];
    
    // Search functionality
    if (!empty($search)) {
        $whereClause .= " AND (CONCAT(first_name, ' ', last_name) LIKE :search 
                              OR phone LIKE :search 
                              OR email LIKE :search 
                              OR gender LIKE :search 
                              OR status LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }
    
    // Count total records
    $totalQuery = "SELECT COUNT(*) as count FROM patients $whereClause";
    $stmt = $pdo->prepare($totalQuery);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $totalRecords = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get data with pagination
    $dataQuery = "SELECT id, first_name, last_name, phone, email, date_of_birth, gender, status, address, created_at
                  FROM patients 
                  $whereClause 
                  ORDER BY $orderBy $orderDir 
                  LIMIT :start, :length";
    
    $stmt = $pdo->prepare($dataQuery);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':start', $start, PDO::PARAM_INT);
    $stmt->bindValue(':length', $length, PDO::PARAM_INT);
    $stmt->execute();
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format data for DataTables
    $data = [];
    foreach ($patients as $patient) {
        // Calculate age
        $age = '';
        if (!empty($patient['date_of_birth'])) {
            $dob = new DateTime($patient['date_of_birth']);
            $now = new DateTime();
            $age = $now->diff($dob)->y;
        }
        
        $data[] = [
            'id' => $patient['id'],
            'first_name' => $patient['first_name'],
            'last_name' => $patient['last_name'],
            'phone' => $patient['phone'] ?: '',
            'email' => $patient['email'] ?: '',
            'date_of_birth' => $patient['date_of_birth'],
            'gender' => $patient['gender'] ?: '',
            'status' => $patient['status'] ?: 'active',
            'address' => $patient['address'] ?: '',
            'created_at' => $patient['created_at']
        ];
    }
    
    // Response
    echo json_encode([
        'draw' => $draw,
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $totalRecords,
        'data' => $data
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'draw' => intval($_POST['draw'] ?? 1),
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
    $params = [];
    
    // Search functionality
    if (!empty($search)) {
        $baseQuery .= " AND (
            first_name LIKE ? OR 
            last_name LIKE ? OR 
            phone LIKE ? OR 
            email LIKE ?
        )";
        $searchParam = "%$search%";
        $params = array_fill(0, 4, $searchParam);
    }
    
    // Count total records
    $totalRecords = $conn->query("SELECT COUNT(*) FROM patients")->fetchColumn();
    
    // Count filtered records
    $filteredQuery = "SELECT COUNT(*) $baseQuery";
    $stmt = $conn->prepare($filteredQuery);
    $stmt->execute($params);
    $filteredRecords = $stmt->fetchColumn();
    
    // Get data
    $dataQuery = "
        SELECT 
            id,
            CONCAT(first_name, ' ', last_name) as name,
            phone,
            COALESCE(email, '-') as email,
            TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) as age,
            gender,
            status,
            date_of_birth
        $baseQuery 
        ORDER BY $orderBy $orderDir 
        LIMIT $start, $length
    ";
    
    $stmt = $conn->prepare($dataQuery);
    $stmt->execute($params);
    $data = [];
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = [
            'id' => (int)$row['id'],
            'name' => htmlspecialchars($row['name']),
            'phone' => htmlspecialchars($row['phone']),
            'email' => htmlspecialchars($row['email']),
            'age' => (int)$row['age'],
            'gender' => $row['gender'],
            'status' => $row['status'],
            'actions' => generateActionButtons($row['id'])
        ];
    }
    
    echo json_encode([
        'draw' => $draw,
        'recordsTotal' => (int)$totalRecords,
        'recordsFiltered' => (int)$filteredRecords,
        'data' => $data
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}

function generateActionButtons($id) {
    return '
        <div class="btn-group btn-group-sm">
            <button class="btn btn-info" onclick="viewPatient(' . $id . ')" title="View">
                <i class="fas fa-eye"></i>
            </button>
            <button class="btn btn-warning" onclick="editPatient(' . $id . ')" title="Edit">
                <i class="fas fa-edit"></i>
            </button>
            <button class="btn btn-danger" onclick="deletePatient(' . $id . ')" title="Delete">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    ';
}
?>
