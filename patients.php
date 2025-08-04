
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
                    $response = ['success' => true, 'message' => 'Patient added successfully', 'refresh_table' => true];
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
                    $response = ['success' => true, 'message' => 'Patient updated successfully', 'refresh_table' => true];
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
                    $response = ['success' => true, 'message' => 'Patient deleted successfully', 'refresh_table' => true];
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
    }
    
    if (isset($response)) {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
  <!-- Content Header -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">
            <i class="fas fa-user-injured mr-2"></i>Patients Management
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
      <!-- Statistics Row -->
      <div class="row mb-4">
        <div class="col-lg-3 col-6">
          <div class="small-box bg-info">
            <div class="inner">
              <h3 id="totalPatients">0</h3>
              <p>Total Patients</p>
            </div>
            <div class="icon">
              <i class="fas fa-users"></i>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-success">
            <div class="inner">
              <h3 id="todayRegistrations">0</h3>
              <p>Today's Registrations</p>
            </div>
            <div class="icon">
              <i class="fas fa-user-plus"></i>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-warning">
            <div class="inner">
              <h3 id="malePatients">0</h3>
              <p>Male Patients</p>
            </div>
            <div class="icon">
              <i class="fas fa-mars"></i>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-purple">
            <div class="inner">
              <h3 id="femalePatients">0</h3>
              <p>Female Patients</p>
            </div>
            <div class="icon">
              <i class="fas fa-venus"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Action Buttons and Filters -->
      <div class="row mb-3">
        <div class="col-md-6">
          <div class="input-group">
            <input type="text" class="form-control" id="searchPatients" placeholder="Search patients by name, phone, or email...">
            <div class="input-group-append">
              <button class="btn btn-outline-secondary" type="button" onclick="searchPatients()">
                <i class="fas fa-search"></i>
              </button>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="row">
            <div class="col-md-4">
              <select class="form-control" id="genderFilter" onchange="filterPatients()">
                <option value="">All Genders</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
              </select>
            </div>
            <div class="col-md-4">
              <select class="form-control" id="ageFilter" onchange="filterPatients()">
                <option value="">All Ages</option>
                <option value="0-18">0-18 years</option>
                <option value="19-40">19-40 years</option>
                <option value="41-60">41-60 years</option>
                <option value="60+">60+ years</option>
              </select>
            </div>
            <div class="col-md-4">
              <button class="btn btn-success btn-block" onclick="showAddPatientModal()">
                <i class="fas fa-plus mr-1"></i>Add Patient
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Patients Table -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">
            <i class="fas fa-list mr-2"></i>Patients Database
          </h3>
          <div class="card-tools">
            <button type="button" class="btn btn-tool" onclick="refreshPatientsTable()">
              <i class="fas fa-sync-alt"></i>
            </button>
            <button type="button" class="btn btn-tool" onclick="exportPatients()">
              <i class="fas fa-download"></i>
            </button>
          </div>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table id="patientsTable" class="table table-bordered table-striped table-hover">
              <thead class="thead-light">
                <tr>
                  <th>Patient ID</th>
                  <th>Full Name</th>
                  <th>Contact</th>
                  <th>Gender</th>
                  <th>Age</th>
                  <th>Registered</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <!-- Data will be loaded via JavaScript -->
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
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">
          <i class="fas fa-user-plus mr-2"></i>Add New Patient
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <form id="addPatientForm">
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
                <label for="phone">Phone Number</label>
                <input type="tel" class="form-control" id="phone" name="phone" placeholder="(123) 456-7890">
                <div class="invalid-feedback"></div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="patient@example.com">
                <div class="invalid-feedback"></div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="date_of_birth">Date of Birth</label>
                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth">
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
                <textarea class="form-control" id="address" name="address" rows="2" placeholder="Complete address"></textarea>
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
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">
          <i class="fas fa-edit mr-2"></i>Edit Patient
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <form id="editPatientForm">
        <input type="hidden" id="edit_id" name="id">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_name">Full Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="edit_name" name="name" required>
                <div class="invalid-feedback"></div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_phone">Phone Number</label>
                <input type="tel" class="form-control" id="edit_phone" name="phone">
                <div class="invalid-feedback"></div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_email">Email Address</label>
                <input type="email" class="form-control" id="edit_email" name="email">
                <div class="invalid-feedback"></div>
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
                <textarea class="form-control" id="edit_address" name="address" rows="2"></textarea>
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

<script>
$(document).ready(function() {
    // Initialize page
    loadPatientsStats();
    initializePatientsTable();
    setupFormValidation();
    
    // Search on Enter
    $('#searchPatients').on('keypress', function(e) {
        if (e.which === 13) {
            searchPatients();
        }
    });
    
    // Auto-refresh every 60 seconds
    setInterval(loadPatientsStats, 60000);
});

// Load patients statistics
function loadPatientsStats() {
    $.ajax({
        url: 'api/patients_api.php',
        method: 'GET',
        data: { action: 'stats' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const stats = response.data;
                $('#totalPatients').text(stats.total || 0);
                $('#todayRegistrations').text(stats.today || 0);
                $('#malePatients').text(stats.male || 0);
                $('#femalePatients').text(stats.female || 0);
            }
        },
        error: function() {
            console.log('Error loading patients statistics');
        }
    });
}

