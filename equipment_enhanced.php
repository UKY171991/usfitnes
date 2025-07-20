<?php
// Set page title
$page_title = 'Equipment Management';

// Include header and sidebar
include 'includes/header.php';
include 'includes/sidebar.php';

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add':
            $equipment_name = trim($_POST['equipment_name'] ?? '');
            $equipment_code = trim($_POST['equipment_code'] ?? '');
            $manufacturer = trim($_POST['manufacturer'] ?? '') ?: null;
            $model = trim($_POST['model'] ?? '') ?: null;
            $serial_number = trim($_POST['serial_number'] ?? '') ?: null;
            $purchase_date = $_POST['purchase_date'] ?: null;
            $warranty_expiry = $_POST['warranty_expiry'] ?: null;
            $status = $_POST['status'] ?? 'active';
            $location = trim($_POST['location'] ?? '') ?: null;
            $description = trim($_POST['description'] ?? '') ?: null;
            $maintenance_schedule = $_POST['maintenance_schedule'] ?? 'monthly';
            $last_maintenance = $_POST['last_maintenance'] ?: null;
            $next_maintenance = $_POST['next_maintenance'] ?: null;
            
            if (empty($equipment_name) || empty($equipment_code)) {
                $response = ['success' => false, 'message' => 'Equipment name and code are required'];
                break;
            }
            
            try {
                $stmt = $pdo->prepare("INSERT INTO equipment (equipment_name, equipment_code, manufacturer, model, serial_number, purchase_date, warranty_expiry, status, location, description, maintenance_schedule, last_maintenance, next_maintenance) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                if ($stmt->execute([$equipment_name, $equipment_code, $manufacturer, $model, $serial_number, $purchase_date, $warranty_expiry, $status, $location, $description, $maintenance_schedule, $last_maintenance, $next_maintenance])) {
                    $response = ['success' => true, 'message' => 'Equipment added successfully'];
                } else {
                    $response = ['success' => false, 'message' => 'Failed to add equipment'];
                }
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
            }
            break;
            
        case 'edit':
            $id = $_POST['id'] ?? '';
            $equipment_name = trim($_POST['equipment_name'] ?? '');
            $equipment_code = trim($_POST['equipment_code'] ?? '');
            $manufacturer = trim($_POST['manufacturer'] ?? '') ?: null;
            $model = trim($_POST['model'] ?? '') ?: null;
            $serial_number = trim($_POST['serial_number'] ?? '') ?: null;
            $purchase_date = $_POST['purchase_date'] ?: null;
            $warranty_expiry = $_POST['warranty_expiry'] ?: null;
            $status = $_POST['status'] ?? 'active';
            $location = trim($_POST['location'] ?? '') ?: null;
            $description = trim($_POST['description'] ?? '') ?: null;
            $maintenance_schedule = $_POST['maintenance_schedule'] ?? 'monthly';
            $last_maintenance = $_POST['last_maintenance'] ?: null;
            $next_maintenance = $_POST['next_maintenance'] ?: null;
            
            if (empty($id) || empty($equipment_name) || empty($equipment_code)) {
                $response = ['success' => false, 'message' => 'Equipment ID, name and code are required'];
                break;
            }
            
            try {
                $stmt = $pdo->prepare("UPDATE equipment SET equipment_name = ?, equipment_code = ?, manufacturer = ?, model = ?, serial_number = ?, purchase_date = ?, warranty_expiry = ?, status = ?, location = ?, description = ?, maintenance_schedule = ?, last_maintenance = ?, next_maintenance = ?, updated_at = NOW() WHERE id = ?");
                
                if ($stmt->execute([$equipment_name, $equipment_code, $manufacturer, $model, $serial_number, $purchase_date, $warranty_expiry, $status, $location, $description, $maintenance_schedule, $last_maintenance, $next_maintenance, $id])) {
                    $response = ['success' => true, 'message' => 'Equipment updated successfully'];
                } else {
                    $response = ['success' => false, 'message' => 'Failed to update equipment'];
                }
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
            }
            break;
            
        case 'delete':
            $id = $_POST['id'] ?? '';
            
            if (empty($id)) {
                $response = ['success' => false, 'message' => 'Equipment ID is required'];
                break;
            }
            
            try {
                $stmt = $pdo->prepare("DELETE FROM equipment WHERE id = ?");
                
                if ($stmt->execute([$id])) {
                    $response = ['success' => true, 'message' => 'Equipment deleted successfully'];
                } else {
                    $response = ['success' => false, 'message' => 'Failed to delete equipment'];
                }
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
            }
            break;
            
        case 'get':
            $id = $_POST['id'] ?? '';
            
            if (empty($id)) {
                $response = ['success' => false, 'message' => 'Equipment ID is required'];
                break;
            }
            
            try {
                $stmt = $pdo->prepare("SELECT * FROM equipment WHERE id = ?");
                $stmt->execute([$id]);
                $equipment = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($equipment) {
                    $response = ['success' => true, 'data' => $equipment];
                } else {
                    $response = ['success' => false, 'message' => 'Equipment not found'];
                }
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
            }
            break;
            
        case 'datatable':
            try {
                $draw = intval($_POST['draw']);
                $start = intval($_POST['start']);
                $length = intval($_POST['length']);
                $search = $_POST['search']['value'];
                
                // Total records count
                $totalRecords = $pdo->query("SELECT COUNT(*) FROM equipment")->fetchColumn();
                
                // Search query
                $searchQuery = "";
                $params = [];
                if (!empty($search)) {
                    $searchQuery = " WHERE equipment_name LIKE ? OR equipment_code LIKE ? OR manufacturer LIKE ? OR model LIKE ?";
                    $searchTerm = "%$search%";
                    $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
                }
                
                // Filtered records count
                $filteredRecords = $pdo->prepare("SELECT COUNT(*) FROM equipment" . $searchQuery);
                $filteredRecords->execute($params);
                $filteredRecords = $filteredRecords->fetchColumn();
                
                // Get records
                $sql = "SELECT id, equipment_name, equipment_code, manufacturer, model, status, location, last_maintenance, next_maintenance, warranty_expiry FROM equipment" . $searchQuery . " ORDER BY created_at DESC LIMIT $start, $length";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $equipment = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $data = [];
                foreach ($equipment as $item) {
                    $statusBadge = '';
                    switch ($item['status']) {
                        case 'active':
                            $statusBadge = '<span class="badge badge-success">Active</span>';
                            break;
                        case 'maintenance':
                            $statusBadge = '<span class="badge badge-warning">Maintenance</span>';
                            break;
                        case 'inactive':
                            $statusBadge = '<span class="badge badge-secondary">Inactive</span>';
                            break;
                        case 'broken':
                            $statusBadge = '<span class="badge badge-danger">Broken</span>';
                            break;
                        default:
                            $statusBadge = '<span class="badge badge-info">' . ucfirst($item['status']) . '</span>';
                    }
                    
                    // Check maintenance status
                    $maintenanceStatus = '';
                    if ($item['next_maintenance']) {
                        $nextDate = new DateTime($item['next_maintenance']);
                        $today = new DateTime();
                        $diff = $today->diff($nextDate)->days;
                        
                        if ($nextDate < $today) {
                            $maintenanceStatus = '<span class="badge badge-danger">Overdue</span>';
                        } elseif ($diff <= 7) {
                            $maintenanceStatus = '<span class="badge badge-warning">Due Soon</span>';
                        } else {
                            $maintenanceStatus = '<span class="badge badge-success">Up to Date</span>';
                        }
                    }
                    
                    // Check warranty status
                    $warrantyStatus = '';
                    if ($item['warranty_expiry']) {
                        $expiryDate = new DateTime($item['warranty_expiry']);
                        $today = new DateTime();
                        
                        if ($expiryDate < $today) {
                            $warrantyStatus = '<span class="badge badge-danger">Expired</span>';
                        } else {
                            $diff = $today->diff($expiryDate)->days;
                            if ($diff <= 30) {
                                $warrantyStatus = '<span class="badge badge-warning">Expiring Soon</span>';
                            } else {
                                $warrantyStatus = '<span class="badge badge-success">Valid</span>';
                            }
                        }
                    }
                    
                    $actions = '
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-info" onclick="viewEquipment(' . $item['id'] . ')" title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-primary" onclick="editEquipment(' . $item['id'] . ')" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-warning" onclick="scheduleMaintenan ce(' . $item['id'] . ')" title="Maintenance">
                                <i class="fas fa-tools"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteEquipment(' . $item['id'] . ')" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    ';
                    
                    $data[] = [
                        'equipment' => '<strong>' . htmlspecialchars($item['equipment_name']) . '</strong><br><small class="text-muted">' . htmlspecialchars($item['equipment_code']) . '</small>',
                        'details' => ($item['manufacturer'] ? htmlspecialchars($item['manufacturer']) : '<span class="text-muted">-</span>') . '<br>' . ($item['model'] ? '<small class="text-muted">' . htmlspecialchars($item['model']) . '</small>' : ''),
                        'status' => $statusBadge,
                        'location' => $item['location'] ? htmlspecialchars($item['location']) : '<span class="text-muted">Not specified</span>',
                        'maintenance' => $maintenanceStatus . ($item['next_maintenance'] ? '<br><small class="text-muted">' . date('Y-m-d', strtotime($item['next_maintenance'])) . '</small>' : ''),
                        'warranty' => $warrantyStatus . ($item['warranty_expiry'] ? '<br><small class="text-muted">' . date('Y-m-d', strtotime($item['warranty_expiry'])) . '</small>' : ''),
                        'actions' => $actions
                    ];
                }
                
                $response = [
                    'draw' => $draw,
                    'recordsTotal' => $totalRecords,
                    'recordsFiltered' => $filteredRecords,
                    'data' => $data
                ];
            } catch (Exception $e) {
                $response = ['error' => 'Database error: ' . $e->getMessage()];
            }
            break;
            
        default:
            $response = ['success' => false, 'message' => 'Invalid action'];
            break;
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Get summary data
try {
    $totalEquipment = $pdo->query("SELECT COUNT(*) FROM equipment")->fetchColumn();
    $activeEquipment = $pdo->query("SELECT COUNT(*) FROM equipment WHERE status = 'active'")->fetchColumn();
    $maintenanceEquipment = $pdo->query("SELECT COUNT(*) FROM equipment WHERE status = 'maintenance'")->fetchColumn();
    $brokenEquipment = $pdo->query("SELECT COUNT(*) FROM equipment WHERE status = 'broken'")->fetchColumn();
} catch (Exception $e) {
    $totalEquipment = $activeEquipment = $maintenanceEquipment = $brokenEquipment = 0;
}
?>

