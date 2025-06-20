<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

require_once '../config.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            handleGet($pdo);
            break;
        case 'POST':
            handlePost($pdo);
            break;
        case 'PUT':
            handlePut($pdo);
            break;
        case 'DELETE':
            handleDelete($pdo);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

function handleGet($pdo) {
    $action = $_GET['action'] ?? 'list';
    
    switch ($action) {
        case 'list':
            getEquipmentList($pdo);
            break;
        case 'single':
            getSingleEquipment($pdo);
            break;
        case 'maintenance':
            getMaintenanceHistory($pdo);
            break;
        case 'categories':
            getEquipmentCategories($pdo);
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
}

function handlePost($pdo) {
    $action = $_POST['action'] ?? 'add';
    
    switch ($action) {
        case 'add':
            addEquipment($pdo);
            break;
        case 'maintenance':
            addMaintenance($pdo);
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
}

function handlePut($pdo) {
    parse_str(file_get_contents("php://input"), $_PUT);
    $action = $_PUT['action'] ?? 'update';
    
    switch ($action) {
        case 'update':
            updateEquipment($pdo, $_PUT);
            break;
        case 'maintenance':
            updateMaintenance($pdo, $_PUT);
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
}

function handleDelete($pdo) {
    parse_str(file_get_contents("php://input"), $_DELETE);
    $action = $_DELETE['action'] ?? 'delete';
    
    switch ($action) {
        case 'delete':
            deleteEquipment($pdo, $_DELETE);
            break;
        case 'maintenance':
            deleteMaintenance($pdo, $_DELETE);
            break;
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
}

function getEquipmentList($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT 
                e.*,
                (SELECT COUNT(*) FROM equipment_maintenance m WHERE m.equipment_id = e.equipment_id) as maintenance_count
            FROM equipment e
            ORDER BY e.equipment_name
        ");
        $equipment = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $equipment]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function getSingleEquipment($pdo) {
    try {
        $id = $_GET['id'] ?? 0;
        
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Equipment ID is required']);
            return;
        }
        
        $stmt = $pdo->prepare("
            SELECT * FROM equipment 
            WHERE equipment_id = :id
        ");
        $stmt->execute(['id' => $id]);
        $equipment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$equipment) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Equipment not found']);
            return;
        }
        
        echo json_encode(['success' => true, 'data' => $equipment]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function getMaintenanceHistory($pdo) {
    try {
        $id = $_GET['id'] ?? 0;
        
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Equipment ID is required']);
            return;
        }
        
        $stmt = $pdo->prepare("
            SELECT * FROM equipment_maintenance 
            WHERE equipment_id = :id
            ORDER BY maintenance_date DESC
        ");
        $stmt->execute(['id' => $id]);
        $maintenance = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $maintenance]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function getEquipmentCategories($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT DISTINCT category FROM equipment ORDER BY category
        ");
        $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo json_encode(['success' => true, 'data' => $categories]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function addEquipment($pdo) {
    try {
        $name = $_POST['equipment_name'] ?? '';
        $model = $_POST['model'] ?? '';
        $serial = $_POST['serial_number'] ?? '';
        $category = $_POST['category'] ?? '';
        $purchase_date = $_POST['purchase_date'] ?? null;
        $warranty_expiry = $_POST['warranty_expiry'] ?? null;
        $status = $_POST['status'] ?? 'Working';
        $notes = $_POST['notes'] ?? '';
        
        // Validate inputs
        if (empty($name)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Equipment name is required']);
            return;
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO equipment (
                equipment_name, model, serial_number, category, 
                purchase_date, warranty_expiry, status, notes, created_at
            ) VALUES (
                :name, :model, :serial, :category, 
                :purchase_date, :warranty_expiry, :status, :notes, NOW()
            )
        ");
        
        $stmt->execute([
            'name' => $name,
            'model' => $model,
            'serial' => $serial,
            'category' => $category,
            'purchase_date' => $purchase_date,
            'warranty_expiry' => $warranty_expiry,
            'status' => $status,
            'notes' => $notes
        ]);
        
        $equipmentId = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Equipment added successfully',
            'data' => ['equipment_id' => $equipmentId]
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function updateEquipment($pdo, $data) {
    try {
        $id = $data['equipment_id'] ?? 0;
        $name = $data['equipment_name'] ?? '';
        $model = $data['model'] ?? '';
        $serial = $data['serial_number'] ?? '';
        $category = $data['category'] ?? '';
        $purchase_date = $data['purchase_date'] ?? null;
        $warranty_expiry = $data['warranty_expiry'] ?? null;
        $status = $data['status'] ?? 'Working';
        $notes = $data['notes'] ?? '';
        
        // Validate inputs
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Equipment ID is required']);
            return;
        }
        
        if (empty($name)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Equipment name is required']);
            return;
        }
        
        $stmt = $pdo->prepare("
            UPDATE equipment SET
                equipment_name = :name,
                model = :model,
                serial_number = :serial,
                category = :category,
                purchase_date = :purchase_date,
                warranty_expiry = :warranty_expiry,
                status = :status,
                notes = :notes,
                updated_at = NOW()
            WHERE equipment_id = :id
        ");
        
        $stmt->execute([
            'id' => $id,
            'name' => $name,
            'model' => $model,
            'serial' => $serial,
            'category' => $category,
            'purchase_date' => $purchase_date,
            'warranty_expiry' => $warranty_expiry,
            'status' => $status,
            'notes' => $notes
        ]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Equipment updated successfully'
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function deleteEquipment($pdo, $data) {
    try {
        $id = $data['equipment_id'] ?? 0;
        
        // Validate inputs
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Equipment ID is required']);
            return;
        }
        
        // First delete related maintenance records
        $stmt = $pdo->prepare("DELETE FROM equipment_maintenance WHERE equipment_id = :id");
        $stmt->execute(['id' => $id]);
        
        // Then delete the equipment
        $stmt = $pdo->prepare("DELETE FROM equipment WHERE equipment_id = :id");
        $stmt->execute(['id' => $id]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Equipment deleted successfully'
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function addMaintenance($pdo) {
    try {
        $equipment_id = $_POST['equipment_id'] ?? 0;
        $maintenance_type = $_POST['maintenance_type'] ?? '';
        $maintenance_date = $_POST['maintenance_date'] ?? date('Y-m-d');
        $performed_by = $_POST['performed_by'] ?? '';
        $cost = $_POST['cost'] ?? 0;
        $notes = $_POST['notes'] ?? '';
        
        // Validate inputs
        if (!$equipment_id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Equipment ID is required']);
            return;
        }
        
        if (empty($maintenance_type)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Maintenance type is required']);
            return;
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO equipment_maintenance (
                equipment_id, maintenance_type, maintenance_date, 
                performed_by, cost, notes, created_at
            ) VALUES (
                :equipment_id, :maintenance_type, :maintenance_date, 
                :performed_by, :cost, :notes, NOW()
            )
        ");
        
        $stmt->execute([
            'equipment_id' => $equipment_id,
            'maintenance_type' => $maintenance_type,
            'maintenance_date' => $maintenance_date,
            'performed_by' => $performed_by,
            'cost' => $cost,
            'notes' => $notes
        ]);
        
        $maintenanceId = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Maintenance record added successfully',
            'data' => ['maintenance_id' => $maintenanceId]
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function updateMaintenance($pdo, $data) {
    try {
        $maintenance_id = $data['maintenance_id'] ?? 0;
        $maintenance_type = $data['maintenance_type'] ?? '';
        $maintenance_date = $data['maintenance_date'] ?? date('Y-m-d');
        $performed_by = $data['performed_by'] ?? '';
        $cost = $data['cost'] ?? 0;
        $notes = $data['notes'] ?? '';
        
        // Validate inputs
        if (!$maintenance_id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Maintenance ID is required']);
            return;
        }
        
        if (empty($maintenance_type)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Maintenance type is required']);
            return;
        }
        
        $stmt = $pdo->prepare("
            UPDATE equipment_maintenance SET
                maintenance_type = :maintenance_type,
                maintenance_date = :maintenance_date,
                performed_by = :performed_by,
                cost = :cost,
                notes = :notes,
                updated_at = NOW()
            WHERE maintenance_id = :maintenance_id
        ");
        
        $stmt->execute([
            'maintenance_id' => $maintenance_id,
            'maintenance_type' => $maintenance_type,
            'maintenance_date' => $maintenance_date,
            'performed_by' => $performed_by,
            'cost' => $cost,
            'notes' => $notes
        ]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Maintenance record updated successfully'
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function deleteMaintenance($pdo, $data) {
    try {
        $maintenance_id = $data['maintenance_id'] ?? 0;
        
        // Validate inputs
        if (!$maintenance_id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Maintenance ID is required']);
            return;
        }
        
        $stmt = $pdo->prepare("DELETE FROM equipment_maintenance WHERE maintenance_id = :maintenance_id");
        $stmt->execute(['maintenance_id' => $maintenance_id]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Maintenance record deleted successfully'
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
