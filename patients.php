<?php
// Set page title
$page_title = 'Patients Management - PathLab Pro';

// Include database connection
require_once 'config.php';

// Include AdminLTE header and sidebar
include 'includes/adminlte_header.php';
include 'includes/adminlte_sidebar.php';
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">
            <i class="fas fa-user-injured mr-2 text-primary"></i>
            Patients Management
          </h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item">
              <a href="dashboard.php">
                <i class="fas fa-home"></i> Home
              </a>
            </li>
            <li class="breadcrumb-item active">Patients</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      
      <!-- Patients List -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-list mr-1"></i>
                All Patients
              </h3>
              <div class="card-tools">
                <button type="button" class="btn btn-primary btn-sm" onclick="openPatientModal()">
                  <i class="fas fa-plus mr-1"></i>Add New Patient
                </button>
              </div>
            </div>
            <div class="card-body">
              <div id="loading" class="text-center p-4" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                  <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-2">Loading patients...</p>
              </div>
              
              <div id="no-data" class="text-center p-4" style="display: none;">
                <i class="fas fa-user-injured fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Patients Found</h5>
                <p class="text-muted">Start by adding your first patient to the system.</p>
                <button type="button" class="btn btn-primary" onclick="openPatientModal()">
                  <i class="fas fa-plus mr-2"></i>Add First Patient
                </button>
              </div>
              
              <div id="patients-table-container" style="display: none;">
                <table id="patientsTable" class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Patient Name</th>
                      <th>Phone</th>
                      <th>Email</th>
                      <th>Age</th>
                      <th>Status</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody id="patients-tbody">
                    <!-- Data will be loaded via AJAX -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
      
    </div><!-- /.container-fluid -->
  </section>
  <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<!-- Patient Modal -->
<div class="modal fade" id="patientModal" tabindex="-1" role="dialog" aria-labelledby="patientModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="patientModalLabel">
          <i class="fas fa-user-injured mr-2"></i>
          <span id="modal-title">Add New Patient</span>
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="patientForm">
        <div class="modal-body">
          <input type="hidden" id="patient_id" name="patient_id">
          
          <!-- Essential Patient Information -->
          <div class="card mb-3">
            <div class="card-header bg-light">
              <h6 class="mb-0"><i class="fas fa-user mr-2"></i>Personal Details</h6>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="first_name">First Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="first_name" name="first_name" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="last_name">Last Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="last_name" name="last_name" required>
                  </div>
                </div>
              </div>
              
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="date_of_birth">Date of Birth</label>
                    <input type="date" class="form-control" id="date_of_birth" name="date_of_birth">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="gender">Gender</label>
                    <select class="form-control" id="gender" name="gender">
                      <option value="">Select Gender</option>
                      <option value="male">Male</option>
                      <option value="female">Female</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Contact Information -->
          <div class="card">
            <div class="card-header bg-light">
              <h6 class="mb-0"><i class="fas fa-phone mr-2"></i>Contact Information</h6>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="phone">Phone Number <span class="text-danger">*</span></label>
                    <input type="tel" class="form-control" id="phone" name="phone" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email">
                  </div>
                </div>
              </div>
              
              <div class="form-group">
                <label for="address">Address</label>
                <textarea class="form-control" id="address" name="address" rows="2" placeholder="Enter patient address"></textarea>
              </div>
            </div>
          </div>
        </div>
        
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i>Cancel
          </button>
          <button type="submit" class="btn btn-primary" id="submit-btn">
            <i class="fas fa-save mr-1"></i>
            <span id="submit-text">Add Patient</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- View Patient Modal -->
<div class="modal fade" id="viewPatientModal" tabindex="-1" role="dialog" aria-labelledby="viewPatientModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewPatientModalLabel">
          <i class="fas fa-user mr-2"></i>
          Patient Details
        </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="view-patient-content">
        <!-- Patient details will be loaded here -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
          <i class="fas fa-times mr-1"></i>Close
        </button>
        <button type="button" class="btn btn-warning" onclick="editPatientFromView()">
          <i class="fas fa-edit mr-1"></i>Edit Patient
        </button>
      </div>
    </div>
  </div>
