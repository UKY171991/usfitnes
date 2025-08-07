// Doctors Management JavaScript - AdminLTE3 with AJAX
$(document).ready(function() {
    // Initialize DataTable
    initializeDoctorsTable();
    
    // Initialize form validation
    initializeFormValidation('#doctorForm', {
        first_name: {
            required: true,
            minlength: 2,
            maxlength: 50
        },
        last_name: {
            required: true,
            minlength: 2,
            maxlength: 50
        },
        phone: {
            required: true,
            minlength: 10,
            maxlength: 15,
            pattern: /^[0-9+\-\s]+$/
        },
        email: {
            email: true
        },
        specialization: {
            required: true
        },
        license_number: {
            required: true,
            minlength: 3,
            maxlength: 50
        },
        status: {
            required: true
        }
    }, {
        first_name: {
            required: "Please enter first name",
            minlength: "First name must be at least 2 characters",
            maxlength: "First name cannot exceed 50 characters"
        },
        last_name: {
            required: "Please enter last name",
            minlength: "Last name must be at least 2 characters",
            maxlength: "Last name cannot exceed 50 characters"
        },
        phone: {
            required: "Please enter phone number",
            minlength: "Phone number must be at least 10 digits",
            maxlength: "Phone number cannot exceed 15 characters",
            pattern: "Please enter a valid phone number"
        },
        email: {
            email: "Please enter a valid email address"
        },
        specialization: {
            required: "Please select specialization"
        },
        license_number: {
            required: "Please enter license number",
            minlength: "License number must be at least 3 characters",
            maxlength: "License number cannot exceed 50 characters"
        },
        status: {
            required: "Please select status"
        }
    });
    
    // Handle form submission
    $('#doctorForm').on('submit', function(e) {
        e.preventDefault();
        
        if ($(this).valid()) {
            submitForm('#doctorForm', 'doctors_api.php', {
                onSuccess: function(response) {
                    $('#doctorModal').modal('hide');
                    refreshTable();
                    showSuccessToast(response.message);
                }
            });
        }
    });
});