<style>
.content-wrapper {
    background-color: #f4f6f9;
}
.card {
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    border: none;
}
.btn-group .btn {
    margin-right: 2px;
}
.btn-group .btn:last-child {
    margin-right: 0;
}
.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}
.modal-header {
    background-color: #007bff;
    color: white;
}
.modal-header .close {
    color: white;
    opacity: 0.8;
}
.modal-header .close:hover {
    opacity: 1;
}
.form-group label {
    font-weight: 600;
    color: #495057;
}
.required {
    color: #dc3545;
}
.info-box {
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    border-radius: 0.25rem;
    background: #fff;
    display: flex;
    margin-bottom: 1rem;
    min-height: 80px;
    padding: .5rem;
    position: relative;
    width: 100%;
}
.info-box .info-box-icon {
    border-radius: 0.25rem;
    align-items: center;
    display: flex;
    font-size: 1.875rem;
    justify-content: center;
    text-align: center;
    width: 70px;
}
.info-box .info-box-content {
    display: flex;
    flex-direction: column;
    justify-content: center;
    line-height: 1.8;
    flex: 1;
    padding: 0 10px;
}
.info-box .info-box-number {
    display: block;
    margin-top: -.25rem;
    font-size: 1.125rem;
    font-weight: 700;
}
.info-box .info-box-text {
    display: block;
    font-size: .875rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    text-transform: uppercase;
}
</style>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <i class="fas fa-cogs mr-2"></i>Equipment Management
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                        <li class="breadcrumb-item active">Equipment</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Summary Cards -->
            <div class="row mb-3">
                <div class="col-lg-3 col-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-info">
                            <i class="fas fa-cogs"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Equipment</span>
                            <span class="info-box-number"><?php echo number_format($totalEquipment); ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-success">
                            <i class="fas fa-check-circle"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Active</span>
                            <span class="info-box-number"><?php echo number_format($activeEquipment); ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning">
                            <i class="fas fa-tools"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Maintenance</span>
                            <span class="info-box-number"><?php echo number_format($maintenanceEquipment); ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Broken</span>
                            <span class="info-box-number"><?php echo number_format($brokenEquipment); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="row mb-3">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body py-2">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-search mr-2 text-muted"></i>
                                <span class="text-muted mr-3">Quick Actions:</span>
                                <button class="btn btn-success btn-sm mr-2" id="addEquipmentBtn">
                                    <i class="fas fa-plus mr-1"></i>Add Equipment
                                </button>
                                <button class="btn btn-info btn-sm mr-2" id="refreshBtn">
                                    <i class="fas fa-sync-alt mr-1"></i>Refresh
                                </button>
                                <button class="btn btn-warning btn-sm mr-2" id="maintenanceBtn">
                                    <i class="fas fa-tools mr-1"></i>Maintenance Schedule
                                </button>
                                <button class="btn btn-secondary btn-sm" id="exportBtn">
                                    <i class="fas fa-download mr-1"></i>Export
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body py-2">
                            <div class="input-group">
                                <input type="text" class="form-control form-control-sm" id="globalSearch" placeholder="Search equipment...">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary btn-sm" type="button">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Equipment Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-list mr-2"></i>Equipment Inventory
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="equipmentTable" class="table table-striped table-hover">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Equipment</th>
                                            <th>Manufacturer/Model</th>
                                            <th>Status</th>
                                            <th>Location</th>
                                            <th>Maintenance</th>
                                            <th>Warranty</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Data will be loaded via AJAX -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Add Equipment Modal -->