</div>

    <!-- Custom CSS for enhanced styling -->
    <style>
        .patients-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
        }
        
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 8px 8px 0 0;
        }
        
        .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }
        
        .card-section {
            background: #f8f9fc;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 4px solid #667eea;
        }
        
        .form-group label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
        }
        
        .form-control {
            border-radius: 6px;
            border: 1px solid #e3e6f0;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 6px;
            padding: 10px 30px;
            font-weight: 600;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            transform: translateY(-1px);
        }
        
        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .table thead th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            font-weight: 600;
            padding: 15px 12px;
        }
        
        .table tbody tr:hover {
            background-color: #f8f9fc;
        }
        
        .badge {
            padding: 8px 12px;
            font-weight: 500;
            border-radius: 20px;
        }
        
        .btn-group .btn {
            margin: 0 2px;
            border-radius: 4px !important;
        }
        
        .loading-overlay {
            background: rgba(255,255,255,0.9);
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
        }
        
        .spinner-border {
            color: #667eea;
        }
        
        .is-invalid {
            border-color: #e74a3b !important;
            box-shadow: 0 0 0 0.2rem rgba(231, 74, 59, 0.25) !important;
        }
        
        .invalid-feedback {
            display: block;
            color: #e74a3b;
            font-size: 0.875rem;
            margin-top: 5px;
        }
        
        @media (max-width: 768px) {
            .patients-header h1 {
                font-size: 1.5rem;
            }
            
            .card-section {
                padding: 15px;
            }
            
            .btn-group .btn {
                padding: 5px 8px;
                font-size: 0.875rem;
            }
        }
    </style>

    <script>
// PathLab Pro utilities
const PathLabPro = {
    notifications: {
        success: function(message) {
            if (typeof toastr !== 'undefined') {
                toastr.success(message);
            } else {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: message,
                    timer: 3000,
                    showConfirmButton: false
                });
            }
        },
        error: function(message) {
            if (typeof toastr !== 'undefined') {
                toastr.error(message);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: message
                });
            }
        },
        warning: function(message) {
            if (typeof toastr !== 'undefined') {
                toastr.warning(message);
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Warning!',
                    text: message
                });
            }
        }
    },
    modal: {
        confirm: function(options) {
            Swal.fire({
                title: options.title || 'Are you sure?',
                text: options.text || 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: options.confirmText || 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed && options.callback) {
                    options.callback();
                }
            });
        }
    },
    formatDate: function(dateStr) {
        if (!dateStr) return '';
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    },
    calculateAge: function(birthDate) {
        if (!birthDate) return '';
        const today = new Date();
        const birth = new Date(birthDate);
        let age = today.getFullYear() - birth.getFullYear();
        const monthDiff = today.getMonth() - birth.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
            age--;
        }
        
        return age + ' years';
    }
};

let patientsTable;
let currentPatientId = null;

