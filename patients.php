<?php
// Set page title
$page_title = 'Patients';

// Include header
include 'includes/header.php';
// Include sidebar with user info
include 'includes/sidebar.php';
?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Patient Management</h1>
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
    <section class="content">
      <div class="container-fluid">
        <!-- Alert Messages -->
        <div id="alertContainer"></div>
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Patient Records</h3>
                <div class="card-tools">
                  <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addPatientModal">
                    <i class="fas fa-plus"></i> Add New Patient
                  </button>
                </div>
              </div>
              <div class="card-body">
                <div class="row mb-3">
                  <div class="col-md-6">
                    <div class="input-group">
                      <input type="text" class="form-control" id="searchInput" placeholder="Search patients...">
                      <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                          <i class="fas fa-search"></i>
                        </button>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <button class="btn btn-info" id="refreshBtn" title="Refresh Table">
                      <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                  </div>
                </div>
                <div class="table-responsive">
                  <table id="patientsTable" class="table table-bordered table-striped" style="width:100%">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Patient ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Gender</th>
                        <th>Age</th>
                        <th>Date of Birth</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <!-- DataTables will populate this -->
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
          <h4 class="modal-title">Add New Patient</h4>
          <button type="button" class="close" data-dismiss="modal">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="addPatientForm">
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label for="fullName">Full Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="fullName" name="full_name" required>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="email">Email</label>
                  <input type="email" class="form-control" id="email" name="email">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="phone">Phone <span class="text-danger">*</span></label>
                  <input type="tel" class="form-control" id="phone" name="phone" required>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label for="gender">Gender <span class="text-danger">*</span></label>
                  <select class="form-control" id="gender" name="gender" required>
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="dateOfBirth">Date of Birth <span class="text-danger">*</span></label>
                  <input type="date" class="form-control" id="dateOfBirth" name="date_of_birth" required>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="emergencyPhone">Emergency Phone</label>
                  <input type="text" class="form-control" id="emergencyPhone" name="emergency_phone">
                </div>
              </div>
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
                  <label for="address">Address</label>
                  <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                </div>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" id="savePatientBtn">
            <i class="fas fa-save"></i> Save Patient
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Patient Modal -->
  <div class="modal fade" id="editPatientModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Edit Patient</h4>
          <button type="button" class="close" data-dismiss="modal">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="editPatientForm">
            <input type="hidden" id="editPatientId" name="id">
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label for="editFullName">Full Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="editFullName" name="full_name" required>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="editEmail">Email</label>
                  <input type="email" class="form-control" id="editEmail" name="email">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="editPhone">Phone <span class="text-danger">*</span></label>
                  <input type="tel" class="form-control" id="editPhone" name="phone" required>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label for="editGender">Gender <span class="text-danger">*</span></label>
                  <select class="form-control" id="editGender" name="gender" required>
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="editDateOfBirth">Date of Birth <span class="text-danger">*</span></label>
                  <input type="date" class="form-control" id="editDateOfBirth" name="date_of_birth" required>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="editEmergencyPhone">Emergency Phone</label>
                  <input type="text" class="form-control" id="editEmergencyPhone" name="emergency_phone">
                </div>
              </div>
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
                  <label for="editAddress">Address</label>
                  <textarea class="form-control" id="editAddress" name="address" rows="3"></textarea>
                </div>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" id="updatePatientBtn">
            <i class="fas fa-save"></i> Update Patient
          </button>
        </div>
      </div>
    </div>
  </div>

