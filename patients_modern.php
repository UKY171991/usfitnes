<?php
require_once 'config.php';
require_once 'includes/init.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pageTitle = 'Patients Management';
$pageIcon = 'fas fa-user-injured';
$breadcrumbs = ['Patients'];

include 'includes/adminlte_template_header_modern.php';
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <i class="<?php echo $pageIcon; ?> mr-2 text-primary"></i>
                        <?php echo $pageTitle; ?>
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="dashboard_modern.php">Home</a></li>
                        <li class="breadcrumb-item active">Patients</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            
            <!-- Patients Table Card -->
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-users mr-1"></i>
                        All Patients
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-light btn-sm" onclick="openPatientModal()">
                            <i class="fas fa-plus"></i> Add New Patient
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <table id="patientsTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Age</th>
                                <th>Gender</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>
    </section>
</div>

<!-- Patient Modal -->
<div class="modal fade" id="patientModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h4 class="modal-title">
                    <i class="fas fa-user-plus"></i>
                    <span id="patientModalTitle">Add New Patient</span>
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="patientForm">
                <input type="hidden" id="patientId" name="patient_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="firstName">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="firstName" name="first_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="lastName">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="lastName" name="last_name" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone">Phone <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="phone" name="phone" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="dateOfBirth">Date of Birth <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="dateOfBirth" name="date_of_birth" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="gender">Gender <span class="text-danger">*</span></label>
                                <select class="form-control" id="gender" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="M">Male</option>
                                    <option value="F">Female</option>
                                </select>
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

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h4 class="modal-title text-white">
                    <i class="fas fa-exclamation-triangle"></i>
                    Confirm Delete
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this patient?</p>
                <p class="text-muted">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmDelete">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let patientsTable;
let deletePatientId;

$(document).ready(function() {
    // Initialize DataTable
    patientsTable = $('#patientsTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "ajax/patients_datatable.php",
            "type": "POST"
        },
        "columns": [
            { "data": "id" },
            { "data": "name" },
            { "data": "phone" },
            { "data": "email" },
            { "data": "age" },
            { 
                "data": "gender",
                "render": function(data) {
                    return data === 'M' ? '<span class="badge badge-info">Male</span>' : '<span class="badge badge-pink">Female</span>';
                }
            },
            { 
                "data": "status",
                "render": function(data) {
                    return data === 'active' ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>';
                }
            },
            { 
                "data": "actions",
                "orderable": false,
                "searchable": false
            }
        ],
        "order": [[0, "desc"]],
        "pageLength": 25,
        "responsive": true,
        "dom": 'Bfrtip',
        "buttons": [
            {
                text: '<i class="fas fa-plus"></i> Add Patient',
                className: 'btn btn-primary btn-sm',
                action: function() {
                    openPatientModal();
                }
            },
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Export',
                className: 'btn btn-success btn-sm'
            }
        ],
        "language": {
            "processing": '<div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>',
            "emptyTable": "No patients found",
            "zeroRecords": "No matching patients found"
        }
    });
});

// Modal Functions
function openPatientModal(id = null) {
    if (id) {
        // Edit mode
        loadPatientData(id);
        $('#patientModalTitle').text('Edit Patient');
    } else {
        // Add mode
        $('#patientForm')[0].reset();
        $('#patientId').val('');
        $('#patientModalTitle').text('Add New Patient');
    }
    $('#patientModal').modal('show');
}

function loadPatientData(id) {
    $.ajax({
        url: 'ajax/patient_get.php',
        method: 'GET',
        data: { id: id },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const patient = response.data;
                $('#patientId').val(patient.id);
                $('#firstName').val(patient.first_name);
                $('#lastName').val(patient.last_name);
                $('#phone').val(patient.phone);
                $('#email').val(patient.email);
                $('#dateOfBirth').val(patient.date_of_birth);
                $('#gender').val(patient.gender);
            } else {
                showToast('error', response.message);
            }
        },
        error: function() {
            showToast('error', 'Failed to load patient data');
        }
    });
}

function deletePatient(id) {
    deletePatientId = id;
    $('#deleteModal').modal('show');
}

$('#confirmDelete').on('click', function() {
    $.ajax({
        url: 'ajax/patient_delete.php',
        method: 'POST',
        data: { id: deletePatientId },
        dataType: 'json',
        success: function(response) {
            $('#deleteModal').modal('hide');
            if (response.success) {
                showToast('success', response.message);
                patientsTable.ajax.reload();
            } else {
                showToast('error', response.message);
            }
        },
        error: function() {
            $('#deleteModal').modal('hide');
            showToast('error', 'Failed to delete patient');
        }
    });
});

// Form Submission
$('#patientForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    $.ajax({
        url: 'ajax/patient_save.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#patientModal').modal('hide');
                showToast('success', response.message);
                patientsTable.ajax.reload();
            } else {
                showToast('error', response.message);
            }
        },
        error: function() {
            showToast('error', 'Failed to save patient');
        }
    });
});

// Global functions for table actions
window.editPatient = function(id) {
    openPatientModal(id);
};

window.deletePatient = deletePatient;

window.viewPatient = function(id) {
    // Implement view functionality if needed
    window.open('patient_details.php?id=' + id, '_blank');
};

// Toast function
function showToast(type, message) {
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "timeOut": "5000"
    };
    
    switch(type) {
        case 'success':
            toastr.success(message);
            break;
        case 'error':
            toastr.error(message);
            break;
        case 'warning':
            toastr.warning(message);
            break;
        case 'info':
            toastr.info(message);
            break;
    }
}
</script>

<?php include 'includes/adminlte_template_footer_modern.php'; ?>
