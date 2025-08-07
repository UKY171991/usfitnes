<?php
/**
 * Patients DataTable AJAX Handler
 * USFitness Lab - AdminLTE3
 */

require_once '../config.php';
header('Content-Type: application/json');

try {
    $conn = getDatabaseConnection();
    
    // DataTables parameters
    $draw = (int)($_POST['draw'] ?? 1);
    $start = (int)($_POST['start'] ?? 0);
    $length = (int)($_POST['length'] ?? 25);
    $search = $_POST['search']['value'] ?? '';
    $orderColumn = (int)($_POST['order'][0]['column'] ?? 0);
    $orderDir = $_POST['order'][0]['dir'] ?? 'desc';
    
    // Column mapping
    $columns = ['id', 'name', 'email', 'phone', 'gender', 'created_at'];
    $orderBy = $columns[$orderColumn] ?? 'id';
    
    // Build query
    $where = "1=1";
    $params = [];
    
    // Search functionality
    if (!empty($search)) {
        $where .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ?)";
        $searchParam = "%{$search}%";
        $params = [$searchParam, $searchParam, $searchParam];
    }
    
    // Get total records
    $stmt = $conn->prepare("SELECT COUNT(*) FROM patients WHERE {$where}");
    $stmt->execute($params);
    $totalRecords = $stmt->fetchColumn();
    
    // Get filtered data
    $sql = "SELECT id, name, email, phone, gender, created_at 
            FROM patients 
            WHERE {$where} 
            ORDER BY {$orderBy} {$orderDir} 
            LIMIT {$start}, {$length}";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format data for DataTables
    $formattedData = [];
    foreach ($data as $row) {
        $formattedData[] = [
            'id' => $row['id'],
            'name' => htmlspecialchars($row['name']),
            'email' => htmlspecialchars($row['email'] ?: 'N/A'),
            'phone' => htmlspecialchars($row['phone'] ?: 'N/A'),
            'gender' => ucfirst($row['gender'] ?: 'N/A'),
            'created_at' => date('M j, Y', strtotime($row['created_at'])),
            'actions' => generateActionButtons($row['id'], $row['name'])
        ];
    }
    
    echo json_encode([
        'draw' => $draw,
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $totalRecords,
        'data' => $formattedData
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'draw' => 1,
        'recordsTotal' => 0,
        'recordsFiltered' => 0,
        'data' => [],
        'error' => $e->getMessage()
    ]);
}

function generateActionButtons($id, $name) {
    return '
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-sm btn-info" onclick="viewPatient(' . $id . ')" title="View">
                <i class="fas fa-eye"></i>
            </button>
            <button type="button" class="btn btn-sm btn-warning" onclick="editPatient(' . $id . ')" title="Edit">
                <i class="fas fa-edit"></i>
            </button>
            <button type="button" class="btn btn-sm btn-danger" onclick="deletePatient(' . $id . ', \'' . addslashes($name) . '\')" title="Delete">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    ';
}
?>