$(document).ready(function() {
    // Initialize DataTable
    patientsTable = $('#patientsTable').DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 25,
        order: [[0, 'desc']],
        columnDefs: [
            { 
                targets: -1,
                orderable: false,
                searchable: false
            }
        ],
        language: {
            emptyTable: "No patients found",
            search: "Search patients:",
            lengthMenu: "Show _MENU_ patients per page",
            info: "Showing _START_ to _END_ of _TOTAL_ patients",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        }
    });
    
    // Load patients data
    loadPatients();
    
    // Form submission with enhanced validation
    $('#patientForm').on('submit', function(e) {
        e.preventDefault();
        
        // Clear previous validation
        $(this).find('.is-invalid').removeClass('is-invalid');
        
        // Enhanced validation
        let isValid = true;
        
        // Required fields validation
        $(this).find('[required]').each(function() {
            if (!$(this).val().trim()) {
                isValid = false;
                $(this).addClass('is-invalid');
            }
        });
        
        // Phone number validation
        const phone = $('#phone').val().trim();
        if (phone && !/^[\d\-\+\(\)\s]+$/.test(phone)) {
            isValid = false;
            $('#phone').addClass('is-invalid');
        }
        
        // Email validation
        const email = $('#email').val().trim();
        if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            isValid = false;
            $('#email').addClass('is-invalid');
        }
        
        if (isValid) {
            savePatient();
        } else {
            PathLabPro.notifications.error('Please check the form for errors and try again.');
        }
    });
    
    // Reset modal on close
    $('#patientModal').on('hidden.bs.modal', function() {
        resetForm();
    });
    
    // Real-time validation feedback
    $('#phone').on('input', function() {
        const phone = $(this).val().trim();
        if (phone && !/^[\d\-\+\(\)\s]+$/.test(phone)) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
    
    $('#email').on('input', function() {
        const email = $(this).val().trim();
        if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
});

// Load patients via AJAX
function loadPatients() {
    $('#loading').show();
    $('#patients-table-container').hide();
    $('#no-data').hide();
    
    $.ajax({
        url: 'api/patients_api.php',
        method: 'GET',
        data: { action: 'list' },
        dataType: 'json',
        success: function(response) {
            $('#loading').hide();
            
            if (response.success && response.data && response.data.length > 0) {
                populateTable(response.data);
                $('#patients-table-container').show();
            } else {
                $('#no-data').show();
            }
        },
        error: function(xhr, status, error) {
            $('#loading').hide();
            console.error('Error loading patients:', error);
            PathLabPro.notifications.error('Failed to load patients');
        }
    });
}

// Populate DataTable with patients data
function populateTable(patients) {
    patientsTable.clear();
    
    patients.forEach(function(patient) {
        const age = patient.date_of_birth ? 
            Math.floor((new Date() - new Date(patient.date_of_birth)) / (365.25 * 24 * 60 * 60 * 1000)) : '';
            
        const statusBadge = `<span class="badge badge-${patient.status === 'active' ? 'success' : 'secondary'}">${patient.status.charAt(0).toUpperCase() + patient.status.slice(1)}</span>`;
        
        const actions = `
            <div class="btn-group">
                <button class="btn btn-info btn-sm" onclick="viewPatient(${patient.id})" title="View Details">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="btn btn-warning btn-sm" onclick="editPatient(${patient.id})" title="Edit Patient">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-danger btn-sm" onclick="deletePatient(${patient.id})" title="Delete Patient">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        
        patientsTable.row.add([
            `<strong>#${patient.id}</strong>`,
            `<div><strong>${patient.first_name} ${patient.last_name}</strong><br><small class="text-muted">${patient.gender ? patient.gender.charAt(0).toUpperCase() + patient.gender.slice(1) : ''}</small></div>`,
            `<a href="tel:${patient.phone}">${patient.phone || ''}</a>`,
            patient.email ? `<a href="mailto:${patient.email}">${patient.email}</a>` : '<span class="text-muted">Not provided</span>',
            age ? `${age} years` : '<span class="text-muted">Unknown</span>',
            statusBadge,
            actions
        ]);
    });
    
    patientsTable.draw();
}

// Open patient modal for adding new patient
function openPatientModal() {
    currentPatientId = null;
    $('#modal-title').text('Add New Patient');
    $('#submit-text').text('Add Patient');
    resetForm();
    $('#patientModal').modal('show');
}

// Edit patient
function editPatient(id) {
    currentPatientId = id;
    $('#modal-title').text('Edit Patient');
    $('#submit-text').text('Update Patient');
    
    // Load patient data
    $.ajax({
        url: 'api/patients_api.php',
        method: 'GET',
        data: { action: 'get', id: id },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                populateForm(response.data);
                $('#patientModal').modal('show');
            } else {
                PathLabPro.notifications.error('Failed to load patient data');
            }
        },
        error: function() {
            PathLabPro.notifications.error('Failed to load patient data');
        }
    });
}

