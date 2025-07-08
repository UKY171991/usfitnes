<?php
// Set page title
$page_title = 'Doctors';

// Include header
include 'includes/header.php';
// Include sidebar with user info
include 'includes/sidebar.php';
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
  <!-- Content Header -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0"><i class="fas fa-user-md mr-2"></i>Doctors Management</h1>
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
      <!-- Stats Row -->
      <div class="row mb-4">
        <div class="col-lg-3 col-6">
          <div class="small-box bg-info">
            <div class="inner">
              <h3 id="totalDoctors">8</h3>
              <p>Total Doctors</p>
            </div>
            <div class="icon">
              <i class="fas fa-user-md"></i>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-success">
            <div class="inner">
              <h3 id="activeDoctors">6</h3>
              <p>Active Doctors</p>
            </div>
            <div class="icon">
              <i class="fas fa-check-circle"></i>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-warning">
            <div class="inner">
              <h3 id="specializations">5</h3>
              <p>Specializations</p>
            </div>
            <div class="icon">
              <i class="fas fa-stethoscope"></i>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-danger">
            <div class="inner">
              <h3 id="referralsToday">12</h3>
              <p>Referrals Today</p>
            </div>
            <div class="icon">
              <i class="fas fa-paper-plane"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="row mb-3">
        <div class="col-md-8">
          <div class="input-group">
            <input type="text" class="form-control" id="searchInput" placeholder="Search doctors by name, specialization, or license...">
            <div class="input-group-append">
              <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                <i class="fas fa-search"></i>
              </button>
            </div>
          </div>
        </div>
        <div class="col-md-4 text-right">
          <button class="btn btn-success" data-toggle="modal" data-target="#addDoctorModal">
            <i class="fas fa-plus mr-2"></i>Add Doctor
          </button>
          <button class="btn btn-info ml-2" onclick="refreshTable()">
            <i class="fas fa-sync-alt"></i>
          </button>
        </div>
      </div>

      <!-- Doctors Table -->
      <div class="card">
        <div class="card-body">
          <div class="table-responsive">
            <table id="doctorsTable" class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th>Doctor ID</th>
                  <th>Name</th>
                  <th>Specialization</th>
                  <th>Phone</th>
                  <th>License No.</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <!-- Sample Data -->
                <tr>
                  <td>DR-001</td>
                  <td>Dr. Sarah Johnson</td>
                  <td>Cardiology</td>
                  <td>+1-555-0101</td>
                  <td>LIC-12345</td>
                  <td><span class="badge badge-success">Active</span></td>
                  <td>
                    <button class="btn btn-sm btn-info" onclick="viewDoctor('DR-001')">
                      <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-warning" onclick="editDoctor('DR-001')">
                      <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteDoctor('DR-001')">
                      <i class="fas fa-trash"></i>
                    </button>
                  </td>
                </tr>
                <tr>
                  <td>DR-002</td>
                  <td>Dr. Michael Brown</td>
                  <td>Neurology</td>
                  <td>+1-555-0102</td>
                  <td>LIC-12346</td>
                  <td><span class="badge badge-success">Active</span></td>
                  <td>
                    <button class="btn btn-sm btn-info" onclick="viewDoctor('DR-002')">
                      <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-warning" onclick="editDoctor('DR-002')">
                      <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteDoctor('DR-002')">
                      <i class="fas fa-trash"></i>
                    </button>
                  </td>
                </tr>
                <tr>
                  <td>DR-003</td>
                  <td>Dr. Emily Davis</td>
                  <td>Pediatrics</td>
                  <td>+1-555-0103</td>
                  <td>LIC-12347</td>
                  <td><span class="badge badge-warning">Inactive</span></td>
                  <td>
                    <button class="btn btn-sm btn-info" onclick="viewDoctor('DR-003')">
                      <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-warning" onclick="editDoctor('DR-003')">
                      <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteDoctor('DR-003')">
                      <i class="fas fa-trash"></i>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<!-- Add Doctor Modal -->
