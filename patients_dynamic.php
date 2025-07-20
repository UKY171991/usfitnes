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
                $required_fields = ['patient_id', 'first_name', 'last_name', 'phone'];
                $errors = validateInput($_POST, $required_fields);
                
                if (!empty($errors)) {
                    jsonResponse(false, implode(', ', $errors));
                }
                
                $patient_id = sanitizeInput($_POST['patient_id']);
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
                
                // Check if patient_id already exists
                $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM patients WHERE patient_id = ?");
                $checkStmt->execute([$patient_id]);
                if ($checkStmt->fetchColumn() > 0) {
                    jsonResponse(false, 'Patient ID already exists. Please use a different ID.');
                }
                
                $stmt = $pdo->prepare("
                    INSERT INTO patients (patient_id, first_name, last_name, date_of_birth, gender, phone, email, address, emergency_contact, emergency_phone, blood_group, medical_history, allergies) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                if ($stmt->execute([$patient_id, $first_name, $last_name, $date_of_birth, $gender, $phone, $email, $address, $emergency_contact, $emergency_phone, $blood_group, $medical_history, $allergies])) {
                    jsonResponse(true, 'Patient added successfully!', ['id' => $pdo->lastInsertId()]);
                } else {
                    jsonResponse(false, 'Failed to add patient. Please try again.');
                }
                break;
                
            case 'edit':
                $required_fields = ['id', 'patient_id', 'first_name', 'last_name', 'phone'];
                $errors = validateInput($_POST, $required_fields);
                
                if (!empty($errors)) {
                    jsonResponse(false, implode(', ', $errors));
                }
                
                $id = (int)$_POST['id'];
                $patient_id = sanitizeInput($_POST['patient_id']);
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
                $status = sanitizeInput($_POST['status']) ?: 'active';
                
                // Check if patient_id already exists for other patients
                $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM patients WHERE patient_id = ? AND id != ?");
                $checkStmt->execute([$patient_id, $id]);
                if ($checkStmt->fetchColumn() > 0) {
                    jsonResponse(false, 'Patient ID already exists. Please use a different ID.');
                }
                
                $stmt = $pdo->prepare("
                    UPDATE patients SET 
                        patient_id = ?, first_name = ?, last_name = ?, date_of_birth = ?, gender = ?, 
                        phone = ?, email = ?, address = ?, emergency_contact = ?, emergency_phone = ?, 
                        blood_group = ?, medical_history = ?, allergies = ?, status = ?, updated_at = NOW() 
                    WHERE id = ?
                ");
                
                if ($stmt->execute([$patient_id, $first_name, $last_name, $date_of_birth, $gender, $phone, $email, $address, $emergency_contact, $emergency_phone, $blood_group, $medical_history, $allergies, $status, $id])) {
                    jsonResponse(true, 'Patient updated successfully!');
                } else {
                    jsonResponse(false, 'Failed to update patient. Please try again.');
                }
                break;
                
            case 'delete':
                if (empty($_POST['id'])) {
                    jsonResponse(false, 'Patient ID is required');
                }
                
                $id = (int)$_POST['id'];
                
                // Check if patient has any test orders
                $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM test_orders WHERE patient_id = ?");
                $checkStmt->execute([$id]);
                $orderCount = $checkStmt->fetchColumn();
                
                if ($orderCount > 0) {
                    jsonResponse(false, 'Cannot delete patient. Patient has ' . $orderCount . ' test order(s) associated.');
                }
                
                $stmt = $pdo->prepare("DELETE FROM patients WHERE id = ?");
                
                if ($stmt->execute([$id])) {
                    jsonResponse(true, 'Patient deleted successfully!');
                } else {
                    jsonResponse(false, 'Failed to delete patient. Please try again.');
                }
                break;
                
            case 'get':
                if (empty($_POST['id'])) {
                    jsonResponse(false, 'Patient ID is required');
                }
                
                $id = (int)$_POST['id'];
                $stmt = $pdo->prepare("SELECT * FROM patients WHERE id = ?");
                $stmt->execute([$id]);
                $patient = $stmt->fetch();
                
                if ($patient) {
                    jsonResponse(true, 'Patient data retrieved successfully', $patient);
                } else {
                    jsonResponse(false, 'Patient not found');
                }
                break;
                
            case 'datatable':
                $draw = (int)($_POST['draw'] ?? 1);
                $start = (int)($_POST['start'] ?? 0);
                $length = (int)($_POST['length'] ?? 10);
                $search = sanitizeInput($_POST['search']['value'] ?? '');
                $orderColumn = (int)($_POST['order'][0]['column'] ?? 0);
                $orderDir = sanitizeInput($_POST['order'][0]['dir'] ?? 'asc');
                
                // Column mapping for ordering
                $columns = ['patient_id', 'first_name', 'phone', 'email', 'status', 'created_at'];
                $orderBy = $columns[$orderColumn] ?? 'created_at';
                $orderBy = $orderBy . ' ' . ($orderDir === 'desc' ? 'DESC' : 'ASC');
                
                // Base query
                $baseQuery = "FROM patients";
                $whereClause = "";
                $params = [];
                
                // Search functionality
                if (!empty($search)) {
                    $whereClause = " WHERE (patient_id LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR phone LIKE ? OR email LIKE ?)";
                    $searchTerm = "%$search%";
                    $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm];
                }
                
                // Get total records
                $totalRecords = $pdo->query("SELECT COUNT(*) " . $baseQuery)->fetchColumn();
                
                // Get filtered records count
                $filteredQuery = "SELECT COUNT(*) " . $baseQuery . $whereClause;
                $filteredStmt = $pdo->prepare($filteredQuery);
                $filteredStmt->execute($params);
                $filteredRecords = $filteredStmt->fetchColumn();
                
                // Get actual data
                $dataQuery = "SELECT * " . $baseQuery . $whereClause . " ORDER BY " . $orderBy . " LIMIT ?, ?";
                $params[] = $start;
                $params[] = $length;
                $dataStmt = $pdo->prepare($dataQuery);
                $dataStmt->execute($params);
                $patients = $dataStmt->fetchAll();
                
                $data = [];
                foreach ($patients as $patient) {
                    $statusBadge = $patient['status'] === 'active' 
                        ? '<span class="badge badge-success">Active</span>' 
                        : '<span class="badge badge-secondary">Inactive</span>';
                    
                    $age = '';
                    if ($patient['date_of_birth']) {
                        $birthDate = new DateTime($patient['date_of_birth']);
                        $today = new DateTime();
                        $age = $today->diff($birthDate)->y . ' years';
                    }
                    
                    $actions = '
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-info" onclick="viewPatient(' . $patient['id'] . ')" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-primary" onclick="editPatient(' . $patient['id'] . ')" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-warning" onclick="createOrder(' . $patient['id'] . ')" title="Create Order">
                                <i class="fas fa-plus"></i>
                            </button>
                            <button type="button" class="btn btn-danger" onclick="deletePatient(' . $patient['id'] . ')" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    ';
                    
                    $data[] = [
                        'patient_id' => '<span class="badge badge-info">' . htmlspecialchars($patient['patient_id']) . '</span>',
                        'name' => '<strong>' . htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) . '</strong>' . 
                                 ($age ? '<br><small class="text-muted">' . $age . '</small>' : ''),
                        'contact' => htmlspecialchars($patient['phone']) . 
                                   ($patient['email'] ? '<br><small class="text-muted">' . htmlspecialchars($patient['email']) . '</small>' : ''),
                        'gender' => $patient['gender'] ? '<span class="badge badge-secondary">' . ucfirst($patient['gender']) . '</span>' : '-',
                        'blood_group' => $patient['blood_group'] ? '<span class="badge badge-danger">' . htmlspecialchars($patient['blood_group']) . '</span>' : '-',
                        'status' => $statusBadge,
                        'created' => date('M d, Y', strtotime($patient['created_at'])),
                        'actions' => $actions
                    ];
                }
                
                jsonResponse(true, 'Data retrieved successfully', [
                    'draw' => $draw,
                    'recordsTotal' => $totalRecords,
                    'recordsFiltered' => $filteredRecords,
                    'data' => $data
                ]);
                break;
                
            case 'stats':
                $stats = [
                    'total' => $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn(),
                    'active' => $pdo->query("SELECT COUNT(*) FROM patients WHERE status = 'active'")->fetchColumn(),
                    'today' => $pdo->query("SELECT COUNT(*) FROM patients WHERE DATE(created_at) = CURDATE()")->fetchColumn(),
                    'this_week' => $pdo->query("SELECT COUNT(*) FROM patients WHERE YEARWEEK(created_at) = YEARWEEK(CURDATE())")->fetchColumn(),
                    'this_month' => $pdo->query("SELECT COUNT(*) FROM patients WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())")->fetchColumn()
                ];
                jsonResponse(true, 'Statistics retrieved successfully', $stats);
                break;
                
            default:
                jsonResponse(false, 'Invalid action specified');
        }
        
    } catch (Exception $e) {
        error_log("Patients AJAX Error: " . $e->getMessage());
        jsonResponse(false, 'An error occurred: ' . $e->getMessage());
    }
}

