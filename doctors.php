<?php
// Start session and check authentication before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in - redirect immediately if not
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Set page title and active menu
$page_title = 'Doctors';
$active_menu = 'doctors';

// Include header and sidebar
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
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
      <!-- Alert Messages -->
      <div id="alertContainer"></div>

      <!-- Stats Row -->
      <div class="row">
        <div class="col-lg-3 col-6">
          <div class="small-box bg-info">
            <div class="inner">
              <h3 id="totalDoctors">0</h3>
              <p>Total Doctors</p>
            </div>
            <div class="icon">
              <i class="fas fa-user-md"></i>
            </div>
            <a href="#" class="small-box-footer">View Details <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-success">
            <div class="inner">
              <h3 id="activeDoctors">0</h3>
              <p>Active Doctors</p>
            </div>
            <div class="icon">
              <i class="fas fa-check-circle"></i>
            </div>
            <a href="#" class="small-box-footer">View Details <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-warning">
            <div class="inner">
              <h3 id="specializations">0</h3>
              <p>Specializations</p>
            </div>
            <div class="icon">
              <i class="fas fa-stethoscope"></i>
            </div>
            <a href="#" class="small-box-footer">View Details <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-danger">
            <div class="inner">
              <h3 id="referralsToday">0</h3>
              <p>Referrals Today</p>
            </div>
            <div class="icon">
              <i class="fas fa-paper-plane"></i>
            </div>
            <a href="#" class="small-box-footer">View Details <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
      </div>

      <!-- Main Card -->
      <div class="row">
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
            const doctorId = row.id || row.doctor_id;
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
        if (response.success) {
          showAlert('success', response.message);
          $('#doctorModal').modal('hide');
          doctorsTable.ajax.reload();
        } else {
          showAlert('error', response.message);
        }
      },
      error: function(xhr) {
        showAlert('error', 'Error saving doctor: ' + xhr.responseText);
      }
    });
  });

  // Edit doctor
  $('#doctorsTable').on('click', '.btn-edit', function() {
    let id = $(this).data('id');
    
    // Find doctor data
    let doctor = doctors.find(d => d.id == id || d.doctor_id == id);
    if (doctor) {
      $('#doctorModalTitle').html('<i class="fas fa-edit"></i> Edit Doctor');
      $('#doctorId').val(doctor.id || doctor.doctor_id);
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
    let doctor = doctors.find(d => d.id == id || d.doctor_id == id);
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
    
    if (confirm('Are you sure you want to delete this doctor?')) {
      $.ajax({
        url: 'api/doctors_api.php',
        type: 'DELETE',
        contentType: 'application/json',
        data: JSON.stringify({ doctor_id: id }),
        success: function(response) {
          if (response.success) {
            showAlert('success', response.message);
            doctorsTable.ajax.reload();
          } else {
            showAlert('error', response.message);
          }
        },
        error: function(xhr) {
          showAlert('error', 'Error deleting doctor: ' + xhr.responseText);
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
    doctorsTable.column(2).search(this.value).draw();
  });

  // Filter by status
  $('#filterStatus').on('change', function() {
    doctorsTable.column(6).search(this.value).draw();
  });
});

function clearFilters() {
  $('#searchDoctors').val('');
  $('#filterSpecialization').val('');
  $('#filterStatus').val('');
  doctorsTable.search('').columns().search('').draw();
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