// Initialize doctors DataTable
function initializeDoctorsTable() {
    window.dataTable = initializeDataTable('#doctorsTable', {
        ajax: {
            url: 'ajax/doctors_datatable.php',
            type: 'POST',
            data: function(d) {
                d.status_filter = $('#statusFilter').val();
                d.specialization_filter = $('#specializationFilter').val();
                d.date_from = $('#dateFromFilter').val();
                d.date_to = $('#dateToFilter').val();
            }
        },
        columns: [
            { 
                data: 'id',
                width: '60px',
                className: 'text-center'
            },
            { 
                data: 'full_name',
                render: function(data, type, row) {
                    return `<strong>${data}</strong>`;
                }
            },
            { 
                data: 'phone',
                render: function(data, type, row) {
                    return `<a href="tel:${data}" class="text-decoration-none">${data}</a>`;
                }
            },
            { 
                data: 'specialization',
                className: 'text-center',
                render: function(data, type, row) {
                    return `<span class="badge badge-info">${data}</span>`;
                }
            },
            { 
                data: 'license_number',
                className: 'text-center',
                render: function(data, type, row) {
                    return `<span class="badge badge-secondary">${data}</span>`;
                }
            },
            { 
                data: 'hospital_affiliation',
                render: function(data, type, row) {
                    return data || '-';
                }
            },
            { 
                data: 'status',
                className: 'text-center',
                width: '100px',
                render: function(data, type, row) {
                    return getStatusBadge(data);
                }
            },
            { 
                data: 'created_date',
                width: '120px',
                render: function(data, type, row) {
                    return formatDate(data);
                }
            },
            { 
                data: 'id',
                orderable: false,
                searchable: false,
                width: '120px',
                className: 'text-center',
                render: function(data, type, row) {
                    return `
                        <div class="action-buttons">
                            <button class="btn btn-info btn-sm" onclick="viewDoctor(${data})" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-warning btn-sm" onclick="editDoctor(${data})" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="deleteDoctor(${data})" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        order: [[0, 'desc']],
        pageLength: 25,
        export: true
    });
}

// Show add doctor modal
function showAddDoctorModal() {
    $('#doctorModalLabel').text('Add New Doctor');
    $('#doctorForm')[0].reset();
    $('#doctor_id').val('');
    clearFormValidation('#doctorForm');
    
    // Reset Select2
    $('#doctorForm .select2').val(null).trigger('change');
    $('#status').val('active').trigger('change');
    
    $('#doctorModal').modal('show');
}

// Edit doctor
function editDoctor(id) {
    showLoading();
    
    makeAjaxRequest({
        url: 'api/doctors_api.php',
        type: 'POST',
        data: {
            action: 'get',
            id: id
        },
        success: function(response) {
            if (response.success && response.data) {
                populateDoctorForm(response.data);
                $('#doctorModalLabel').text('Edit Doctor');
                $('#doctorModal').modal('show');
            } else {
                showErrorToast(response.message || 'Failed to load doctor data');
            }
        }
    });
}

// Populate doctor form with data
function populateDoctorForm(doctor) {
    $('#doctor_id').val(doctor.id);
    $('#first_name').val(doctor.first_name);
    $('#last_name').val(doctor.last_name);
    $('#phone').val(doctor.phone);
    $('#email').val(doctor.email);
    $('#specialization').val(doctor.specialization).trigger('change');
    $('#license_number').val(doctor.license_number);
    $('#hospital_affiliation').val(doctor.hospital_affiliation);
    $('#notes').val(doctor.notes);
    $('#status').val(doctor.status).trigger('change');
    
    clearFormValidation('#doctorForm');
}

// View doctor details
function viewDoctor(id) {
    showLoading();
    
    makeAjaxRequest({
        url: 'api/doctors_api.php',
        type: 'POST',
        data: {
            action: 'get',
            id: id
        },
        success: function(response) {
            if (response.success && response.data) {
                displayDoctorDetails(response.data);
                $('#viewDoctorModal').modal('show');
            } else {
                showErrorToast(response.message || 'Failed to load doctor data');
            }
        }
    });
}

// Display doctor details in modal
function displayDoctorDetails(doctor) {
    const html = `
        <div class="row">
            <div class="col-md-6">
                <table class="table table-borderless doctor-details-table">
                    <tr>
                        <td><strong>Doctor ID:</strong></td>
                        <td>#${doctor.id}</td>
                    </tr>
                    <tr>
                        <td><strong>Full Name:</strong></td>
                        <td>Dr. ${doctor.first_name} ${doctor.last_name}</td>
                    </tr>
                    <tr>
                        <td><strong>Phone:</strong></td>
                        <td><a href="tel:${doctor.phone}">${doctor.phone}</a></td>
                    </tr>
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td>${doctor.email ? `<a href="mailto:${doctor.email}">${doctor.email}</a>` : '-'}</td>
                    </tr>
                    <tr>
                        <td><strong>Specialization:</strong></td>
                        <td><span class="badge badge-info">${doctor.specialization}</span></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-borderless doctor-details-table">
                    <tr>
                        <td><strong>License Number:</strong></td>
                        <td><span class="badge badge-secondary">${doctor.license_number}</span></td>
                    </tr>
                    <tr>
                        <td><strong>Hospital:</strong></td>
                        <td>${doctor.hospital_affiliation || '-'}</td>
                    </tr>
                    <tr>
                        <td><strong>Status:</strong></td>
                        <td>${getStatusBadge(doctor.status)}</td>
                    </tr>
                    <tr>
                        <td><strong>Created Date:</strong></td>
                        <td>${formatDate(doctor.created_date, 'DD/MM/YYYY HH:mm')}</td>
                    </tr>
                    <tr>
                        <td><strong>Last Updated:</strong></td>
                        <td>${formatDate(doctor.updated_date, 'DD/MM/YYYY HH:mm')}</td>
                    </tr>
                </table>
            </div>
        </div>
        ${doctor.notes ? `
            <div class="row">
                <div class="col-12">
                    <h6><strong>Notes:</strong></h6>
                    <p class="text-muted">${doctor.notes}</p>
                </div>
            </div>
        ` : ''}
        <div class="row">
            <div class="col-12">
                <h6><strong>Statistics:</strong></h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="info-box">
                            <span class="info-box-icon bg-info"><i class="fas fa-flask"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Orders</span>
                                <span class="info-box-number">${doctor.total_orders || 0}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box">
                            <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">This Month</span>
                                <span class="info-box-number">${doctor.monthly_orders || 0}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box">
                            <span class="info-box-icon bg-warning"><i class="fas fa-calendar"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">This Week</span>
                                <span class="info-box-number">${doctor.weekly_orders || 0}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    $('#doctorDetailsContent').html(html);
}

// Delete doctor
function deleteDoctor(id) {
    deleteRecord(id, 'doctors_api.php', {
        title: 'Delete Doctor',
        text: 'Are you sure you want to delete this doctor? All related records will also be affected.',
        onSuccess: function() {
            refreshTable();
        }
    });
}

// Filter table
function filterTable() {
    if (window.dataTable) {
        window.dataTable.ajax.reload(null, false);
    }
}

// Refresh table
function refreshTable() {
    if (window.dataTable) {
        window.dataTable.ajax.reload(null, false);
        showInfoToast('Table refreshed successfully');
    }
}

// Export doctors
function exportDoctors() {
    const filters = {
        status: $('#statusFilter').val(),
        specialization: $('#specializationFilter').val(),
        date_from: $('#dateFromFilter').val(),
        date_to: $('#dateToFilter').val()
    };
    
    const queryString = new URLSearchParams(filters).toString();
    window.open(`api/doctors_api.php?action=export&${queryString}`, '_blank');
    showInfoToast('Export initiated');
}

// Print doctor details
function printDoctorDetails() {
    const printContent = $('#doctorDetailsContent').html();
    const printWindow = window.open('', '_blank');
    
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Doctor Details - PathLab Pro</title>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/css/bootstrap.min.css">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
            <style>
                @media print {
                    .no-print { display: none !important; }
                }
                body { font-family: Arial, sans-serif; }
                .header { text-align: center; margin-bottom: 30px; }
                .table { font-size: 14px; }
                .doctor-details-table td:first-child { width: 35%; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h3>PathLab Pro - Doctor Details</h3>
                    <p class="text-muted">Generated on ${new Date().toLocaleDateString()}</p>
                </div>
                ${printContent}
            </div>
            <script>
                window.onload = function() {
                    window.print();
                    window.onafterprint = function() {
                        window.close();
                    };
                };
            </script>
        </body>
        </html>
    `);
    
    printWindow.document.close();
}
