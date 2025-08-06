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
          <div class="card card-outline card-primary">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-list mr-1"></i>
                All Patients
              </h3>
              <div class="card-tools">
                <button type="button" class="btn btn-primary btn-sm" id="addPatientBtn" onclick="openPatientModal()">
                  <i class="fas fa-plus mr-1"></i>Add New Patient
                </button>
                <button type="button" class="btn btn-success btn-sm ml-1" id="refreshBtn" onclick="refreshPatients()">
                  <i class="fas fa-sync-alt mr-1"></i>Refresh
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
                <button type="button" class="btn btn-primary" id="addFirstPatientBtn" onclick="openPatientModal()">
                  <i class="fas fa-plus mr-2"></i>Add First Patient
                </button>
              </div>
              
              <div id="patients-table-container" style="display: none;">
                <div class="table-responsive">
                  <table id="patientsTable" class="table table-bordered table-striped table-hover">
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
      <form id="patientForm" onsubmit="return handleFormSubmit(event);">
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
                    <input type="text" class="form-control" id="first_name" name="first_name" required placeholder="Enter first name">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="last_name">Last Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="last_name" name="last_name" required placeholder="Enter last name">
                  </div>
                </div>
              </div>
              
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="phone">Phone Number <span class="text-danger">*</span></label>
                    <input type="tel" class="form-control" id="phone" name="phone" required placeholder="Enter phone number">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter email address">
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
        </div>
        
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i>Cancel
          </button>
          <button type="button" class="btn btn-primary" id="submit-btn" onclick="handleFormSubmit(event);">
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
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title" id="viewPatientModalLabel">
          <i class="fas fa-eye mr-2"></i>
          Patient Details
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
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
        <button type="button" class="btn btn-warning" id="editFromViewBtn">
          <i class="fas fa-edit mr-1"></i>Edit Patient
        </button>
      </div>
    </div>
  </div>
</div>

<style>
/* Enhanced CSS for Patients Management */
.card-outline.card-primary {
    border-top: 3px solid #007bff;
}

.table-hover tbody tr:hover {
    background-color: rgba(0,123,255,.075);
}

.badge {
    font-size: 0.75em;
    padding: 0.375rem 0.5rem;
}

.btn-group .btn {
    margin: 0 1px;
    padding: 0.25rem 0.5rem;
}

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

.modal-header.bg-primary {
    background-color: #007bff !important;
}

.modal-header.bg-info {
    background-color: #17a2b8 !important;
}

.text-muted {
    color: #6c757d !important;
}

.spinner-border {
    width: 2rem;
    height: 2rem;
}

.is-invalid {
    border-color: #dc3545;
}

.invalid-feedback {
    display: block;
    width: 100%;
    margin-top: 0.25rem;
    font-size: 0.875em;
    color: #dc3545;
}

@media (max-width: 768px) {
    .btn-group .btn {
        padding: 0.125rem 0.25rem;
        font-size: 0.75rem;
    }
    
    .table-responsive {
        font-size: 0.875rem;
    }
}
</style>

<script>
// Enhanced Patients Management System
let patientsTable;
let currentPatientId = null;

// Debug function to check if libraries are loaded
function checkLibraries() {
    console.log('jQuery loaded:', typeof $ !== 'undefined');
    console.log('Bootstrap loaded:', typeof $.fn.modal !== 'undefined');
    console.log('DataTables loaded:', typeof $.fn.DataTable !== 'undefined');
    console.log('Toastr loaded:', typeof toastr !== 'undefined');
    console.log('SweetAlert2 loaded:', typeof Swal !== 'undefined');
}

$(document).ready(function() {
    console.log('Document ready - Patients page');
    checkLibraries();
    
    // Wait longer to ensure AdminLTE is fully initialized first
    setTimeout(function() {
        initializePatientsPage();
    }, 1000);
});

