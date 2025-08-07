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
                <i class="fas fa-list mr-2"></i>All Patients
              </h3>
              <div class="card-tools">
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#patientModal" onclick="openPatientModal()">
                  <i class="fas fa-plus mr-1"></i>Add Patient
                </button>
              </div>
            </div>
            <div class="card-body">
              <table id="patientsTable" class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>Patient ID</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Blood Group</th>
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

<!-- Patient Modal -->
<div class="modal fade" id="patientModal" tabindex="-1" role="dialog" aria-labelledby="patientModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="patientModalLabel">Add Patient</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="patientForm">
        <div class="modal-body">
          <input type="hidden" id="patientId" name="id">
          
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
                <input type="tel" class="form-control" id="phone" name="phone" required>
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
            <div class="col-md-4">
              <div class="form-group">
                <label for="dateOfBirth">Date of Birth</label>
                <input type="date" class="form-control" id="dateOfBirth" name="date_of_birth">
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="gender">Gender</label>
                <select class="form-control" id="gender" name="gender">
                  <option value="">Select Gender</option>
                  <option value="male">Male</option>
                  <option value="female">Female</option>
                  <option value="other">Other</option>
                </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="bloodGroup">Blood Group</label>
                <select class="form-control" id="bloodGroup" name="blood_group">
                  <option value="">Select Blood Group</option>
                  <option value="A+">A+</option>
                  <option value="A-">A-</option>
                  <option value="B+">B+</option>
                  <option value="B-">B-</option>
                  <option value="AB+">AB+</option>
                  <option value="AB-">AB-</option>
                  <option value="O+">O+</option>
                  <option value="O-">O-</option>
                </select>
              </div>
            </div>
          </div>
          
          <div class="form-group">
            <label for="address">Address</label>
            <textarea class="form-control" id="address" name="address" rows="2"></textarea>
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="emergencyContact">Emergency Contact</label>
                <input type="text" class="form-control" id="emergencyContact" name="emergency_contact">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="emergencyPhone">Emergency Phone</label>
                <input type="tel" class="form-control" id="emergencyPhone" name="emergency_phone">
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Patient</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#patientsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: 'ajax/patients_datatable.php',
            type: 'POST',
            error: function(xhr, error, thrown) {
                console.log('DataTable Error:', error);
                toastr.error('Failed to load patient data. Please refresh the page.');
            }
        },
        columns: [
            { data: 'patient_id' },
            { data: 'full_name' },
            { data: 'phone' },
            { data: 'email' },
            { data: 'blood_group' },
            { data: 'status' },
            { data: 'actions', orderable: false, searchable: false }
        ],
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true
    });
    
    // Handle form submission
    $('#patientForm').on('submit', function(e) {
        e.preventDefault();
        savePatient();
    });
});

function openPatientModal(id = null) {
    if (id) {
        // Edit mode
        $('#patientModalLabel').text('Edit Patient');
        loadPatientData(id);
    } else {
        // Add mode
        $('#patientModalLabel').text('Add Patient');
        $('#patientForm')[0].reset();
        $('#patientId').val('');
    }
}

function loadPatientData(id) {
    $.ajax({
        url: 'api/patients_api.php',
        type: 'GET',
        data: { action: 'get', id: id },
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
                $('#bloodGroup').val(patient.blood_group);
                $('#address').val(patient.address);
                $('#emergencyContact').val(patient.emergency_contact);
                $('#emergencyPhone').val(patient.emergency_phone);
            } else {
                toastr.error(response.message);
            }
        },
        error: function() {
            toastr.error('Error loading patient data');
        }
    });
}

function savePatient() {
    const formData = new FormData($('#patientForm')[0]);
    const isEdit = $('#patientId').val() !== '';
    
    $.ajax({
        url: 'api/patients_api.php',
        type: isEdit ? 'PUT' : 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                toastr.success(response.message);
                $('#patientModal').modal('hide');
                $('#patientsTable').DataTable().ajax.reload();
            } else {
                toastr.error(response.message);
            }
        },
        error: function() {
            toastr.error('Error saving patient');
        }
    });
}

function deletePatient(id) {
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
                url: 'api/patients_api.php',
                type: 'DELETE',
                data: { id: id },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('#patientsTable').DataTable().ajax.reload();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('Error deleting patient');
                }
            });
        }
    });
}

function viewPatient(id) {
    // View patient details (can be implemented later)
    toastr.info('View patient functionality coming soon');
}
</script>

<?php include 'includes/adminlte_template_footer.php'; ?>