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
                <button type="button" class="btn btn-primary btn-sm" onclick="openAddModal()">
                  <i class="fas fa-plus mr-1"></i>Add Patient
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm ml-1" onclick="refreshTable()">
                  <i class="fas fa-sync-alt mr-1"></i>Refresh
                </button>
              </div>
            </div>
            <div class="card-body">
              <table id="patientsTable" class="table table-bordered table-striped table-hover">
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
      <div class="modal-header bg-primary">
        <h4 class="modal-title text-white" id="patientModalLabel">
          <i class="fas fa-user-injured mr-2"></i>Add Patient
        </h4>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="patientForm">
        <div class="modal-body">
          <input type="hidden" name="id" id="patientId">
          
          <!-- Basic Information -->
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="firstName">First Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="firstName" name="first_name" required>
                <div class="invalid-feedback">Please provide a first name.</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="lastName">Last Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="lastName" name="last_name" required>
                <div class="invalid-feedback">Please provide a last name.</div>
              </div>
            </div>
          </div>
          
          <!-- Contact Information -->
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="phone">Phone <span class="text-danger">*</span></label>
                <input type="tel" class="form-control" id="phone" name="phone" required>
                <div class="invalid-feedback">Please provide a phone number.</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email">
                <div class="invalid-feedback">Please provide a valid email.</div>
              </div>
            </div>
          </div>
          
          <!-- Personal Details -->
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
          
          <!-- Address -->
          <div class="form-group">
            <label for="address">Address</label>
            <textarea class="form-control" id="address" name="address" rows="2" placeholder="Enter patient address"></textarea>
          </div>
          
          <!-- Emergency Contact -->
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="emergencyContact">Emergency Contact</label>
                <input type="text" class="form-control" id="emergencyContact" name="emergency_contact" placeholder="Contact person name">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="emergencyPhone">Emergency Phone</label>
                <input type="tel" class="form-control" id="emergencyPhone" name="emergency_phone" placeholder="Emergency contact phone">
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i>Cancel
          </button>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save mr-1"></i>Save Patient
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Page specific JavaScript -->
<script src="js/patients.js?v=<?php echo time(); ?>"></script>

<?php include 'includes/adminlte_template_footer.php'; ?>