function initializePatientsPage() {
    console.log('Initializing patients page...');
    
    // Check if required elements exist
    if ($('#patientForm').length === 0) {
        console.error('Patient form not found, retrying in 500ms...');
        setTimeout(initializePatientsPage, 500);
        return;
    }
    
    // Configure toastr options
    configureToastr();
    
    // Initialize DataTable
    initializeDataTable();
    
    // Load patients data
    loadPatients();
    
    // Event handlers - with additional delay to ensure DOM is fully ready
    setTimeout(function() {
        setupEventHandlers();
    }, 200);
    
    console.log('Patients page initialized successfully');
}

function configureToastr() {
    if (typeof toastr !== 'undefined') {
        toastr.options = {
            closeButton: true,
            debug: false,
            newestOnTop: true,
            progressBar: true,
            positionClass: "toast-top-right",
            preventDuplicates: false,
            onclick: null,
            showDuration: "300",
            hideDuration: "1000",
            timeOut: "5000",
            extendedTimeOut: "1000",
            showEasing: "swing",
            hideEasing: "linear",
            showMethod: "fadeIn",
            hideMethod: "fadeOut"
        };
    }
}

function showToast(type, message, title = '') {
    console.log('Showing toast:', type, message);
    
    if (typeof toastr !== 'undefined') {
        switch(type) {
            case 'success':
                toastr.success(message, title || 'Success');
                break;
            case 'error':
                toastr.error(message, title || 'Error');
                break;
            case 'warning':
                toastr.warning(message, title || 'Warning');
                break;
            case 'info':
                toastr.info(message, title || 'Info');
                break;
        }
    } else if (typeof Swal !== 'undefined') {
        // Fallback to SweetAlert2
        Swal.fire({
            icon: type,
            title: title || (type.charAt(0).toUpperCase() + type.slice(1)),
            text: message,
            timer: 3000,
            showConfirmButton: false
        });
    } else {
        // Fallback to console and alert
        console.log('Toast:', type, message);
        alert(type.toUpperCase() + ': ' + message);
    }
}

function initializeDataTable() {
    patientsTable = $('#patientsTable').DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 25,
        order: [[0, 'desc']],
        columnDefs: [
            { 
                targets: -1,
                orderable: false,
                searchable: false,
                width: '120px'
            },
            {
                targets: [0],
                width: '60px'
            }
        ],
        language: {
            emptyTable: "No patients found",
            search: "Search patients:",
            lengthMenu: "Show _MENU_ patients",
            info: "Showing _START_ to _END_ of _TOTAL_ patients",
            paginate: {
                first: "First",
                last: "Last", 
                next: "Next",
                previous: "Previous"
            }
        }
    });
}

function setupEventHandlers() {
    console.log('Setting up event handlers...');
    
    // Add patient button handlers
    $('#addPatientBtn, #addFirstPatientBtn').off('click').on('click', function() {
        console.log('Add patient button clicked');
        openPatientModal();
    });
    
    // Refresh button
    $('#refreshBtn').off('click').on('click', function() {
        console.log('Refresh button clicked');
        loadPatients();
        showToast('info', 'Patient list refreshed');
    });
    
    // Enhanced form submission with multiple handlers
    setupFormSubmission();
    
    // Modal reset on close
    $('#patientModal').off('hidden.bs.modal').on('hidden.bs.modal', function() {
        console.log('Modal closed, resetting form');
        resetForm();
    });
    
    // Edit from view modal
    $('#editFromViewBtn').off('click').on('click', function() {
        console.log('Edit from view button clicked');
        editPatientFromView();
    });
    
    // Real-time validation
    $('#phone').off('input').on('input', function() {
        validatePhone($(this));
    });
    
    $('#email').off('input').on('input', function() {
        validateEmail($(this));
    });
    
    console.log('Event handlers set up successfully');
}

