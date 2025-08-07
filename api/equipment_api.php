<?php
require_once '../config.php';
require_once '../includes/init.php';

// Set JSON header
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$action = $_REQUEST['action'] ?? '';

try {
    switch ($action) {
        case 'list':
            listEquipment();
            break;
        case 'get':
            getEquipment();
            break;
        case 'add':
        case 'create':
            createEquipment();
            break;
        case 'update':
            updateEquipment();
            break;
        case 'delete':
            deleteEquipment();
            break;
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    error_log("Equipment API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function listEquipment() {
    global $pdo;
    
    try {
        $query = "
            SELECT 
                id,
                name,
                model,
                category,
                serial_number,
                manufacturer,
                purchase_date,
                warranty_expiry,
                last_maintenance,
                next_maintenance,
                COALESCE(status, 'Active') as status,
                description,
                created_at
            FROM equipment 
            WHERE (status != 'deleted' OR status IS NULL)
            ORDER BY created_at DESC
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $equipment = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $equipment
        ]);
        
    } catch (Exception $e) {
        throw new Exception('Failed to retrieve equipment: ' . $e->getMessage());
    }
}

function getEquipment() {
    global $pdo;
    
    $id = $_GET['id'] ?? null;
    if (!$id) {
        throw new Exception('Equipment ID is required');
    }
    
    try {
        $query = "
            SELECT 
                id,
                name,
                model,
                category,
                serial_number,
                manufacturer,
                purchase_date,
                warranty_expiry,
                last_maintenance,
                next_maintenance,
                COALESCE(status, 'Active') as status,
                description,
                created_at
            FROM equipment 
            WHERE id = ? AND (status != 'deleted' OR status IS NULL)
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$id]);
        $equipment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$equipment) {
            throw new Exception('Equipment not found');
        }
        
        echo json_encode([
            'success' => true,
            'data' => $equipment
        ]);
        
    } catch (Exception $e) {
        throw new Exception('Failed to retrieve equipment: ' . $e->getMessage());
    }
}

function createEquipment() {
    global $pdo;
    
    // Validation
    if (empty($_POST['name'])) {
        throw new Exception('Equipment name is required');
    }
    
    if (empty($_POST['category'])) {
        throw new Exception('Category is required');
    }
    
    // Validate dates
    $date_fields = ['purchase_date', 'warranty_expiry', 'last_maintenance', 'next_maintenance'];
    foreach ($date_fields as $field) {
        if (!empty($_POST[$field]) && !validateDate($_POST[$field])) {
            throw new Exception(ucfirst(str_replace('_', ' ', $field)) . ' must be a valid date');
        }
    }
    
    // Check for duplicate serial number if provided
    if (!empty($_POST['serial_number'])) {
        $serial_check = $pdo->prepare("SELECT id FROM equipment WHERE serial_number = ? AND (status != 'deleted' OR status IS NULL)");
        $serial_check->execute([$_POST['serial_number']]);
        if ($serial_check->fetch()) {
            throw new Exception('An equipment with this serial number already exists');
        }
    }
    
    try {
        $query = "
            INSERT INTO equipment (
                name, model, category, serial_number, manufacturer,
                purchase_date, warranty_expiry, last_maintenance, next_maintenance,
                status, description, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ";
        
        $stmt = $pdo->prepare($query);
        $success = $stmt->execute([
            $_POST['name'],
            $_POST['model'] ?: null,
            $_POST['category'],
            $_POST['serial_number'] ?: null,
            $_POST['manufacturer'] ?: null,
            $_POST['purchase_date'] ?: null,
            $_POST['warranty_expiry'] ?: null,
            $_POST['last_maintenance'] ?: null,
            $_POST['next_maintenance'] ?: null,
            $_POST['status'] ?: 'Active',
            $_POST['description'] ?: null
        ]);
        
        if ($success) {
            $equipment_id = $pdo->lastInsertId();
            echo json_encode([
                'success' => true,
                'message' => 'Equipment created successfully',
                'id' => $equipment_id
            ]);
        } else {
            throw new Exception('Failed to create equipment');
        }
        
    } catch (Exception $e) {
        throw new Exception('Database error: ' . $e->getMessage());
    }
}

function updateEquipment() {
    global $pdo;
    
    $id = $_POST['id'] ?? null;
    if (!$id) {
        throw new Exception('Equipment ID is required');
    }
    
    // Validation
    if (empty($_POST['name'])) {
        throw new Exception('Equipment name is required');
    }
    
    if (empty($_POST['category'])) {
        throw new Exception('Category is required');
    }
    
    // Validate dates
    $date_fields = ['purchase_date', 'warranty_expiry', 'last_maintenance', 'next_maintenance'];
    foreach ($date_fields as $field) {
        if (!empty($_POST[$field]) && !validateDate($_POST[$field])) {
            throw new Exception(ucfirst(str_replace('_', ' ', $field)) . ' must be a valid date');
        }
    }
    
    // Check for duplicate serial number (excluding current equipment)
    if (!empty($_POST['serial_number'])) {
        $serial_check = $pdo->prepare("SELECT id FROM equipment WHERE serial_number = ? AND id != ? AND (status != 'deleted' OR status IS NULL)");
        $serial_check->execute([$_POST['serial_number'], $id]);
        if ($serial_check->fetch()) {
            throw new Exception('An equipment with this serial number already exists');
        }
    }
    
    try {
        $query = "
            UPDATE equipment SET
                name = ?,
                model = ?,
                category = ?,
                serial_number = ?,
                manufacturer = ?,
                purchase_date = ?,
                warranty_expiry = ?,
                last_maintenance = ?,
                next_maintenance = ?,
                status = ?,
                description = ?,
                updated_at = NOW()
            WHERE id = ?
        ";
        
        $stmt = $pdo->prepare($query);
        $success = $stmt->execute([
            $_POST['name'],
            $_POST['model'] ?: null,
            $_POST['category'],
            $_POST['serial_number'] ?: null,
            $_POST['manufacturer'] ?: null,
            $_POST['purchase_date'] ?: null,
            $_POST['warranty_expiry'] ?: null,
            $_POST['last_maintenance'] ?: null,
            $_POST['next_maintenance'] ?: null,
            $_POST['status'] ?: 'Active',
            $_POST['description'] ?: null,
            $id
        ]);
        
        if ($success && $stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Equipment updated successfully'
            ]);
        } else if ($success && $stmt->rowCount() === 0) {
            echo json_encode([
                'success' => true,
                'message' => 'No changes made'
            ]);
        } else {
            throw new Exception('Failed to update equipment');
        }
        
    } catch (Exception $e) {
        throw new Exception('Database error: ' . $e->getMessage());
    }
}