<div class="modal fade" id="addDoctorModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-success">
        <h5 class="modal-title text-white"><i class="fas fa-plus mr-2"></i>Add New Doctor</h5>
        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <form id="addDoctorForm">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="firstName">First Name *</label>
                <input type="text" class="form-control" id="firstName" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="lastName">Last Name *</label>
                <input type="text" class="form-control" id="lastName" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="specialization">Specialization *</label>
                <select class="form-control" id="specialization" required>
                  <option value="">Select Specialization</option>
                  <option value="Cardiology">Cardiology</option>
                  <option value="Neurology">Neurology</option>
                  <option value="Pediatrics">Pediatrics</option>
                  <option value="Orthopedics">Orthopedics</option>
                  <option value="Dermatology">Dermatology</option>
                  <option value="Psychiatry">Psychiatry</option>
                  <option value="General Medicine">General Medicine</option>
                  <option value="Surgery">Surgery</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="licenseNumber">License Number *</label>
                <input type="text" class="form-control" id="licenseNumber" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="phone">Phone Number *</label>
                <input type="tel" class="form-control" id="phone" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" class="form-control" id="email" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="experience">Years of Experience</label>
                <input type="number" class="form-control" id="experience" min="0" max="50">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="status">Status *</label>
                <select class="form-control" id="status" required>
                  <option value="Active">Active</option>
                  <option value="Inactive">Inactive</option>
                </select>
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-success" onclick="addDoctor()">Add Doctor</button>
      </div>
    </div>
  </div>
</div>

<!-- View Doctor Modal -->
<div class="modal fade" id="viewDoctorModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-info">
        <h5 class="modal-title text-white"><i class="fas fa-eye mr-2"></i>Doctor Details</h5>
        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body" id="viewDoctorContent">
        <!-- Content loaded dynamically -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Edit Doctor Modal -->
<div class="modal fade" id="editDoctorModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title"><i class="fas fa-edit mr-2"></i>Edit Doctor</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <form id="editDoctorForm">
          <input type="hidden" id="editDoctorId">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="editFirstName">First Name *</label>
                <input type="text" class="form-control" id="editFirstName" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="editLastName">Last Name *</label>
                <input type="text" class="form-control" id="editLastName" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="editSpecialization">Specialization *</label>
                <select class="form-control" id="editSpecialization" required>
                  <option value="Cardiology">Cardiology</option>
                  <option value="Neurology">Neurology</option>
                  <option value="Pediatrics">Pediatrics</option>
                  <option value="Orthopedics">Orthopedics</option>
                  <option value="Dermatology">Dermatology</option>
                  <option value="Psychiatry">Psychiatry</option>
                  <option value="General Medicine">General Medicine</option>
                  <option value="Surgery">Surgery</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="editLicenseNumber">License Number *</label>
                <input type="text" class="form-control" id="editLicenseNumber" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="editPhone">Phone Number *</label>
                <input type="tel" class="form-control" id="editPhone" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="editEmail">Email *</label>
                <input type="email" class="form-control" id="editEmail" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="editExperience">Years of Experience</label>
                <input type="number" class="form-control" id="editExperience" min="0" max="50">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="editStatus">Status *</label>
                <select class="form-control" id="editStatus" required>
                  <option value="Active">Active</option>
                  <option value="Inactive">Inactive</option>
                </select>
              </div>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-warning" onclick="updateDoctor()">Update Doctor</button>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#doctorsTable').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[1, 'asc']], // Sort by name
        language: {
            search: "Search doctors:",
            lengthMenu: "Show _MENU_ doctors per page"
        }
    });

    // Configure toastr
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "timeOut": "3000",
        "positionClass": "toast-top-right"
    };
});

