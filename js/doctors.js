// Doctors Management JavaScript - AdminLTE3 with AJAX
// PathLab Pro - Complete Doctor Management System

/**
 * Doctors Management JavaScript - AdminLTE3 AJAX
 */

let doctorsTable;

// Initialize doctors page when DOM is ready
$(document).ready(function() {
    console.log('Doctors page initializing...');
    initializeDoctorsPage();
});

// Main initialization function
function initializeDoctorsPage() {
    try {
        // Initialize DataTable
        initializeDoctorsDataTable();
        
        // Initialize form handler
        initializeDoctorForm();
        
        // Initialize filters
        initializeFilters();
        
        console.log('Doctors page initialized successfully');
    } catch (error) {
        console.error('Error initializing doctors page:', error);
        showToast('error', 'Failed to initialize page: ' + error.message);
    }
}

// Initialize DataTable with server-side processing
function initializeDoctorsDataTable() {
    if ($.fn.DataTable.isDataTable('#doctorsTable')) {
        $('#doctorsTable').DataTable().destroy();
    }

    doctorsTable = $('#doctorsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: 'ajax/doctors_datatable.php',
            type: 'POST',
            data: function(d) {
                // Add custom filters
                d.status = $('#statusFilter').val() || '';
                d.specialization = $('#specializationFilter').val() || '';
                return d;
            },
            error: function(xhr, error, code) {
                console.error('DataTable AJAX Error:', error);
                showToast('error', 'Failed to load data');
            }
        },
        columns: [
            {
                data: 'doctor_id',
                name: 'doctor_id',
                title: 'ID',
                width: '80px'
            },
            {
                data: null,
                name: 'full_name',
                title: 'Doctor Name',
                render: function(data, type, row) {
                    return '<strong>' + (row.first_name || '') + ' ' + (row.last_name || '') + '</strong>';
                }
            },
            {
                data: 'specialization',
                name: 'specialization',
                title: 'Specialization',
                render: function(data, type, row) {
                    return data || '-';
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
                data: 'email',
                name: 'email',
                title: 'Email',
                render: function(data, type, row) {
                    return data || '-';
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
            emptyTable: 'No doctors found',
            zeroRecords: 'No matching doctors found'
        }
    });
    
    console.log('Doctors DataTable initialized successfully');
}

// Generate action buttons for each row
function generateActionButtons(row) {
    return `
        <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-info" onclick="viewDoctor(${row.id})" title="View">
                <i class="fas fa-eye"></i>
            </button>
            <button type="button" class="btn btn-warning" onclick="editDoctor(${row.id})" title="Edit">
                <i class="fas fa-edit"></i>
            </button>
            <button type="button" class="btn btn-danger" onclick="confirmDeleteDoctor(${row.id})" title="Delete">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;
}

// Initialize doctor form handler
function initializeDoctorForm() {
    $('#doctorForm').on('submit', function(e) {
        e.preventDefault();
        handleDoctorFormSubmit();
    });
}

// Handle doctor form submission
function handleDoctorFormSubmit() {
    var form = $('#doctorForm');
    var formData = new FormData(form[0]);
    var submitButton = form.find('button[type="submit"]');
    
    // Show loading state
    showFormLoader(submitButton);
    
    // Determine if this is an update or create
    var doctorId = form.find('input[name="id"]').val();
    formData.append('action', doctorId ? 'update' : 'create');
    
    $.ajax({
        url: 'api/doctors_api.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            hideFormLoader(submitButton);
            
            if (response.success) {
                $('#doctorModal').modal('hide');
                doctorsTable.ajax.reload(null, false);
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
        doctorsTable.ajax.reload();
    });
    
    // Specialization filter change
    $('#specializationFilter').on('change', function() {
        doctorsTable.ajax.reload();
    });
}

// Show add doctor modal
function showAddDoctorModal() {
    $('#doctorForm')[0].reset();
    $('#doctorForm input[name="id"]').val('');
    $('#doctorModal .modal-title').html('<i class="fas fa-plus mr-2"></i>Add New Doctor');
    $('#doctorModal').modal('show');
}

// View doctor details
function viewDoctor(id) {
    $.ajax({
        url: 'api/doctors_api.php',
        type: 'GET',
        data: { action: 'get', id: id },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                var doctor = response.data;
                var html = `
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Doctor ID:</strong> ${doctor.doctor_id || 'N/A'}</p>
                            <p><strong>Name:</strong> ${doctor.first_name} ${doctor.last_name}</p>
                            <p><strong>Email:</strong> ${doctor.email || 'N/A'}</p>
                            <p><strong>Phone:</strong> ${doctor.phone || 'N/A'}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Specialization:</strong> ${doctor.specialization || 'N/A'}</p>
                            <p><strong>Status:</strong> <span class="badge badge-${doctor.status === 'active' ? 'success' : 'secondary'}">${doctor.status || 'N/A'}</span></p>
                            <p><strong>Created:</strong> ${doctor.created_at || 'N/A'}</p>
                        </div>
                    </div>
                `;
                
                Swal.fire({
                    title: `Doctor Details - ${doctor.first_name} ${doctor.last_name}`,
                    html: html,
                    width: 600,
                    showCloseButton: true,
                    showConfirmButton: false
                });
            } else {
                showToast('error', response.message || 'Failed to load doctor details');
            }
        },
        error: function() {
            showToast('error', 'Failed to load doctor details');
        }
    });
}

// Edit doctor
function editDoctor(id) {
    $.ajax({
        url: 'api/doctors_api.php',
        type: 'GET',
        data: { action: 'get', id: id },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                var doctor = response.data;
                var form = $('#doctorForm');
                
                // Fill form with doctor data
                form.find('input[name="id"]').val(doctor.id);
                form.find('input[name="first_name"]').val(doctor.first_name);
                form.find('input[name="last_name"]').val(doctor.last_name);
                form.find('input[name="email"]').val(doctor.email);
                form.find('input[name="phone"]').val(doctor.phone);
                form.find('input[name="specialization"]').val(doctor.specialization);
                form.find('select[name="status"]').val(doctor.status);
                
                // Update modal title and show
                $('#doctorModal .modal-title').html('<i class="fas fa-edit mr-2"></i>Edit Doctor');
                $('#doctorModal').modal('show');
            } else {
                showToast('error', response.message || 'Failed to load doctor data');
            }
        },
        error: function() {
            showToast('error', 'Failed to load doctor data');
        }
    });
}

// Confirm delete doctor
function confirmDeleteDoctor(id) {
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
            deleteDoctor(id);
        }
    });
}

// Delete doctor
function deleteDoctor(id) {
    $.ajax({
        url: 'api/doctors_api.php',
        type: 'POST',
        data: { action: 'delete', id: id },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                doctorsTable.ajax.reload(null, false);
                showToast('success', response.message);
            } else {
                showToast('error', response.message || 'Failed to delete doctor');
            }
        },
        error: function() {
            showToast('error', 'Failed to delete doctor');
        }
    });
}

// Refresh table
function refreshTable() {
    if (doctorsTable) {
        doctorsTable.ajax.reload(null, false);
        showToast('info', 'Table refreshed successfully');
    }
}

// Filter table
function filterTable() {
    if (doctorsTable) {
        doctorsTable.ajax.reload();
    }
}

// Export doctors
function exportDoctors() {
    window.open('api/doctors_api.php?action=export&format=csv', '_blank');
}

// Utility functions
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
window.showAddDoctorModal = showAddDoctorModal;
window.viewDoctor = viewDoctor;
window.editDoctor = editDoctor;
window.confirmDeleteDoctor = confirmDeleteDoctor;
window.deleteDoctor = deleteDoctor;
window.refreshTable = refreshTable;
window.filterTable = filterTable;
window.exportDoctors = exportDoctors;