// Get summary statistics for display
try {
    $totalPatients = $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();
    $activePatients = $pdo->query("SELECT COUNT(*) FROM patients WHERE status = 'active'")->fetchColumn();
    $todayPatients = $pdo->query("SELECT COUNT(*) FROM patients WHERE DATE(created_at) = CURDATE()")->fetchColumn();
    $weekPatients = $pdo->query("SELECT COUNT(*) FROM patients WHERE YEARWEEK(created_at) = YEARWEEK(CURDATE())")->fetchColumn();
} catch (Exception $e) {
    $totalPatients = $activePatients = $todayPatients = $weekPatients = 0;
}
?>

<style>
.content-wrapper {
    background-color: #f4f6f9;
}

.card {
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    border: none;
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-1px);
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
    transition: transform 0.2s ease-in-out;
}

.info-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 4px 8px rgba(0,0,0,.15);
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
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
    border-bottom: none;
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

.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #007bff;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.fade-in {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    0% { opacity: 0; transform: translateY(10px); }
    100% { opacity: 1; transform: translateY(0); }
}
</style>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner"></div>
</div>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <i class="fas fa-users mr-2 text-primary"></i>Patients Management
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                        <li class="breadcrumb-item active">Patients</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Statistics Cards -->
            <div class="row mb-3">
                <div class="col-lg-3 col-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-info">
                            <i class="fas fa-users"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Patients</span>
                            <span class="info-box-number" id="totalPatientsCount"><?php echo number_format($totalPatients); ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-success">
                            <i class="fas fa-user-check"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Active Patients</span>
                            <span class="info-box-number" id="activePatientsCount"><?php echo number_format($activePatients); ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning">
                            <i class="fas fa-user-plus"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Today</span>
                            <span class="info-box-number" id="todayPatientsCount"><?php echo number_format($todayPatients); ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-danger">
                            <i class="fas fa-calendar-week"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">This Week</span>
                            <span class="info-box-number" id="weekPatientsCount"><?php echo number_format($weekPatients); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row mb-3">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body py-2">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-bolt mr-2 text-primary"></i>
                                <span class="text-muted mr-3">Quick Actions:</span>
                                <button class="btn btn-success btn-sm mr-2" id="addPatientBtn">
                                    <i class="fas fa-user-plus mr-1"></i>Add Patient
                                </button>
                                <button class="btn btn-info btn-sm mr-2" id="refreshBtn">
                                    <i class="fas fa-sync-alt mr-1"></i>Refresh
                                </button>
                                <button class="btn btn-primary btn-sm mr-2" id="bulkActionsBtn">
                                    <i class="fas fa-tasks mr-1"></i>Bulk Actions
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
                                <input type="text" class="form-control form-control-sm" id="globalSearch" placeholder="Search patients...">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary btn-sm" type="button" id="searchBtn">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Patients Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-list mr-2"></i>Patients List
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <button type="button" class="btn btn-tool" data-card-widget="maximize">
                                    <i class="fas fa-expand"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="patientsTable" class="table table-striped table-hover table-bordered">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>Patient ID</th>
                                            <th>Name</th>
                                            <th>Contact</th>
                                            <th>Gender</th>
                                            <th>Blood Group</th>
                                            <th>Status</th>
                                            <th>Registered</th>
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