// Add new doctor
function addDoctor() {
    const form = document.getElementById('addDoctorForm');
    
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const firstName = document.getElementById('firstName').value;
    const lastName = document.getElementById('lastName').value;
    const specialization = document.getElementById('specialization').value;
    const phone = document.getElementById('phone').value;
    const licenseNumber = document.getElementById('licenseNumber').value;
    const status = document.getElementById('status').value;

    const doctorId = 'DR-' + String(Date.now()).slice(-6);
    const statusBadge = status === 'Active' ? 
        '<span class="badge badge-success">Active</span>' : 
        '<span class="badge badge-warning">Inactive</span>';

    // Add to table
    const table = $('#doctorsTable').DataTable();
    table.row.add([
        doctorId,
        `Dr. ${firstName} ${lastName}`,
        specialization,
        phone,
        licenseNumber,
        statusBadge,
        `<button class="btn btn-sm btn-info" onclick="viewDoctor('${doctorId}')">
           <i class="fas fa-eye"></i>
         </button>
         <button class="btn btn-sm btn-warning" onclick="editDoctor('${doctorId}')">
           <i class="fas fa-edit"></i>
         </button>
         <button class="btn btn-sm btn-danger" onclick="deleteDoctor('${doctorId}')">
           <i class="fas fa-trash"></i>
         </button>`
    ]).draw();

    $('#addDoctorModal').modal('hide');
    form.reset();
    toastr.success('Doctor added successfully!', 'Success');
}

// View doctor details
function viewDoctor(doctorId) {
    const content = `
        <div class="row">
            <div class="col-md-6">
                <h6>Personal Information</h6>
                <p><strong>Doctor ID:</strong> ${doctorId}</p>
                <p><strong>Name:</strong> Dr. Sarah Johnson</p>
                <p><strong>Phone:</strong> +1-555-0101</p>
                <p><strong>Email:</strong> sarah.johnson@hospital.com</p>
            </div>
            <div class="col-md-6">
                <h6>Professional Information</h6>
                <p><strong>Specialization:</strong> Cardiology</p>
                <p><strong>License No.:</strong> LIC-12345</p>
                <p><strong>Experience:</strong> 15 years</p>
                <p><strong>Status:</strong> <span class="badge badge-success">Active</span></p>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <h6>Recent Activity</h6>
                <p>Last patient consultation: 2025-07-09 10:30 AM</p>
                <p>Total patients this month: 45</p>
            </div>
        </div>
    `;
    
    document.getElementById('viewDoctorContent').innerHTML = content;
    $('#viewDoctorModal').modal('show');
    toastr.info(`Viewing details for ${doctorId}`, 'Doctor Details');
}

// Edit doctor
function editDoctor(doctorId) {
    // Load doctor data (in real app, fetch from API)
    document.getElementById('editDoctorId').value = doctorId;
    document.getElementById('editFirstName').value = 'Sarah';
    document.getElementById('editLastName').value = 'Johnson';
    document.getElementById('editSpecialization').value = 'Cardiology';
    document.getElementById('editLicenseNumber').value = 'LIC-12345';
    document.getElementById('editPhone').value = '+1-555-0101';
    document.getElementById('editEmail').value = 'sarah.johnson@hospital.com';
    document.getElementById('editExperience').value = '15';
    document.getElementById('editStatus').value = 'Active';
    
    $('#editDoctorModal').modal('show');
    toastr.info(`Loading edit form for ${doctorId}`, 'Edit Doctor');
}

// Update doctor
function updateDoctor() {
    const form = document.getElementById('editDoctorForm');
    
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const doctorId = document.getElementById('editDoctorId').value;
    
    $('#editDoctorModal').modal('hide');
    toastr.success(`Doctor ${doctorId} updated successfully!`, 'Updated');
}

// Delete doctor
function deleteDoctor(doctorId) {
    if (confirm(`Are you sure you want to delete doctor ${doctorId}?`)) {
        const table = $('#doctorsTable').DataTable();
        table.rows().every(function() {
            const data = this.data();
            if (data[0] === doctorId) {
                this.remove();
            }
        });
        table.draw();
        
        toastr.success(`Doctor ${doctorId} deleted successfully!`, 'Deleted');
    }
}

