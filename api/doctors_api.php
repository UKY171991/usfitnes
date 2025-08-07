<?php
/**
 * Doctors API - AdminLTE3 AJAX Handler
 */

require_once '../config.php';
header('Content-Type: application/json');

try {
    $action = $_REQUEST['action'] ?? '';
    
    switch ($action) {
        case 'list':
            echo json_encode(getDoctorsList());
            break;
            
        case 'add_form':
            echo getDoctorForm();
            break;
            
        case 'edit_form':
            $id = (int)($_REQUEST['id'] ?? 0);
            echo getDoctorForm($id);
            break;
            
        case 'view':
            $id = (int)($_REQUEST['id'] ?? 0);
            echo getDoctorView($id);
            break;
            
        case 'save':
            echo json_encode(saveDoctor());
            break;
            
        case 'delete':
            $id = (int)($_REQUEST['id'] ?? 0);
            echo json_encode(deleteDoctor($id));
            break;
            
        default:
            echo json_encode(getDoctorsList());
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function getDoctorsList() {
    try {
        $conn = getDatabaseConnection();
        
        $draw = (int)($_POST['draw'] ?? 1);
        $start = (int)($_POST['start'] ?? 0);
        $length = (int)($_POST['length'] ?? 25);
        $search = $_POST['search']['value'] ?? '';
        
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (name LIKE ? OR email LIKE ? OR specialization LIKE ?)";
            $searchParam = "%{$search}%";
            $params = [$searchParam, $searchParam, $searchParam];
        }
        
        $stmt = $conn->prepare("SELECT COUNT(*) FROM doctors WHERE {$where}");
        $stmt->execute($params);
        $totalRecords = $stmt->fetchColumn();
        
        $sql = "SELECT id, name, email, phone, specialization, license_number, created_at FROM doctors WHERE {$where} ORDER BY id DESC LIMIT {$start}, {$length}";
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
            'data' => []
        ];
    }
}

function getDoctorForm($id = 0) {
    $doctor = null;
    $title = 'Add New Doctor';
    
    if ($id > 0) {
        $conn = getDatabaseConnection();
        $stmt = $conn->prepare("SELECT * FROM doctors WHERE id = ?");
        $stmt->execute([$id]);
        $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
        $title = 'Edit Doctor';
    }
    
    ob_start();
    ?>
    <div class="modal-header">
        <h4 class="modal-title"><?= $title ?></h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    <form id="doctorForm" action="api/doctors_api.php" method="POST" onsubmit="return saveDoctor()">
        <div class="modal-body">
            <input type="hidden" name="action" value="save">
            <?php if ($doctor): ?>
                <input type="hidden" name="id" value="<?= $doctor['id'] ?>">
            <?php endif; ?>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Doctor Name *</label>
                        <input type="text" class="form-control" name="name" 
                               value="<?= htmlspecialchars($doctor['name'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" class="form-control" name="email" 
                               value="<?= htmlspecialchars($doctor['email'] ?? '') ?>">
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" class="form-control" name="phone" 
                               value="<?= htmlspecialchars($doctor['phone'] ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Specialization</label>
                        <input type="text" class="form-control" name="specialization" 
                               value="<?= htmlspecialchars($doctor['specialization'] ?? '') ?>">
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>License Number</label>
                        <input type="text" class="form-control" name="license_number" 
                               value="<?= htmlspecialchars($doctor['license_number'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save Doctor</button>
        </div>
    </form>
    <?php
    return ob_get_clean();
}

function getDoctorView($id) {
    $conn = getDatabaseConnection();
    $stmt = $conn->prepare("SELECT * FROM doctors WHERE id = ?");
    $stmt->execute([$id]);
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$doctor) {
        return '<div class="modal-body">Doctor not found.</div>';
    }
    
    ob_start();
    ?>
    <div class="modal-header">
        <h4 class="modal-title">Doctor Details</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    <div class="modal-body">
        <table class="table table-striped">
            <tr><th width="150">ID:</th><td><?= $doctor['id'] ?></td></tr>
            <tr><th>Name:</th><td><?= htmlspecialchars($doctor['name']) ?></td></tr>
            <tr><th>Email:</th><td><?= htmlspecialchars($doctor['email'] ?: 'N/A') ?></td></tr>
            <tr><th>Phone:</th><td><?= htmlspecialchars($doctor['phone'] ?: 'N/A') ?></td></tr>
            <tr><th>Specialization:</th><td><?= htmlspecialchars($doctor['specialization'] ?: 'N/A') ?></td></tr>
            <tr><th>License:</th><td><?= htmlspecialchars($doctor['license_number'] ?: 'N/A') ?></td></tr>
            <tr><th>Created:</th><td><?= date('M j, Y g:i A', strtotime($doctor['created_at'])) ?></td></tr>
        </table>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick="editDoctor(<?= $doctor['id'] ?>)">Edit</button>
    </div>
    <?php
    return ob_get_clean();
}

function saveDoctor() {
    try {
        $conn = getDatabaseConnection();
        
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $specialization = trim($_POST['specialization'] ?? '');
        $license_number = trim($_POST['license_number'] ?? '');
        
        if (empty($name)) {
            throw new Exception('Doctor name is required');
        }
        
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Please enter a valid email address');
        }
        
        if ($id > 0) {
            $sql = "UPDATE doctors SET name = ?, email = ?, phone = ?, specialization = ?, license_number = ? WHERE id = ?";
            $params = [$name, $email, $phone, $specialization, $license_number, $id];
            $message = 'Doctor updated successfully';
        } else {
            $sql = "INSERT INTO doctors (name, email, phone, specialization, license_number) VALUES (?, ?, ?, ?, ?)";
            $params = [$name, $email, $phone, $specialization, $license_number];
            $message = 'Doctor added successfully';
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        
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

function deleteDoctor($id) {
    try {
        $conn = getDatabaseConnection();
        
        $stmt = $conn->prepare("SELECT COUNT(*) FROM test_orders WHERE doctor_id = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception('Cannot delete doctor with existing test orders');
        }
        
        $stmt = $conn->prepare("DELETE FROM doctors WHERE id = ?");
        $stmt->execute([$id]);
        
        return [
            'success' => true,
            'message' => 'Doctor deleted successfully'
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}
?>