<!-- Add Patient Modal -->
<div class="modal fade" id="addPatientModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus mr-2"></i>Add New Patient
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addPatientForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="patient_id">Patient ID <span class="required">*</span></label>
                                <input type="text" class="form-control" id="patient_id" name="patient_id" required>
                                <small class="form-text text-muted">Unique identifier for the patient</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="first_name">First Name <span class="required">*</span></label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="last_name">Last Name <span class="required">*</span></label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="date_of_birth">Date of Birth</label>
                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth">
                            </div>
                        </div>
                        <div class="col-md-3">
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
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="phone">Phone Number <span class="required">*</span></label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="address">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="emergency_contact">Emergency Contact</label>
                                <input type="text" class="form-control" id="emergency_contact" name="emergency_contact">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="emergency_phone">Emergency Phone</label>
                                <input type="tel" class="form-control" id="emergency_phone" name="emergency_phone">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="blood_group">Blood Group</label>
                                <select class="form-control" id="blood_group" name="blood_group">
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
                        <div class="col-md-9">
                            <div class="form-group">
                                <label for="medical_history">Medical History</label>
                                <textarea class="form-control" id="medical_history" name="medical_history" rows="2" placeholder="Previous medical conditions, surgeries, etc."></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="allergies">Allergies</label>
                        <textarea class="form-control" id="allergies" name="allergies" rows="2" placeholder="Known allergies and reactions"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save mr-1"></i>Add Patient
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Patient Modal -->
<div class="modal fade" id="editPatientModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title">
                    <i class="fas fa-user-edit mr-2"></i>Edit Patient
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editPatientForm">
                <input type="hidden" id="edit_id" name="id">
                <div class="modal-body">
                    <!-- Same form fields as add modal but with edit_ prefix -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_patient_id">Patient ID <span class="required">*</span></label>
                                <input type="text" class="form-control" id="edit_patient_id" name="patient_id" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="edit_first_name">First Name <span class="required">*</span></label>
                                <input type="text" class="form-control" id="edit_first_name" name="first_name" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="edit_last_name">Last Name <span class="required">*</span></label>
                                <input type="text" class="form-control" id="edit_last_name" name="last_name" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="edit_date_of_birth">Date of Birth</label>
                                <input type="date" class="form-control" id="edit_date_of_birth" name="date_of_birth">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="edit_gender">Gender</label>
                                <select class="form-control" id="edit_gender" name="gender">
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="edit_phone">Phone Number <span class="required">*</span></label>
                                <input type="tel" class="form-control" id="edit_phone" name="phone" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="edit_email">Email Address</label>
                                <input type="email" class="form-control" id="edit_email" name="email">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_address">Address</label>
                                <textarea class="form-control" id="edit_address" name="address" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="edit_emergency_contact">Emergency Contact</label>
                                <input type="text" class="form-control" id="edit_emergency_contact" name="emergency_contact">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="edit_emergency_phone">Emergency Phone</label>
                                <input type="tel" class="form-control" id="edit_emergency_phone" name="emergency_phone">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="edit_blood_group">Blood Group</label>
                                <select class="form-control" id="edit_blood_group" name="blood_group">
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
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="edit_status">Status</label>
                                <select class="form-control" id="edit_status" name="status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_medical_history">Medical History</label>
                                <textarea class="form-control" id="edit_medical_history" name="medical_history" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_allergies">Allergies</label>
                        <textarea class="form-control" id="edit_allergies" name="allergies" rows="2"></textarea>
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
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title">
                    <i class="fas fa-user mr-2"></i>Patient Details
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="patientDetailsContent">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Close
                </button>
                <button type="button" class="btn btn-primary" onclick="printPatientDetails()">
                    <i class="fas fa-print mr-1"></i>Print Details
                </button>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
