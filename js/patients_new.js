// Patients Management JavaScript - AdminLTE3 with AJAX
// PathLab Pro - Complete Patient Management System

let patientsTable;

// Initialize patients page when DOM is ready
$(document).ready(function() {
    console.log('Patients page initializing...');
    initializePatientsPage();
});

// Main initialization function
function initializePatientsPage() {
    try {
        // Initialize DataTable
        initializePatientsDataTable();
        
        // Initialize form handler
        initializePatientForm();
        
        // Initialize filters
        initializeFilters();
        
        console.log('Patients page initialized successfully');
    } catch (error) {
        console.error('Error initializing patients page:', error);
        showToast('error', 'Failed to initialize page: ' + error.message);
    }
}

// Initialize DataTable with server-side processing
function initializePatientsDataTable() {
    if ($.fn.DataTable.isDataTable('#patientsTable')) {
        $('#patientsTable').DataTable().destroy();
    }

    patientsTable = $('#patientsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: 'ajax/patients_datatable.php',
            type: 'POST',
            data: function(d) {
                // Add custom filters
                d.status = $('#statusFilter').val() || '';
                d.blood_group = $('#bloodGroupFilter').val() || '';
                d.date_from = $('#dateFromFilter').val() || '';
                d.date_to = $('#dateToFilter').val() || '';
                return d;
            },
            error: function(xhr, error, code) {
                console.error('DataTable AJAX Error:', error);
                showToast('error', 'Failed to load data');
            }
        },
        columns: [
            {
                data: 'patient_id',
                name: 'patient_id',
                title: 'ID',
                width: '80px'
            },
            {
                data: null,
                name: 'full_name',
                title: 'Patient Name',
                render: function(data, type, row) {
                    return '<strong>' + (row.first_name || '') + ' ' + (row.last_name || '') + '</strong>';
                }
            },
            {
                data: 'phone',
                name: 'phone',
                title: 'Phone',
                render: function(data, type, row) {
                    return data || '-';
                }
            },
            {
                data: 'blood_group',
                name: 'blood_group',
                title: 'Blood Group',
                render: function(data, type, row) {
                    if (!data) return '-';
                    return '<span class="badge badge-info">' + data + '</span>';
                }
            },
            {
                data: 'date_of_birth',
                name: 'age',
                title: 'Age',
                render: function(data, type, row) {
                    if (!data) return '-';
                    var age = calculateAge(data);
                    return age + ' years';
                }
            },
            {
                data: 'gender',
                name: 'gender',
                title: 'Gender',
                render: function(data, type, row) {
                    if (!data) return '-';
                    return capitalizeFirst(data);
                }
            },
            {
                data: 'status',
                name: 'status',
                title: 'Status',
                render: function(data, type, row) {
                    var badgeClass = data === 'active' ? 'success' : 'secondary';
                    return '<span class="badge badge-' + badgeClass + '">' + capitalizeFirst(data || 'inactive') + '</span>';
                }
            },
            {
                data: 'created_at',
                name: 'created_at',
                title: 'Created',
                render: function(data, type, row) {
                    if (!data) return '-';
                    return formatDate(data);
                }
            },
            {
                data: null,
                name: 'actions',
                title: 'Actions',
                orderable: false,
                searchable: false,
                width: '120px',
                render: function(data, type, row) {
                    return generateActionButtons(row);
                }
            }
        ],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [[0, 'desc']],
        responsive: true,
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'copy',
                text: '<i class="fas fa-copy"></i> Copy',
                className: 'btn btn-secondary btn-sm'
            },
            {
                extend: 'csv',
                text: '<i class="fas fa-file-csv"></i> CSV',
                className: 'btn btn-success btn-sm'
            },
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-success btn-sm'
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn btn-danger btn-sm'
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Print',
                className: 'btn btn-info btn-sm'
            }
        ],
        language: {
            processing: '<i class="fas fa-spinner fa-spin"></i> Loading...',
            emptyTable: 'No patients found',
            zeroRecords: 'No matching patients found'
        }
    });
    
    console.log('DataTable initialized successfully');
}