// Enhanced form submission setup
function setupFormSubmission() {
    console.log('Setting up form submission handlers...');
    
    // Check if form exists
    if ($('#patientForm').length === 0) {
        console.error('Patient form not found for event binding');
        return;
    }
    
    // Remove any existing handlers
    $('#patientForm').off('submit.patients');
    $('#submit-btn').off('click.submitBtn');
    $(document).off('submit.patientsForm', '#patientForm');
    
    // Primary form submission handler
    $('#patientForm').on('submit.patients', function(e) {
        console.log('Form submission event triggered');
        e.preventDefault();
        e.stopPropagation();
        
        if (validateForm()) {
            savePatient();
        }
        
        return false;
    });
    
    // Additional handler for submit button click
    $('#submit-btn').on('click.submitBtn', function(e) {
        console.log('Submit button clicked');
        e.preventDefault();
        e.stopPropagation();
        
        // Trigger form validation and submission
        if (validateForm()) {
            savePatient();
        }
        
        return false;
    });
    
    // Backup handler using document delegation
    $(document).on('submit.patientsForm', '#patientForm', function(e) {
        console.log('Delegated form submission handler triggered');
        e.preventDefault();
        e.stopImmediatePropagation();
        
        if (validateForm()) {
            savePatient();
        }
        
        return false;
    });
    
    console.log('Form submission handlers setup complete');
}

// Global functions for onclick handlers
function refreshPatients() {
    console.log('Refresh patients called');
    loadPatients();
    showToast('info', 'Patient list refreshed');
}

// Global form submission handler
function handleFormSubmit(event) {
    console.log('Global form submit handler called');
    event.preventDefault();
    event.stopPropagation();
    
    if (validateForm()) {
        savePatient();
    }
    
    return false;
}

// Make functions globally available
window.openPatientModal = openPatientModal;
window.refreshPatients = refreshPatients;
window.handleFormSubmit = handleFormSubmit;
window.viewPatient = viewPatient;
window.editPatient = editPatient;
window.deletePatient = deletePatient;

function loadPatients() {
    showLoading(true);
    
    $.ajax({
        url: 'api/patients_api.php',
        method: 'GET',
        data: { action: 'list' },
        dataType: 'json',
        success: function(response) {
            showLoading(false);
            
            if (response.success && response.data && response.data.length > 0) {
                populateTable(response.data);
                showPatientsTable(true);
            } else {
                showNoData(true);
            }
        },
        error: function(xhr, status, error) {
            showLoading(false);
            console.error('Error loading patients:', error);
            showToast('error', 'Failed to load patients. Please try again.');
        }
    });
}