<div class="modal fade" id="addEquipmentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus mr-2"></i>Add New Equipment
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addEquipmentForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="equipment_name">Equipment Name <span class="required">*</span></label>
                                <input type="text" class="form-control" id="equipment_name" name="equipment_name" required>
                                <small class="form-text text-muted">Full name of the equipment</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="equipment_code">Equipment Code <span class="required">*</span></label>
                                <input type="text" class="form-control" id="equipment_code" name="equipment_code" required>
                                <small class="form-text text-muted">Unique identifier code</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="manufacturer">Manufacturer</label>
                                <input type="text" class="form-control" id="manufacturer" name="manufacturer">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="model">Model</label>
                                <input type="text" class="form-control" id="model" name="model">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="serial_number">Serial Number</label>
                                <input type="text" class="form-control" id="serial_number" name="serial_number">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="location">Location</label>
                                <input type="text" class="form-control" id="location" name="location" placeholder="e.g., Lab Room 1, Basement">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="maintenance">Under Maintenance</option>
                                    <option value="broken">Broken</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="maintenance_schedule">Maintenance Schedule</label>
                                <select class="form-control" id="maintenance_schedule" name="maintenance_schedule">
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly" selected>Monthly</option>
                                    <option value="quarterly">Quarterly</option>
                                    <option value="yearly">Yearly</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="purchase_date">Purchase Date</label>
                                <input type="date" class="form-control" id="purchase_date" name="purchase_date">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="warranty_expiry">Warranty Expiry</label>
                                <input type="date" class="form-control" id="warranty_expiry" name="warranty_expiry">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="next_maintenance">Next Maintenance</label>
                                <input type="date" class="form-control" id="next_maintenance" name="next_maintenance">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Equipment description and specifications"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save mr-1"></i>Add Equipment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Equipment Modal -->