// Refresh table
function refreshTable() {
    $('#doctorsTable').DataTable().ajax.reload();
    toastr.success('Doctors list refreshed!', 'Refreshed');
}
</script>
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Registered Doctors</h3>
              <div class="card-tools">
                <button type="button" class="btn btn-primary btn-sm" id="addDoctorBtn">
                  <i class="fas fa-plus"></i> Add Doctor
                </button>
                <button type="button" class="btn btn-info btn-sm" id="refreshBtn">
                  <i class="fas fa-refresh"></i> Refresh
                </button>
              </div>
            </div>
            <div class="card-body">
              <!-- Filters -->
              <div class="row mb-3">
                <div class="col-md-3">
                  <input type="text" class="form-control" id="searchDoctors" placeholder="Search doctors...">
                </div>
                <div class="col-md-3">
                  <select class="form-control" id="filterSpecialization">
                    <option value="">All Specializations</option>
                  </select>
                </div>
                <div class="col-md-3">
                  <select class="form-control" id="filterStatus">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                  </select>
                </div>
                <div class="col-md-3">
                  <button type="button" class="btn btn-primary" onclick="clearFilters()">
                    <i class="fas fa-times"></i> Clear Filters
                  </button>
                </div>
              </div>

              <!-- Doctors Table -->
              <div class="table-responsive">
                <table id="doctorsTable" class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th>Doctor Name</th>
                      <th>Hospital</th>
                      <th>Contact No.</th>
                      <th>Address</th>
                      <th>Ref. %</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody></tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<!-- Add/Edit Doctor Modal -->
<div class="modal fade" id="doctorModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h4 class="modal-title text-white" id="doctorModalTitle">
          <i class="fas fa-user-plus"></i> Add Doctor
        </h4>
        <button type="button" class="close text-white" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <form id="doctorForm">
        <div class="modal-body">
          <input type="hidden" id="doctorId" name="id">
          
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
                <label for="hospital">Hospital/Clinic <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="hospital" name="hospital" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="phone">Contact No. <span class="text-danger">*</span></label>
                <input type="tel" class="form-control" id="phone" name="phone" required>
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-8">
              <div class="form-group">
                <label for="address">Address</label>
                <textarea class="form-control" id="address" name="address" rows="2"></textarea>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label for="referralPercentage">Referral % <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="referralPercentage" name="referral_percentage" 
                       min="0" max="100" step="0.1" required>
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="specialization">Specialization</label>
                <select class="form-control" id="specialization" name="specialization">
                  <option value="">Select Specialization</option>
                  <option value="Pathology">Pathology</option>
                  <option value="Hematology">Hematology</option>
                  <option value="Microbiology">Microbiology</option>
                  <option value="Biochemistry">Biochemistry</option>
                  <option value="Immunology">Immunology</option>
                  <option value="Cytology">Cytology</option>
                  <option value="Histopathology">Histopathology</option>
                  <option value="General Practice">General Practice</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="licenseNumber">License Number</label>
                <input type="text" class="form-control" id="licenseNumber" name="license_number">
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
                <label for="status">Status</label>
                <select class="form-control" id="status" name="status">
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>
            </div>
          </div>
        </div>
        
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times"></i> Cancel
          </button>
          <button type="submit" class="btn btn-primary" id="saveDoctorBtn">
            <i class="fas fa-save"></i> Save Doctor
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- View Doctor Modal -->
<div class="modal fade" id="viewDoctorModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-info">
        <h4 class="modal-title text-white">
          <i class="fas fa-user-md"></i> Doctor Details
        </h4>
        <button type="button" class="close text-white" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body" id="viewDoctorContent">
        <!-- Doctor details will be loaded here -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
          <i class="fas fa-times"></i> Close
        </button>
      </div>
    </div>
  </div>
