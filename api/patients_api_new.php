<?php
/**
 * Patients API
 * Handles patient-related AJAX requests
 */

require_once '../config.php';
header('Content-Type: application/json');

try {
    $action = $_REQUEST['action'] ?? '';
    
    switch ($action) {
        case 'list':
            // DataTables server-side processing
            echo json_encode(getPatientsList());
            break;
            
        case 'add_form':
            echo getPatientForm();
            break;
            
        case 'edit_form':
            $id = (int)($_REQUEST['id'] ?? 0);
            echo getPatientForm($id);
            break;
            
        case 'view':
            $id = (int)($_REQUEST['id'] ?? 0);
            echo getPatientView($id);
            break;
            
        case 'save':
            echo json_encode(savePatient());
            break;
            
        case 'delete':
            $id = (int)($_REQUEST['id'] ?? 0);
            echo json_encode(deletePatient($id));
            break;
            
        default:
            // Default action for DataTables
            echo json_encode(getPatientsList());
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Get patients list for DataTables
 */
function getPatientsList() {
    try {
        $conn = getDatabaseConnection();
        
        // DataTables parameters
        $draw = (int)($_POST['draw'] ?? 1);
        $start = (int)($_POST['start'] ?? 0);
        $length = (int)($_POST['length'] ?? 25);
        $search = $_POST['search']['value'] ?? '';
        
        // Base query
        $where = "1=1";
        $params = [];
        
        // Search
        if (!empty($search)) {
            $where .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ?)";
            $searchParam = "%{$search}%";
            $params = [$searchParam, $searchParam, $searchParam];
        }
        
        // Get total records
        $stmt = $conn->prepare("SELECT COUNT(*) FROM patients WHERE {$where}");
        $stmt->execute($params);
        $totalRecords = $stmt->fetchColumn();
        
        // Get data
        $sql = "SELECT * FROM patients WHERE {$where} ORDER BY id DESC LIMIT {$start}, {$length}";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $data
        ];
        
    } catch (Exception $e) {
        return [
            'draw' => 1,
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => [],
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Get patient form HTML
 */
function getPatientForm($id = 0) {
    $patient = null;
    $title = 'Add New Patient';
    $action = 'save';
    
    if ($id > 0) {
        $conn = getDatabaseConnection();
        $stmt = $conn->prepare("SELECT * FROM patients WHERE id = ?");
        $stmt->execute([$id]);
        $patient = $stmt->fetch(PDO::FETCH_ASSOC);
        $title = 'Edit Patient';
        $action = 'save';
    }
    
    ob_start();
    ?>
    <div class="modal-header">
        <h4 class="modal-title"><?= $title ?></h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <form id="patientForm" action="api/patients_api.php" method="POST" onsubmit="return savePatient()">
        <div class="modal-body">
            <input type="hidden" name="action" value="<?= $action ?>">
            <?php if ($patient): ?>
                <input type="hidden" name="id" value="<?= $patient['id'] ?>">
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?= htmlspecialchars($patient['name'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?= htmlspecialchars($patient['email'] ?? '') ?>">
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone" 
                               value="<?= htmlspecialchars($patient['phone'] ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="date_of_birth">Date of Birth</label>
                        <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" 
                               value="<?= $patient['date_of_birth'] ?? '' ?>">
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select class="form-control" id="gender" name="gender">
                            <option value="">Select Gender</option>
                            <option value="male" <?= ($patient['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                            <option value="female" <?= ($patient['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                            <option value="other" <?= ($patient['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save Patient</button>
        </div>
    </form>
    <?php
    return ob_get_clean();
}

/**
 * Get patient view HTML
 */
function getPatientView($id) {
    $conn = getDatabaseConnection();
    $stmt = $conn->prepare("SELECT * FROM patients WHERE id = ?");
    $stmt->execute([$id]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$patient) {
        return '<div class="modal-header"><h4>Patient Not Found</h4></div><div class="modal-body">Patient not found.</div>';
    }
    
    ob_start();
    ?>
    <div class="modal-header">
        <h4 class="modal-title">Patient Details</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-striped">
                    <tr>
                        <th width="150">Patient ID:</th>
                        <td><?= $patient['id'] ?></td>
                    </tr>
                    <tr>
                        <th>Name:</th>
                        <td><?= htmlspecialchars($patient['name']) ?></td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td><?= htmlspecialchars($patient['email'] ?: 'N/A') ?></td>
                    </tr>
                    <tr>
                        <th>Phone:</th>
                        <td><?= htmlspecialchars($patient['phone'] ?: 'N/A') ?></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-striped">
                    <tr>
                        <th width="150">Date of Birth:</th>
                        <td><?= $patient['date_of_birth'] ? date('F j, Y', strtotime($patient['date_of_birth'])) : 'N/A' ?></td>
                    </tr>
                    <tr>
                        <th>Gender:</th>
                        <td><?= ucfirst($patient['gender'] ?: 'N/A') ?></td>
                    </tr>
                    <tr>
                        <th>Created:</th>
                        <td><?= date('F j, Y g:i A', strtotime($patient['created_at'])) ?></td>
                    </tr>
                    <tr>
                        <th>Last Updated:</th>
                        <td><?= date('F j, Y g:i A', strtotime($patient['updated_at'])) ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick="editPatient(<?= $patient['id'] ?>)">Edit Patient</button>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Save patient (add or update)
 */
function savePatient() {
    try {
        $conn = getDatabaseConnection();
        
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $date_of_birth = $_POST['date_of_birth'] ?? null;
        $gender = $_POST['gender'] ?? null;
        
        // Validation
        if (empty($name)) {
            throw new Exception('Patient name is required');
        }
        
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Please enter a valid email address');
        }
        
        if ($id > 0) {
            // Update existing patient
            $sql = "UPDATE patients SET name = ?, email = ?, phone = ?, date_of_birth = ?, gender = ? WHERE id = ?";
            $params = [$name, $email, $phone, $date_of_birth, $gender, $id];
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            
            $message = 'Patient updated successfully';
        } else {
            // Add new patient
            $sql = "INSERT INTO patients (name, email, phone, date_of_birth, gender) VALUES (?, ?, ?, ?, ?)";
            $params = [$name, $email, $phone, $date_of_birth, $gender];
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            
            $message = 'Patient added successfully';
        }
        
        return [
            'success' => true,
            'message' => $message
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

/**
 * Delete patient
 */
function deletePatient($id) {
    try {
        $conn = getDatabaseConnection();
        
        // Check if patient has any test orders
        $stmt = $conn->prepare("SELECT COUNT(*) FROM test_orders WHERE patient_id = ?");
        $stmt->execute([$id]);
        $orderCount = $stmt->fetchColumn();
        
        if ($orderCount > 0) {
            throw new Exception('Cannot delete patient. Patient has existing test orders.');
        }
        
        // Delete patient
        $stmt = $conn->prepare("DELETE FROM patients WHERE id = ?");
        $stmt->execute([$id]);
        
        return [
            'success' => true,
            'message' => 'Patient deleted successfully'
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}
?>