<div class="modal fade" id="editEquipmentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title">
                    <i class="fas fa-edit mr-2"></i>Edit Equipment
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editEquipmentForm">
                <input type="hidden" id="edit_id" name="id">
                <div class="modal-body">
                    <!-- Same form fields as add modal but with edit_ prefix -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_equipment_name">Equipment Name <span class="required">*</span></label>
                                <input type="text" class="form-control" id="edit_equipment_name" name="equipment_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_equipment_code">Equipment Code <span class="required">*</span></label>
                                <input type="text" class="form-control" id="edit_equipment_code" name="equipment_code" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_manufacturer">Manufacturer</label>
                                <input type="text" class="form-control" id="edit_manufacturer" name="manufacturer">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_model">Model</label>
                                <input type="text" class="form-control" id="edit_model" name="model">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_serial_number">Serial Number</label>
                                <input type="text" class="form-control" id="edit_serial_number" name="serial_number">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_location">Location</label>
                                <input type="text" class="form-control" id="edit_location" name="location">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_status">Status</label>
                                <select class="form-control" id="edit_status" name="status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="maintenance">Under Maintenance</option>
                                    <option value="broken">Broken</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_maintenance_schedule">Maintenance Schedule</label>
                                <select class="form-control" id="edit_maintenance_schedule" name="maintenance_schedule">
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="quarterly">Quarterly</option>
                                    <option value="yearly">Yearly</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_purchase_date">Purchase Date</label>
                                <input type="date" class="form-control" id="edit_purchase_date" name="purchase_date">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_warranty_expiry">Warranty Expiry</label>
                                <input type="date" class="form-control" id="edit_warranty_expiry" name="warranty_expiry">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_next_maintenance">Next Maintenance</label>
                                <input type="date" class="form-control" id="edit_next_maintenance" name="next_maintenance">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_description">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>Update Equipment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Equipment Modal -->
