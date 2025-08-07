/**
 * Patients Management JavaScript - Clean Implementation
 * PathLab Pro - Patient Management System
 */

let patientsTable;
let patientsAPI;

// Wait for application to be initialized
function initializePatientsPage() {
    // Initialize API handler
    patientsAPI = new CrudOperations('api/patients_api.php', 'Patient');
    
    // Initialize DataTable
    initializePatientsDataTable();
    
    // Initialize form handlers
    initializePatientForm();
    
    // Initialize filters
    initializeFilters();
    
    console.log('Patients page initialized successfully');
}

// Initialize DataTable with enhanced error handling
function initializePatientsDataTable() {
    // Check if DataTables is available
    if (typeof jQuery === 'undefined') {
        console.error('jQuery is not available');
        showToast('error', 'jQuery library is not loaded');
        return;
    }
    
    if (!jQuery.fn.DataTable) {
        console.error('DataTables is not available');
        showToast('error', 'DataTables library is not loaded');
        return;
    }

    // Check if table element exists
    const tableElement = $('#patientsTable');
    if (tableElement.length === 0) {
        console.error('Patients table element not found');
        showToast('error', 'Patients table not found in DOM');
        return;
    }

    try {
        // Destroy existing table if it exists
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
                    d.status = $('#statusFilter').val() || '';
                    d.blood_group = $('#bloodGroupFilter').val() || '';
                    d.date_from = $('#dateFromFilter').val() || '';
                    d.date_to = $('#dateToFilter').val() || '';
                    return d;
                },
                error: function(xhr, error, thrown) {
                    console.error('DataTable AJAX error:', error, thrown, xhr);
                    hideLoading();
                    showToast('error', 'Failed to load patient data. Please refresh the page.');
                }
            },
            columns: [
                { 
                    data: 'id',
                    title: 'ID',
                    width: '50px',
                    className: 'text-center'
                },
                { 
                    data: 'full_name',
                    title: 'Full Name',
                    render: function(data, type, row) {
                        return escapeHtml(data || '');
                    }
                },
                { 
                    data: 'date_of_birth',
                    title: 'Age',
                    render: function(data, type, row) {
                        if (data) {
                            const age = calculateAge(data);
                            return `<span class="badge badge-info">${age} years</span>`;
                        }
                        return '<span class="badge badge-secondary">N/A</span>';
                    }
                },
                { 
                    data: 'gender',
                    title: 'Gender',
                    render: function(data, type, row) {
                        const badge = data === 'Male' ? 'badge-primary' : 
                                     data === 'Female' ? 'badge-success' : 'badge-secondary';
                        return `<span class="badge ${badge}">${escapeHtml(data || 'N/A')}</span>`;
                    }
                },
                { 
                    data: 'phone',
                    title: 'Phone',
                    render: function(data, type, row) {
                        return data ? formatPhone(data) : 'N/A';
                    }
                },
                { 
                    data: 'blood_group',
                    title: 'Blood Group',
                    render: function(data, type, row) {
                        return data ? `<span class="badge badge-danger">${escapeHtml(data)}</span>` : 'N/A';
                    }
                },
                { 
                    data: 'status',
                    title: 'Status',
                    render: function(data, type, row) {
                        const badge = data === 'Active' ? 'badge-success' : 'badge-secondary';
                        return `<span class="badge ${badge}">${escapeHtml(data || 'Inactive')}</span>`;
                    }
                },
                {
                    data: null,
                    title: 'Actions',
                    orderable: false,
                    className: 'text-center',
                    width: '120px',
                    render: function(data, type, row) {
                        return `
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-info btn-sm" onclick="viewPatient(${row.id})" title="View">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button" class="btn btn-warning btn-sm" onclick="editPatient(${row.id})" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="deletePatient(${row.id})" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            pageLength: 25,
            responsive: true,
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'copy',
                    className: 'btn btn-primary btn-sm',
                    text: '<i class="fas fa-copy"></i> Copy'
                },
                {
                    extend: 'csv',
                    className: 'btn btn-success btn-sm',
                    text: '<i class="fas fa-file-csv"></i> CSV'
                },
                {
                    extend: 'excel',
                    className: 'btn btn-success btn-sm',
                    text: '<i class="fas fa-file-excel"></i> Excel'
                },
                {
                    extend: 'pdf',
                    className: 'btn btn-danger btn-sm',
                    text: '<i class="fas fa-file-pdf"></i> PDF'
                }
            ],
            language: {
                processing: '<div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>',
                emptyTable: 'No patients found',
                zeroRecords: 'No matching patients found'
            },
            order: [[0, 'desc']]
        });
        
        console.log('Patients DataTable initialized successfully');
        
    } catch (error) {
        console.error('Error initializing patients DataTable:', error);
        showToast('error', 'Failed to initialize patients table: ' + error.message);
    }
}

// Initialize patient form
function initializePatientForm() {
    const patientForm = new FormHandler('#patientForm', async function(formData) {
        try {
            showLoading();
            
            const isEdit = formData.patient_id && formData.patient_id !== '';
            let response;
            
            if (isEdit) {
                response = await patientsAPI.update(formData.patient_id, formData);
            } else {
                response = await patientsAPI.create(formData);
            }
            
            if (response.success) {
                $('#patientModal').modal('hide');
                refreshTable();
                patientForm.resetForm();
            }
        } catch (error) {
            console.error('Form submission error:', error);
        } finally {
            hideLoading();
        }
    });
}

// Initialize filters
function initializeFilters() {
    // Status filter
    $('#statusFilter').on('change', function() {
        if (patientsTable) {
            patientsTable.ajax.reload();
        }
    });
    
    // Blood group filter
    $('#bloodGroupFilter').on('change', function() {
        if (patientsTable) {
            patientsTable.ajax.reload();
        }
    });
    
    // Date filters
    $('#dateFromFilter, #dateToFilter').on('change', function() {
        if (patientsTable) {
            patientsTable.ajax.reload();
        }
    });
}

// Filter table function (called from HTML onchange events)
function filterTable() {
    if (patientsTable) {
        patientsTable.ajax.reload();
    }
}

// Show add patient modal
function showAddPatientModal() {
    try {
        $('#patientModalLabel').text('Add New Patient');
        $('#patientForm')[0].reset();
        $('input[name="patient_id"]').val('');
        
        // Reset Select2 if available
        if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
            $('.select2').val(null).trigger('change');
        }
        
        $('#patientModal').modal('show');
    } catch (error) {
        console.error('Error showing add modal:', error);
        showToast('error', 'Failed to open add patient form');
    }
}

// Edit patient
async function editPatient(id) {
    try {
        showLoading();
        
        const response = await patientsAPI.read(id);
        
        if (response.success && response.data) {
            const patient = response.data;
            
            $('#patientModalLabel').text('Edit Patient');
            
            // Populate form fields
            $('input[name="patient_id"]').val(patient.id);
            $('input[name="first_name"]').val(patient.first_name || '');
            $('input[name="last_name"]').val(patient.last_name || '');
            $('input[name="date_of_birth"]').val(patient.date_of_birth || '');
            $('select[name="gender"]').val(patient.gender || '').trigger('change');
            $('input[name="phone"]').val(patient.phone || '');
            $('input[name="email"]').val(patient.email || '');
            $('textarea[name="address"]').val(patient.address || '');
            $('select[name="blood_group"]').val(patient.blood_group || '').trigger('change');
            $('textarea[name="emergency_contact"]').val(patient.emergency_contact || '');
            $('textarea[name="medical_history"]').val(patient.medical_history || '');
            $('select[name="status"]').val(patient.status || 'Active').trigger('change');
            
            $('#patientModal').modal('show');
        }
    } catch (error) {
        console.error('Error editing patient:', error);
        showToast('error', 'Failed to load patient details');
    } finally {
        hideLoading();
    }
}

// View patient details
async function viewPatient(id) {
    try {
        showLoading();
        
        const response = await patientsAPI.read(id);
        
        if (response.success && response.data) {
            const patient = response.data;
            
            const modalBody = `
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Name:</strong> ${escapeHtml(patient.full_name || 'N/A')}</p>
                        <p><strong>Date of Birth:</strong> ${patient.date_of_birth || 'N/A'}</p>
                        <p><strong>Age:</strong> ${calculateAge(patient.date_of_birth)} years</p>
                        <p><strong>Gender:</strong> ${escapeHtml(patient.gender || 'N/A')}</p>
                        <p><strong>Blood Group:</strong> ${escapeHtml(patient.blood_group || 'N/A')}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Phone:</strong> ${formatPhone(patient.phone) || 'N/A'}</p>
                        <p><strong>Email:</strong> ${escapeHtml(patient.email || 'N/A')}</p>
                        <p><strong>Status:</strong> ${escapeHtml(patient.status || 'N/A')}</p>
                        <p><strong>Created:</strong> ${patient.created_at || 'N/A'}</p>
                    </div>
                </div>
                ${patient.address ? `<p><strong>Address:</strong><br>${escapeHtml(patient.address)}</p>` : ''}
                ${patient.emergency_contact ? `<p><strong>Emergency Contact:</strong><br>${escapeHtml(patient.emergency_contact)}</p>` : ''}
                ${patient.medical_history ? `<p><strong>Medical History:</strong><br>${escapeHtml(patient.medical_history)}</p>` : ''}
            `;
            
            $('#patientDetailsBody').html(modalBody);
            $('#patientDetailsModal').modal('show');
        }
    } catch (error) {
        console.error('Error viewing patient:', error);
        showToast('error', 'Failed to load patient details');
    } finally {
        hideLoading();
    }
}

// Delete patient
async function deletePatient(id) {
    try {
        // Use SweetAlert if available, otherwise use confirm
        let confirmed = false;
        
        if (typeof Swal !== 'undefined') {
            const result = await Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            });
            confirmed = result.isConfirmed;
        } else {
            confirmed = confirm('Are you sure you want to delete this patient?');
        }
        
        if (confirmed) {
            showLoading();
            
            const response = await patientsAPI.delete(id);
            
            if (response.success) {
                refreshTable();
            }
        }
    } catch (error) {
        console.error('Error deleting patient:', error);
        showToast('error', 'Failed to delete patient');
    } finally {
        hideLoading();
    }
}

// Refresh table
function refreshTable() {
    if (patientsTable) {
        patientsTable.ajax.reload();
        showToast('info', 'Table refreshed successfully');
    }
}

// Export patients
function exportPatients() {
    showToast('info', 'Export functionality will be implemented');
}

// Print patient details
function printPatientDetails() {
    const printContent = document.getElementById('patientDetailsBody').innerHTML;
    const newWindow = window.open('', '_blank');
    
    newWindow.document.write(`
        <html>
            <head>
                <title>Patient Details</title>
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/css/bootstrap.min.css">
                <style>
                    body { margin: 20px; }
                    @media print {
                        .btn { display: none; }
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <h3>Patient Details</h3>
                    ${printContent}
                </div>
                <script>window.print();</script>
            </body>
        </html>
    `);
    
    newWindow.document.close();
}

// Enhanced initialization with better error handling
function waitForInitialization(callback, maxAttempts = 20, currentAttempt = 0) {
    if (currentAttempt >= maxAttempts) {
        console.error('Failed to initialize patients page after ' + maxAttempts + ' attempts');
        showToast('error', 'Failed to initialize patients page. Please refresh the page.');
        return;
    }
    
    // Check if all required dependencies are available
    if (typeof jQuery !== 'undefined' && 
        jQuery.fn.DataTable && 
        typeof CrudOperations !== 'undefined' &&
        typeof showToast !== 'undefined' &&
        typeof escapeHtml !== 'undefined') {
        try {
            callback();
        } catch (error) {
            console.error('Error during patients page initialization:', error);
            showToast('error', 'Error initializing patients page: ' + error.message);
        }
    } else {
        console.log('Waiting for dependencies... attempt ' + (currentAttempt + 1));
        setTimeout(() => {
            waitForInitialization(callback, maxAttempts, currentAttempt + 1);
        }, 250);
    }
}

// Initialize when DOM is ready and dependencies are loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        waitForInitialization(initializePatientsPage);
    });
} else {
    // DOM is already ready
    waitForInitialization(initializePatientsPage);
}
