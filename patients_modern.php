<?php
// Set page title
$page_title = 'Patient Management';

// Include header
include 'includes/header.php';
// Include sidebar with user info
include 'includes/sidebar.php';
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>
                        <i class="fas fa-user-injured mr-2"></i>
                        Patient Management
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                        <li class="breadcrumb-item active">Patients</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Alert Messages -->
            <div id="alertContainer"></div>
            
            <!-- Control Panel -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-filter mr-1"></i>
                                Patient Controls
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-end">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="searchInput">Search Patients</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="searchInput" 
                                                   placeholder="Search by name, ID, email...">
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                                                    <i class="fas fa-search"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="genderFilter">Gender</label>
                                        <select class="form-control" id="genderFilter">
                                            <option value="">All Genders</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="ageFilter">Age Range</label>
                                        <select class="form-control" id="ageFilter">
                                            <option value="">All Ages</option>
                                            <option value="0-18">0-18 years</option>
                                            <option value="19-35">19-35 years</option>
                                            <option value="36-60">36-60 years</option>
                                            <option value="60+">60+ years</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <button type="button" class="btn btn-info btn-block" id="refreshBtn">
                                            <i class="fas fa-sync-alt"></i> Refresh
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <button type="button" class="btn btn-primary btn-block" 
                                                data-toggle="modal" data-target="#addPatientModal">
                                            <i class="fas fa-plus"></i> Add Patient
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Patients Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-users mr-1"></i>
                                All Patients
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-tool dropdown-toggle" data-toggle="dropdown">
                                        <i class="fas fa-download"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right" role="menu">
                                        <a href="#" class="dropdown-item" id="exportPDF">
                                            <i class="fas fa-file-pdf mr-2"></i> Export PDF
                                        </a>
                                        <a href="#" class="dropdown-item" id="exportExcel">
                                            <i class="fas fa-file-excel mr-2"></i> Export Excel
                                        </a>
                                        <a href="#" class="dropdown-item" id="exportCSV">
                                            <i class="fas fa-file-csv mr-2"></i> Export CSV
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="patientsTable" class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>
                                                <input type="checkbox" id="selectAll" class="form-control" style="width: auto;">
                                            </th>
                                            <th>Patient ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Gender</th>
                                            <th>Age</th>
                                            <th>Date of Birth</th>
                                            <th>Registration Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- DataTables will populate this -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-sm-3">
                                    <div class="dataTables_info" id="tableInfo"></div>
                                </div>
                                <div class="col-sm-9">
                                    <div class="float-right">
                                        <button class="btn btn-danger btn-sm" id="bulkDelete" style="display: none;">
                                            <i class="fas fa-trash"></i> Delete Selected
                                        </button>
                                        <button class="btn btn-info btn-sm ml-2" id="bulkExport" style="display: none;">
                                            <i class="fas fa-download"></i> Export Selected
                                        </button>
                                    </div>
                                </div>
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
                <h4 class="modal-title">
                    <i class="fas fa-user-plus mr-2"></i>
                    Add New Patient
                </h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addPatientForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="patient_id">Patient ID <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="patient_id" name="patient_id" 
                                       placeholder="Auto-generated" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="full_name">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="full_name" name="full_name" 
                                       placeholder="Enter full name" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="first_name">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       placeholder="Enter first name">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="last_name">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       placeholder="Enter last name">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       placeholder="Enter email address">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       placeholder="Enter phone number">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="date_of_birth">Date of Birth <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" required>
                            </div>
                        </div>
                        <div class="col-md-6">
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
                    </div>
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3" 
                                  placeholder="Enter complete address"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="emergency_contact">Emergency Contact Name</label>
                                <input type="text" class="form-control" id="emergency_contact" 
                                       name="emergency_contact" placeholder="Enter emergency contact name">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="emergency_phone">Emergency Contact Phone</label>
                                <input type="tel" class="form-control" id="emergency_phone" 
                                       name="emergency_phone" placeholder="Enter emergency contact phone">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Patient
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Patient Modal -->
<div class="modal fade" id="viewPatientModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-user mr-2"></i>
                    Patient Details
                </h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="viewPatientContent">
                <!-- Patient details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Close
                </button>
                <button type="button" class="btn btn-primary" id="editPatientBtn">
                    <i class="fas fa-edit"></i> Edit Patient
                </button>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
$(document).ready(function() {
    // Initialize DataTable
    initializeDataTable();
    
    // Generate Patient ID
    generatePatientId();
    
    // Event handlers
    $('#addPatientForm').on('submit', handleAddPatient);
    $('#refreshBtn').on('click', function() {
        table.ajax.reload();
    });
    
    // Bulk selection handlers
    $('#selectAll').on('change', handleSelectAll);
    $(document).on('change', '.patient-checkbox', handlePatientSelect);
    
    // Filter handlers
    $('#genderFilter, #ageFilter').on('change', function() {
        table.draw();
    });
    
    // Search handler
    $('#searchInput').on('keyup', function() {
        table.search(this.value).draw();
    });
});

