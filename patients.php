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
                    <button class="btn btn-info" id="refreshBtn">
                      <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                  </div>
                </div>
                <div id="loadingIndicator" class="text-center" style="display: none;">
                  <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                  </div>
                  <p>Loading patients...</p>
                </div>
                <table id="patientsTable" class="table table-bordered table-striped" style="display: none;">
                  <thead>
                  <tr>
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
                  <tbody id="patientsTableBody">
                  </tbody>
                </table>
                <!-- Pagination -->
                <nav aria-label="Patient pagination" id="paginationContainer" style="display: none;">
                  <ul class="pagination justify-content-center" id="pagination">
                  </ul>
                </nav>
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
              <div class="col-md-6">
                <div class="form-group">
                  <label for="firstName">First Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="firstName" name="first_name" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="lastName">Last Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="lastName" name="last_name" required>
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
              <div class="col-md-6">
                <div class="form-group">
                  <label for="editFirstName">First Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="editFirstName" name="first_name" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="editLastName">Last Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="editLastName" name="last_name" required>
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
  <footer class="main-footer">
    <strong>Copyright &copy; 2024 <a href="#">PathLab Pro</a>.</strong> All rights reserved.
  </footer>
</div>

<!-- jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>
<!-- DataTables & Plugins -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables.net/1.11.3/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables.net-bs4/1.11.3/dataTables.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables.net-responsive/2.2.9/dataTables.responsive.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables.net-responsive-bs4/2.2.9/responsive.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables.net-buttons/2.0.1/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables.net-buttons-bs4/2.0.1/buttons.bootstrap4.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>

<script>
// Global variables
let currentPage = 1;
let currentSearch = '';
const recordsPerPage = 10;

$(document).ready(function() {
    // Initialize page
    loadPatients();
    
    // Event listeners
    $('#savePatientBtn').click(savePatient);
    $('#updatePatientBtn').click(updatePatient);
    $('#searchBtn').click(searchPatients);
    $('#refreshBtn').click(function() {
        currentSearch = '';
        $('#searchInput').val('');
        loadPatients();
    });
    
    // Search on Enter key
    $('#searchInput').keypress(function(e) {
        if (e.which == 13) {
            searchPatients();
        }
    });
    
    // Clear form when modal closes
    $('#addPatientModal').on('hidden.bs.modal', function() {
        $('#addPatientForm')[0].reset();
    });
    
    $('#editPatientModal').on('hidden.bs.modal', function() {
        $('#editPatientForm')[0].reset();
    });
});

// Load patients with AJAX
function loadPatients(page = 1, search = '') {
    currentPage = page;
    currentSearch = search;
    
    showLoading();
    
    $.ajax({
        url: 'api/patients_api.php',
        method: 'GET',
        data: {
            page: page,
            limit: recordsPerPage,
            search: search
        },
        dataType: 'json',
        success: function(response) {
            hideLoading();
            if (response.success) {
                displayPatients(response.data);
                displayPagination(response.pagination);
            } else {
                showToaster('danger', 'Error loading patients: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            hideLoading();
            console.error('AJAX Error:', error);
            showToaster('danger', 'Failed to load patients. Please try again.');
        }
    });
}

// Display patients in table
function displayPatients(patients) {
    const tbody = $('#patientsTableBody');
    tbody.empty();
    
    if (patients.length === 0) {
        tbody.append('<tr><td colspan="8" class="text-center">No patients found</td></tr>');
        $('#patientsTable').show();
        return;
    }
    
    patients.forEach(function(patient) {
        const age = calculateAge(patient.date_of_birth);
        const row = `
            <tr>
                <td><span class="badge badge-primary">${patient.patient_id || 'N/A'}</span></td>
                <td>${escapeHtml(patient.first_name + ' ' + patient.last_name)}</td>
                <td>${escapeHtml(patient.email || '')}</td>
                <td>${escapeHtml(patient.phone || '')}</td>
                <td>
                    <span class="badge badge-${patient.gender === 'Male' ? 'info' : (patient.gender === 'Female' ? 'pink' : 'secondary')}">
                        ${escapeHtml(patient.gender || '')}
                    </span>
                </td>
                <td>${age} years</td>
                <td>${formatDate(patient.date_of_birth)}</td>
                <td>                    <button class="btn btn-info btn-sm" onclick="viewPatient(${patient.id})" title="View Profile">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-success btn-sm" onclick="newTestOrder(${patient.id})" title="New Test Order">
                        <i class="fas fa-plus-circle"></i>
                    </button>
                    <button class="btn btn-primary btn-sm" onclick="editPatient(${patient.id})" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="deletePatient(${patient.id})" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
    
    $('#patientsTable').show();
}

// Display pagination
function displayPagination(pagination) {
    const container = $('#pagination');
    container.empty();
    
    if (pagination.pages <= 1) {
        $('#paginationContainer').hide();
        return;
    }
    
    // Previous button
    const prevDisabled = pagination.page <= 1 ? 'disabled' : '';
    container.append(`
        <li class="page-item ${prevDisabled}">
            <a class="page-link" href="#" onclick="loadPatients(${pagination.page - 1}, '${currentSearch}')">Previous</a>
        </li>
    `);
    
    // Page numbers
    const startPage = Math.max(1, pagination.page - 2);
    const endPage = Math.min(pagination.pages, pagination.page + 2);
    
    if (startPage > 1) {
        container.append('<li class="page-item"><a class="page-link" href="#" onclick="loadPatients(1, \'' + currentSearch + '\')">1</a></li>');
        if (startPage > 2) {
            container.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
        }
    }
    
    for (let i = startPage; i <= endPage; i++) {
        const active = i === pagination.page ? 'active' : '';
        container.append(`
            <li class="page-item ${active}">
                <a class="page-link" href="#" onclick="loadPatients(${i}, '${currentSearch}')">${i}</a>
            </li>
        `);
    }
    
    if (endPage < pagination.pages) {
        if (endPage < pagination.pages - 1) {
            container.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
        }
        container.append(`<li class="page-item"><a class="page-link" href="#" onclick="loadPatients(${pagination.pages}, '${currentSearch}')">${pagination.pages}</a></li>`);
    }
    
    // Next button
    const nextDisabled = pagination.page >= pagination.pages ? 'disabled' : '';
    container.append(`
        <li class="page-item ${nextDisabled}">
            <a class="page-link" href="#" onclick="loadPatients(${pagination.page + 1}, '${currentSearch}')">Next</a>
        </li>
    `);
    
    $('#paginationContainer').show();
}

// Search patients
function searchPatients() {
    const search = $('#searchInput').val().trim();
    loadPatients(1, search);
}

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
                loadPatients(currentPage, currentSearch);
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
                $('#editFirstName').val(patient.first_name);
                $('#editLastName').val(patient.last_name);
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
                loadPatients(currentPage, currentSearch);
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
                loadPatients(currentPage, currentSearch);
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
    // TODO: Implement patient detail view
    showAlert('info', 'Patient detail view will be implemented in the next phase.');
}

// New test order for patient
function newTestOrder(patientId) {
    // Redirect to test orders page with patient ID
    window.location.href = `test-orders.php?patient_id=${patientId}`;
}

// Utility functions
function showLoading() {
    $('#loadingIndicator').show();
    $('#patientsTable').hide();
    $('#paginationContainer').hide();
}

function hideLoading() {
    $('#loadingIndicator').hide();
}

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
    const date = new Date(dateString);    return date.toLocaleDateString('en-US', { 
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