// View patient details
function viewPatient(id) {
    $.ajax({
        url: 'api/patients_api.php',
        method: 'GET',
        data: { action: 'get', id: id },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                const patient = response.data;
                const content = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6><strong>Personal Information</strong></h6>
                            <p><strong>Name:</strong> ${patient.first_name} ${patient.last_name}</p>
                            <p><strong>Phone:</strong> ${patient.phone || 'Not provided'}</p>
                            <p><strong>Email:</strong> ${patient.email || 'Not provided'}</p>
                            <p><strong>Date of Birth:</strong> ${patient.date_of_birth ? new Date(patient.date_of_birth).toLocaleDateString() : 'Not provided'}</p>
                            <p><strong>Gender:</strong> ${patient.gender ? patient.gender.charAt(0).toUpperCase() + patient.gender.slice(1) : 'Not specified'}</p>
                        </div>
                        <div class="col-md-6">
                            <h6><strong>Contact Information</strong></h6>
                            <p><strong>Address:</strong></p>
                            <p>${patient.address || 'Not provided'}</p>
                            
                            <h6><strong>Status</strong></h6>
                            <p><span class="badge badge-${patient.status === 'active' ? 'success' : 'secondary'}">${patient.status.charAt(0).toUpperCase() + patient.status.slice(1)}</span></p>
                            
                            <h6><strong>Registration Date</strong></h6>
                            <p>${patient.created_at ? new Date(patient.created_at).toLocaleDateString() : 'Unknown'}</p>
                        </div>
                    </div>
                `;
                
                $('#view-patient-content').html(content);
                currentPatientId = id;
                $('#viewPatientModal').modal('show');
            } else {
                PathLabPro.notifications.error('Failed to load patient details');
            }
        },
        error: function() {
            PathLabPro.notifications.error('Failed to load patient details');
        }
    });
}

// Edit patient from view modal
function editPatientFromView() {
    if (currentPatientId) {
        $('#viewPatientModal').modal('hide');
        setTimeout(() => {
            editPatient(currentPatientId);
        }, 500);
    }
}

// Delete patient
function deletePatient(id) {
    PathLabPro.modal.confirm({
        title: 'Delete Patient',
        text: 'Are you sure you want to delete this patient? This action cannot be undone.',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'api/patients_api.php',
                method: 'POST',
                data: { action: 'delete', id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        PathLabPro.notifications.success(response.message);
                        loadPatients();
                    } else {
                        PathLabPro.notifications.error(response.message);
                    }
                },
                error: function() {
                    PathLabPro.notifications.error('Failed to delete patient');
                }
            });
        }
    });
}

// Save patient (create or update)
function savePatient() {
    const formData = new FormData($('#patientForm')[0]);
    const action = currentPatientId ? 'update' : 'create';
    
    if (currentPatientId) {
        formData.append('id', currentPatientId);
    }
    formData.append('action', action);
    
    // Disable submit button
    $('#submit-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Saving...');
    
    $.ajax({
        url: 'api/patients_api.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                PathLabPro.notifications.success(response.message);
                $('#patientModal').modal('hide');
                loadPatients();
            } else {
                PathLabPro.notifications.error(response.message);
            }
        },
        error: function() {
            PathLabPro.notifications.error('Failed to save patient');
        },
        complete: function() {
            // Re-enable submit button
            $('#submit-btn').prop('disabled', false).html(`<i class="fas fa-save mr-1"></i>${currentPatientId ? 'Update' : 'Add'} Patient`);
        }
    });
}

// Populate form with patient data
function populateForm(patient) {
    $('#patient_id').val(patient.id);
    $('#first_name').val(patient.first_name);
    $('#last_name').val(patient.last_name);
    $('#phone').val(patient.phone);
    $('#email').val(patient.email);
    $('#date_of_birth').val(patient.date_of_birth);
    $('#gender').val(patient.gender);
    $('#address').val(patient.address);
}

// Reset form
function resetForm() {
    $('#patientForm')[0].reset();
    $('#patientForm').find('.is-invalid').removeClass('is-invalid');
    currentPatientId = null;
}
</script>

<?php include 'includes/adminlte_footer.php'; ?>
