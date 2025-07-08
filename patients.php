<?php
// Set page title
$page_title = 'Patients';

// Include header and session handling
include 'includes/header.php';
include 'includes/sidebar.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add':
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $date_of_birth = $_POST['date_of_birth'] ?? '';
            $gender = $_POST['gender'] ?? '';
            $address = trim($_POST['address'] ?? '');
            $emergency_contact = trim($_POST['emergency_contact'] ?? '');
            $medical_history = trim($_POST['medical_history'] ?? '');
            
            if ($name && $phone) {
                // Generate patient ID
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM patients");
                $count = $stmt->fetch()['count'] + 1;
                $patient_id = 'PAT-' . str_pad($count, 4, '0', STR_PAD_LEFT);
                
                $stmt = $pdo->prepare("INSERT INTO patients (patient_id, name, email, phone, date_of_birth, gender, address, emergency_contact, medical_history, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                if ($stmt->execute([$patient_id, $name, $email, $phone, $date_of_birth, $gender, $address, $emergency_contact, $medical_history])) {
                    $response = ['success' => true, 'message' => 'Patient added successfully'];
                } else {
                    $response = ['success' => false, 'message' => 'Error adding patient'];
                }
            } else {
                $response = ['success' => false, 'message' => 'Name and phone are required'];
            }
            break;
            
        case 'edit':
            $id = $_POST['id'] ?? '';
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $date_of_birth = $_POST['date_of_birth'] ?? '';
            $gender = $_POST['gender'] ?? '';
            $address = trim($_POST['address'] ?? '');
            $emergency_contact = trim($_POST['emergency_contact'] ?? '');
            $medical_history = trim($_POST['medical_history'] ?? '');
            
            if ($id && $name && $phone) {
                $stmt = $pdo->prepare("UPDATE patients SET name = ?, email = ?, phone = ?, date_of_birth = ?, gender = ?, address = ?, emergency_contact = ?, medical_history = ? WHERE id = ?");
                if ($stmt->execute([$name, $email, $phone, $date_of_birth, $gender, $address, $emergency_contact, $medical_history, $id])) {
                    $response = ['success' => true, 'message' => 'Patient updated successfully'];
                } else {
                    $response = ['success' => false, 'message' => 'Error updating patient'];
                }
            } else {
                $response = ['success' => false, 'message' => 'ID, name and phone are required'];
            }
            break;
            
        case 'delete':
            $id = $_POST['id'] ?? '';
            
            if ($id) {
                $stmt = $pdo->prepare("DELETE FROM patients WHERE id = ?");
                if ($stmt->execute([$id])) {
                    $response = ['success' => true, 'message' => 'Patient deleted successfully'];
                } else {
                    $response = ['success' => false, 'message' => 'Error deleting patient'];
                }
            }
            break;
            
        case 'get':
            $id = $_POST['id'] ?? '';
            
            if ($id) {
                $stmt = $pdo->prepare("SELECT * FROM patients WHERE id = ?");
                $stmt->execute([$id]);
                $patient = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($patient) {
                    $response = ['success' => true, 'data' => $patient];
                } else {
                    $response = ['success' => false, 'message' => 'Patient not found'];
                }
            }
            break;
    }
    
    if (isset($response)) {
        echo json_encode($response);
        exit;
    }
}