<!-- View Patient Modal -->
<div class="modal fade" id="viewPatientModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Patient Details</h4>
        <button type="button" class="close" data-dismiss="modal">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="viewPatientContent">
        <!-- Patient details will be loaded here -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTable with server-side processing
    const patientsTable = $('#patientsTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "api/patients_api.php",
            "type": "GET",
            "dataType": "json",
            "data": function(d) {
                // Add any additional parameters if needed
                return d;
            },
            "dataSrc": function(json) {
                if (json.success) {
                    return json.data;
                } else {
                    showAlert('error', 'Error loading patients: ' + (json.message || 'Unknown error'));
                    return [];
                }
            }
        },
        "columns": [
            { "data": "id" },
            { "data": "patient_id", "render": function(data) {
                return `<span class="badge badge-primary">${escapeHtml(data || 'N/A')}</span>`;
            }},
            { "data": "full_name", "render": function(data) { return escapeHtml(data); }},
            { "data": "email", "render": function(data) { return escapeHtml(data || ''); }},
            { "data": "phone", "render": function(data) { return escapeHtml(data || ''); }},
            { "data": "gender", "render": function(data) {
                const badgeClass = data === 'Male' ? 'info' : (data === 'Female' ? 'pink' : 'secondary');
                return `<span class="badge badge-${badgeClass}">${escapeHtml(data || '')}</span>`;
            }},
            { "data": "date_of_birth", "render": function(data) {
                return calculateAge(data) + ' years';
            }},
            { "data": "date_of_birth", "render": function(data) { return formatDate(data); }},
            { 
                "data": null,
                "orderable": false,
                "searchable": false,
                "render": function(data, type, row) {
                    return `
                        <div class="btn-group">
                            <button class="btn btn-info btn-sm btn-view" data-id="${row.id}" title="View Profile">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-primary btn-sm btn-edit" data-id="${row.id}" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-sm btn-delete" data-id="${row.id}" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "pageLength": 10,
        "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
        "dom": 'lBfrtip',
        "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
    });
    
    // Event listeners
    $('#savePatientBtn').click(savePatient);
    $('#updatePatientBtn').click(updatePatient);
    $('#searchBtn').click(() => patientsTable.search($('#searchInput').val()).draw());
    $('#refreshBtn').click(() => patientsTable.ajax.reload(null, false));
    
    // Search on Enter key
    $('#searchInput').keypress(function(e) { 
        if (e.which == 13) {
            patientsTable.search($('#searchInput').val()).draw();
        }
    });

    // Delegated event listeners for action buttons
    $('#patientsTable').on('click', '.btn-edit', function() {
        const patientId = $(this).data('id');
        editPatient(patientId);
    });

    $('#patientsTable').on('click', '.btn-delete', function() {
        const patientId = $(this).data('id');
        deletePatient(patientId);
    });

    $('#patientsTable').on('click', '.btn-view', function() {
        const patientId = $(this).data('id');
        viewPatient(patientId);
    });
    
    // Clear form when modal closes
    $('#addPatientModal, #editPatientModal').on('hidden.bs.modal', function() {
        $(this).find('form')[0].reset();
    });
});

// Save new patient
function savePatient() {
    const form = $('#addPatientForm')[0];
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const formData = new FormData(form);
    const patientData = {};
    formData.forEach((value, key) => {
        patientData[key] = value;
    });
    
    $('#savePatientBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
    
    $.ajax({
        url: 'api/patients_api.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(patientData),
        dataType: 'json',
        success: function(response) {
            $('#savePatientBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Save Patient');
            
            if (response.success) {
                showAlert('success', response.message);
                $('#addPatientModal').modal('hide');
                $('#patientsTable').DataTable().ajax.reload();
            } else {
                showAlert('error', response.message);
            }
        },
        error: function(xhr, status, error) {
            $('#savePatientBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Save Patient');
            console.error('AJAX Error:', error);
            
            let message = 'Failed to save patient. Please try again.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            showAlert('error', message);
        }
    });
}

// Edit patient
function editPatient(patientId) {
    // Get patient data
    $.ajax({
        url: 'api/patients_api.php',
        method: 'GET',
        data: { id: patientId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const patient = response.data;
                
                // Populate edit form
                $('#editPatientId').val(patient.id);
                $('#editFullName').val(patient.full_name);
                $('#editEmail').val(patient.email || '');
                $('#editPhone').val(patient.phone);
                $('#editGender').val(patient.gender);
                $('#editDateOfBirth').val(patient.date_of_birth);
                $('#editEmergencyContact').val(patient.emergency_contact || '');
                $('#editEmergencyPhone').val(patient.emergency_phone || '');
                $('#editAddress').val(patient.address || '');
                
                $('#editPatientModal').modal('show');
            } else {
                showAlert('error', 'Failed to load patient data: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            showAlert('error', 'Failed to load patient data. Please try again.');
        }
    });
}

// Update patient
function updatePatient() {
    const form = $('#editPatientForm')[0];
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const formData = new FormData(form);
    const patientData = {};
    formData.forEach((value, key) => {
        patientData[key] = value;
    });
    
    $('#updatePatientBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');
    
    $.ajax({
        url: 'api/patients_api.php',
        method: 'PUT',
        contentType: 'application/json',
        data: JSON.stringify(patientData),
        dataType: 'json',
        success: function(response) {
            $('#updatePatientBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Update Patient');
            
            if (response.success) {
                showAlert('success', response.message);
                $('#editPatientModal').modal('hide');
                $('#patientsTable').DataTable().ajax.reload(null, false);
            } else {
                showAlert('error', response.message);
            }
        },
        error: function(xhr, status, error) {
            $('#updatePatientBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Update Patient');
            console.error('AJAX Error:', error);
            
            let message = 'Failed to update patient. Please try again.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            showAlert('error', message);
        }
    });
}

// Delete patient
function deletePatient(patientId) {
    if (!confirm('Are you sure you want to delete this patient? This action cannot be undone.')) {
        return;
    }
    
    $.ajax({
        url: 'api/patients_api.php',
        method: 'DELETE',
        contentType: 'application/json',
        data: JSON.stringify({ id: patientId }),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showAlert('success', response.message);
                $('#patientsTable').DataTable().ajax.reload(null, false);
            } else {
                showAlert('error', response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            
            let message = 'Failed to delete patient. Please try again.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            showAlert('error', message);
        }
    });
}

// View patient details
function viewPatient(patientId) {
    $.ajax({
        url: 'api/patients_api.php',
        method: 'GET',
        data: { id: patientId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const patient = response.data;
                const age = calculateAge(patient.date_of_birth);
                
                const content = `
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Personal Information</h5>
                            <table class="table table-borderless">
                                <tr><td><strong>Patient ID:</strong></td><td><span class="badge badge-primary">${patient.patient_id}</span></td></tr>
                                <tr><td><strong>Full Name:</strong></td><td>${escapeHtml(patient.full_name)}</td></tr>
                                <tr><td><strong>Email:</strong></td><td>${escapeHtml(patient.email || 'N/A')}</td></tr>
                                <tr><td><strong>Phone:</strong></td><td>${escapeHtml(patient.phone)}</td></tr>
                                <tr><td><strong>Gender:</strong></td><td><span class="badge badge-${patient.gender === 'Male' ? 'info' : (patient.gender === 'Female' ? 'pink' : 'secondary')}">${patient.gender}</span></td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Additional Information</h5>
                            <table class="table table-borderless">
                                <tr><td><strong>Date of Birth:</strong></td><td>${formatDate(patient.date_of_birth)}</td></tr>
                                <tr><td><strong>Age:</strong></td><td>${age} years</td></tr>
                                <tr><td><strong>Emergency Contact:</strong></td><td>${escapeHtml(patient.emergency_contact || 'N/A')}</td></tr>
                                <tr><td><strong>Emergency Phone:</strong></td><td>${escapeHtml(patient.emergency_phone || 'N/A')}</td></tr>
                                <tr><td><strong>Address:</strong></td><td>${escapeHtml(patient.address || 'N/A')}</td></tr>
                            </table>
                        </div>
                    </div>
                `;
                
                $('#viewPatientContent').html(content);
                $('#viewPatientModal').modal('show');
            } else {
                showAlert('error', 'Failed to load patient data: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            showAlert('error', 'Failed to load patient data. Please try again.');
        }
    });
}

// Utility functions
function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 
                     type === 'error' ? 'alert-danger' : 
                     type === 'warning' ? 'alert-warning' : 'alert-info';
    
    const icon = type === 'success' ? 'fas fa-check' : 
                type === 'error' ? 'fas fa-ban' : 
                type === 'warning' ? 'fas fa-exclamation-triangle' : 'fas fa-info-circle';
    
    const alert = `
        <div class="alert ${alertClass} alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <i class="icon ${icon}"></i> ${message}
        </div>
    `;
    
    $('#alertContainer').html(alert);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        $('#alertContainer .alert').fadeOut();
    }, 5000);
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function calculateAge(birthDate) {
    if (!birthDate) return 'N/A';
    const today = new Date();
    const birth = new Date(birthDate);
    let age = today.getFullYear() - birth.getFullYear();
    const monthDiff = today.getMonth() - birth.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
        age--;
    }
    
    return age;
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
}
</script>

<?php
// Include footer
include 'includes/footer.php';
?>