// Generate action buttons for each row
function generateActionButtons(row) {
    return `
        <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-info" onclick="viewPatient(${row.id})" title="View">
                <i class="fas fa-eye"></i>
            </button>
            <button type="button" class="btn btn-warning" onclick="editPatient(${row.id})" title="Edit">
                <i class="fas fa-edit"></i>
            </button>
            <button type="button" class="btn btn-danger" onclick="confirmDeletePatient(${row.id})" title="Delete">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;
}

// Initialize patient form handler
function initializePatientForm() {
    $('#patientForm').on('submit', function(e) {
        e.preventDefault();
        handlePatientFormSubmit();
    });
}

// Handle patient form submission
function handlePatientFormSubmit() {
    var form = $('#patientForm');
    var formData = new FormData(form[0]);
    var submitButton = form.find('button[type="submit"]');
    
    // Show loading state
    showFormLoader(submitButton);
    
    // Determine if this is an update or create
    var patientId = form.find('input[name="id"]').val();
    formData.append('action', patientId ? 'update' : 'create');
    
    $.ajax({
        url: 'api/patients_api.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            hideFormLoader(submitButton);
            
            if (response.success) {
                $('#patientModal').modal('hide');
                patientsTable.ajax.reload(null, false);
                showToast('success', response.message);
                form[0].reset();
            } else {
                showToast('error', response.message || 'An error occurred');
            }
        },
        error: function(xhr, status, error) {
            hideFormLoader(submitButton);
            console.error('Form submission error:', error);
            showToast('error', 'Request failed: ' + error);
        }
    });
}

// Initialize filters
function initializeFilters() {
    // Status filter change
    $('#statusFilter').on('change', function() {
        patientsTable.ajax.reload();
    });
    
    // Blood group filter change
    $('#bloodGroupFilter').on('change', function() {
        patientsTable.ajax.reload();
    });
    
    // Date filters change
    $('#dateFromFilter, #dateToFilter').on('change', function() {
        patientsTable.ajax.reload();
    });
}

// Show add patient modal
function showAddPatientModal() {
    $('#patientForm')[0].reset();
    $('#patientForm input[name="id"]').val('');
    $('#patientModal .modal-title').html('<i class="fas fa-plus mr-2"></i>Add New Patient');
    $('#patientModal').modal('show');
}

// View patient details
function viewPatient(id) {
    $.ajax({
        url: 'api/patients_api.php',
        type: 'GET',
        data: { action: 'get', id: id },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                var patient = response.data;
                var html = `
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Patient ID:</strong> ${patient.patient_id || 'N/A'}</p>
                            <p><strong>Name:</strong> ${patient.first_name} ${patient.last_name}</p>
                            <p><strong>Email:</strong> ${patient.email || 'N/A'}</p>
                            <p><strong>Phone:</strong> ${patient.phone || 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Date of Birth:</strong> ${patient.date_of_birth || 'N/A'}</p>
                            <p><strong>Gender:</strong> ${patient.gender || 'N/A'}</p>
                            <p><strong>Blood Group:</strong> ${patient.blood_group || 'N/A'}</p>
                            <p><strong>Status:</strong> <span class="badge badge-${patient.status === 'active' ? 'success' : 'secondary'}">${patient.status || 'N/A'}</span></p>
                        </div>
                    </div>
                    ${patient.address ? '<div class="row"><div class="col-12"><p><strong>Address:</strong> ' + patient.address + '</p></div></div>' : ''}
                    ${patient.notes ? '<div class="row"><div class="col-12"><p><strong>Notes:</strong> ' + patient.notes + '</p></div></div>' : ''}
                `;
                
                Swal.fire({
                    title: `Patient Details - ${patient.first_name} ${patient.last_name}`,
                    html: html,
                    width: 600,
                    showCloseButton: true,
                    showConfirmButton: false
                });
            } else {
                showToast('error', response.message || 'Failed to load patient details');
            }
        },
        error: function() {
            showToast('error', 'Failed to load patient details');
        }
    });
}

// Edit patient
function editPatient(id) {
    $.ajax({
        url: 'api/patients_api.php',
        type: 'GET',
        data: { action: 'get', id: id },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                var patient = response.data;
                var form = $('#patientForm');
                
                // Fill form with patient data
                form.find('input[name="id"]').val(patient.id);
                form.find('input[name="first_name"]').val(patient.first_name);
                form.find('input[name="last_name"]').val(patient.last_name);
                form.find('input[name="email"]').val(patient.email);
                form.find('input[name="phone"]').val(patient.phone);
                form.find('input[name="date_of_birth"]').val(patient.date_of_birth);
                form.find('select[name="gender"]').val(patient.gender);
                form.find('select[name="blood_group"]').val(patient.blood_group);
                form.find('select[name="status"]').val(patient.status);
                form.find('textarea[name="address"]').val(patient.address);
                form.find('textarea[name="notes"]').val(patient.notes);
                
                // Update modal title and show
                $('#patientModal .modal-title').html('<i class="fas fa-edit mr-2"></i>Edit Patient');
                $('#patientModal').modal('show');
            } else {
                showToast('error', response.message || 'Failed to load patient data');
            }
        },
        error: function() {
            showToast('error', 'Failed to load patient data');
        }
    });
}

// Confirm delete patient
function confirmDeletePatient(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            deletePatient(id);
        }
    });
}

// Delete patient
function deletePatient(id) {
    $.ajax({
        url: 'api/patients_api.php',
        type: 'POST',
        data: { action: 'delete', id: id },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                patientsTable.ajax.reload(null, false);
                showToast('success', response.message);
            } else {
                showToast('error', response.message || 'Failed to delete patient');
            }
        },
        error: function() {
            showToast('error', 'Failed to delete patient');
        }
    });
}

// Refresh table
function refreshTable() {
    if (patientsTable) {
        patientsTable.ajax.reload(null, false);
        showToast('info', 'Table refreshed successfully');
    }
}

// Filter table
function filterTable() {
    if (patientsTable) {
        patientsTable.ajax.reload();
    }
}

// Export patients
function exportPatients() {
    window.open('api/patients_api.php?action=export&format=csv', '_blank');
}

// Utility functions
function calculateAge(birthDate) {
    var today = new Date();
    var birth = new Date(birthDate);
    var age = today.getFullYear() - birth.getFullYear();
    var monthDiff = today.getMonth() - birth.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
        age--;
    }
    
    return age;
}

function capitalizeFirst(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
}

function formatDate(dateString) {
    if (!dateString) return '';
    var date = new Date(dateString);
    return date.toLocaleDateString();
}

function showFormLoader(button) {
    button.prop('disabled', true);
    var originalText = button.html();
    button.data('original-text', originalText);
    button.html('<i class="fas fa-spinner fa-spin mr-1"></i> Processing...');
}

function hideFormLoader(button) {
    button.prop('disabled', false);
    var originalText = button.data('original-text');
    if (originalText) {
        button.html(originalText);
    }
}

// Make functions globally accessible
window.showAddPatientModal = showAddPatientModal;
window.viewPatient = viewPatient;
window.editPatient = editPatient;
window.confirmDeletePatient = confirmDeletePatient;
window.deletePatient = deletePatient;
window.refreshTable = refreshTable;
window.filterTable = filterTable;
window.exportPatients = exportPatients;
