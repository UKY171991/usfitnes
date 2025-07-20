<?php
// Set page title
$page_title = 'Patients Management';

// Include header and sidebar
include 'includes/header.php';
include 'includes/sidebar.php';

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
    $action = sanitizeInput($_POST['action']);
    
    try {
        switch ($action) {
            case 'add':
                $required_fields = ['first_name', 'last_name', 'phone'];
                $errors = validateInput($_POST, $required_fields);
                
                if (!empty($errors)) {
                    jsonResponse(false, implode(', ', $errors));
                }
                
                // Generate unique patient ID
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM patients");
                $count = $stmt->fetch()['count'] + 1;
                $patient_id = 'PAT' . str_pad($count, 4, '0', STR_PAD_LEFT);
                
                // Ensure uniqueness
                while (true) {
                    $checkStmt = $pdo->prepare("SELECT id FROM patients WHERE patient_id = ?");
                    $checkStmt->execute([$patient_id]);
                    if (!$checkStmt->fetch()) break;
                    $count++;
                    $patient_id = 'PAT' . str_pad($count, 4, '0', STR_PAD_LEFT);
                }
                
                $first_name = sanitizeInput($_POST['first_name']);
                $last_name = sanitizeInput($_POST['last_name']);
                $date_of_birth = sanitizeInput($_POST['date_of_birth']) ?: null;
                $gender = sanitizeInput($_POST['gender']) ?: null;
                $phone = sanitizeInput($_POST['phone']);
                $email = sanitizeInput($_POST['email']) ?: null;
                $address = sanitizeInput($_POST['address']) ?: null;
                $emergency_contact = sanitizeInput($_POST['emergency_contact']) ?: null;
                $emergency_phone = sanitizeInput($_POST['emergency_phone']) ?: null;
                $blood_group = sanitizeInput($_POST['blood_group']) ?: null;
                $medical_history = sanitizeInput($_POST['medical_history']) ?: null;
                $allergies = sanitizeInput($_POST['allergies']) ?: null;
                
                // Validate email if provided
                if ($email && !validateEmail($email)) {
                    jsonResponse(false, 'Please enter a valid email address');
                }
                
                // Validate phone
                if (!validatePhone($phone)) {
                    jsonResponse(false, 'Please enter a valid phone number');
                }
                
                $stmt = $pdo->prepare("
                    INSERT INTO patients (
                        patient_id, first_name, last_name, date_of_birth, gender, 
                        phone, email, address, emergency_contact, emergency_phone, 
                        blood_group, medical_history, allergies
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                if ($stmt->execute([
                    $patient_id, $first_name, $last_name, $date_of_birth, $gender,
                    $phone, $email, $address, $emergency_contact, $emergency_phone,
                    $blood_group, $medical_history, $allergies
                ])) {
                    logActivity($_SESSION['user_id'] ?? 0, 'Patient Added', "Added patient: $first_name $last_name ($patient_id)");
                    jsonResponse(true, 'Patient added successfully!', [
                        'patient_id' => $patient_id,
                        'name' => "$first_name $last_name"
                    ], [
                        'refresh_table' => 'patientsTable',
                        'close_modal' => true,
                        'action' => 'reset_form'
                    ]);
                } else {
                    jsonResponse(false, 'Failed to add patient. Please try again.');
                }
                break;
                
            case 'edit':
                $required_fields = ['id', 'first_name', 'last_name', 'phone'];
                $errors = validateInput($_POST, $required_fields);
                
                if (!empty($errors)) {
                    jsonResponse(false, implode(', ', $errors));
                }
                
                $id = (int)$_POST['id'];
                $first_name = sanitizeInput($_POST['first_name']);
                $last_name = sanitizeInput($_POST['last_name']);
                $date_of_birth = sanitizeInput($_POST['date_of_birth']) ?: null;
                $gender = sanitizeInput($_POST['gender']) ?: null;
                $phone = sanitizeInput($_POST['phone']);
                $email = sanitizeInput($_POST['email']) ?: null;
                $address = sanitizeInput($_POST['address']) ?: null;
                $emergency_contact = sanitizeInput($_POST['emergency_contact']) ?: null;
                $emergency_phone = sanitizeInput($_POST['emergency_phone']) ?: null;
                $blood_group = sanitizeInput($_POST['blood_group']) ?: null;
                $medical_history = sanitizeInput($_POST['medical_history']) ?: null;
                $allergies = sanitizeInput($_POST['allergies']) ?: null;
                
                // Validate email if provided
                if ($email && !validateEmail($email)) {
                    jsonResponse(false, 'Please enter a valid email address');
                }
                
                // Validate phone
                if (!validatePhone($phone)) {
                    jsonResponse(false, 'Please enter a valid phone number');
                }
                
                $stmt = $pdo->prepare("
                    UPDATE patients SET 
                        first_name = ?, last_name = ?, date_of_birth = ?, gender = ?,
                        phone = ?, email = ?, address = ?, emergency_contact = ?, 
                        emergency_phone = ?, blood_group = ?, medical_history = ?, 
                        allergies = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                
                if ($stmt->execute([
                    $first_name, $last_name, $date_of_birth, $gender, $phone, $email,
                    $address, $emergency_contact, $emergency_phone, $blood_group,
                    $medical_history, $allergies, $id
                ])) {
                    logActivity($_SESSION['user_id'] ?? 0, 'Patient Updated', "Updated patient: $first_name $last_name (ID: $id)");
                    jsonResponse(true, 'Patient updated successfully!', null, [
                        'refresh_table' => 'patientsTable',
                        'close_modal' => true
                    ]);
                } else {
                    jsonResponse(false, 'Failed to update patient. Please try again.');
                }
                break;
                
            case 'delete':
                $id = (int)($_POST['id'] ?? 0);
                if (!$id) {
                    jsonResponse(false, 'Invalid patient ID');
                }
                
                // Get patient info for logging
                $patientStmt = $pdo->prepare("SELECT first_name, last_name, patient_id FROM patients WHERE id = ?");
                $patientStmt->execute([$id]);
                $patient = $patientStmt->fetch();
                
                if (!$patient) {
                    jsonResponse(false, 'Patient not found');
                }
                
                $stmt = $pdo->prepare("DELETE FROM patients WHERE id = ?");
                if ($stmt->execute([$id])) {
                    logActivity($_SESSION['user_id'] ?? 0, 'Patient Deleted', "Deleted patient: {$patient['first_name']} {$patient['last_name']} ({$patient['patient_id']})");
                    jsonResponse(true, 'Patient deleted successfully!', null, [
                        'refresh_table' => 'patientsTable'
                    ]);
                } else {
                    jsonResponse(false, 'Failed to delete patient. Please try again.');
                }
                break;
                
            case 'get':
                $id = (int)($_POST['id'] ?? 0);
                if (!$id) {
                    jsonResponse(false, 'Invalid patient ID');
                }
                
                $stmt = $pdo->prepare("
                    SELECT *, TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) as age
                    FROM patients WHERE id = ?
                ");
                $stmt->execute([$id]);
                $patient = $stmt->fetch();
                
                if ($patient) {
                    jsonResponse(true, 'Patient data retrieved', $patient);
                } else {
                    jsonResponse(false, 'Patient not found');
                }
                break;
                
            case 'datatable':
                $draw = (int)($_POST['draw'] ?? 1);
                $start = (int)($_POST['start'] ?? 0);
                $length = (int)($_POST['length'] ?? 10);
                $search = sanitizeInput($_POST['search']['value'] ?? '');
                
                $whereClause = '';
                $params = [];
                
                if (!empty($search)) {
                    $whereClause = "WHERE (first_name LIKE ? OR last_name LIKE ? OR patient_id LIKE ? OR phone LIKE ? OR email LIKE ?)";
                    $searchTerm = "%$search%";
                    $params = array_fill(0, 5, $searchTerm);
                }
                
                // Get total count
                $totalStmt = $pdo->prepare("SELECT COUNT(*) FROM patients $whereClause");
                $totalStmt->execute($params);
                $totalRecords = $totalStmt->fetchColumn();
                
                // Get filtered data
                $sql = "
                    SELECT id, patient_id, first_name, last_name, date_of_birth, gender, 
                           phone, email, TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) as age,
                           created_at, status
                    FROM patients 
                    $whereClause
                    ORDER BY created_at DESC
                    LIMIT $start, $length
                ";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $patients = $stmt->fetchAll();
                
                $data = [];
                foreach ($patients as $patient) {
                    $age = $patient['age'] ? $patient['age'] . ' years' : 'N/A';
                    $statusBadge = $patient['status'] === 'active' 
                        ? '<span class="badge badge-success">Active</span>'
                        : '<span class="badge badge-secondary">Inactive</span>';
                    
                    $actions = "
                        <div class='btn-group' role='group'>
                            <button class='btn btn-sm btn-info' onclick='viewPatient({$patient['id']})' title='View'>
                                <i class='fas fa-eye'></i>
                            </button>
                            <button class='btn btn-sm btn-warning' onclick='editPatient({$patient['id']})' title='Edit'>
                                <i class='fas fa-edit'></i>
                            </button>
                            <button class='btn btn-sm btn-danger' onclick='deletePatient({$patient['id']})' title='Delete'>
                                <i class='fas fa-trash'></i>
                            </button>
                        </div>
                    ";
                    
                    $data[] = [
                        'id' => $patient['id'],
                        'patient_id' => $patient['patient_id'],
                        'name' => $patient['first_name'] . ' ' . $patient['last_name'],
                        'phone' => $patient['phone'] ?: 'N/A',
                        'email' => $patient['email'] ?: 'N/A',
                        'gender' => $patient['gender'] ? ucfirst($patient['gender']) : 'N/A',
                        'age' => $age,
                        'status' => $statusBadge,
                        'created_at' => formatDate($patient['created_at'], 'M j, Y'),
                        'actions' => $actions
                    ];
                }
                
                jsonResponse(true, 'Data retrieved', null, [
                    'draw' => $draw,
                    'recordsTotal' => $totalRecords,
                    'recordsFiltered' => $totalRecords,
                    'data' => $data
                ]);
                break;
                
            case 'get_stats':
                $stats = [
                    'total' => $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn(),
                    'active' => $pdo->query("SELECT COUNT(*) FROM patients WHERE status = 'active'")->fetchColumn(),
                    'today' => $pdo->query("SELECT COUNT(*) FROM patients WHERE DATE(created_at) = CURDATE()")->fetchColumn(),
                    'this_week' => $pdo->query("SELECT COUNT(*) FROM patients WHERE YEARWEEK(created_at) = YEARWEEK(CURDATE())")->fetchColumn(),
                    'this_month' => $pdo->query("SELECT COUNT(*) FROM patients WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())")->fetchColumn()
                ];
                jsonResponse(true, 'Statistics retrieved', $stats);
                break;
                
            default:
                jsonResponse(false, 'Invalid action');
        }
        
    } catch (Exception $e) {
        jsonResponse(false, 'Error: ' . $e->getMessage());
    }
}
?>

<style>
.patients-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.stat-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    text-align: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    border-left: 4px solid #3498db;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
}

