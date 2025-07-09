<?php
// Set page title
$page_title = 'Patients Management';

// Include header and sidebar
include 'includes/header.php';
include 'includes/sidebar.php';

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add':
            $name = trim($_POST['name'] ?? '');
            $phone = trim($_POST['phone'] ?? '') ?: null;
            $email = trim($_POST['email'] ?? '') ?: null;
            $date_of_birth = $_POST['date_of_birth'] ?: null;
            $gender = $_POST['gender'] ?: null;
            $address = trim($_POST['address'] ?? '') ?: null;
            
            if (empty($name)) {
                $response = ['success' => false, 'message' => 'Patient name is required'];
                break;
            }
            
            try {
                // Generate unique patient ID
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM patients");
                $count = $stmt->fetch()['count'] + 1;
                $patient_id = 'PAT-' . str_pad($count, 4, '0', STR_PAD_LEFT);
                
                // Check if patient ID already exists
                while (true) {
                    $checkStmt = $pdo->prepare("SELECT id FROM patients WHERE patient_id = ?");
                    $checkStmt->execute([$patient_id]);
                    if (!$checkStmt->fetch()) break;
                    $count++;
                    $patient_id = 'PAT-' . str_pad($count, 4, '0', STR_PAD_LEFT);
                }
                
                $stmt = $pdo->prepare("INSERT INTO patients (patient_id, name, phone, email, date_of_birth, gender, address) VALUES (?, ?, ?, ?, ?, ?, ?)");
                
                if ($stmt->execute([$patient_id, $name, $phone, $email, $date_of_birth, $gender, $address])) {
                    $response = ['success' => true, 'message' => 'Patient added successfully'];
                } else {
                    $response = ['success' => false, 'message' => 'Failed to add patient'];
                }
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
            }
            break;
            
        case 'edit':
            $id = $_POST['id'] ?? '';
            $name = trim($_POST['name'] ?? '');
            $phone = trim($_POST['phone'] ?? '') ?: null;
            $email = trim($_POST['email'] ?? '') ?: null;
            $date_of_birth = $_POST['date_of_birth'] ?: null;
            $gender = $_POST['gender'] ?: null;
            $address = trim($_POST['address'] ?? '') ?: null;
            
            if (empty($id) || empty($name)) {
                $response = ['success' => false, 'message' => 'Patient ID and name are required'];
                break;
            }
            
            try {
                $stmt = $pdo->prepare("UPDATE patients SET name = ?, phone = ?, email = ?, date_of_birth = ?, gender = ?, address = ?, updated_at = NOW() WHERE id = ?");
                
                if ($stmt->execute([$name, $phone, $email, $date_of_birth, $gender, $address, $id])) {
                    $response = ['success' => true, 'message' => 'Patient updated successfully'];
                } else {
                    $response = ['success' => false, 'message' => 'Failed to update patient'];
                }
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
            }
            break;
            
        case 'delete':
            $id = $_POST['id'] ?? '';
            
            if (empty($id)) {
                $response = ['success' => false, 'message' => 'Patient ID is required'];
                break;
            }
            
            try {
                $stmt = $pdo->prepare("DELETE FROM patients WHERE id = ?");
                
                if ($stmt->execute([$id])) {
                    $response = ['success' => true, 'message' => 'Patient deleted successfully'];
                } else {
                    $response = ['success' => false, 'message' => 'Failed to delete patient'];
                }
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
            }
            break;
            
        case 'get':
            $id = $_POST['id'] ?? '';
            
            if (empty($id)) {
                $response = ['success' => false, 'message' => 'Patient ID is required'];
                break;
            }
            
            try {
                $stmt = $pdo->prepare("SELECT * FROM patients WHERE id = ?");
                $stmt->execute([$id]);
                $patient = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($patient) {
                    $response = ['success' => true, 'data' => $patient];
                } else {
                    $response = ['success' => false, 'message' => 'Patient not found'];
                }
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
            }
            break;
            
        case 'datatable':
            // DataTables server-side processing
            try {
                $draw = intval($_POST['draw']);
                $start = intval($_POST['start']);
                $length = intval($_POST['length']);
                $search = $_POST['search']['value'];
                
                // Total records count
                $totalRecords = $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();
                
                // Search query
                $searchQuery = "";
                $params = [];
                if (!empty($search)) {
                    $searchQuery = " WHERE name LIKE ? OR patient_id LIKE ? OR phone LIKE ? OR email LIKE ?";
                    $searchTerm = "%$search%";
                    $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
                }
                
                // Filtered records count
                $filteredRecords = $pdo->prepare("SELECT COUNT(*) FROM patients" . $searchQuery);
                $filteredRecords->execute($params);
                $filteredRecords = $filteredRecords->fetchColumn();
                
                // Get records
                $sql = "SELECT id, patient_id, name, phone, email, gender, date_of_birth, created_at FROM patients" . $searchQuery . " ORDER BY created_at DESC LIMIT $start, $length";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $data = [];
                foreach ($patients as $patient) {
                    $age = 'N/A';
                    if ($patient['date_of_birth']) {
                        $age = date_diff(date_create($patient['date_of_birth']), date_create('today'))->y . ' years';
                    }
                    
                    $actions = '
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-info" onclick="viewPatient(' . $patient['id'] . ')" title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-primary" onclick="editPatient(' . $patient['id'] . ')" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="deletePatient(' . $patient['id'] . ')" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    ';
                    
                    $data[] = [
                        'patient_id' => '<strong>' . htmlspecialchars($patient['patient_id']) . '</strong>',
                        'name' => htmlspecialchars($patient['name']),
                        'contact' => htmlspecialchars($patient['phone'] ?? 'N/A') . '<br><small>' . htmlspecialchars($patient['email'] ?? 'N/A') . '</small>',
                        'gender' => ucfirst(htmlspecialchars($patient['gender'] ?? 'N/A')),
                        'age' => $age,
                        'created_at' => date('M d, Y', strtotime($patient['created_at'])),
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $page_title; ?> | PathLab Pro</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap 4 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- AdminLTE -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    
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
        .patient-contact {
            font-size: 0.9em;
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
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .stats-icon {
            font-size: 2rem;
            color: #007bff;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">
    
    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Page Header -->
        <div class="page-header">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h1 class="mb-0">
                            <i class="fas fa-user-injured mr-3"></i>
                            Patient Management System
                        </h1>
                        <p class="mb-0 mt-2">Manage patient records efficiently</p>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="stats-card">
                                    <div class="stats-icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <h4 id="totalPatients">-</h4>
                                    <p class="mb-0">Total Patients</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="stats-card">
                                    <div class="stats-icon">
                                        <i class="fas fa-user-plus"></i>
                                    </div>
                                    <h4 id="todayRegistrations">-</h4>
                                    <p class="mb-0">Today's Registrations</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <!-- Action Buttons -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-body py-2">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-search mr-2 text-muted"></i>
                                    <span class="text-muted mr-3">Quick Actions:</span>
                                    <button class="btn btn-success btn-sm mr-2" id="addPatientBtn">
                                        <i class="fas fa-plus mr-1"></i>Add Patient
                                    </button>
                                    <button class="btn btn-info btn-sm mr-2" id="refreshBtn">
                                        <i class="fas fa-sync-alt mr-1"></i>Refresh
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
                                        <button class="btn btn-outline-secondary btn-sm" type="button">
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
                                    <i class="fas fa-list mr-2"></i>Patients Database
                                </h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="patientsTable" class="table table-striped table-hover">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Patient ID</th>
                                                <th>Full Name</th>
                                                <th>Contact Info</th>
                                                <th>Gender</th>
                                                <th>Age</th>
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
        <div class="modal-dialog modal-lg" role="document">
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
                                    <label for="name">Full Name <span class="required">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                    <small class="form-text text-muted">Enter patient's full name</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" placeholder="e.g., +1-234-567-8900">
                                    <small class="form-text text-muted">Optional contact number</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="patient@example.com">
                                    <small class="form-text text-muted">Optional email address</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date_of_birth">Date of Birth</label>
                                    <input type="date" class="form-control" id="date_of_birth" name="date_of_birth">
                                    <small class="form-text text-muted">Used to calculate age</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
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
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="address">Address</label>
                                    <textarea class="form-control" id="address" name="address" rows="3" placeholder="Enter complete address"></textarea>
                                </div>
                            </div>
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
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title">
                        <i class="fas fa-edit mr-2"></i>Edit Patient
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editPatientForm">
                    <input type="hidden" id="edit_id" name="id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_name">Full Name <span class="required">*</span></label>
                                    <input type="text" class="form-control" id="edit_name" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_phone">Phone Number</label>
                                    <input type="tel" class="form-control" id="edit_phone" name="phone">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_email">Email Address</label>
                                    <input type="email" class="form-control" id="edit_email" name="email">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_date_of_birth">Date of Birth</label>
                                    <input type="date" class="form-control" id="edit_date_of_birth" name="date_of_birth">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
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
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_address">Address</label>
                                    <textarea class="form-control" id="edit_address" name="address" rows="3"></textarea>
                                </div>
                            </div>
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
                <div class="modal-header bg-info">
                    <h5 class="modal-title">
                        <i class="fas fa-eye mr-2"></i>Patient Details
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
        const table = $('#patientsTable').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "patients.php",
                "type": "POST",
                "data": function(d) {
                    d.action = 'datatable';
                }
            },
            "columns": [
                { "data": "patient_id", "width": "12%" },
                { "data": "name", "width": "20%" },
                { "data": "contact", "width": "20%" },
                { "data": "gender", "width": "8%" },
                { "data": "age", "width": "8%" },
                { "data": "created_at", "width": "12%" },
                { "data": "actions", "width": "20%", "orderable": false, "searchable": false }
            ],
            "order": [[5, "desc"]],
            "pageLength": 25,
            "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
            "responsive": true,
            "language": {
                "processing": "<i class='fas fa-spinner fa-spin'></i> Loading patients...",
                "emptyTable": "No patients found in the system",
                "zeroRecords": "No matching patients found"
            }
        });
        
        // Global search
        $('#globalSearch').on('keyup', function() {
            table.search(this.value).draw();
        });
        
        // Button event handlers
        $('#addPatientBtn').click(function() {
            $('#addPatientModal').modal('show');
        });
        
        $('#refreshBtn').click(function() {
            table.ajax.reload(null, false);
            loadStats();
            toastr.info('Table refreshed');
        });
        
        // Add Patient Form Submission
        $('#addPatientForm').submit(function(e) {
            e.preventDefault();
            
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();
            
            submitBtn.html('<i class="fas fa-spinner fa-spin mr-1"></i>Adding...').prop('disabled', true);
            
            $.ajax({
                url: 'patients.php',
                type: 'POST',
                data: $(this).serialize() + '&action=add',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('#addPatientModal').modal('hide');
                        $('#addPatientForm')[0].reset();
                        table.ajax.reload(null, false);
                        loadStats();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('An error occurred while adding the patient');
                },
                complete: function() {
                    submitBtn.html(originalText).prop('disabled', false);
                }
            });
        });
        
        // Edit Patient Form Submission
        $('#editPatientForm').submit(function(e) {
            e.preventDefault();
            
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();
            
            submitBtn.html('<i class="fas fa-spinner fa-spin mr-1"></i>Updating...').prop('disabled', true);
            
            $.ajax({
                url: 'patients.php',
                type: 'POST',
                data: $(this).serialize() + '&action=edit',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('#editPatientModal').modal('hide');
                        table.ajax.reload(null, false);
                        loadStats();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('An error occurred while updating the patient');
                },
                complete: function() {
                    submitBtn.html(originalText).prop('disabled', false);
                }
            });
        });
        
        // Clear forms when modals are hidden
        $('#addPatientModal').on('hidden.bs.modal', function() {
            $('#addPatientForm')[0].reset();
        });
        
        $('#editPatientModal').on('hidden.bs.modal', function() {
            $('#editPatientForm')[0].reset();
        });
        
        // Load statistics
        loadStats();
    });

    function loadStats() {
        $.ajax({
            url: 'patients.php',
            type: 'POST',
            data: { action: 'datatable', start: 0, length: 0 },
            dataType: 'json',
            success: function(response) {
                if (response.recordsTotal !== undefined) {
                    $('#totalPatients').text(response.recordsTotal);
                }
            }
        });
        
        // Get today's registrations (placeholder - you can implement this)
        $('#todayRegistrations').text('0');
    }

    function viewPatient(id) {
        $.ajax({
            url: 'patients.php',
            type: 'POST',
            data: { action: 'get', id: id },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const patient = response.data;
                    const age = patient.date_of_birth ? 
                        new Date().getFullYear() - new Date(patient.date_of_birth).getFullYear() + ' years' : 'N/A';
                    
                    const content = `
                        <div class="row">
                            <div class="col-md-8">
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="font-weight-bold" style="width: 30%;">Patient ID:</td>
                                        <td><span class="badge badge-primary">${patient.patient_id}</span></td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Full Name:</td>
                                        <td>${patient.name}</td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Phone Number:</td>
                                        <td>${patient.phone || '<span class="text-muted">Not provided</span>'}</td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Email Address:</td>
                                        <td>${patient.email || '<span class="text-muted">Not provided</span>'}</td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Gender:</td>
                                        <td>${patient.gender ? patient.gender.charAt(0).toUpperCase() + patient.gender.slice(1) : '<span class="text-muted">Not specified</span>'}</td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Age:</td>
                                        <td>${age}</td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Date of Birth:</td>
                                        <td>${patient.date_of_birth || '<span class="text-muted">Not provided</span>'}</td>
                                    </tr>
                                    <tr>
                                        <td class="font-weight-bold">Registered:</td>
                                        <td>${new Date(patient.created_at).toLocaleDateString('en-US', { 
                                            year: 'numeric', 
                                            month: 'long', 
                                            day: 'numeric',
                                            hour: '2-digit',
                                            minute: '2-digit'
                                        })}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-4">
                                <h6 class="font-weight-bold mb-3">Address:</h6>
                                <div class="bg-light p-3 rounded">
                                    ${patient.address || '<span class="text-muted">No address provided</span>'}
                                </div>
                            </div>
                        </div>
                    `;
                    
                    $('#patientDetailsContent').html(content);
                    $('#viewPatientModal').modal('show');
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('An error occurred while loading patient details');
            }
        });
    }

    function editPatient(id) {
        $.ajax({
            url: 'patients.php',
            type: 'POST',
            data: { action: 'get', id: id },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const patient = response.data;
                    $('#edit_id').val(patient.id);
                    $('#edit_name').val(patient.name);
                    $('#edit_phone').val(patient.phone || '');
                    $('#edit_email').val(patient.email || '');
                    $('#edit_date_of_birth').val(patient.date_of_birth || '');
                    $('#edit_gender').val(patient.gender || '');
                    $('#edit_address').val(patient.address || '');
                    $('#editPatientModal').modal('show');
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('An error occurred while loading patient details');
            }
        });
    }

    function deletePatient(id) {
        if (confirm('Are you sure you want to delete this patient?\n\nThis action cannot be undone and will permanently remove all patient data.')) {
            $.ajax({
                url: 'patients.php',
                type: 'POST',
                data: { action: 'delete', id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('#patientsTable').DataTable().ajax.reload(null, false);
                        loadStats();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('An error occurred while deleting the patient');
                }
            });
        }
    }
    </script>
</body>
</html>