<div class="modal fade" id="viewEquipmentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title">
                    <i class="fas fa-eye mr-2"></i>Equipment Details
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="equipmentDetailsContent">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
$(document).ready(function() {
    // Configure Toastr
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };
    
    // Initialize DataTable with server-side processing
    const table = $('#equipmentTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "equipment.php",
            "type": "POST",
            "data": function(d) {
                d.action = 'datatable';
            }
        },
        "columns": [
            { "data": "equipment", "width": "20%" },
            { "data": "details", "width": "18%" },
            { "data": "status", "width": "10%" },
            { "data": "location", "width": "15%" },
            { "data": "maintenance", "width": "15%" },
            { "data": "warranty", "width": "12%" },
            { "data": "actions", "width": "10%", "orderable": false, "searchable": false }
        ],
        "order": [[0, "asc"]],
        "pageLength": 25,
        "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
        "responsive": true,
        "language": {
            "processing": "<i class='fas fa-spinner fa-spin'></i> Loading equipment...",
            "emptyTable": "No equipment found in the system",
            "zeroRecords": "No matching equipment found"
        }
    });
    
    // Global search
    $('#globalSearch').on('keyup', function() {
        table.search(this.value).draw();
    });
    
    // Button event handlers
    $('#addEquipmentBtn').click(function() {
        $('#addEquipmentModal').modal('show');
    });
    
    $('#refreshBtn').click(function() {
        table.ajax.reload(null, false);
        toastr.info('Table refreshed');
    });
    
    // Add Equipment Form Submission
    $('#addEquipmentForm').submit(function(e) {
        e.preventDefault();
        
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.html('<i class="fas fa-spinner fa-spin mr-1"></i>Adding...').prop('disabled', true);
        
        $.ajax({
            url: 'equipment.php',
            type: 'POST',
            data: $(this).serialize() + '&action=add',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#addEquipmentModal').modal('hide');
                    $('#addEquipmentForm')[0].reset();
                    table.ajax.reload(null, false);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('An error occurred while adding the equipment');
            },
            complete: function() {
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });
    
    // Edit Equipment Form Submission
    $('#editEquipmentForm').submit(function(e) {
        e.preventDefault();
        
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.html('<i class="fas fa-spinner fa-spin mr-1"></i>Updating...').prop('disabled', true);
        
        $.ajax({
            url: 'equipment.php',
            type: 'POST',
            data: $(this).serialize() + '&action=edit',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#editEquipmentModal').modal('hide');
                    table.ajax.reload(null, false);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('An error occurred while updating the equipment');
            },
            complete: function() {
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });
    
    // Clear forms when modals are hidden
    $('#addEquipmentModal').on('hidden.bs.modal', function() {
        $('#addEquipmentForm')[0].reset();
    });
    
    $('#editEquipmentModal').on('hidden.bs.modal', function() {
        $('#editEquipmentForm')[0].reset();
    });
});

function viewEquipment(id) {
    $.ajax({
        url: 'equipment.php',
        type: 'POST',
        data: { action: 'get', id: id },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const equipment = response.data;
                
                let statusBadge = '';
                switch (equipment.status) {
                    case 'active':
                        statusBadge = '<span class="badge badge-success">Active</span>';
                        break;
                    case 'maintenance':
                        statusBadge = '<span class="badge badge-warning">Under Maintenance</span>';
                        break;
                    case 'inactive':
                        statusBadge = '<span class="badge badge-secondary">Inactive</span>';
                        break;
                    case 'broken':
                        statusBadge = '<span class="badge badge-danger">Broken</span>';
                        break;
                }
                
                const content = `
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-cogs mr-2"></i>Equipment Information</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless mb-0">
                                        <tr>
                                            <td class="font-weight-bold" style="width: 40%;">Equipment Name:</td>
                                            <td>${equipment.equipment_name}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">Equipment Code:</td>
                                            <td><span class="badge badge-info">${equipment.equipment_code}</span></td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">Manufacturer:</td>
                                            <td>${equipment.manufacturer || '<span class="text-muted">Not specified</span>'}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">Model:</td>
                                            <td>${equipment.model || '<span class="text-muted">Not specified</span>'}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">Serial Number:</td>
                                            <td>${equipment.serial_number || '<span class="text-muted">Not specified</span>'}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">Status:</td>
                                            <td>${statusBadge}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">Location:</td>
                                            <td>${equipment.location || '<span class="text-muted">Not specified</span>'}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-calendar-alt mr-2"></i>Dates & Maintenance</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless mb-0">
                                        <tr>
                                            <td class="font-weight-bold" style="width: 40%;">Purchase Date:</td>
                                            <td>${equipment.purchase_date ? new Date(equipment.purchase_date).toLocaleDateString() : '<span class="text-muted">Not specified</span>'}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">Warranty Expiry:</td>
                                            <td>${equipment.warranty_expiry ? new Date(equipment.warranty_expiry).toLocaleDateString() : '<span class="text-muted">Not specified</span>'}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">Maintenance Schedule:</td>
                                            <td><span class="badge badge-primary">${equipment.maintenance_schedule ? equipment.maintenance_schedule.charAt(0).toUpperCase() + equipment.maintenance_schedule.slice(1) : 'Monthly'}</span></td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">Last Maintenance:</td>
                                            <td>${equipment.last_maintenance ? new Date(equipment.last_maintenance).toLocaleDateString() : '<span class="text-muted">Never</span>'}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">Next Maintenance:</td>
                                            <td>${equipment.next_maintenance ? new Date(equipment.next_maintenance).toLocaleDateString() : '<span class="text-muted">Not scheduled</span>'}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">Created:</td>
                                            <td>${new Date(equipment.created_at).toLocaleString()}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    ${equipment.description ? `
                    <div class="card mt-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-info-circle mr-2"></i>Description</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">${equipment.description}</p>
                        </div>
                    </div>
                    ` : ''}
                `;
                
                $('#equipmentDetailsContent').html(content);
                $('#viewEquipmentModal').modal('show');
            } else {
                toastr.error(response.message);
            }
        },
        error: function() {
            toastr.error('An error occurred while loading equipment details');
        }
    });
}