let table;

function initializeDataTable() {
    table = $('#patientsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: 'api/patients_api.php',
            type: 'POST',
            data: function(d) {
                d.action = 'list';
                d.gender = $('#genderFilter').val();
                d.age_range = $('#ageFilter').val();
            }
        },
        columns: [
            {
                data: 'id',
                orderable: false,
                render: function(data, type, row) {
                    return `<input type="checkbox" class="patient-checkbox" value="${data}">`;
                }
            },
            { data: 'patient_id' },
            { 
                data: 'full_name',
                render: function(data, type, row) {
                    return `<strong>${data}</strong>`;
                }
            },
            { data: 'email' },
            { data: 'phone' },
            { 
                data: 'gender',
                render: function(data, type, row) {
                    const colors = {
                        'Male': 'primary',
                        'Female': 'pink',
                        'Other': 'secondary'
                    };
                    return `<span class="badge badge-${colors[data] || 'secondary'}">${data}</span>`;
                }
            },
            { data: 'age' },
            { data: 'date_of_birth' },
            { data: 'created_at' },
            {
                data: 'id',
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-info btn-sm" 
                                    onclick="viewPatient(${data})" title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-warning btn-sm" 
                                    onclick="editPatient(${data})" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" 
                                    onclick="deletePatient(${data})" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        order: [[1, 'desc']],
        pageLength: 25,
        responsive: true,
        dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>rtip',
        language: {
            processing: '<i class="fas fa-spinner fa-spin"></i> Loading patients...',
            emptyTable: 'No patients found',
            zeroRecords: 'No patients match your search criteria'
        }
    });
}

function generatePatientId() {
    $.ajax({
        url: 'api/patients_api.php',
        method: 'POST',
        data: JSON.stringify({ action: 'generateId' }),
        contentType: 'application/json',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#patient_id').val(response.patient_id);
            }
        }
    });
}

function handleAddPatient(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    data.action = 'add';
    
    $.ajax({
        url: 'api/patients_api.php',
        method: 'POST',
        data: JSON.stringify(data),
        contentType: 'application/json',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Patient added successfully',
                    timer: 2000,
                    showConfirmButton: false
                });
                $('#addPatientModal').modal('hide');
                $('#addPatientForm')[0].reset();
                table.ajax.reload();
                generatePatientId();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: response.message || 'Failed to add patient'
                });
            }
        },
        error: function(xhr, status, error) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Failed to add patient. Please try again.'
            });
        }
    });
}

function viewPatient(id) {
    $.ajax({
        url: 'api/patients_api.php',
        method: 'POST',
        data: JSON.stringify({ action: 'view', id: id }),
        contentType: 'application/json',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const patient = response.data;
                const content = `
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr><td><strong>Patient ID:</strong></td><td>${patient.patient_id}</td></tr>
                                <tr><td><strong>Full Name:</strong></td><td>${patient.full_name}</td></tr>
                                <tr><td><strong>Email:</strong></td><td>${patient.email || 'N/A'}</td></tr>
                                <tr><td><strong>Phone:</strong></td><td>${patient.phone || 'N/A'}</td></tr>
                                <tr><td><strong>Gender:</strong></td><td>${patient.gender}</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr><td><strong>Date of Birth:</strong></td><td>${patient.date_of_birth}</td></tr>
                                <tr><td><strong>Age:</strong></td><td>${patient.age} years</td></tr>
                                <tr><td><strong>Address:</strong></td><td>${patient.address || 'N/A'}</td></tr>
                                <tr><td><strong>Emergency Contact:</strong></td><td>${patient.emergency_contact || 'N/A'}</td></tr>
                                <tr><td><strong>Emergency Phone:</strong></td><td>${patient.emergency_phone || 'N/A'}</td></tr>
                            </table>
                        </div>
                    </div>
                `;
                $('#viewPatientContent').html(content);
                $('#viewPatientModal').modal('show');
            }
        }
    });
}

function editPatient(id) {
    // Implementation for edit patient
    console.log('Edit patient:', id);
}

function deletePatient(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'api/patients_api.php',
                method: 'POST',
                data: JSON.stringify({ action: 'delete', id: id }),
                contentType: 'application/json',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Deleted!', 'Patient has been deleted.', 'success');
                        table.ajax.reload();
                    } else {
                        Swal.fire('Error!', response.message || 'Failed to delete patient', 'error');
                    }
                }
            });
        }
    });
}

function handleSelectAll() {
    const isChecked = $(this).is(':checked');
    $('.patient-checkbox').prop('checked', isChecked);
    updateBulkActions();
}

function handlePatientSelect() {
    updateBulkActions();
}

function updateBulkActions() {
    const selectedCount = $('.patient-checkbox:checked').length;
    if (selectedCount > 0) {
        $('#bulkDelete, #bulkExport').show();
    } else {
        $('#bulkDelete, #bulkExport').hide();
    }
}
</script>
