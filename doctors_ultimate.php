<?php
// Set page title
$page_title = 'Doctors Management';

// Include header and sidebar
include 'includes/header.php';
include 'includes/sidebar.php';

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
    $action = sanitizeInput($_POST['action']);
    
    try {
        switch ($action) {
            case 'add':
                $required_fields = ['name', 'phone', 'specialization'];
                $errors = validateInput($_POST, $required_fields);
                
                if (!empty($errors)) {
                    jsonResponse(false, implode(', ', $errors));
                }
                
                // Generate unique doctor ID
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM doctors");
                $count = $stmt->fetch()['count'] + 1;
                $doctor_id = 'DOC' . str_pad($count, 4, '0', STR_PAD_LEFT);
                
                // Ensure uniqueness
                while (true) {
                    $checkStmt = $pdo->prepare("SELECT id FROM doctors WHERE doctor_id = ?");
                    $checkStmt->execute([$doctor_id]);
                    if (!$checkStmt->fetch()) break;
                    $count++;
                    $doctor_id = 'DOC' . str_pad($count, 4, '0', STR_PAD_LEFT);
                }
                
                $name = sanitizeInput($_POST['name']);
                $email = sanitizeInput($_POST['email']) ?: null;
                $phone = sanitizeInput($_POST['phone']);
                $specialization = sanitizeInput($_POST['specialization']);
                $qualification = sanitizeInput($_POST['qualification']) ?: null;
                $experience = sanitizeInput($_POST['experience']) ?: null;
                $license_number = sanitizeInput($_POST['license_number']) ?: null;
                $hospital = sanitizeInput($_POST['hospital']) ?: null;
                $address = sanitizeInput($_POST['address']) ?: null;
                $consultation_fee = sanitizeInput($_POST['consultation_fee']) ?: null;
                $available_days = sanitizeInput($_POST['available_days']) ?: null;
                $available_time = sanitizeInput($_POST['available_time']) ?: null;
                $notes = sanitizeInput($_POST['notes']) ?: null;
                
                // Validate email if provided
                if ($email && !validateEmail($email)) {
                    jsonResponse(false, 'Please enter a valid email address');
                }
                
                // Validate phone
                if (!validatePhone($phone)) {
                    jsonResponse(false, 'Please enter a valid phone number');
                }
                
                $stmt = $pdo->prepare("
                    INSERT INTO doctors (
                        doctor_id, name, email, phone, specialization, qualification, 
                        experience, license_number, hospital, address, consultation_fee,
                        available_days, available_time, notes
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                if ($stmt->execute([
                    $doctor_id, $name, $email, $phone, $specialization, $qualification,
                    $experience, $license_number, $hospital, $address, $consultation_fee,
                    $available_days, $available_time, $notes
                ])) {
                    logActivity($_SESSION['user_id'] ?? 0, 'Doctor Added', "Added doctor: $name ($doctor_id)");
                    jsonResponse(true, 'Doctor added successfully!', [
                        'doctor_id' => $doctor_id,
                        'name' => $name
                    ], [
                        'refresh_table' => 'doctorsTable',
                        'close_modal' => true,
                        'action' => 'reset_form'
                    ]);
                } else {
                    jsonResponse(false, 'Failed to add doctor. Please try again.');
                }
                break;
                
            case 'edit':
                $required_fields = ['id', 'name', 'phone', 'specialization'];
                $errors = validateInput($_POST, $required_fields);
                
                if (!empty($errors)) {
                    jsonResponse(false, implode(', ', $errors));
                }
                
                $id = (int)$_POST['id'];
                $name = sanitizeInput($_POST['name']);
                $email = sanitizeInput($_POST['email']) ?: null;
                $phone = sanitizeInput($_POST['phone']);
                $specialization = sanitizeInput($_POST['specialization']);
                $qualification = sanitizeInput($_POST['qualification']) ?: null;
                $experience = sanitizeInput($_POST['experience']) ?: null;
                $license_number = sanitizeInput($_POST['license_number']) ?: null;
                $hospital = sanitizeInput($_POST['hospital']) ?: null;
                $address = sanitizeInput($_POST['address']) ?: null;
                $consultation_fee = sanitizeInput($_POST['consultation_fee']) ?: null;
                $available_days = sanitizeInput($_POST['available_days']) ?: null;
                $available_time = sanitizeInput($_POST['available_time']) ?: null;
                $notes = sanitizeInput($_POST['notes']) ?: null;
                $status = sanitizeInput($_POST['status']) ?: 'active';
                
                // Validate email if provided
                if ($email && !validateEmail($email)) {
                    jsonResponse(false, 'Please enter a valid email address');
                }
                
                // Validate phone
                if (!validatePhone($phone)) {
                    jsonResponse(false, 'Please enter a valid phone number');
                }
                
                $stmt = $pdo->prepare("
                    UPDATE doctors SET 
                        name = ?, email = ?, phone = ?, specialization = ?, qualification = ?,
                        experience = ?, license_number = ?, hospital = ?, address = ?,
                        consultation_fee = ?, available_days = ?, available_time = ?,
                        notes = ?, status = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                
                if ($stmt->execute([
                    $name, $email, $phone, $specialization, $qualification, $experience,
                    $license_number, $hospital, $address, $consultation_fee, $available_days,
                    $available_time, $notes, $status, $id
                ])) {
                    logActivity($_SESSION['user_id'] ?? 0, 'Doctor Updated', "Updated doctor: $name (ID: $id)");
                    jsonResponse(true, 'Doctor updated successfully!', null, [
                        'refresh_table' => 'doctorsTable',
                        'close_modal' => true
                    ]);
                } else {
                    jsonResponse(false, 'Failed to update doctor. Please try again.');
                }
                break;
                
            case 'delete':
                $id = (int)($_POST['id'] ?? 0);
                if (!$id) {
                    jsonResponse(false, 'Invalid doctor ID');
                }
                
                // Get doctor info for logging
                $doctorStmt = $pdo->prepare("SELECT name, doctor_id FROM doctors WHERE id = ?");
                $doctorStmt->execute([$id]);
                $doctor = $doctorStmt->fetch();
                
                if (!$doctor) {
                    jsonResponse(false, 'Doctor not found');
                }
                
                $stmt = $pdo->prepare("DELETE FROM doctors WHERE id = ?");
                if ($stmt->execute([$id])) {
                    logActivity($_SESSION['user_id'] ?? 0, 'Doctor Deleted', "Deleted doctor: {$doctor['name']} ({$doctor['doctor_id']})");
                    jsonResponse(true, 'Doctor deleted successfully!', null, [
                        'refresh_table' => 'doctorsTable'
                    ]);
                } else {
                    jsonResponse(false, 'Failed to delete doctor. Please try again.');
                }
                break;
                
            case 'get':
                $id = (int)($_POST['id'] ?? 0);
                if (!$id) {
                    jsonResponse(false, 'Invalid doctor ID');
                }
                
                $stmt = $pdo->prepare("SELECT * FROM doctors WHERE id = ?");
                $stmt->execute([$id]);
                $doctor = $stmt->fetch();
                
                if ($doctor) {
                    jsonResponse(true, 'Doctor data retrieved', $doctor);
                } else {
                    jsonResponse(false, 'Doctor not found');
                }
                break;
                
            case 'toggle_status':
                $id = (int)($_POST['id'] ?? 0);
                if (!$id) {
                    jsonResponse(false, 'Invalid doctor ID');
                }
                
                $stmt = $pdo->prepare("SELECT name, status FROM doctors WHERE id = ?");
                $stmt->execute([$id]);
                $doctor = $stmt->fetch();
                
                if (!$doctor) {
                    jsonResponse(false, 'Doctor not found');
                }
                
                $new_status = $doctor['status'] === 'active' ? 'inactive' : 'active';
                
                $updateStmt = $pdo->prepare("UPDATE doctors SET status = ?, updated_at = NOW() WHERE id = ?");
                if ($updateStmt->execute([$new_status, $id])) {
                    logActivity($_SESSION['user_id'] ?? 0, 'Doctor Status Changed', "Changed status of {$doctor['name']} to $new_status");
                    jsonResponse(true, "Doctor status changed to $new_status", null, [
                        'refresh_table' => 'doctorsTable'
                    ]);
                } else {
                    jsonResponse(false, 'Failed to update doctor status');
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
                    $whereClause = "WHERE (name LIKE ? OR doctor_id LIKE ? OR phone LIKE ? OR email LIKE ? OR specialization LIKE ? OR hospital LIKE ?)";
                    $searchTerm = "%$search%";
                    $params = array_fill(0, 6, $searchTerm);
                }
                
                // Get total count
                $totalStmt = $pdo->prepare("SELECT COUNT(*) FROM doctors $whereClause");
                $totalStmt->execute($params);
                $totalRecords = $totalStmt->fetchColumn();
                
                // Get filtered data
                $sql = "
                    SELECT id, doctor_id, name, email, phone, specialization, hospital, 
                           consultation_fee, status, experience, created_at
                    FROM doctors 
                    $whereClause
                    ORDER BY created_at DESC
                    LIMIT $start, $length
                ";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $doctors = $stmt->fetchAll();
                
                $data = [];
                foreach ($doctors as $doctor) {
                    $statusBadge = $doctor['status'] === 'active' 
                        ? '<span class="badge badge-success">Active</span>'
                        : '<span class="badge badge-secondary">Inactive</span>';
                    
                    $fee = $doctor['consultation_fee'] ? '$' . number_format($doctor['consultation_fee'], 2) : 'N/A';
                    $experience = $doctor['experience'] ? $doctor['experience'] . ' years' : 'N/A';
                    
                    $actions = "
                        <div class='btn-group' role='group'>
                            <button class='btn btn-sm btn-info' onclick='viewDoctor({$doctor['id']})' title='View'>
                                <i class='fas fa-eye'></i>
                            </button>
                            <button class='btn btn-sm btn-warning' onclick='editDoctor({$doctor['id']})' title='Edit'>
                                <i class='fas fa-edit'></i>
                            </button>
                            <button class='btn btn-sm btn-" . ($doctor['status'] === 'active' ? 'secondary' : 'success') . "' 
                                    onclick='toggleDoctorStatus({$doctor['id']})' 
                                    title='" . ($doctor['status'] === 'active' ? 'Deactivate' : 'Activate') . "'>
                                <i class='fas fa-" . ($doctor['status'] === 'active' ? 'pause' : 'play') . "'></i>
                            </button>
                            <button class='btn btn-sm btn-danger' onclick='deleteDoctor({$doctor['id']})' title='Delete'>
                                <i class='fas fa-trash'></i>
                            </button>
                        </div>
                    ";
                    
                    $data[] = [
                        'id' => $doctor['id'],
                        'doctor_id' => $doctor['doctor_id'],
                        'name' => $doctor['name'],
                        'phone' => $doctor['phone'] ?: 'N/A',
                        'email' => $doctor['email'] ?: 'N/A',
                        'specialization' => $doctor['specialization'],
                        'hospital' => $doctor['hospital'] ?: 'N/A',
                        'fee' => $fee,
                        'experience' => $experience,
                        'status' => $statusBadge,
                        'created_at' => formatDate($doctor['created_at'], 'M j, Y'),
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
                    'total' => $pdo->query("SELECT COUNT(*) FROM doctors")->fetchColumn(),
                    'active' => $pdo->query("SELECT COUNT(*) FROM doctors WHERE status = 'active'")->fetchColumn(),
                    'today' => $pdo->query("SELECT COUNT(*) FROM doctors WHERE DATE(created_at) = CURDATE()")->fetchColumn(),
                    'this_week' => $pdo->query("SELECT COUNT(*) FROM doctors WHERE YEARWEEK(created_at) = YEARWEEK(CURDATE())")->fetchColumn(),
                    'this_month' => $pdo->query("SELECT COUNT(*) FROM doctors WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())")->fetchColumn(),
                    'specializations' => $pdo->query("SELECT COUNT(DISTINCT specialization) FROM doctors WHERE specialization IS NOT NULL AND specialization != ''")->fetchColumn()
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
.doctors-header {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
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
    border-left: 4px solid #28a745;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
}

.stat-card.primary { border-left-color: #28a745; }
.stat-card.success { border-left-color: #17a2b8; }
.stat-card.warning { border-left-color: #ffc107; }
.stat-card.info { border-left-color: #6f42c1; }
.stat-card.secondary { border-left-color: #6c757d; }
.stat-card.danger { border-left-color: #dc3545; }

.action-card {
    background: white;
    border-radius: 15px;
    padding: 1rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 1.5rem;
}

.doctors-table-card {
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
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    border-radius: 15px 15px 0 0;
}

.specialization-tag {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    padding: 0.3rem 0.8rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 500;
}

.consultation-fee {
    font-weight: bold;
    color: #28a745;
}

.doctor-experience {
    color: #6c757d;
    font-style: italic;
}

#doctorsTable tbody tr:hover {
    background: linear-gradient(135deg, rgba(40, 167, 69, 0.05), rgba(32, 201, 151, 0.05));
}
</style>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="doctors-header fade-in">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="display-4 mb-2">
                            <i class="fas fa-user-md mr-3"></i>
                            Doctors Management
                        </h1>
                        <p class="lead mb-0">
                            Comprehensive medical professionals directory and management
                        </p>
                    </div>
                    <div class="col-md-4 text-right">
                        <button class="btn btn-light btn-lg" id="addDoctorBtn">
                            <i class="fas fa-plus mr-2"></i>Add New Doctor
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
                <div class="col-lg-2 col-md-4 col-sm-6 col-12 mb-3">
                    <div class="stat-card primary">
                        <h2 class="text-success mb-2" id="totalDoctors">0</h2>
                        <h6 class="text-muted mb-0">Total Doctors</h6>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 col-12 mb-3">
                    <div class="stat-card success">
                        <h2 class="text-info mb-2" id="activeDoctors">0</h2>
                        <h6 class="text-muted mb-0">Active</h6>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 col-12 mb-3">
                    <div class="stat-card warning">
                        <h2 class="text-warning mb-2" id="todayDoctors">0</h2>
                        <h6 class="text-muted mb-0">Added Today</h6>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 col-12 mb-3">
                    <div class="stat-card info">
                        <h2 class="text-purple mb-2" id="monthDoctors">0</h2>
                        <h6 class="text-muted mb-0">This Month</h6>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 col-12 mb-3">
                    <div class="stat-card secondary">
                        <h2 class="text-secondary mb-2" id="weekDoctors">0</h2>
                        <h6 class="text-muted mb-0">This Week</h6>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 col-12 mb-3">
                    <div class="stat-card danger">
                        <h2 class="text-primary mb-2" id="specializationCount">0</h2>
                        <h6 class="text-muted mb-0">Specializations</h6>
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
                                       placeholder="Search doctors..." data-live-search="doctorsTable">
                            </div>
                            <button class="btn btn-outline-success mr-2" data-refresh-table="doctorsTable">
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
                        <button class="btn btn-success" id="addDoctorBtn2">
                            <i class="fas fa-user-plus mr-2"></i>Add Doctor
                        </button>
                    </div>
                </div>
            </div>

            <!-- Doctors Table -->
            <div class="doctors-table-card">
                <div class="card-header bg-light">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-list mr-2"></i>Medical Professionals Directory
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="doctorsTable" class="table table-hover mb-0" style="width: 100%">
                            <thead>
                                <tr>
                                    <th>Doctor ID</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th>Specialization</th>
                                    <th>Hospital</th>
                                    <th>Fee</th>
                                    <th>Experience</th>
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

<!-- Add Doctor Modal -->
<div class="modal fade" id="addDoctorModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus mr-2"></i>Add New Doctor
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="addDoctorForm" data-auto-submit="true" data-action="add">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required>
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
                                <label for="specialization">Specialization <span class="text-danger">*</span></label>
                                <select class="form-control" id="specialization" name="specialization" required>
                                    <option value="">Select Specialization</option>
                                    <option value="Cardiology">Cardiology</option>
                                    <option value="Dermatology">Dermatology</option>
                                    <option value="Endocrinology">Endocrinology</option>
                                    <option value="Gastroenterology">Gastroenterology</option>
                                    <option value="General Medicine">General Medicine</option>
                                    <option value="Gynecology">Gynecology</option>
                                    <option value="Neurology">Neurology</option>
                                    <option value="Oncology">Oncology</option>
                                    <option value="Orthopedics">Orthopedics</option>
                                    <option value="Pediatrics">Pediatrics</option>
                                    <option value="Psychiatry">Psychiatry</option>
                                    <option value="Radiology">Radiology</option>
                                    <option value="Surgery">Surgery</option>
                                    <option value="Urology">Urology</option>
                                    <option value="Other">Other</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="qualification">Qualification</label>
                                <input type="text" class="form-control" id="qualification" name="qualification" placeholder="e.g., MBBS, MD">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="experience">Experience (Years)</label>
                                <input type="number" class="form-control" id="experience" name="experience" min="0" max="50">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="licenseNumber">License Number</label>
                                <input type="text" class="form-control" id="licenseNumber" name="license_number">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="consultationFee">Consultation Fee ($)</label>
                                <input type="number" class="form-control" id="consultationFee" name="consultation_fee" min="0" step="0.01">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="hospital">Hospital/Clinic</label>
                        <input type="text" class="form-control" id="hospital" name="hospital">
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="availableDays">Available Days</label>
                                <input type="text" class="form-control" id="availableDays" name="available_days" placeholder="e.g., Mon-Fri">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="availableTime">Available Time</label>
                                <input type="text" class="form-control" id="availableTime" name="available_time" placeholder="e.g., 9:00 AM - 5:00 PM">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes">Additional Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save mr-1"></i>Save Doctor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Doctor Modal -->
<div class="modal fade" id="editDoctorModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-edit mr-2"></i>Edit Doctor
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="editDoctorForm" data-auto-submit="true" data-action="edit">
                <input type="hidden" id="editDoctorId" name="id">
                <div class="modal-body">
                    <!-- Same fields as add form but with edit prefix -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editName">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="editName" name="name" required>
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
                                <label for="editSpecialization">Specialization <span class="text-danger">*</span></label>
                                <select class="form-control" id="editSpecialization" name="specialization" required>
                                    <option value="">Select Specialization</option>
                                    <option value="Cardiology">Cardiology</option>
                                    <option value="Dermatology">Dermatology</option>
                                    <option value="Endocrinology">Endocrinology</option>
                                    <option value="Gastroenterology">Gastroenterology</option>
                                    <option value="General Medicine">General Medicine</option>
                                    <option value="Gynecology">Gynecology</option>
                                    <option value="Neurology">Neurology</option>
                                    <option value="Oncology">Oncology</option>
                                    <option value="Orthopedics">Orthopedics</option>
                                    <option value="Pediatrics">Pediatrics</option>
                                    <option value="Psychiatry">Psychiatry</option>
                                    <option value="Radiology">Radiology</option>
                                    <option value="Surgery">Surgery</option>
                                    <option value="Urology">Urology</option>
                                    <option value="Other">Other</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editQualification">Qualification</label>
                                <input type="text" class="form-control" id="editQualification" name="qualification" placeholder="e.g., MBBS, MD">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editExperience">Experience (Years)</label>
                                <input type="number" class="form-control" id="editExperience" name="experience" min="0" max="50">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editLicenseNumber">License Number</label>
                                <input type="text" class="form-control" id="editLicenseNumber" name="license_number">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editConsultationFee">Consultation Fee ($)</label>
                                <input type="number" class="form-control" id="editConsultationFee" name="consultation_fee" min="0" step="0.01">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="editHospital">Hospital/Clinic</label>
                        <input type="text" class="form-control" id="editHospital" name="hospital">
                    </div>
                    
                    <div class="form-group">
                        <label for="editAddress">Address</label>
                        <textarea class="form-control" id="editAddress" name="address" rows="2"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editAvailableDays">Available Days</label>
                                <input type="text" class="form-control" id="editAvailableDays" name="available_days" placeholder="e.g., Mon-Fri">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editAvailableTime">Available Time</label>
                                <input type="text" class="form-control" id="editAvailableTime" name="available_time" placeholder="e.g., 9:00 AM - 5:00 PM">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="editStatus">Status</label>
                        <select class="form-control" id="editStatus" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="editNotes">Additional Notes</label>
                        <textarea class="form-control" id="editNotes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save mr-1"></i>Update Doctor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Doctor Modal -->
<div class="modal fade" id="viewDoctorModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-md mr-2"></i>Doctor Profile
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="doctorDetailsContent">
                <!-- Doctor details loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Close
                </button>
                <button type="button" class="btn btn-success" onclick="printDoctorDetails()">
                    <i class="fas fa-print mr-1"></i>Print Profile
                </button>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
// Global variables
let doctorsTable;

$(document).ready(function() {
    // Initialize page
    initializeDoctorsPage();
    
    // Setup event listeners
    setupEventListeners();
    
    // Load initial data
    loadStatistics();
    
    // Show welcome message
    notify('success', 'Doctors management system loaded!', 'Welcome');
});

function initializeDoctorsPage() {
    // Initialize DataTable
    doctorsTable = $('#doctorsTable').DataTable({
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
                notify('error', 'Failed to load doctors data', 'Table Error');
                console.error('DataTable error:', error);
            }
        },
        columns: [
            { data: 'doctor_id', width: '8%' },
            { data: 'name', width: '12%' },
            { data: 'phone', width: '10%' },
            { data: 'email', width: '12%' },
            { data: 'specialization', width: '12%' },
            { data: 'hospital', width: '12%' },
            { data: 'fee', width: '8%' },
            { data: 'experience', width: '8%' },
            { data: 'status', width: '8%' },
            { data: 'created_at', width: '8%' },
            { data: 'actions', width: '12%', orderable: false, searchable: false }
        ],
        order: [[9, 'desc']], // Sort by created_at
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        responsive: true,
        language: {
            processing: '<i class="fas fa-spinner fa-spin"></i> Loading doctors...',
            emptyTable: 'No doctors found in the system',
            zeroRecords: 'No matching doctors found',
            lengthMenu: 'Show _MENU_ doctors per page',
            info: 'Showing _START_ to _END_ of _TOTAL_ doctors',
            infoEmpty: 'No doctors available',
            infoFiltered: '(filtered from _MAX_ total doctors)'
        },
        drawCallback: function() {
            // Add animations to new rows
            $('#doctorsTable tbody tr').addClass('fade-in');
        },
        initComplete: function() {
            notify('info', 'Doctors table loaded successfully!');
        }
    });
}

function setupEventListeners() {
    // Add doctor buttons
    $('#addDoctorBtn, #addDoctorBtn2').click(function() {
        $('#addDoctorModal').modal('show');
        $('#addDoctorForm')[0].reset();
        $('.form-control').removeClass('is-valid is-invalid');
    });
    
    // Modal events
    $('#addDoctorModal').on('hidden.bs.modal', function() {
        $('#addDoctorForm')[0].reset();
        $('.form-control').removeClass('is-valid is-invalid');
    });
    
    $('#editDoctorModal').on('hidden.bs.modal', function() {
        $('#editDoctorForm')[0].reset();
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
    $('#totalDoctors').text(stats.total || 0);
    $('#activeDoctors').text(stats.active || 0);
    $('#todayDoctors').text(stats.today || 0);
    $('#monthDoctors').text(stats.this_month || 0);
    $('#weekDoctors').text(stats.this_week || 0);
    $('#specializationCount').text(stats.specializations || 0);
}

// Doctor action functions
function viewDoctor(id) {
    showLoading('Loading doctor profile...');
    
    $.ajax({
        url: window.location.href,
        type: 'POST',
        data: { action: 'get', id: id },
        dataType: 'json',
        success: function(response) {
            hideLoading();
            if (response.success) {
                displayDoctorDetails(response.data);
                $('#viewDoctorModal').modal('show');
            } else {
                notify('error', response.message, 'Error');
            }
        },
        error: function() {
            hideLoading();
            notify('error', 'Failed to load doctor profile', 'Error');
        }
    });
}

function editDoctor(id) {
    showLoading('Loading doctor data...');
    
    $.ajax({
        url: window.location.href,
        type: 'POST',
        data: { action: 'get', id: id },
        dataType: 'json',
        success: function(response) {
            hideLoading();
            if (response.success) {
                populateEditForm(response.data);
                $('#editDoctorModal').modal('show');
            } else {
                notify('error', response.message, 'Error');
            }
        },
        error: function() {
            hideLoading();
            notify('error', 'Failed to load doctor data', 'Error');
        }
    });
}

function toggleDoctorStatus(id) {
    DynamicUtils.confirm(
        'Are you sure you want to change this doctor\'s status?',
        function(result) {
            if (result) {
                showLoading('Updating status...');
                
                $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: { action: 'toggle_status', id: id },
                    dataType: 'json',
                    success: function(response) {
                        hideLoading();
                        if (response.success) {
                            notify('success', response.message, 'Success');
                            doctorsTable.ajax.reload(null, false);
                            loadStatistics();
                        } else {
                            notify('error', response.message, 'Error');
                        }
                    },
                    error: function() {
                        hideLoading();
                        notify('error', 'Failed to update status', 'Error');
                    }
                });
            }
        },
        {
            title: 'Confirm Status Change',
            type: 'warning',
            confirmText: 'Change Status',
            cancelText: 'Cancel'
        }
    );
}

function deleteDoctor(id) {
    DynamicUtils.confirm(
        'Are you sure you want to delete this doctor? This action cannot be undone.',
        function(result) {
            if (result) {
                showLoading('Deleting doctor...');
                
                $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: { action: 'delete', id: id },
                    dataType: 'json',
                    success: function(response) {
                        hideLoading();
                        if (response.success) {
                            notify('success', response.message, 'Success');
                            doctorsTable.ajax.reload(null, false);
                            loadStatistics();
                        } else {
                            notify('error', response.message, 'Error');
                        }
                    },
                    error: function() {
                        hideLoading();
                        notify('error', 'Failed to delete doctor', 'Error');
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

function displayDoctorDetails(doctor) {
    const experience = doctor.experience ? `${doctor.experience} years` : 'Not specified';
    const fee = doctor.consultation_fee ? `$${parseFloat(doctor.consultation_fee).toFixed(2)}` : 'Not specified';
    const status = doctor.status === 'active' ? 'Active' : 'Inactive';
    const statusClass = doctor.status === 'active' ? 'badge-success' : 'badge-secondary';
    
    let content = `
        <div class="row">
            <div class="col-md-6">
                <h5 class="text-success mb-3">Professional Information</h5>
                <table class="table table-borderless">
                    <tr><th width="40%">Doctor ID:</th><td><span class="badge badge-info">${doctor.doctor_id}</span></td></tr>
                    <tr><th>Full Name:</th><td><strong>${doctor.name}</strong></td></tr>
                    <tr><th>Specialization:</th><td><span class="specialization-tag">${doctor.specialization}</span></td></tr>
                    <tr><th>Qualification:</th><td>${doctor.qualification || 'Not specified'}</td></tr>
                    <tr><th>Experience:</th><td class="doctor-experience">${experience}</td></tr>
                    <tr><th>License Number:</th><td>${doctor.license_number || 'Not specified'}</td></tr>
                    <tr><th>Status:</th><td><span class="badge ${statusClass}">${status}</span></td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h5 class="text-success mb-3">Contact & Practice Information</h5>
                <table class="table table-borderless">
                    <tr><th width="40%">Phone:</th><td>${doctor.phone || 'Not provided'}</td></tr>
                    <tr><th>Email:</th><td>${doctor.email || 'Not provided'}</td></tr>
                    <tr><th>Hospital/Clinic:</th><td>${doctor.hospital || 'Not specified'}</td></tr>
                    <tr><th>Address:</th><td>${doctor.address || 'Not provided'}</td></tr>
                    <tr><th>Consultation Fee:</th><td class="consultation-fee">${fee}</td></tr>
                    <tr><th>Available Days:</th><td>${doctor.available_days || 'Not specified'}</td></tr>
                    <tr><th>Available Time:</th><td>${doctor.available_time || 'Not specified'}</td></tr>
                </table>
            </div>
        </div>
    `;
    
    if (doctor.notes) {
        content += `
            <div class="row mt-3">
                <div class="col-12">
                    <h5 class="text-success mb-3">Additional Notes</h5>
                    <div class="alert alert-info">
                        ${doctor.notes}
                    </div>
                </div>
            </div>
        `;
    }
    
    content += `
        <div class="row mt-3">
            <div class="col-12">
                <small class="text-muted">
                    <i class="fas fa-calendar-plus mr-1"></i>Registered: ${formatDate(doctor.created_at)}
                    ${doctor.updated_at !== doctor.created_at ? `<br><i class="fas fa-edit mr-1"></i>Last updated: ${formatDate(doctor.updated_at)}` : ''}
                </small>
            </div>
        </div>
    `;
    
    $('#doctorDetailsContent').html(content);
}

function populateEditForm(doctor) {
    $('#editDoctorId').val(doctor.id);
    $('#editName').val(doctor.name);
    $('#editEmail').val(doctor.email);
    $('#editPhone').val(doctor.phone);
    $('#editSpecialization').val(doctor.specialization);
    $('#editQualification').val(doctor.qualification);
    $('#editExperience').val(doctor.experience);
    $('#editLicenseNumber').val(doctor.license_number);
    $('#editConsultationFee').val(doctor.consultation_fee);
    $('#editHospital').val(doctor.hospital);
    $('#editAddress').val(doctor.address);
    $('#editAvailableDays').val(doctor.available_days);
    $('#editAvailableTime').val(doctor.available_time);
    $('#editStatus').val(doctor.status);
    $('#editNotes').val(doctor.notes);
}

function printDoctorDetails() {
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

// Override DynamicUtils handleResponse for doctors-specific actions
const originalHandleResponse = DynamicUtils.handleResponse;
DynamicUtils.handleResponse = function(response, context) {
    originalHandleResponse.call(this, response, context);
    
    if (response.success && response.refresh_table === 'doctorsTable') {
        doctorsTable.ajax.reload(null, false);
        loadStatistics();
    }
};
</script>