let patientsTable;
let currentPatientId = null;

$(document).ready(function() {
    // Configure Toastr with enhanced options
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
    
    // Initialize DataTable with enhanced features
    patientsTable = $('#patientsTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "patients_dynamic.php",
            "type": "POST",
            "data": function(d) {
                d.action = 'datatable';
            },
            "error": function(xhr, error, thrown) {
                console.error('DataTable error:', error);
                toastr.error('Failed to load patient data. Please refresh the page.');
            }
        },
        "columns": [
            { "data": "patient_id", "width": "12%" },
            { "data": "name", "width": "18%" },
            { "data": "contact", "width": "15%" },
            { "data": "gender", "width": "8%" },
            { "data": "blood_group", "width": "10%" },
            { "data": "status", "width": "10%" },
            { "data": "created", "width": "12%" },
            { "data": "actions", "width": "15%", "orderable": false, "searchable": false }
        ],
        "order": [[6, "desc"]],
        "pageLength": 25,
        "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
        "responsive": true,
        "autoWidth": false,
        "language": {
            "processing": '<i class="fas fa-spinner fa-spin fa-2x"></i><br>Loading patients...',
            "emptyTable": "No patients found in the system",
            "zeroRecords": "No matching patients found",
            "lengthMenu": "Show _MENU_ patients per page",
            "info": "Showing _START_ to _END_ of _TOTAL_ patients",
            "infoEmpty": "No patients available",
            "infoFiltered": "(filtered from _MAX_ total patients)"
        },
        "drawCallback": function() {
            // Add fade-in animation to new rows
            $('#patientsTable tbody tr').addClass('fade-in');
        },
        "initComplete": function() {
            toastr.success('Patients data loaded successfully!');
        }
    });
    
    // Global search functionality
    $('#globalSearch').on('keyup', function() {
        patientsTable.search(this.value).draw();
    });
    
    // Quick search button
    $('#searchBtn').click(function() {
        const searchTerm = $('#globalSearch').val();
        patientsTable.search(searchTerm).draw();
        if (searchTerm) {
            toastr.info('Searching for: ' + searchTerm);
        }
    });
    
    // Button event handlers
    $('#addPatientBtn').click(function() {
        generatePatientId();
        $('#addPatientModal').modal('show');
        toastr.info('Ready to add new patient');
    });
    
    $('#refreshBtn').click(function() {
        showLoading();
        patientsTable.ajax.reload(function() {
            hideLoading();
            updateStatistics();
            toastr.success('Data refreshed successfully!');
        }, false);
    });
    
    $('#exportBtn').click(function() {
        toastr.info('Export functionality coming soon!');
    });
    
    // Form submissions with enhanced error handling
    $('#addPatientForm').submit(function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', 'add');
        
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.html('<i class="fas fa-spinner fa-spin mr-1"></i>Adding...').prop('disabled', true);
        showLoading();
        
        $.ajax({
            url: 'patients_dynamic.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                hideLoading();
                if (response.success) {
                    toastr.success(response.message);
                    $('#addPatientModal').modal('hide');
                    $('#addPatientForm')[0].reset();
                    patientsTable.ajax.reload(null, false);
                    updateStatistics();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr, status, error) {
                hideLoading();
                console.error('AJAX Error:', error);
                toastr.error('Network error occurred. Please try again.');
            },
            complete: function() {
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });
    
    $('#editPatientForm').submit(function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', 'edit');
        
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.html('<i class="fas fa-spinner fa-spin mr-1"></i>Updating...').prop('disabled', true);
        showLoading();
        
        $.ajax({
            url: 'patients_dynamic.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                hideLoading();
                if (response.success) {
                    toastr.success(response.message);
                    $('#editPatientModal').modal('hide');
                    patientsTable.ajax.reload(null, false);
                    updateStatistics();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr, status, error) {
                hideLoading();
                console.error('AJAX Error:', error);
                toastr.error('Network error occurred. Please try again.');
            },
            complete: function() {
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });
    
    // Modal event handlers
    $('#addPatientModal').on('hidden.bs.modal', function() {
        $('#addPatientForm')[0].reset();
        $('.form-control').removeClass('is-invalid');
    });
    
    $('#editPatientModal').on('hidden.bs.modal', function() {
        $('#editPatientForm')[0].reset();
        $('.form-control').removeClass('is-invalid');
    });
    
    // Auto-generate patient ID
    function generatePatientId() {
        const date = new Date();
        const year = date.getFullYear().toString().substr(-2);
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
        $('#patient_id').val('PAT' + year + month + random);
    }
    
    // Update statistics
    function updateStatistics() {
        $.ajax({
            url: 'patients_dynamic.php',
            type: 'POST',
            data: { action: 'stats' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#totalPatientsCount').text(response.data.total);
                    $('#activePatientsCount').text(response.data.active);
                    $('#todayPatientsCount').text(response.data.today);
                    $('#weekPatientsCount').text(response.data.this_week);
                }
            }
        });
    }
    
    // Loading overlay functions
    function showLoading() {
        $('#loadingOverlay').show();
    }
    
    function hideLoading() {
        $('#loadingOverlay').hide();
    }
    
    // Initial statistics update
    updateStatistics();
    
    // Auto-refresh every 5 minutes
    setInterval(function() {
        updateStatistics();
    }, 300000);
});

// Patient action functions
function viewPatient(id) {
    currentPatientId = id;
    showLoading();
    
    $.ajax({
        url: 'patients_dynamic.php',
        type: 'POST',
        data: { action: 'get', id: id },
        dataType: 'json',
        success: function(response) {
            hideLoading();
            if (response.success) {
                const patient = response.data;
                
                let age = '';
                if (patient.date_of_birth) {
                    const birthDate = new Date(patient.date_of_birth);
                    const today = new Date();
                    age = Math.floor((today - birthDate) / (365.25 * 24 * 60 * 60 * 1000));
                }
                
                const content = `
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-user mr-2"></i>Personal Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <td class="font-weight-bold" style="width: 40%;">Patient ID:</td>
                                                    <td><span class="badge badge-info">${patient.patient_id}</span></td>
                                                </tr>
                                                <tr>
                                                    <td class="font-weight-bold">Full Name:</td>
                                                    <td>${patient.first_name} ${patient.last_name}</td>
                                                </tr>
                                                <tr>
                                                    <td class="font-weight-bold">Date of Birth:</td>
                                                    <td>${patient.date_of_birth ? new Date(patient.date_of_birth).toLocaleDateString() : 'Not specified'}</td>
                                                </tr>
                                                <tr>
                                                    <td class="font-weight-bold">Age:</td>
                                                    <td>${age ? age + ' years' : 'Not calculated'}</td>
                                                </tr>
                                                <tr>
                                                    <td class="font-weight-bold">Gender:</td>
                                                    <td>${patient.gender ? patient.gender.charAt(0).toUpperCase() + patient.gender.slice(1) : 'Not specified'}</td>
                                                </tr>
                                                <tr>
                                                    <td class="font-weight-bold">Blood Group:</td>
                                                    <td>${patient.blood_group ? '<span class="badge badge-danger">' + patient.blood_group + '</span>' : 'Not specified'}</td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <td class="font-weight-bold" style="width: 40%;">Phone:</td>
                                                    <td><a href="tel:${patient.phone}">${patient.phone}</a></td>
                                                </tr>
                                                <tr>
                                                    <td class="font-weight-bold">Email:</td>
                                                    <td>${patient.email ? '<a href="mailto:' + patient.email + '">' + patient.email + '</a>' : 'Not provided'}</td>
                                                </tr>
                                                <tr>
                                                    <td class="font-weight-bold">Address:</td>
                                                    <td>${patient.address || 'Not provided'}</td>
                                                </tr>
                                                <tr>
                                                    <td class="font-weight-bold">Emergency Contact:</td>
                                                    <td>${patient.emergency_contact || 'Not provided'}</td>
                                                </tr>
                                                <tr>
                                                    <td class="font-weight-bold">Emergency Phone:</td>
                                                    <td>${patient.emergency_phone ? '<a href="tel:' + patient.emergency_phone + '">' + patient.emergency_phone + '</a>' : 'Not provided'}</td>
                                                </tr>
                                                <tr>
                                                    <td class="font-weight-bold">Status:</td>
                                                    <td><span class="badge badge-${patient.status === 'active' ? 'success' : 'secondary'}">${patient.status.charAt(0).toUpperCase() + patient.status.slice(1)}</span></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-history mr-2"></i>Registration Info</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless mb-0">
                                        <tr>
                                            <td class="font-weight-bold">Registered:</td>
                                            <td>${new Date(patient.created_at).toLocaleString()}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">Last Updated:</td>
                                            <td>${new Date(patient.updated_at).toLocaleString()}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    ${patient.medical_history ? `
                    <div class="card mt-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-notes-medical mr-2"></i>Medical History</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">${patient.medical_history}</p>
                        </div>
                    </div>
                    ` : ''}
                    
                    ${patient.allergies ? `
                    <div class="card mt-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-exclamation-triangle mr-2"></i>Allergies</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-0 text-danger">${patient.allergies}</p>
                        </div>
                    </div>
                    ` : ''}
                `;
                
                $('#patientDetailsContent').html(content);
                $('#viewPatientModal').modal('show');
                toastr.success('Patient details loaded successfully');
            } else {
                toastr.error(response.message);
            }
        },
        error: function() {
            hideLoading();
            toastr.error('Failed to load patient details');
        }
    });
}

function editPatient(id) {
    showLoading();
    
    $.ajax({
        url: 'patients_dynamic.php',
        type: 'POST',
        data: { action: 'get', id: id },
        dataType: 'json',
        success: function(response) {
            hideLoading();
            if (response.success) {
                const patient = response.data;
                $('#edit_id').val(patient.id);
                $('#edit_patient_id').val(patient.patient_id);
                $('#edit_first_name').val(patient.first_name);
                $('#edit_last_name').val(patient.last_name);
                $('#edit_date_of_birth').val(patient.date_of_birth);
                $('#edit_gender').val(patient.gender);
                $('#edit_phone').val(patient.phone);
                $('#edit_email').val(patient.email);
                $('#edit_address').val(patient.address);
                $('#edit_emergency_contact').val(patient.emergency_contact);
                $('#edit_emergency_phone').val(patient.emergency_phone);
                $('#edit_blood_group').val(patient.blood_group);
                $('#edit_medical_history').val(patient.medical_history);
                $('#edit_allergies').val(patient.allergies);
                $('#edit_status').val(patient.status);
                $('#editPatientModal').modal('show');
                toastr.info('Ready to edit patient information');
            } else {
                toastr.error(response.message);
            }
        },
        error: function() {
            hideLoading();
            toastr.error('Failed to load patient data for editing');
        }
    });
}

function deletePatient(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'Do you want to delete this patient? This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            showLoading();
            
            $.ajax({
                url: 'patients_dynamic.php',
                type: 'POST',
                data: { action: 'delete', id: id },
                dataType: 'json',
                success: function(response) {
                    hideLoading();
                    if (response.success) {
                        toastr.success(response.message);
                        patientsTable.ajax.reload(null, false);
                        updateStatistics();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    hideLoading();
                    toastr.error('Failed to delete patient');
                }
            });
        }
    });
}

function createOrder(patientId) {
    toastr.info('Redirecting to create test order...');
    window.location.href = 'test-orders.php?patient_id=' + patientId;
}

function printPatientDetails() {
    if (currentPatientId) {
        window.open('print_patient.php?id=' + currentPatientId, '_blank');
        toastr.info('Opening print preview...');
    }
}

// Keyboard shortcuts
$(document).keydown(function(e) {
    if (e.ctrlKey) {
        switch(e.which) {
            case 78: // Ctrl+N
                e.preventDefault();
                $('#addPatientBtn').click();
                break;
            case 82: // Ctrl+R
                e.preventDefault();
                $('#refreshBtn').click();
                break;
            case 70: // Ctrl+F
                e.preventDefault();
                $('#globalSearch').focus();
                break;
        }
    }
});
</script>
