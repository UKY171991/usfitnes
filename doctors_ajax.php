<?php
require_once 'config.php';
require_once 'includes/adminlte_template.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

renderTemplate('doctors_ajax', 'Doctors Management', [
    'page_title' => 'Doctors Management',
    'breadcrumbs' => [
        ['name' => 'Home', 'url' => 'dashboard.php'],
        ['name' => 'Doctors', 'url' => '']
    ]
]);

function getContent() {
    ob_start();
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Doctors Management</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                    <li class="breadcrumb-item active">Doctors</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <!-- Doctors Table Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user-md mr-2"></i>
                    All Doctors
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" id="addDoctorBtn">
                        <i class="fas fa-plus mr-1"></i>
                        Add Doctor
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="doctorsTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Specialization</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- DataTables will populate this -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Add/Edit Doctor Modal -->
<div class="modal fade" id="doctorModal" tabindex="-1" role="dialog" aria-labelledby="doctorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="doctorModalLabel">Add Doctor</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="doctorForm">
                <div class="modal-body">
                    <input type="hidden" id="doctorId" name="id">
                    <input type="hidden" name="action" id="formAction" value="add">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="firstName">First Name *</label>
                                <input type="text" class="form-control" id="firstName" name="first_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="lastName">Last Name *</label>
                                <input type="text" class="form-control" id="lastName" name="last_name" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="specialization">Specialization *</label>
                                <select class="form-control" id="specialization" name="specialization" required>
                                    <option value="">Select Specialization</option>
                                    <option value="Cardiology">Cardiology</option>
                                    <option value="Neurology">Neurology</option>
                                    <option value="Orthopedics">Orthopedics</option>
                                    <option value="Pediatrics">Pediatrics</option>
                                    <option value="Dermatology">Dermatology</option>
                                    <option value="General Medicine">General Medicine</option>
                                    <option value="Surgery">Surgery</option>
                                    <option value="Gynecology">Gynecology</option>
                                    <option value="Oncology">Oncology</option>
                                    <option value="Psychiatry">Psychiatry</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone">Phone *</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
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
                                <label for="license">License Number</label>
                                <input type="text" class="form-control" id="license" name="license_number">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="saveDoctorBtn">
                        <i class="fas fa-save mr-1"></i>
                        Save Doctor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTable
    let doctorsTable = $('#doctorsTable').DataTable({
        ajax: {
            url: 'api/doctors_api.php',
            type: 'GET',
            data: { action: 'list' },
            dataSrc: function(json) {
                if (!json.success) {
                    toastr.error(json.message || 'Failed to load doctors');
                    return [];
                }
                return json.data || [];
            }
        },
        columns: [
            { 
                data: 'doctor_id',
                defaultContent: 'N/A'
            },
            { 
                data: null,
                render: function(data, type, row) {
                    return `${row.first_name || ''} ${row.last_name || ''}`.trim();
                }
            },
            { 
                data: 'specialization',
                defaultContent: 'N/A'
            },
            { 
                data: 'phone',
                defaultContent: 'N/A'
            },
            { 
                data: 'email',
                defaultContent: 'N/A'
            },
            { 
                data: 'status',
                render: function(data, type, row) {
                    if (data === 'active') {
                        return '<span class="badge badge-success">Active</span>';
                    }
                    return '<span class="badge badge-secondary">' + (data || 'Inactive') + '</span>';
                }
            },
            {
                data: null,
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-info btn-sm edit-doctor" data-id="${row.id}" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-sm delete-doctor" data-id="${row.id}" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        responsive: true,
        order: [[0, 'desc']],
        pageLength: 25,
        language: {
            emptyTable: "No doctors found",
            loadingRecords: "Loading doctors...",
            processing: "Processing..."
        }
    });

    // Add Doctor Button
    $('#addDoctorBtn').click(function() {
        resetForm();
        $('#doctorModalLabel').text('Add Doctor');
        $('#formAction').val('add');
        $('#doctorModal').modal('show');
    });

    // Edit Doctor
    $(document).on('click', '.edit-doctor', function() {
        const doctorId = $(this).data('id');
        editDoctor(doctorId);
    });

    // Delete Doctor
    $(document).on('click', '.delete-doctor', function() {
        const doctorId = $(this).data('id');
        deleteDoctor(doctorId);
    });

    // Form Submission
    $('#doctorForm').submit(function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const action = $('#formAction').val();
        
        // Show loading state
        $('#saveDoctorBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Saving...');
        
        $.ajax({
            url: 'api/doctors_api.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#doctorModal').modal('hide');
                    doctorsTable.ajax.reload(null, false);
                } else {
                    toastr.error(response.message || 'Operation failed');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                toastr.error('Network error occurred. Please try again.');
            },
            complete: function() {
                $('#saveDoctorBtn').prop('disabled', false).html('<i class="fas fa-save mr-1"></i>Save Doctor');
            }
        });
    });

    function editDoctor(id) {
        $.ajax({
            url: 'api/doctors_api.php',
            type: 'GET',
            data: { action: 'get', id: id },
            success: function(response) {
                if (response.success && response.data) {
                    const doctor = response.data;
                    
                    // Populate form
                    $('#doctorId').val(doctor.id);
                    $('#firstName').val(doctor.first_name);
                    $('#lastName').val(doctor.last_name);
                    $('#specialization').val(doctor.specialization);
                    $('#phone').val(doctor.phone);
                    $('#email').val(doctor.email);
                    $('#license').val(doctor.license_number);
                    
                    $('#doctorModalLabel').text('Edit Doctor');
                    $('#formAction').val('update');
                    $('#doctorModal').modal('show');
                } else {
                    toastr.error(response.message || 'Failed to load doctor data');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                toastr.error('Failed to load doctor data');
            }
        });
    }

    function deleteDoctor(id) {
        Swal.fire({
            title: 'Delete Doctor',
            text: 'Are you sure you want to delete this doctor? This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, Delete',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'api/doctors_api.php',
                    type: 'POST',
                    data: { 
                        action: 'delete', 
                        id: id 
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            doctorsTable.ajax.reload(null, false);
                        } else {
                            toastr.error(response.message || 'Delete failed');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', xhr.responseText);
                        toastr.error('Failed to delete doctor');
                    }
                });
            }
        });
    }

    function resetForm() {
        $('#doctorForm')[0].reset();
        $('#doctorId').val('');
        $('#formAction').val('add');
    }

    // Auto-refresh table every 30 seconds
    setInterval(function() {
        if ($('#doctorModal').is(':visible') === false) {
            doctorsTable.ajax.reload(null, false);
        }
    }, 30000);
});
</script>

<?php
    return ob_get_clean();
}
?>