.stat-card.primary { border-left-color: #3498db; }
.stat-card.success { border-left-color: #2ecc71; }
.stat-card.warning { border-left-color: #f39c12; }
.stat-card.info { border-left-color: #17a2b8; }

.action-card {
    background: white;
    border-radius: 15px;
    padding: 1rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 1.5rem;
}

.patients-table-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.modal-content {
    border-radius: 15px;
    border: none;
}

.modal-header {
    background: linear-gradient(135deg, #3498db, #2980b9);
    color: white;
    border-radius: 15px 15px 0 0;
}

.btn {
    border-radius: 25px;
    padding: 0.5rem 1.5rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.form-control {
    border-radius: 10px;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #3498db;
    box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
}

.table-responsive {
    border-radius: 0 0 15px 15px;
}

#patientsTable {
    border: none;
}

#patientsTable thead th {
    background: #f8f9fa;
    border: none;
    color: #495057;
    font-weight: 600;
    padding: 1rem;
}

#patientsTable tbody td {
    border: none;
    padding: 1rem;
    vertical-align: middle;
}

#patientsTable tbody tr {
    border-bottom: 1px solid #f8f9fa;
    transition: all 0.3s ease;
}

#patientsTable tbody tr:hover {
    background-color: #f8f9fa;
}

.badge {
    border-radius: 15px;
    padding: 0.4rem 0.8rem;
    font-size: 0.75rem;
    font-weight: 500;
}

.btn-group .btn {
    border-radius: 20px;
    margin: 0 2px;
}

.loading-state {
    opacity: 0.6;
    pointer-events: none;
}

.fade-in {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="patients-header fade-in">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="display-4 mb-2">
                            <i class="fas fa-users mr-3"></i>
                            Patients Management
                        </h1>
                        <p class="lead mb-0">
                            Comprehensive patient records and management system
                        </p>
                    </div>
                    <div class="col-md-4 text-right">
                        <button class="btn btn-light btn-lg" id="addPatientBtn">
                            <i class="fas fa-plus mr-2"></i>Add New Patient
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            
            <!-- Statistics Cards -->
            <div class="row mb-4" id="statsContainer">
                <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3">
                    <div class="stat-card primary">
                        <h2 class="text-primary mb-2" id="totalPatients">0</h2>
                        <h6 class="text-muted mb-0">Total Patients</h6>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3">
                    <div class="stat-card success">
                        <h2 class="text-success mb-2" id="activePatients">0</h2>
                        <h6 class="text-muted mb-0">Active Patients</h6>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3">
                    <div class="stat-card warning">
                        <h2 class="text-warning mb-2" id="todayPatients">0</h2>
                        <h6 class="text-muted mb-0">Added Today</h6>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3">
                    <div class="stat-card info">
                        <h2 class="text-info mb-2" id="monthPatients">0</h2>
                        <h6 class="text-muted mb-0">This Month</h6>
                    </div>
                </div>
            </div>

            <!-- Action Controls -->
            <div class="action-card">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center">
                            <div class="input-group mr-3" style="max-width: 300px;">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                </div>
                                <input type="text" class="form-control" id="globalSearch" 
                                       placeholder="Search patients..." data-live-search="patientsTable">
                            </div>
                            <button class="btn btn-outline-primary mr-2" data-refresh-table="patientsTable">
                                <i class="fas fa-sync-alt mr-1"></i>Refresh
                            </button>
                            <div class="btn-group">
                                <button class="btn btn-outline-success" data-export="csv">
                                    <i class="fas fa-file-csv mr-1"></i>CSV
                                </button>
                                <button class="btn btn-outline-danger" data-export="pdf">
                                    <i class="fas fa-file-pdf mr-1"></i>PDF
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-right">
                        <button class="btn btn-success" id="addPatientBtn2">
                            <i class="fas fa-user-plus mr-2"></i>Add Patient
                        </button>
                    </div>
                </div>
            </div>

            <!-- Patients Table -->
            <div class="patients-table-card">
                <div class="card-header bg-light">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-list mr-2"></i>Patients Directory
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="patientsTable" class="table table-hover mb-0" style="width: 100%">
                            <thead>
                                <tr>
                                    <th>Patient ID</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th>Gender</th>
                                    <th>Age</th>
                                    <th>Status</th>
                                    <th>Registered</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

<!-- Add Patient Modal -->
<div class="modal fade" id="addPatientModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus mr-2"></i>Add New Patient
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="addPatientForm" data-auto-submit="true" data-action="add">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="firstName">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="firstName" name="first_name" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="lastName">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="lastName" name="last_name" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="dateOfBirth">Date of Birth</label>
                                <input type="date" class="form-control" id="dateOfBirth" name="date_of_birth">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="gender">Gender</label>
                                <select class="form-control" id="gender" name="gender">
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="phone" name="phone" data-format="phone" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="emergencyContact">Emergency Contact</label>
                                <input type="text" class="form-control" id="emergencyContact" name="emergency_contact">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="emergencyPhone">Emergency Phone</label>
                                <input type="tel" class="form-control" id="emergencyPhone" name="emergency_phone" data-format="phone">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bloodGroup">Blood Group</label>
                                <select class="form-control" id="bloodGroup" name="blood_group">
                                    <option value="">Select Blood Group</option>
                                    <option value="A+">A+</option>
                                    <option value="A-">A-</option>
                                    <option value="B+">B+</option>
                                    <option value="B-">B-</option>
                                    <option value="AB+">AB+</option>
                                    <option value="AB-">AB-</option>
                                    <option value="O+">O+</option>
                                    <option value="O-">O-</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="medicalHistory">Medical History</label>
                        <textarea class="form-control" id="medicalHistory" name="medical_history" rows="2"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="allergies">Allergies</label>
                        <textarea class="form-control" id="allergies" name="allergies" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>Save Patient
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Patient Modal -->
<div class="modal fade" id="editPatientModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-edit mr-2"></i>Edit Patient
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="editPatientForm" data-auto-submit="true" data-action="edit">
                <input type="hidden" id="editPatientId" name="id">
                <div class="modal-body">
                    <!-- Same fields as add form but with edit prefix -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editFirstName">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="editFirstName" name="first_name" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editLastName">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="editLastName" name="last_name" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editDateOfBirth">Date of Birth</label>
                                <input type="date" class="form-control" id="editDateOfBirth" name="date_of_birth">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editGender">Gender</label>
                                <select class="form-control" id="editGender" name="gender">
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editPhone">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="editPhone" name="phone" data-format="phone" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editEmail">Email Address</label>
                                <input type="email" class="form-control" id="editEmail" name="email">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="editAddress">Address</label>
                        <textarea class="form-control" id="editAddress" name="address" rows="2"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editEmergencyContact">Emergency Contact</label>
                                <input type="text" class="form-control" id="editEmergencyContact" name="emergency_contact">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editEmergencyPhone">Emergency Phone</label>
                                <input type="tel" class="form-control" id="editEmergencyPhone" name="emergency_phone" data-format="phone">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editBloodGroup">Blood Group</label>
                                <select class="form-control" id="editBloodGroup" name="blood_group">
                                    <option value="">Select Blood Group</option>
                                    <option value="A+">A+</option>
                                    <option value="A-">A-</option>
                                    <option value="B+">B+</option>
                                    <option value="B-">B-</option>
                                    <option value="AB+">AB+</option>
                                    <option value="AB-">AB-</option>
                                    <option value="O+">O+</option>
                                    <option value="O-">O-</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="editMedicalHistory">Medical History</label>
                        <textarea class="form-control" id="editMedicalHistory" name="medical_history" rows="2"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="editAllergies">Allergies</label>
                        <textarea class="form-control" id="editAllergies" name="allergies" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>Update Patient
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Patient Modal -->
<div class="modal fade" id="viewPatientModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user mr-2"></i>Patient Details
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="patientDetailsContent">
                <!-- Patient details loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Close
                </button>
                <button type="button" class="btn btn-primary" onclick="printPatientDetails()">
                    <i class="fas fa-print mr-1"></i>Print
                </button>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
// Global variables
let patientsTable;

$(document).ready(function() {
    // Initialize page
    initializePatientsPage();
    
    // Setup event listeners
    setupEventListeners();
    
    // Load initial data
    loadStatistics();
    
    // Show welcome message
    notify('success', 'Patients management system loaded!', 'Welcome');
});

function initializePatientsPage() {
    // Initialize DataTable
    patientsTable = $('#patientsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: window.location.href,
            type: 'POST',
            data: function(d) {
                d.action = 'datatable';
                return d;
            },
            error: function(xhr, error, thrown) {
                notify('error', 'Failed to load patients data', 'Table Error');
                console.error('DataTable error:', error);
            }
        },
        columns: [
            { data: 'patient_id', width: '10%' },
            { data: 'name', width: '15%' },
            { data: 'phone', width: '12%' },
            { data: 'email', width: '15%' },
            { data: 'gender', width: '8%' },
            { data: 'age', width: '8%' },
            { data: 'status', width: '8%' },
            { data: 'created_at', width: '10%' },
            { data: 'actions', width: '14%', orderable: false, searchable: false }
        ],
        order: [[7, 'desc']], // Sort by created_at
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        responsive: true,
        language: {
            processing: '<i class="fas fa-spinner fa-spin"></i> Loading patients...',
            emptyTable: 'No patients found in the system',
            zeroRecords: 'No matching patients found',
            lengthMenu: 'Show _MENU_ patients per page',
            info: 'Showing _START_ to _END_ of _TOTAL_ patients',
            infoEmpty: 'No patients available',
            infoFiltered: '(filtered from _MAX_ total patients)'
        },
        drawCallback: function() {
            // Add animations to new rows
            $('#patientsTable tbody tr').addClass('fade-in');
        },
        initComplete: function() {
            notify('info', 'Patients table loaded successfully!');
        }
    });
}