function populateTable(patients) {
    patientsTable.clear();
    
    patients.forEach(function(patient) {
        const age = calculateAge(patient.date_of_birth);
        const statusBadge = `<span class="badge badge-${patient.status === 'active' ? 'success' : 'secondary'}">${patient.status.charAt(0).toUpperCase() + patient.status.slice(1)}</span>`;
        
        const actions = `
            <div class="btn-group" role="group">
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
            `<div><strong>${patient.first_name} ${patient.last_name}</strong><br><small class="text-muted">${patient.gender ? patient.gender.charAt(0).toUpperCase() + patient.gender.slice(1) : 'Not specified'}</small></div>`,
            patient.phone ? `<a href="tel:${patient.phone}">${patient.phone}</a>` : '<span class="text-muted">Not provided</span>',
            patient.email ? `<a href="mailto:${patient.email}">${patient.email}</a>` : '<span class="text-muted">Not provided</span>',
            age ? `${age} years` : '<span class="text-muted">Unknown</span>',
            statusBadge,
            actions
        ]);
    });
    
    patientsTable.draw();
}

function openPatientModal() {
    console.log('Opening patient modal...');
    
    try {
        currentPatientId = null;
        $('#modal-title').text('Add New Patient');
        $('#submit-text').text('Add Patient');
        resetForm();
        
        // Check if modal exists
        if ($('#patientModal').length === 0) {
            console.error('Patient modal not found in DOM');
            showToast('error', 'Modal not found. Please refresh the page.');
            return;
        }
        
        // Check if Bootstrap modal is available
        if (typeof $.fn.modal === 'undefined') {
            console.error('Bootstrap modal not loaded');
            showToast('error', 'Modal functionality not available. Please refresh the page.');
            return;
        }
        
        $('#patientModal').modal('show');
        console.log('Patient modal opened successfully');
        
    } catch (error) {
        console.error('Error opening patient modal:', error);
        showToast('error', 'Error opening modal: ' + error.message);
    }
}

function editPatient(id) {
    currentPatientId = id;
    $('#modal-title').text('Edit Patient');
    $('#submit-text').text('Update Patient');
    
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
                showToast('error', 'Failed to load patient data');
            }
        },
        error: function() {
            showToast('error', 'Failed to load patient data');
        }
    });
}

function viewPatient(id) {
    $.ajax({
        url: 'api/patients_api.php',
        method: 'GET',
        data: { action: 'get', id: id },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                const patient = response.data;
                const age = calculateAge(patient.date_of_birth);
                
                const content = `
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-user mr-2"></i>Personal Information</h6>
                                </div>
                                <div class="card-body">
                                    <p><strong>Name:</strong> ${patient.first_name} ${patient.last_name}</p>
                                    <p><strong>Phone:</strong> ${patient.phone || 'Not provided'}</p>
                                    <p><strong>Email:</strong> ${patient.email || 'Not provided'}</p>
                                    <p><strong>Date of Birth:</strong> ${patient.date_of_birth ? new Date(patient.date_of_birth).toLocaleDateString() : 'Not provided'}</p>
                                    <p><strong>Age:</strong> ${age ? age + ' years' : 'Unknown'}</p>
                                    <p><strong>Gender:</strong> ${patient.gender ? patient.gender.charAt(0).toUpperCase() + patient.gender.slice(1) : 'Not specified'}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-info-circle mr-2"></i>Additional Information</h6>
                                </div>
                                <div class="card-body">
                                    <p><strong>Status:</strong> <span class="badge badge-${patient.status === 'active' ? 'success' : 'secondary'}">${patient.status.charAt(0).toUpperCase() + patient.status.slice(1)}</span></p>
                                    <p><strong>Registration Date:</strong> ${patient.created_at ? new Date(patient.created_at).toLocaleDateString() : 'Unknown'}</p>
                                    <p><strong>Last Updated:</strong> ${patient.updated_at ? new Date(patient.updated_at).toLocaleDateString() : 'Never'}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                $('#view-patient-content').html(content);
                currentPatientId = id;
                $('#viewPatientModal').modal('show');
            } else {
                showToast('error', 'Failed to load patient details');
            }
        },
        error: function() {
            showToast('error', 'Failed to load patient details');
        }
    });
}

function editPatientFromView() {
    if (currentPatientId) {
        $('#viewPatientModal').modal('hide');
        setTimeout(() => {
            editPatient(currentPatientId);
        }, 500);
    }
}

function deletePatient(id) {
    Swal.fire({
        title: 'Delete Patient',
        text: 'Are you sure you want to delete this patient? This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'api/patients_api.php',
                method: 'POST',
                data: { action: 'delete', id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showToast('success', response.message || 'Patient deleted successfully');
                        loadPatients();
                    } else {
                        showToast('error', response.message || 'Failed to delete patient');
                    }
                },
                error: function() {
                    showToast('error', 'Failed to delete patient');
                }
            });
        }
    });
}

function savePatient() {
    console.log('savePatient function called');
    
    try {
        const formData = new FormData($('#patientForm')[0]);
        const action = currentPatientId ? 'update' : 'create';
        
        console.log('Form data created, action:', action);
        console.log('Current patient ID:', currentPatientId);
        
        if (currentPatientId) {
            formData.append('id', currentPatientId);
        }
        formData.append('action', action);
        
        // Log form data for debugging
        for (let pair of formData.entries()) {
            console.log('Form field:', pair[0], '=', pair[1]);
        }
        
        // Show loading state
        setSubmitButtonLoading(true);
        
        console.log('Sending AJAX request to api/patients_api.php');
        
        $.ajax({
            url: 'api/patients_api.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                console.log('AJAX success response:', response);
                if (response.success) {
                    showToast('success', response.message || `Patient ${action === 'create' ? 'created' : 'updated'} successfully`);
                    $('#patientModal').modal('hide');
                    loadPatients();
                } else {
                    showToast('error', response.message || `Failed to ${action} patient`);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', status, error);
                console.error('Response:', xhr.responseText);
                showToast('error', `Failed to ${action} patient: ${error}`);
            },
            complete: function() {
                console.log('AJAX request completed');
                setSubmitButtonLoading(false);
            }
        });
        
    } catch (error) {
        console.error('Error in savePatient function:', error);
        showToast('error', 'Error saving patient: ' + error.message);
        setSubmitButtonLoading(false);
    }
}