function editEquipment(id) {
    $.ajax({
        url: 'equipment.php',
        type: 'POST',
        data: { action: 'get', id: id },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const equipment = response.data;
                $('#edit_id').val(equipment.id);
                $('#edit_equipment_name').val(equipment.equipment_name);
                $('#edit_equipment_code').val(equipment.equipment_code);
                $('#edit_manufacturer').val(equipment.manufacturer || '');
                $('#edit_model').val(equipment.model || '');
                $('#edit_serial_number').val(equipment.serial_number || '');
                $('#edit_location').val(equipment.location || '');
                $('#edit_status').val(equipment.status);
                $('#edit_maintenance_schedule').val(equipment.maintenance_schedule || 'monthly');
                $('#edit_purchase_date').val(equipment.purchase_date || '');
                $('#edit_warranty_expiry').val(equipment.warranty_expiry || '');
                $('#edit_next_maintenance').val(equipment.next_maintenance || '');
                $('#edit_description').val(equipment.description || '');
                $('#editEquipmentModal').modal('show');
            } else {
                toastr.error(response.message);
            }
        },
        error: function() {
            toastr.error('An error occurred while loading equipment details');
        }
    });
}

function deleteEquipment(id) {
    if (confirm('Are you sure you want to delete this equipment?\n\nThis action cannot be undone and will permanently remove the equipment data.')) {
        $.ajax({
            url: 'equipment.php',
            type: 'POST',
            data: { action: 'delete', id: id },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#equipmentTable').DataTable().ajax.reload(null, false);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('An error occurred while deleting the equipment');
            }
        });
    }
}

function scheduleMaintenance(id) {
    // This could open a maintenance scheduling modal
    toastr.info('Maintenance scheduling feature coming soon');
}
</script>