function setupEventListeners() {
    // Add patient buttons
    $('#addPatientBtn, #addPatientBtn2').click(function() {
        $('#addPatientModal').modal('show');
        $('#addPatientForm')[0].reset();
        $('.form-control').removeClass('is-valid is-invalid');
    });
    
    // Modal events
    $('#addPatientModal').on('hidden.bs.modal', function() {
        $('#addPatientForm')[0].reset();
        $('.form-control').removeClass('is-valid is-invalid');
    });
    
    $('#editPatientModal').on('hidden.bs.modal', function() {
        $('#editPatientForm')[0].reset();
        $('.form-control').removeClass('is-valid is-invalid');
    });
    
    // Statistics cards click to refresh
    $('.stat-card').click(function() {
        $(this).addClass('pulse');
        loadStatistics();
        setTimeout(() => $(this).removeClass('pulse'), 1000);
    });
}

function loadStatistics() {
    $.ajax({
        url: window.location.href,
        type: 'POST',
        data: { action: 'get_stats' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                updateStatistics(response.data);
            }
        },
        error: function() {
            notify('error', 'Failed to load statistics', 'Error');
        }
    });
}

function updateStatistics(stats) {
    $('#totalPatients').text(stats.total || 0);
    $('#activePatients').text(stats.active || 0);
    $('#todayPatients').text(stats.today || 0);
    $('#monthPatients').text(stats.this_month || 0);
}

