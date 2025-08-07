// Doctors Management JavaScript
// AdminLTE3 Template with AJAX Operations

let doctorsTable;
let doctorsCrud;
let doctorsFormHandler;

$(document).ready(function() {
    initializeDoctorsPage();
});

function initializeDoctorsPage() {
    // Initialize CRUD operations
    doctorsCrud = new CrudOperations('api/doctors_api.php', 'Doctor');
    
    // Initialize form handler
    doctorsFormHandler = new FormHandler('#doctorForm', 'api/doctors_api.php', {
        onSuccess: function(response) {
            $('#doctorModal').modal('hide');
            doctorsTable.ajax.reload(null, false);
            showSuccess(response.message);
        }
    });
    
    // Initialize DataTable
    initializeDoctorsTable();
    
    // Initialize filters
    initializeFilters();
}

function initializeDoctorsTable() {
    const columns = [
        {
            data: 'doctor_id',
            name: 'doctor_id',
            title: 'ID',
            width: '80px'
        },
        {
            data: 'name',
            name: 'name',
            title: 'Name',
            render: function(data, type, row) {
                return `<strong>${data}</strong>`;
            }
        },
        {
            data: 'specialization',
            name: 'specialization',
            title: 'Specialization',
            render: function(data, type, row) {
                return `<span class="badge badge-primary">${data}</span>`;
            }
        },
        {
            data: 'phone',
            name: 'phone',
            title: 'Phone'
        },
        {
            data: 'hospital',
            name: 'hospital',
            title: 'Hospital',
            render: function(data, type, row) {
                return data || '-';
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
                        <button type="button" class="btn btn-info btn-action" onclick="viewDoctor(${row.doctor_id})" title="View">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="btn btn-warning btn-action" onclick="editDoctor(${row.doctor_id})" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-action" onclick="deleteDoctor(${row.doctor_id})" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
            }
        }
    ];

    doctorsTable = initializeDataTable('#doctorsTable', 'ajax/doctors_datatable.php', columns, {
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
    globalDataTable = doctorsTable;
}

function initializeFilters() {
    $('#statusFilter, #specializationFilter').on('change', function() {
        applyFilters();
    });
    
    $('#hospitalFilter').on('keyup', debounce(function() {
        applyFilters();
    }, 500));
}

function getCustomFilters() {
    return {
        status: $('#statusFilter').val(),
        specialization: $('#specializationFilter').val(),
        hospital: $('#hospitalFilter').val()
    };
}

function applyFilters() {
    if (doctorsTable) {
        doctorsTable.ajax.reload();
    }
}

function clearFilters() {
    $('#statusFilter, #specializationFilter, #hospitalFilter').val('').trigger('change');
    applyFilters();
}

// Modal Functions
function showAddDoctorModal() {
    resetForm('#doctorForm');
    $('#doctorId').val('');
    $('#doctorModal .modal-title').text('Add New Doctor');
    $('#doctorModal').modal('show');
}

async function editDoctor(id) {
    try {
        const doctor = await doctorsCrud.getById(id);
        
        // Populate form
        doctorsFormHandler.populateForm(doctor);
        
        $('#doctorModal .modal-title').text('Edit Doctor');
        $('#doctorModal').modal('show');
    } catch (error) {
        showError('Failed to load doctor data');
    }
}

async function viewDoctor(id) {
    try {
        showLoader('Loading doctor details...');
        const doctor = await doctorsCrud.getById(id);
        
        const detailsHtml = `
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Doctor ID:</th>
                            <td>${doctor.doctor_id}</td>
                        </tr>
                        <tr>
                            <th>Name:</th>
                            <td><strong>${doctor.name}</strong></td>
                        </tr>
                        <tr>
                            <th>Specialization:</th>
                            <td><span class="badge badge-primary">${doctor.specialization}</span></td>
                        </tr>
                        <tr>
                            <th>Phone:</th>
                            <td>${doctor.phone}</td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td>${doctor.email || 'N/A'}</td>
                        </tr>
                        <tr>
                            <th>License Number:</th>
                            <td>${doctor.license_number || 'N/A'}</td>
                        </tr>
                        <tr>
                            <th>Hospital/Clinic:</th>
                            <td>${doctor.hospital || 'N/A'}</td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td><span class="badge badge-${doctor.status === 'active' ? 'success' : 'secondary'}">${capitalizeFirst(doctor.status)}</span></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Address:</th>
                            <td>${doctor.address || 'N/A'}</td>
                        </tr>
                        <tr>
                            <th>Notes:</th>
                            <td>${doctor.notes || 'N/A'}</td>
                        </tr>
                        <tr>
                            <th>Registered:</th>
                            <td>${formatDateTime(doctor.created_at)}</td>
                        </tr>
                        <tr>
                            <th>Last Updated:</th>
                            <td>${formatDateTime(doctor.updated_at)}</td>
                        </tr>
                    </table>
                </div>
            </div>
        `;
        
        $('#doctorDetails').html(detailsHtml);
        $('#viewDoctorModal').modal('show');
        
    } catch (error) {
        showError('Failed to load doctor details');
    } finally {
        hideLoader();
    }
}

async function deleteDoctor(id) {
    try {
        await doctorsCrud.delete(id);
        doctorsTable.ajax.reload(null, false);
    } catch (error) {
        // Error handling is done in CrudOperations class
    }
}

// Export Functions
function exportDoctors() {
    const format = 'csv'; // Can be made dynamic
    const filters = getCustomFilters();
    
    AjaxUtils.exportData('api/doctors_api.php?action=export', format, filters);
}

// Utility Functions
function resetDoctorForm() {
    resetForm('#doctorForm');
}

// Global functions for external access
window.showAddDoctorModal = showAddDoctorModal;
window.editDoctor = editDoctor;
window.viewDoctor = viewDoctor;
window.deleteDoctor = deleteDoctor;
window.exportDoctors = exportDoctors;
window.applyFilters = applyFilters;
window.clearFilters = clearFilters;