function deleteEquipment() {
    global $pdo;
    
    $id = $_POST['id'] ?? null;
    if (!$id) {
        throw new Exception('Equipment ID is required');
    }
    
    try {
        // Soft delete - mark as deleted
        $query = "UPDATE equipment SET status = 'deleted', updated_at = NOW() WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $success = $stmt->execute([$id]);
        
        if ($success && $stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Equipment deleted successfully'
            ]);
        } else {
            throw new Exception('Equipment not found or already deleted');
        }
        
    } catch (Exception $e) {
        throw new Exception('Database error: ' . $e->getMessage());
    }
}

function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}
?>
    
    $stmt = $pdo->prepare("
        SELECT id, equipment_id, name, type, model, serial_number, 
               manufacturer, purchase_date, status, created_at
        FROM equipment 
        WHERE status != 'deleted'
        ORDER BY created_at DESC
    ");
    $stmt->execute();
    $equipment = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $equipment,
        'total' => count($equipment)
    ]);
}

function getEquipment() {
    global $pdo;
    
    $id = $_GET['id'] ?? 0;
    if (!$id) {
        throw new Exception('Equipment ID is required');
    }
    
    $stmt = $pdo->prepare("
        SELECT * FROM equipment 
        WHERE id = ? AND status != 'deleted'
    ");
    $stmt->execute([$id]);
    $equipment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$equipment) {
        throw new Exception('Equipment not found');
    }
    
    echo json_encode([
        'success' => true,
        'data' => $equipment
    ]);
}

