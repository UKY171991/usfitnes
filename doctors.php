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
                <button type="button" class="btn btn-primary btn-sm" onclick="openAddModal()">
                  <i class="fas fa-plus mr-1"></i>Add Doctor
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm ml-1" onclick="refreshTable()">
                  <i class="fas fa-sync-alt mr-1"></i>Refresh
                </button>
              </div>
            </div>
            <div class="card-body">
              <table id="doctorsTable" class="table table-bordered table-striped table-hover">
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
      <div class="modal-header bg-primary">
        <h4 class="modal-title text-white" id="doctorModalLabel">
          <i class="fas fa-user-md mr-2"></i>Add Doctor
        </h4>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="doctorForm">
        <div class="modal-body">
          <input type="hidden" name="id" id="doctorId">
          
          <!-- Basic Information -->
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="doctorName">Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="doctorName" name="name" required>
                <div class="invalid-feedback">Please provide doctor's name.</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="specialization">Specialization <span class="text-danger">*</span></label>
                <select class="form-control select2" id="specialization" name="specialization" required>
                  <option value="">Select Specialization</option>
                  <option value="General Medicine">General Medicine</option>
                  <option value="Pathology">Pathology</option>
                  <option value="Cardiology">Cardiology</option>
                  <option value="Dermatology">Dermatology</option>
                  <option value="Endocrinology">Endocrinology</option>
                  <option value="Gastroenterology">Gastroenterology</option>
                  <option value="Hematology">Hematology</option>
                  <option value="Immunology">Immunology</option>
                  <option value="Microbiology">Microbiology</option>
                  <option value="Oncology">Oncology</option>
                  <option value="Radiology">Radiology</option>
                  <option value="Other">Other</option>
                </select>
                <div class="invalid-feedback">Please select a specialization.</div>
              </div>
            </div>
          </div>
          
          <!-- Contact Information -->
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="doctorPhone">Phone <span class="text-danger">*</span></label>
                <input type="tel" class="form-control" id="doctorPhone" name="phone" required>
                <div class="invalid-feedback">Please provide phone number.</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="doctorEmail">Email</label>
                <input type="email" class="form-control" id="doctorEmail" name="email">
                <div class="invalid-feedback">Please provide a valid email.</div>
              </div>
            </div>
          </div>
          
          <!-- Professional Details -->
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="licenseNumber">License Number</label>
                <input type="text" class="form-control" id="licenseNumber" name="license_number" placeholder="Medical license number">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="hospital">Hospital/Clinic</label>
                <input type="text" class="form-control" id="hospital" name="hospital" placeholder="Hospital or clinic name">
              </div>
            </div>
          </div>
          
          <!-- Address -->
          <div class="form-group">
            <label for="doctorAddress">Address</label>
            <textarea class="form-control" id="doctorAddress" name="address" rows="2" placeholder="Doctor's address"></textarea>
          </div>
          
          <!-- Notes -->
          <div class="form-group">
            <label for="notes">Notes</label>
            <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Additional notes about the doctor"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i>Cancel
          </button>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save mr-1"></i>Save Doctor
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Page specific JavaScript -->
<script src="js/doctors.js?v=<?php echo time(); ?>"></script>

<?php include 'includes/adminlte_template_footer.php'; ?>