function validateForm() {
    let isValid = true;
    
    // Clear previous validation
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').remove();
    
    // Required fields validation
    $('[required]').each(function() {
        if (!$(this).val().trim()) {
            isValid = false;
            $(this).addClass('is-invalid');
            $(this).after('<div class="invalid-feedback">This field is required.</div>');
        }
    });
    
    // Phone validation
    if (!validatePhone($('#phone'))) {
        isValid = false;
    }
    
    // Email validation
    if (!validateEmail($('#email'))) {
        isValid = false;
    }
    
    if (!isValid) {
        showToast('error', 'Please check the form for errors and try again.');
    }
    
    return isValid;
}

function validatePhone($input) {
    const phone = $input.val().trim();
    if (phone && !/^[\d\-\+\(\)\s]+$/.test(phone)) {
        $input.addClass('is-invalid');
        $input.siblings('.invalid-feedback').remove();
        $input.after('<div class="invalid-feedback">Please enter a valid phone number.</div>');
        return false;
    } else {
        $input.removeClass('is-invalid');
        $input.siblings('.invalid-feedback').remove();
        return true;
    }
}

function validateEmail($input) {
    const email = $input.val().trim();
    if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        $input.addClass('is-invalid');
        $input.siblings('.invalid-feedback').remove();
        $input.after('<div class="invalid-feedback">Please enter a valid email address.</div>');
        return false;
    } else {
        $input.removeClass('is-invalid');
        $input.siblings('.invalid-feedback').remove();
        return true;
    }
}

function populateForm(patient) {
    $('#patient_id').val(patient.id);
    $('#first_name').val(patient.first_name);
    $('#last_name').val(patient.last_name);
    $('#phone').val(patient.phone);
    $('#email').val(patient.email);
    $('#date_of_birth').val(patient.date_of_birth);
    $('#gender').val(patient.gender);
}

function resetForm() {
    $('#patientForm')[0].reset();
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').remove();
    currentPatientId = null;
}

function calculateAge(birthDate) {
    if (!birthDate) return null;
    const today = new Date();
    const birth = new Date(birthDate);
    let age = today.getFullYear() - birth.getFullYear();
    const monthDiff = today.getMonth() - birth.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
        age--;
    }
    
    return age;
}

function showLoading(show) {
    if (show) {
        $('#loading').show();
        $('#patients-table-container').hide();
        $('#no-data').hide();
    } else {
        $('#loading').hide();
    }
}

function showPatientsTable(show) {
    if (show) {
        $('#patients-table-container').show();
        $('#no-data').hide();
    }
}

function showNoData(show) {
    if (show) {
        $('#no-data').show();
        $('#patients-table-container').hide();
    }
}

function setSubmitButtonLoading(loading) {
    const $btn = $('#submit-btn');
    const $text = $('#submit-text');
    
    if (loading) {
        $btn.prop('disabled', true);
        $btn.find('i').removeClass('fa-save').addClass('fa-spinner fa-spin');
        $text.text('Saving...');
    } else {
        $btn.prop('disabled', false);
        $btn.find('i').removeClass('fa-spinner fa-spin').addClass('fa-save');
        $text.text(currentPatientId ? 'Update Patient' : 'Add Patient');
    }
}
</script>

<?php include 'includes/adminlte_footer.php'; ?>
