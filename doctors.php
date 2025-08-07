<?php
require_once 'config.php';
require_once 'includes/init.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pageTitle = 'Doctors Management';
$pageIcon = 'fas fa-user-md';
$breadcrumbs = ['Doctors'];

include 'includes/adminlte_template_header.php';
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
            <i class="<?php echo $pageIcon; ?> mr-2 text-primary"></i><?php echo $pageTitle; ?>
          </h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
            <?php foreach($breadcrumbs as $index => $crumb): ?>
              <li class="breadcrumb-item active"><?php echo $crumb; ?></li>
            <?php endforeach; ?>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">
          <div class="card card-primary card-outline">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-list mr-2"></i>All Doctors
              </h3>
              <div class="card-tools">
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#doctorModal" onclick="openDoctorModal()">
                  <i class="fas fa-plus mr-1"></i>Add Doctor
                </button>
              </div>
            </div>
            <div class="card-body">
              <table id="doctorsTable" class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>Doctor ID</th>
                    <th>Name</th>
                    <th>Specialization</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <!-- Data will be loaded via AJAX -->
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<!-- Doctor Modal -->
<div class="modal fade" id="doctorModal" tabindex="-1" role="dialog" aria-labelledby="doctorModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="doctorModalLabel">Add Doctor</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="doctorForm">
        <div class="modal-body">
          <input type="hidden" id="doctorId" name="id">
          
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="doctorName">Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="doctorName" name="name" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="specialization">Specialization <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="specialization" name="specialization" required>
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="doctorPhone">Phone <span class="text-danger">*</span></label>
                <input type="tel" class="form-control" id="doctorPhone" name="phone" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="doctorEmail">Email</label>
                <input type="email" class="form-control" id="doctorEmail" name="email">
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="licenseNumber">License Number</label>
                <input type="text" class="form-control" id="licenseNumber" name="license_number">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="hospital">Hospital/Clinic</label>
                <input type="text" class="form-control" id="hospital" name="hospital">
              </div>
            </div>
          </div>
          
          <div class="form-group">
            <label for="doctorAddress">Address</label>
            <textarea class="form-control" id="doctorAddress" name="address" rows="2"></textarea>
          </div>
          
          <div class="form-group">
            <label for="notes">Notes</label>
            <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Doctor</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#doctorsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: 'ajax/doctors_datatable.php',
            type: 'POST',
            error: function(xhr, error, thrown) {
                console.log('DataTable Error:', error);
                toastr.error('Failed to load doctor data. Please refresh the page.');
            }
        },
        columns: [
            { data: 'doctor_id' },
            { data: 'name' },
            { data: 'specialization' },
            { data: 'phone' },
            { data: 'email' },
            { data: 'status' },
            { data: 'actions', orderable: false, searchable: false }
        ],
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true
    });
    
    // Handle form submission
    $('#doctorForm').on('submit', function(e) {
        e.preventDefault();
        saveDoctor();
    });
});

function openDoctorModal(id = null) {
    if (id) {
        // Edit mode
        $('#doctorModalLabel').text('Edit Doctor');
        loadDoctorData(id);
    } else {
        // Add mode
        $('#doctorModalLabel').text('Add Doctor');
        $('#doctorForm')[0].reset();
        $('#doctorId').val('');
    }
}

function loadDoctorData(id) {
    $.ajax({
        url: 'api/doctors_api.php',
        type: 'GET',
        data: { action: 'get', id: id },
        success: function(response) {
            if (response.success) {
                const doctor = response.data;
                $('#doctorId').val(doctor.id);
                $('#doctorName').val(doctor.name);
                $('#specialization').val(doctor.specialization);
                $('#doctorPhone').val(doctor.phone);
                $('#doctorEmail').val(doctor.email);
                $('#licenseNumber').val(doctor.license_number);
                $('#hospital').val(doctor.hospital);
                $('#doctorAddress').val(doctor.address);
                $('#notes').val(doctor.notes);
            } else {
                toastr.error(response.message);
            }
        },
        error: function() {
            toastr.error('Error loading doctor data');
        }
    });
}

function saveDoctor() {
    const formData = new FormData($('#doctorForm')[0]);
    const isEdit = $('#doctorId').val() !== '';
    
    $.ajax({
        url: 'api/doctors_api.php',
        type: isEdit ? 'PUT' : 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                toastr.success(response.message);
                $('#doctorModal').modal('hide');
                $('#doctorsTable').DataTable().ajax.reload();
            } else {
                toastr.error(response.message);
            }
        },
        error: function() {
            toastr.error('Error saving doctor');
        }
    });
}

function deleteDoctor(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'api/doctors_api.php',
                type: 'DELETE',
                data: { id: id },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('#doctorsTable').DataTable().ajax.reload();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('Error deleting doctor');
                }
            });
        }
    });
}

function viewDoctor(id) {
    // View doctor details (can be implemented later)
    toastr.info('View doctor functionality coming soon');
}
</script>

<?php include 'includes/adminlte_template_footer.php'; ?>