// Get all patients
$stmt = $pdo->query("SELECT * FROM patients ORDER BY created_at DESC");
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
  <!-- Content Header -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0"><i class="fas fa-user-injured mr-2"></i>Patient Management</h1>
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
      <!-- Action Buttons -->
      <div class="row mb-3">
        <div class="col-md-8">
          <div class="input-group">
            <input type="text" class="form-control" id="searchInput" placeholder="Search patients by name, phone, or ID...">
            <div class="input-group-append">
              <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                <i class="fas fa-search"></i>
              </button>
            </div>
          </div>
        </div>
        <div class="col-md-4 text-right">
          <button class="btn btn-success" data-toggle="modal" data-target="#addPatientModal">
            <i class="fas fa-plus mr-2"></i>Add Patient
          </button>
          <button class="btn btn-info ml-2" onclick="refreshTable()">
            <i class="fas fa-sync-alt"></i>
          </button>
        </div>
      </div>

      <!-- Patients Table -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-list mr-2"></i>Patients List</h3>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table id="patientsTable" class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th>Patient ID</th>
                  <th>Name</th>
                  <th>Phone</th>
                  <th>Email</th>
                  <th>Gender</th>
                  <th>Age</th>
                  <th>Registration Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($patients as $patient): ?>
                <tr>
                  <td><strong><?php echo htmlspecialchars($patient['patient_id']); ?></strong></td>
                  <td><?php echo htmlspecialchars($patient['name']); ?></td>
                  <td><?php echo htmlspecialchars($patient['phone']); ?></td>
                  <td><?php echo htmlspecialchars($patient['email'] ?? 'N/A'); ?></td>
                  <td><?php echo ucfirst(htmlspecialchars($patient['gender'] ?? 'N/A')); ?></td>
                  <td>
                    <?php 
                    if ($patient['date_of_birth']) {
                        $age = date_diff(date_create($patient['date_of_birth']), date_create('today'))->y;
                        echo $age . ' years';
                    } else {
                        echo 'N/A';
                    }
                    ?>
                  </td>
                  <td><?php echo date('Y-m-d', strtotime($patient['created_at'])); ?></td>
                  <td>
                    <button class="btn btn-sm btn-info" onclick="viewPatient(<?php echo $patient['id']; ?>)">
                      <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-primary" onclick="editPatient(<?php echo $patient['id']; ?>)">
                      <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deletePatient(<?php echo $patient['id']; ?>)">
                      <i class="fas fa-trash"></i>
                    </button>
                  </td>
                </tr>
                <?php endforeach; ?>
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
        <h4 class="modal-title"><i class="fas fa-plus mr-2"></i>Add New Patient</h4>
        <button type="button" class="close" data-dismiss="modal">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="addPatientForm">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="name">Full Name *</label>
                <input type="text" class="form-control" id="name" name="name" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="phone">Phone Number *</label>
                <input type="tel" class="form-control" id="phone" name="phone" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" class="form-control" id="email" name="email">
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
                <label for="emergency_contact">Emergency Contact</label>
                <input type="text" class="form-control" id="emergency_contact" name="emergency_contact">
              </div>
            </div>
          </div>
          <div class="form-group">
            <label for="address">Address</label>
            <textarea class="form-control" id="address" name="address" rows="2"></textarea>
          </div>
          <div class="form-group">
            <label for="medical_history">Medical History</label>
            <textarea class="form-control" id="medical_history" name="medical_history" rows="3" placeholder="Any relevant medical history, allergies, current medications..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">
            <i class="fas fa-save mr-2"></i>Add Patient
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
        <h4 class="modal-title"><i class="fas fa-edit mr-2"></i>Edit Patient</h4>
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
                <label for="edit_name">Full Name *</label>
                <input type="text" class="form-control" id="edit_name" name="name" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_phone">Phone Number *</label>
                <input type="tel" class="form-control" id="edit_phone" name="phone" required>
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
                <label for="edit_emergency_contact">Emergency Contact</label>
                <input type="text" class="form-control" id="edit_emergency_contact" name="emergency_contact">
              </div>
            </div>
          </div>
          <div class="form-group">
            <label for="edit_address">Address</label>
            <textarea class="form-control" id="edit_address" name="address" rows="2"></textarea>
          </div>
          <div class="form-group">
            <label for="edit_medical_history">Medical History</label>
            <textarea class="form-control" id="edit_medical_history" name="medical_history" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save mr-2"></i>Update Patient
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
        <h4 class="modal-title"><i class="fas fa-eye mr-2"></i>Patient Details</h4>
        <button type="button" class="close" data-dismiss="modal">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="patientDetailsContent">
        <!-- Content will be loaded dynamically -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#patientsTable').DataTable({
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        "order": [[0, "desc"]],
        "pageLength": 25
    });

    // Add Patient Form Submission
    $('#addPatientForm').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'patients.php',
            type: 'POST',
            data: $(this).serialize() + '&action=add',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#addPatientModal').modal('hide');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('An error occurred while adding the patient');
            }
        });
    });

    // Edit Patient Form Submission
    $('#editPatientForm').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'patients.php',
            type: 'POST',
            data: $(this).serialize() + '&action=edit',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#editPatientModal').modal('hide');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('An error occurred while updating the patient');
            }
        });
    });

    // Search functionality
    $('#searchInput').on('keyup', function() {
        $('#patientsTable').DataTable().search(this.value).draw();
    });

    // Clear forms when modals are hidden
    $('#addPatientModal').on('hidden.bs.modal', function() {
        $('#addPatientForm')[0].reset();
    });
});

function viewPatient(id) {
    $.ajax({
        url: 'patients.php',
        type: 'POST',
        data: { action: 'get', id: id },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                var patient = response.data;
                var age = patient.date_of_birth ? 
                    new Date().getFullYear() - new Date(patient.date_of_birth).getFullYear() + ' years' : 'N/A';
                
                var content = `
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr><td><strong>Patient ID:</strong></td><td>${patient.patient_id}</td></tr>
                                <tr><td><strong>Name:</strong></td><td>${patient.name}</td></tr>
                                <tr><td><strong>Phone:</strong></td><td>${patient.phone}</td></tr>
                                <tr><td><strong>Email:</strong></td><td>${patient.email || 'N/A'}</td></tr>
                                <tr><td><strong>Gender:</strong></td><td>${patient.gender ? patient.gender.charAt(0).toUpperCase() + patient.gender.slice(1) : 'N/A'}</td></tr>
                                <tr><td><strong>Age:</strong></td><td>${age}</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr><td><strong>Date of Birth:</strong></td><td>${patient.date_of_birth || 'N/A'}</td></tr>
                                <tr><td><strong>Emergency Contact:</strong></td><td>${patient.emergency_contact || 'N/A'}</td></tr>
                                <tr><td><strong>Registration Date:</strong></td><td>${new Date(patient.created_at).toLocaleDateString()}</td></tr>
                            </table>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <h6><strong>Address:</strong></h6>
                            <p>${patient.address || 'No address provided'}</p>
                            <h6><strong>Medical History:</strong></h6>
                            <p>${patient.medical_history || 'No medical history recorded'}</p>
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
                var patient = response.data;
                $('#edit_id').val(patient.id);
                $('#edit_name').val(patient.name);
                $('#edit_phone').val(patient.phone);
                $('#edit_email').val(patient.email || '');
                $('#edit_date_of_birth').val(patient.date_of_birth || '');
                $('#edit_gender').val(patient.gender || '');
                $('#edit_emergency_contact').val(patient.emergency_contact || '');
                $('#edit_address').val(patient.address || '');
                $('#edit_medical_history').val(patient.medical_history || '');
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
    if (confirm('Are you sure you want to delete this patient? This action cannot be undone.')) {
        $.ajax({
            url: 'patients.php',
            type: 'POST',
            data: { action: 'delete', id: id },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    setTimeout(() => location.reload(), 1000);
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

function refreshTable() {
    location.reload();
}
</script>
