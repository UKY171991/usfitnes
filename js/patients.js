// Patients Management JavaScript
// AdminLTE3 Template with AJAX Operations

let patientsTable;
let patientsCrud;
let patientsFormHandler;

$(document).ready(function() {
    initializePatientsPage();
});

function initializePatientsPage() {
    // Initialize CRUD operations
    patientsCrud = new CrudOperations('api/patients_api.php', 'Patient');
    
    // Initialize form handler
    patientsFormHandler = new FormHandler('#patientForm', 'api/patients_api.php', {
        onSuccess: function(response) {
            $('#patientModal').modal('hide');
            patientsTable.ajax.reload(null, false);
            showToast('success', response.message);
        }
    });
    
    // Initialize DataTable
    initializePatientsTable();
    
    // Initialize filters
    initializeFilters();
}

function initializePatientsTable() {
    const columns = [
        {
            data: 'patient_id',
            name: 'patient_id',
            title: 'ID',
            width: '80px'
        },
        {
            data: null,
            name: 'full_name',
            title: 'Name',
            render: function(data, type, row) {
                return `<strong>${row.first_name} ${row.last_name}</strong>`;
            }
        },
        {
            data: 'phone',
            name: 'phone',
            title: 'Phone'
        },
        {
            data: 'blood_group',
            name: 'blood_group',
            title: 'Blood Group',
            render: function(data, type, row) {
                return data ? `<span class="badge badge-info">${data}</span>` : '-';
            }
        },
        {
            data: 'date_of_birth',
            name: 'age',
            title: 'Age',
            render: function(data, type, row) {
                if (!data) return '-';
                const age = moment().diff(moment(data), 'years');
                return age + ' years';
            }
        },
        {
            data: 'status',
            name: 'status',
            title: 'Status',
            render: function(data, type, row) {
                const statusClass = data === 'active' ? 'success' : 'secondary';
                return `<span class="badge badge-${statusClass}">${capitalizeFirst(data)}</span>`;
            }
        },
        {
            data: null,
            name: 'actions',
            title: 'Actions',
            orderable: false,
            searchable: false,
            width: '150px',
            render: function(data, type, row) {
                return `
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-info btn-action" onclick="viewPatient(${row.patient_id})" title="View">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="btn btn-warning btn-action" onclick="editPatient(${row.patient_id})" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-action" onclick="deletePatient(${row.patient_id})" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
            }
        }
    ];

    patientsTable = initializeDataTable('#patientsTable', 'ajax/patients_datatable.php', columns, {
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true,
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        buttons: [
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
            }
        ]
    });
    
    // Store reference globally
    globalDataTable = patientsTable;
}

function initializeFilters() {
    $('#statusFilter, #bloodGroupFilter').on('change', function() {
        applyFilters();
    });
    
    $('#dateFilter').on('change', function() {
        applyFilters();
    });
}

function getCustomFilters() {
    return {
        status: $('#statusFilter').val(),
        blood_group: $('#bloodGroupFilter').val(),
        registration_date: $('#dateFilter').val()
    };
}

function applyFilters() {
    if (patientsTable) {
        patientsTable.ajax.reload();
    }
}

function clearFilters() {
    $('#statusFilter, #bloodGroupFilter, #dateFilter').val('').trigger('change');
    applyFilters();
}

// Modal Functions
function showAddPatientModal() {
    resetForm('#patientForm');
    $('#patientId').val('');
    $('#patientModal .modal-title').text('Add New Patient');
    $('#patientModal').modal('show');
}

async function editPatient(id) {
    try {
        const patient = await patientsCrud.getById(id);
        
        // Populate form
        patientsFormHandler.populateForm(patient);
        
        $('#patientModal .modal-title').text('Edit Patient');
        $('#patientModal').modal('show');
    } catch (error) {
        showError('Failed to load patient data');
    }
}

async function viewPatient(id) {
    try {
        showLoader('Loading patient details...');
        const patient = await patientsCrud.getById(id);
        
        const age = patient.date_of_birth ? moment().diff(moment(patient.date_of_birth), 'years') : 'N/A';
        
        const detailsHtml = `
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Patient ID:</th>
                            <td>${patient.patient_id}</td>
                        </tr>
                        <tr>
                            <th>Full Name:</th>
                            <td><strong>${patient.first_name} ${patient.last_name}</strong></td>
                        </tr>
                        <tr>
                            <th>Phone:</th>
                            <td>${patient.phone}</td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td>${patient.email || 'N/A'}</td>
                        </tr>
                        <tr>
                            <th>Date of Birth:</th>
                            <td>${patient.date_of_birth ? formatDate(patient.date_of_birth) : 'N/A'}</td>
                        </tr>
                        <tr>
                            <th>Age:</th>
                            <td>${age} years</td>
                        </tr>
                        <tr>
                            <th>Gender:</th>
                            <td>${patient.gender ? capitalizeFirst(patient.gender) : 'N/A'}</td>
                        </tr>
                        <tr>
                            <th>Blood Group:</th>
                            <td>${patient.blood_group ? `<span class="badge badge-info">${patient.blood_group}</span>` : 'N/A'}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Address:</th>
                            <td>${patient.address || 'N/A'}</td>
                        </tr>
                        <tr>
                            <th>Emergency Contact:</th>
                            <td>${patient.emergency_contact || 'N/A'}</td>
                        </tr>
                        <tr>
                            <th>Emergency Phone:</th>
                            <td>${patient.emergency_phone || 'N/A'}</td>
                        </tr>
                        <tr>
                            <th>Medical History:</th>
                            <td>${patient.medical_history || 'N/A'}</td>
                        </tr>
                        <tr>
                            <th>Allergies:</th>
                            <td>${patient.allergies || 'N/A'}</td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td><span class="badge badge-${patient.status === 'active' ? 'success' : 'secondary'}">${capitalizeFirst(patient.status)}</span></td>
                        </tr>
                        <tr>
                            <th>Registered:</th>
                            <td>${formatDateTime(patient.created_at)}</td>
                        </tr>
                        <tr>
                            <th>Last Updated:</th>
                            <td>${formatDateTime(patient.updated_at)}</td>
                        </tr>
                    </table>
                </div>
            </div>
        `;
        
        $('#patientDetails').html(detailsHtml);
        $('#viewPatientModal').modal('show');
        
    } catch (error) {
        showError('Failed to load patient details');
    } finally {
        hideLoader();
    }
}

async function deletePatient(id) {
    try {
        await patientsCrud.delete(id);
        patientsTable.ajax.reload(null, false);
    } catch (error) {
        // Error handling is done in CrudOperations class
    }
}

// Export Functions
function exportPatients() {
    const format = 'csv'; // Can be made dynamic
    const filters = getCustomFilters();
    
    AjaxUtils.exportData('api/patients_api.php?action=export', format, filters);
}

// Utility Functions
function resetPatientForm() {
    resetForm('#patientForm');
}

// Global functions for external access
window.showAddPatientModal = showAddPatientModal;
window.editPatient = editPatient;
window.viewPatient = viewPatient;
window.deletePatient = deletePatient;
window.exportPatients = exportPatients;
window.refreshTable = refreshTable;
window.filterTable = filterTable;

// Additional missing functions
function showAddPatientModal() {
    $('#patientForm')[0].reset();
    $('#patientForm').removeClass('was-validated');
    $('#patientModal .modal-title').html('<i class="fas fa-plus mr-2"></i>Add New Patient');
    $('#patientModal').modal('show');
}

function refreshTable() {
    if (patientsTable) {
        patientsTable.ajax.reload(null, false);
        showToast('info', 'Table refreshed successfully');
    }
}

function filterTable() {
    if (patientsTable) {
        patientsTable.ajax.reload();
    }
}

function viewPatient(id) {
    patientsCrud.get(id).then(response => {
        if (response.success) {
            // Fill modal with patient data for viewing
            const patient = response.data;
            let html = `
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
                        <p><strong>Status:</strong> <span class="badge badge-${patient.status === 'active' ? 'success' : 'secondary'}">${patient.status || 'N/A'}</span></p>
                        <p><strong>Created:</strong> ${patient.created_at || 'N/A'}</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <p><strong>Address:</strong> ${patient.address || 'N/A'}</p>
                        <p><strong>Notes:</strong> ${patient.notes || 'N/A'}</p>
                    </div>
                </div>
            `;
            
            Swal.fire({
                title: `Patient Details - ${patient.first_name} ${patient.last_name}`,
                html: html,
                width: 600,
                showCloseButton: true,
                showConfirmButton: false,
                customClass: {
                    container: 'patient-view-modal'
                }
            });
        }
    }).catch(error => {
        showToast('error', 'Failed to load patient details');
    });
}

function editPatient(id) {
    patientsCrud.get(id).then(response => {
        if (response.success) {
            const patient = response.data;
            
            // Fill form with patient data
            $('#patientForm input[name="id"]').val(patient.id);
            $('#patientForm input[name="patient_id"]').val(patient.patient_id);
            $('#patientForm input[name="first_name"]').val(patient.first_name);
            $('#patientForm input[name="last_name"]').val(patient.last_name);
            $('#patientForm input[name="email"]').val(patient.email);
            $('#patientForm input[name="phone"]').val(patient.phone);
            $('#patientForm input[name="date_of_birth"]').val(patient.date_of_birth);
            $('#patientForm select[name="gender"]').val(patient.gender);
            $('#patientForm select[name="status"]').val(patient.status);
            $('#patientForm textarea[name="address"]').val(patient.address);
            $('#patientForm textarea[name="notes"]').val(patient.notes);
            
            // Update modal title
            $('#patientModal .modal-title').html('<i class="fas fa-edit mr-2"></i>Edit Patient');
            $('#patientModal').modal('show');
        }
    }).catch(error => {
        showToast('error', 'Failed to load patient data');
    });
}