// Patient action functions
function viewPatient(id) {
    showLoading('Loading patient details...');
    
    $.ajax({
        url: window.location.href,
        type: 'POST',
        data: { action: 'get', id: id },
        dataType: 'json',
        success: function(response) {
            hideLoading();
            if (response.success) {
                displayPatientDetails(response.data);
                $('#viewPatientModal').modal('show');
            } else {
                notify('error', response.message, 'Error');
            }
        },
        error: function() {
            hideLoading();
            notify('error', 'Failed to load patient details', 'Error');
        }
    });
}

function editPatient(id) {
    showLoading('Loading patient data...');
    
    $.ajax({
        url: window.location.href,
        type: 'POST',
        data: { action: 'get', id: id },
        dataType: 'json',
        success: function(response) {
            hideLoading();
            if (response.success) {
                populateEditForm(response.data);
                $('#editPatientModal').modal('show');
            } else {
                notify('error', response.message, 'Error');
            }
        },
        error: function() {
            hideLoading();
            notify('error', 'Failed to load patient data', 'Error');
        }
    });
}

function deletePatient(id) {
    DynamicUtils.confirm(
        'Are you sure you want to delete this patient? This action cannot be undone.',
        function(result) {
            if (result) {
                showLoading('Deleting patient...');
                
                $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: { action: 'delete', id: id },
                    dataType: 'json',
                    success: function(response) {
                        hideLoading();
                        if (response.success) {
                            notify('success', response.message, 'Success');
                            patientsTable.ajax.reload(null, false);
                            loadStatistics();
                        } else {
                            notify('error', response.message, 'Error');
                        }
                    },
                    error: function() {
                        hideLoading();
                        notify('error', 'Failed to delete patient', 'Error');
                    }
                });
            }
        },
        {
            title: 'Confirm Deletion',
            type: 'danger',
            confirmText: 'Delete',
            cancelText: 'Cancel'
        }
    );
}