function addEquipment() {
    global $pdo;
    
    // Validate required fields
    $required = ['name', 'type'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception(ucfirst(str_replace('_', ' ', $field)) . ' is required');
        }
    }
    
    // Sanitize inputs
    $name = trim($_POST['name']);
    $type = trim($_POST['type']);
    $model = trim($_POST['model']) ?: null;
    $serial_number = trim($_POST['serial_number']) ?: null;
    $manufacturer = trim($_POST['manufacturer']) ?: null;
    $purchase_date = $_POST['purchase_date'] ?: null;
    
    // Generate unique equipment ID
    $equipment_id = generateEquipmentId();
    
    // Check for duplicate serial number if provided
    if ($serial_number) {
        $stmt = $pdo->prepare("SELECT id FROM equipment WHERE serial_number = ? AND status != 'deleted'");
        $stmt->execute([$serial_number]);
        if ($stmt->fetch()) {
            throw new Exception('Serial number already exists');
        }
    }
    
    // Insert equipment
    $stmt = $pdo->prepare("
        INSERT INTO equipment (
            equipment_id, name, type, model, serial_number, 
            manufacturer, purchase_date, status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'active', NOW())
    ");
    
    $result = $stmt->execute([
        $equipment_id,
        $name,
        $type,
        $model,
        $serial_number,
        $manufacturer,
        $purchase_date
    ]);
    
    if (!$result) {
        throw new Exception('Failed to add equipment');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Equipment added successfully',
        'equipment_id' => $equipment_id,
        'id' => $pdo->lastInsertId()
    ]);
}

function updateEquipment() {
    global $pdo;
    
    $id = $_POST['id'] ?? 0;
    if (!$id) {
        throw new Exception('Equipment ID is required');
    }
    
    // Validate required fields
    $required = ['name', 'type'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception(ucfirst(str_replace('_', ' ', $field)) . ' is required');
        }
    }
    
    // Sanitize inputs
    $name = trim($_POST['name']);
    $type = trim($_POST['type']);
    $model = trim($_POST['model']) ?: null;
    $serial_number = trim($_POST['serial_number']) ?: null;
    $manufacturer = trim($_POST['manufacturer']) ?: null;
    $purchase_date = $_POST['purchase_date'] ?: null;
    
    // Check if equipment exists
    $stmt = $pdo->prepare("SELECT id FROM equipment WHERE id = ? AND status != 'deleted'");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        throw new Exception('Equipment not found');
    }
    
    // Check for duplicate serial number (excluding current equipment)
    if ($serial_number) {
        $stmt = $pdo->prepare("SELECT id FROM equipment WHERE serial_number = ? AND id != ? AND status != 'deleted'");
        $stmt->execute([$serial_number, $id]);
        if ($stmt->fetch()) {
            throw new Exception('Serial number already exists');
        }
    }
    
    // Update equipment
    $stmt = $pdo->prepare("
        UPDATE equipment SET 
            name = ?, type = ?, model = ?, serial_number = ?, 
            manufacturer = ?, purchase_date = ?, updated_at = NOW()
        WHERE id = ?
    ");
    
    $result = $stmt->execute([
        $name,
        $type,
        $model,
        $serial_number,
        $manufacturer,
        $purchase_date,
        $id
    ]);
    
    if (!$result) {
        throw new Exception('Failed to update equipment');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Equipment updated successfully'
    ]);
}

function deleteEquipment() {
    global $pdo;
    
    $id = $_POST['id'] ?? 0;
    if (!$id) {
        throw new Exception('Equipment ID is required');
    }
    
    // Check if equipment exists
    $stmt = $pdo->prepare("SELECT id FROM equipment WHERE id = ? AND status != 'deleted'");
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        throw new Exception('Equipment not found');
    }
    
    // Soft delete (update status instead of actual delete)
    $stmt = $pdo->prepare("UPDATE equipment SET status = 'deleted', updated_at = NOW() WHERE id = ?");
    $result = $stmt->execute([$id]);
    
    if (!$result) {
        throw new Exception('Failed to delete equipment');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Equipment deleted successfully'
    ]);
}

function generateEquipmentId() {
    global $pdo;
    
    // Try to get the last equipment ID number
    $stmt = $pdo->query("
        SELECT equipment_id FROM equipment 
        WHERE equipment_id LIKE 'EQ%' 
        ORDER BY CAST(SUBSTRING(equipment_id, 3) AS UNSIGNED) DESC 
        LIMIT 1
    ");
    
    $lastId = $stmt->fetchColumn();
    
    if ($lastId && preg_match('/EQ(\d+)/', $lastId, $matches)) {
        $nextNumber = intval($matches[1]) + 1;
    } else {
        $nextNumber = 1;
    }
    
    $newId = 'EQ' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    
    // Ensure uniqueness
    $attempts = 0;
    while ($attempts < 10) {
        $stmt = $pdo->prepare("SELECT id FROM equipment WHERE equipment_id = ?");
        $stmt->execute([$newId]);
        if (!$stmt->fetch()) {
            return $newId;
        }
        $nextNumber++;
        $newId = 'EQ' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        $attempts++;
    }
    
    // Fallback to random if sequential fails
    return 'EQ' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
}
?>