</div>

<?php
$additional_scripts = <<<EOT
<script>
$(document).ready(function() {
  // FORCE hide preloader immediately to prevent loading screen issue
  $('.preloader').hide();
  $('body').removeClass('hold-transition');
  
  // Ensure preloader is hidden
  setTimeout(function() {
    $('.preloader').fadeOut();
  }, 100);
  
  console.log('Page loaded, starting doctors initialization...');
  
  // Check if required libraries are loaded
  if (typeof $.fn.DataTable === 'undefined') {
    showAlert('warning', 'DataTables library failed to load. Please check your internet connection and refresh the page.');
    return;
  }
  
  let doctorsTable;
  let doctors = [];
  
  // Initialize DataTable
  function initTable() {
    // Add custom filtering function
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
      let specializationFilter = $('#filterSpecialization').val();
      let statusFilter = $('#filterStatus').val();
      
      // Get doctor data safely
      let doctor = doctors && doctors[dataIndex] ? doctors[dataIndex] : null;
      
      if (specializationFilter && doctor && doctor.specialization !== specializationFilter) {
        return false;
      }
      
      if (statusFilter && doctor && doctor.status !== statusFilter) {
        return false;
      }
      
      return true;
    });
    
    doctorsTable = $('#doctorsTable').DataTable({
      ajax: {
        url: 'api/doctors_api.php',
        dataSrc: function(json) {
          if (json.success) {
            doctors = json.data;
            updateStats();
            updateSpecializationFilter();
            return json.data;
          } else {
            showAlert('error', 'Failed to load doctors: ' + (json.message || 'Unknown error'));
            
            // If unauthorized, redirect to login
            if (json.message && json.message.includes('Unauthorized')) {
              setTimeout(() => {
                window.location.href = 'login.php';
              }, 2000);
            }
            return [];
          }
        },
        error: function(xhr, error, thrown) {
          console.error('AJAX Error:', xhr.responseText);
          let errorMsg = 'Failed to load doctors data. ';
          
          // Handle specific error cases
          if (xhr.status === 401) {
            errorMsg = 'You are not logged in. Redirecting to login page...';
            showAlert('warning', errorMsg);
            setTimeout(() => {
              window.location.href = 'login.php';
            }, 2000);
            return [];
          } else if (xhr.status === 0) {
            errorMsg += 'Please check your internet connection or if the server is running.';
          } else if (xhr.status === 404) {
            errorMsg += 'API endpoint not found.';
          } else {
            errorMsg += 'Server error: ' + error;
          }
          
          showAlert('error', errorMsg);
          return [];
        }
      },
      columns: [
        { 
          data: null,
          render: function(data) {
            return escapeHtml(data.first_name) + ' ' + escapeHtml(data.last_name);
          }
        },
        { 
          data: 'hospital',
          render: function(data) {
            return escapeHtml(data || 'N/A');
          }
        },
        { 
          data: 'phone',
          render: function(data) {
            return escapeHtml(data || '');
          }
        },
        { 
          data: 'address',
          render: function(data) {
            return escapeHtml(data || 'N/A');
          }
        },
        { 
          data: 'referral_percentage',
          render: function(data) {
            return escapeHtml((data || '0')) + '%';
          }
        },
        {
          data: null,
          orderable: false,
          render: function(data, type, row) {
            const doctorId = row.doctor_id || row.id;
            return `
              <div class="btn-group">
                <button class="btn btn-sm btn-info btn-view" data-id="${doctorId}" title="View">
                  <i class="fas fa-eye"></i>
                </button>
                <button class="btn btn-sm btn-warning btn-edit" data-id="${doctorId}" title="Edit">
                  <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger btn-delete" data-id="${doctorId}" title="Delete">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            `;
          }
        }
      ],
      responsive: true,
      pageLength: 25
    });
  }

  // Update statistics
  function updateStats() {
    let total = doctors.length;
    let active = doctors.filter(d => d.status === 'active').length;
    let specializations = [...new Set(doctors.map(d => d.specialization))].length;
    
    $('#totalDoctors').text(total);
    $('#activeDoctors').text(active);
    $('#specializations').text(specializations);
    $('#referralsToday').text('0'); // This would need actual referral data
  }

  // Update specialization filter options
  function updateSpecializationFilter() {
    let specializations = [...new Set(doctors.map(d => d.specialization))];
    let options = '<option value="">All Specializations</option>';
    specializations.forEach(spec => {
      options += `<option value="\${spec}">\${spec}</option>`;
    });
    $('#filterSpecialization').html(options);
  }

  // Initialize
  initTable();

  // Add new doctor
  $('#addDoctorBtn').click(function() {
    $('#doctorModalTitle').html('<i class="fas fa-user-plus"></i> Add Doctor');
    $('#doctorForm')[0].reset();
    $('#doctorId').val('');
    $('#status').val('active');
    $('#doctorModal').modal('show');
  });

  // Save doctor
  $('#doctorForm').submit(function(e) {
    e.preventDefault();
    
    if (!this.checkValidity()) {
      this.reportValidity();
      return;
    }

    let formData = new FormData(this);
    let data = Object.fromEntries(formData.entries());
    let isEdit = !!data.id;
    
    // Show loading state
    $('#saveDoctorBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
    
    // Rename id field to doctor_id for API compatibility
    if (data.id) {
      data.doctor_id = data.id;
      delete data.id;
    }
    
    $.ajax({
      url: 'api/doctors_api.php',
      type: isEdit ? 'PUT' : 'POST',
      contentType: 'application/json',
      data: JSON.stringify(data),
      success: function(response) {
        $('#saveDoctorBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Save Doctor');
        if (response.success) {
          showAlert('success', response.message);
          $('#doctorModal').modal('hide');
          doctorsTable.ajax.reload();
        } else {
          showAlert('error', response.message);
        }
      },
      error: function(xhr) {
        $('#saveDoctorBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Save Doctor');
        let errorMsg = 'Error saving doctor';
        try {
          let response = JSON.parse(xhr.responseText);
          errorMsg = response.message || errorMsg;
        } catch(e) {
          errorMsg += ': ' + xhr.responseText;
        }
        showAlert('error', errorMsg);
      }
    });
  });

  // Edit doctor
  $('#doctorsTable').on('click', '.btn-edit', function() {
    let id = $(this).data('id');
    
    // Find doctor data
    let doctor = doctors.find(d => d.doctor_id == id || d.id == id);
    if (doctor) {
      $('#doctorModalTitle').html('<i class="fas fa-edit"></i> Edit Doctor');
      $('#doctorId').val(doctor.doctor_id || doctor.id);
      $('#firstName').val(doctor.first_name);
      $('#lastName').val(doctor.last_name);
      $('#hospital').val(doctor.hospital || '');
      $('#phone').val(doctor.phone);
      $('#address').val(doctor.address || '');
      $('#referralPercentage').val(doctor.referral_percentage || '0');
      $('#specialization').val(doctor.specialization || '');
      $('#licenseNumber').val(doctor.license_number || '');
      $('#email').val(doctor.email || '');
      $('#status').val(doctor.status);
      $('#doctorModal').modal('show');
    }
  });

  // View doctor
  $('#doctorsTable').on('click', '.btn-view', function() {
    let id = $(this).data('id');
    
    // Find doctor data
    let doctor = doctors.find(d => d.doctor_id == id || d.id == id);
    if (doctor) {
      const content = `
        <div class="row">
          <div class="col-md-6">
            <h5>Personal Information</h5>
            <table class="table table-borderless">
              <tr><td><strong>Name:</strong></td><td>${escapeHtml(doctor.first_name)} ${escapeHtml(doctor.last_name)}</td></tr>
              <tr><td><strong>Hospital/Clinic:</strong></td><td>${escapeHtml(doctor.hospital || 'N/A')}</td></tr>
              <tr><td><strong>Contact No.:</strong></td><td>${escapeHtml(doctor.phone)}</td></tr>
              <tr><td><strong>Email:</strong></td><td>${escapeHtml(doctor.email || 'N/A')}</td></tr>
              <tr><td><strong>Address:</strong></td><td>${escapeHtml(doctor.address || 'N/A')}</td></tr>
            </table>
          </div>
          <div class="col-md-6">
            <h5>Professional Information</h5>
            <table class="table table-borderless">
              <tr><td><strong>Specialization:</strong></td><td>${escapeHtml(doctor.specialization || 'N/A')}</td></tr>
              <tr><td><strong>License Number:</strong></td><td>${escapeHtml(doctor.license_number || 'N/A')}</td></tr>
              <tr><td><strong>Referral %:</strong></td><td><span class="badge badge-primary">${escapeHtml(doctor.referral_percentage || '0')}%</span></td></tr>
              <tr><td><strong>Status:</strong></td><td><span class="badge badge-${doctor.status === 'active' ? 'success' : 'secondary'}">${doctor.status.charAt(0).toUpperCase() + doctor.status.slice(1)}</span></td></tr>
            </table>
          </div>
        </div>
      `;
      
      $('#viewDoctorContent').html(content);
      $('#viewDoctorModal').modal('show');
    }
  });

  // Add escapeHtml function
  function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  // Delete doctor
  $('#doctorsTable').on('click', '.btn-delete', function() {
    let id = $(this).data('id');
    let $btn = $(this);
    
    if (confirm('Are you sure you want to delete this doctor?')) {
      // Show loading state
      $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
      
      $.ajax({
        url: 'api/doctors_api.php',
        type: 'DELETE',
        contentType: 'application/json',
        data: JSON.stringify({ doctor_id: id }),
        success: function(response) {
          $btn.prop('disabled', false).html('<i class="fas fa-trash"></i>');
          if (response.success) {
            showAlert('success', response.message);
            doctorsTable.ajax.reload();
          } else {
            showAlert('error', response.message);
          }
        },
        error: function(xhr) {
          $btn.prop('disabled', false).html('<i class="fas fa-trash"></i>');
          let errorMsg = 'Error deleting doctor';
          try {
            let response = JSON.parse(xhr.responseText);
            errorMsg = response.message || errorMsg;
          } catch(e) {
            errorMsg += ': ' + xhr.responseText;
          }
          showAlert('error', errorMsg);
        }
      });
    }
  });

  // Refresh table
  $('#refreshBtn').click(function() {
    doctorsTable.ajax.reload();
  });

  // Search functionality
  $('#searchDoctors').on('keyup', function() {
    doctorsTable.search(this.value).draw();
  });

  // Filter by specialization
  $('#filterSpecialization').on('change', function() {
    doctorsTable.draw();
  });

  // Filter by status
  $('#filterStatus').on('change', function() {
    doctorsTable.draw();
  });
});

function clearFilters() {
  $('#searchDoctors').val('');
  $('#filterSpecialization').val('');
  $('#filterStatus').val('');
  doctorsTable.search('').draw();
}

  // Alert and utility functions
  function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' :
                      type === 'error' ? 'alert-danger' :
                      type === 'warning' ? 'alert-warning' : 'alert-info';
    const icon = type === 'success' ? 'fas fa-check' :
                 type === 'error' ? 'fas fa-ban' :
                 type === 'warning' ? 'fas fa-exclamation-triangle' : 'fas fa-info-circle';
    const alert = `
      <div class="alert ${alertClass} alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="icon ${icon}"></i> ${message}
      </div>
    `;
    $('#alertContainer').html(alert);
    setTimeout(() => {
      $('#alertContainer .alert').fadeOut();
    }, 5000);
  }
</script>
EOT;

include 'includes/footer.php';
?>