function displayPatientDetails(patient) {
    const age = patient.age ? `${patient.age} years old` : 'Age not specified';
    const gender = patient.gender ? patient.gender.charAt(0).toUpperCase() + patient.gender.slice(1) : 'Not specified';
    
    let content = `
        <div class="row">
            <div class="col-md-6">
                <h5 class="text-primary mb-3">Personal Information</h5>
                <table class="table table-borderless">
                    <tr><th width="40%">Patient ID:</th><td><span class="badge badge-info">${patient.patient_id}</span></td></tr>
                    <tr><th>Full Name:</th><td><strong>${patient.first_name} ${patient.last_name}</strong></td></tr>
                    <tr><th>Date of Birth:</th><td>${patient.date_of_birth || 'Not specified'}</td></tr>
                    <tr><th>Age:</th><td>${age}</td></tr>
                    <tr><th>Gender:</th><td>${gender}</td></tr>
                    <tr><th>Blood Group:</th><td>${patient.blood_group || 'Not specified'}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h5 class="text-primary mb-3">Contact Information</h5>
                <table class="table table-borderless">
                    <tr><th width="40%">Phone:</th><td>${patient.phone || 'Not provided'}</td></tr>
                    <tr><th>Email:</th><td>${patient.email || 'Not provided'}</td></tr>
                    <tr><th>Address:</th><td>${patient.address || 'Not provided'}</td></tr>
                    <tr><th>Emergency Contact:</th><td>${patient.emergency_contact || 'Not provided'}</td></tr>
                    <tr><th>Emergency Phone:</th><td>${patient.emergency_phone || 'Not provided'}</td></tr>
                </table>
            </div>
        </div>
    `;
    
    if (patient.medical_history) {
        content += `
            <div class="row mt-3">
                <div class="col-12">
                    <h5 class="text-primary mb-3">Medical History</h5>
                    <div class="alert alert-info">
                        ${patient.medical_history}
                    </div>
                </div>
            </div>
        `;
    }
    
    if (patient.allergies) {
        content += `
            <div class="row mt-3">
                <div class="col-12">
                    <h5 class="text-danger mb-3">Allergies</h5>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-2"></i>${patient.allergies}
                    </div>
                </div>
            </div>
        `;
    }
    
    content += `
        <div class="row mt-3">
            <div class="col-12">
                <small class="text-muted">
                    <i class="fas fa-calendar-plus mr-1"></i>Registered: ${formatDate(patient.created_at)}
                    ${patient.updated_at !== patient.created_at ? `<br><i class="fas fa-edit mr-1"></i>Last updated: ${formatDate(patient.updated_at)}` : ''}
                </small>
            </div>
        </div>
    `;
    
    $('#patientDetailsContent').html(content);
}

function populateEditForm(patient) {
    $('#editPatientId').val(patient.id);
    $('#editFirstName').val(patient.first_name);
    $('#editLastName').val(patient.last_name);
    $('#editDateOfBirth').val(patient.date_of_birth);
    $('#editGender').val(patient.gender);
    $('#editPhone').val(patient.phone);
    $('#editEmail').val(patient.email);
    $('#editAddress').val(patient.address);
    $('#editEmergencyContact').val(patient.emergency_contact);
    $('#editEmergencyPhone').val(patient.emergency_phone);
    $('#editBloodGroup').val(patient.blood_group);
    $('#editMedicalHistory').val(patient.medical_history);
    $('#editAllergies').val(patient.allergies);
}

function printPatientDetails() {
    window.print();
    notify('info', 'Print dialog opened', 'Print');
}

function formatDate(dateString) {
    if (!dateString) return 'Not specified';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Override DynamicUtils handleResponse for patients-specific actions
const originalHandleResponse = DynamicUtils.handleResponse;
DynamicUtils.handleResponse = function(response, context) {
    originalHandleResponse.call(this, response, context);
    
    if (response.success && response.refresh_table === 'patientsTable') {
        patientsTable.ajax.reload(null, false);
        loadStatistics();
    }
};
</script>