// Initialize patients table
function initializePatientsTable() {
    $('#patientsTable').DataTable({
        processing: true,
        serverSide: false,
        responsive: true,
        ajax: {
            url: 'api/patients_api.php',
            data: { action: 'list' }
        },
        columns: [
            { data: 'patient_id', width: '12%' },
            { data: 'name', width: '20%' },
            { 
                data: null,
                width: '20%',
                render: function(data) {
                    return `${data.phone || 'N/A'}<br><small class="text-muted">${data.email || 'N/A'}</small>`;
                }
            },
            { 
                data: 'gender',
                width: '8%',
                render: function(data) {
                    return data ? data.charAt(0).toUpperCase() + data.slice(1) : 'N/A';
                }
            },
            { 
                data: 'date_of_birth',
                width: '8%',
                render: function(data) {
                    if (!data) return 'N/A';
                    const age = calculateAge(data);
                    return age + ' years';
                }
            },
            { 
                data: 'created_at',
                width: '12%',
                render: function(data) {
                    return new Date(data).toLocaleDateString();
                }
            },
            {
                data: null,
                width: '20%',
                orderable: false,
                render: function(data) {
                    return `
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-info btn-sm" onclick="viewPatient(${data.id})" title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-primary btn-sm" onclick="editPatient(${data.id})" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" onclick="deletePatient(${data.id})" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        order: [[5, 'desc']],
        pageLength: 25,
        language: {
            processing: '<i class="fas fa-spinner fa-spin"></i> Loading patients...',
            emptyTable: 'No patients found',
            zeroRecords: 'No matching patients found'
        }
    });
}

// Setup form validation
function setupFormValidation() {
    // Add patient form
    $('#addPatientForm').on('submit', function(e) {
        e.preventDefault();
        
        if (validatePatientForm(this)) {
            submitPatientForm('add', $(this));
        }
    });
    
    // Edit patient form
    $('#editPatientForm').on('submit', function(e) {
        e.preventDefault();
        
        if (validatePatientForm(this)) {
            submitPatientForm('edit', $(this));
        }
    });
    
    // Real-time validation
    $('input[type="email"]').on('blur', function() {
        validateEmail($(this));
    });
    
    $('input[type="tel"]').on('input', function() {
        formatPhoneNumber($(this));
    });
}

// Validate patient form
function validatePatientForm(form) {
    let isValid = true;
    const nameField = $(form).find('input[name="name"]');
    const emailField = $(form).find('input[name="email"]');
    
    // Reset validation
    $(form).find('.form-control').removeClass('is-invalid is-valid');
    
    // Validate name
    if (!nameField.val().trim()) {
        showFieldError(nameField, 'Patient name is required');
        isValid = false;
    } else {
        showFieldSuccess(nameField);
    }
    
    // Validate email if provided
    if (emailField.val() && !validateEmail(emailField)) {
        isValid = false;
    }
    
    return isValid;
}

// Show field error
function showFieldError(field, message) {
    field.addClass('is-invalid');
    field.siblings('.invalid-feedback').text(message);
}

// Show field success
function showFieldSuccess(field) {
    field.addClass('is-valid');
}

// Validate email
function validateEmail(field) {
    const email = field.val();
    if (!email) return true;
    
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const isValid = emailRegex.test(email);
    
    if (!isValid) {
        showFieldError(field, 'Please enter a valid email address');
    } else {
        showFieldSuccess(field);
    }
    
    return isValid;
}

// Format phone number
function formatPhoneNumber(field) {
    let value = field.val().replace(/\D/g, '');
    if (value.length >= 6) {
        value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
    } else if (value.length >= 3) {
        value = value.replace(/(\d{3})(\d{0,3})/, '($1) $2');
    }
    field.val(value);
}

// Calculate age
function calculateAge(dateOfBirth) {
    const today = new Date();
    const birthDate = new Date(dateOfBirth);
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    
    return age;
}

// Submit patient form
function submitPatientForm(action, form) {
    const submitBtn = form.find('button[type="submit"]');
    const originalText = submitBtn.html();
    
    submitBtn.html('<i class="fas fa-spinner fa-spin mr-1"></i>Saving...').prop('disabled', true);
    
    $.ajax({
        url: 'patients.php',
        type: 'POST',
        data: form.serialize() + '&action=' + action,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                toastr.success(response.message);
                
                if (action === 'add') {
                    $('#addPatientModal').modal('hide');
                    form[0].reset();
                } else {
                    $('#editPatientModal').modal('hide');
                }
                
                refreshPatientsTable();
                loadPatientsStats();
            } else {
                toastr.error(response.message);
            }
        },
        error: function() {
            toastr.error('An error occurred while saving the patient');
        },
        complete: function() {
            submitBtn.html(originalText).prop('disabled', false);
        }
    });
}

// Show add patient modal
function showAddPatientModal() {
    $('#addPatientForm')[0].reset();
    $('#addPatientForm .form-control').removeClass('is-invalid is-valid');
    $('#addPatientModal').modal('show');
}

// Edit patient
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

// View patient
function viewPatient(id) {
    // Implementation for viewing patient details
    toastr.info('Patient details view will be implemented');
}

// Delete patient
function deletePatient(id) {
    if (confirm('Are you sure you want to delete this patient? This action cannot be undone.')) {
        $.ajax({
            url: 'patients.php',
            type: 'POST',
            data: { action: 'delete', id: id },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    refreshPatientsTable();
                    loadPatientsStats();
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

// Search patients
function searchPatients() {
    const searchTerm = $('#searchPatients').val();
    $('#patientsTable').DataTable().search(searchTerm).draw();
}

// Filter patients
function filterPatients() {
    const genderFilter = $('#genderFilter').val();
    const ageFilter = $('#ageFilter').val();
    
    // Apply filters to DataTable
    const table = $('#patientsTable').DataTable();
    
    // This is a simplified filter - in a real implementation,
    // you would need server-side filtering for complex criteria
    if (genderFilter) {
        table.column(3).search(genderFilter).draw();
    } else {
        table.column(3).search('').draw();
    }
}

// Refresh patients table
function refreshPatientsTable() {
    $('#patientsTable').DataTable().ajax.reload(null, false);
    toastr.info('Patients table refreshed');
}

// Export patients
function exportPatients() {
    toastr.info('Export functionality will be implemented');
}
</script>

<?php include 'includes/footer.php'; ?>
