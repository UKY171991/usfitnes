<?php
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
                      <th>Photo</th>
                      <th>Name</th>
                      <th>Specialization</th>
                      <th>License No</th>
                      <th>Phone</th>
                      <th>Email</th>
                      <th>Status</th>
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
                <label for="specialization">Specialization <span class="text-danger">*</span></label>
                <select class="form-control" id="specialization" name="specialization" required>
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
                <label for="licenseNumber">License Number <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="licenseNumber" name="license_number" required>
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
                <label for="email">Email <span class="text-danger">*</span></label>
                <input type="email" class="form-control" id="email" name="email" required>
              </div>
            </div>
          </div>
          
          <div class="row">
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

<?php
$additional_scripts = <<<EOT
<script>
$(document).ready(function() {
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
            showAlert('error', 'Failed to load doctors: ' + json.message);
            return [];
          }
        },
        error: function(xhr, error, thrown) {
          showAlert('error', 'Failed to load doctors: ' + error);
        }
      },
      columns: [
        { 
          data: null,
          render: function() {
            return '<i class="fas fa-user-md fa-2x text-primary"></i>';
          },
          orderable: false,
          width: '60px'
        },
        { 
          data: null,
          render: function(data) {
            return data.first_name + ' ' + data.last_name;
          }
        },
        { data: 'specialization' },
        { data: 'license_number' },
        { data: 'phone' },
        { data: 'email' },
        { 
          data: 'status',
          render: function(data) {
            let badgeClass = data === 'active' ? 'success' : 'secondary';
            return `<span class="badge badge-\${badgeClass}">\${data.charAt(0).toUpperCase() + data.slice(1)}</span>`;
          }
        },
        {
          data: null,
          orderable: false,
          render: function(data, type, row) {
            return `
              <div class="btn-group">
                <button class="btn btn-sm btn-warning btn-edit" data-id="\${row.id}" title="Edit">
                  <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger btn-delete" data-id="\${row.id}" title="Delete">
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
    let doctor = doctors.find(d => d.id == id);
    if (doctor) {
      $('#doctorModalTitle').html('<i class="fas fa-edit"></i> Edit Doctor');
      $('#doctorId').val(doctor.id);
      $('#firstName').val(doctor.first_name);
      $('#lastName').val(doctor.last_name);
      $('#specialization').val(doctor.specialization);
      $('#licenseNumber').val(doctor.license_number);
      $('#phone').val(doctor.phone);
      $('#email').val(doctor.email);
      $('#status').val(doctor.status);
      $('#doctorModal').modal('show');
    }
  });

  // Delete doctor
  $('#doctorsTable').on('click', '.btn-delete', function() {
    let id = $(this).data('id');
    
    if (confirm('Are you sure you want to delete this doctor?')) {
      $.ajax({
        url: 'api/doctors_api.php',
        type: 'DELETE',
        contentType: 'application/json',
        data: JSON.stringify({ id: id }),
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

function showAlert(type, message) {
  const alertClass = type === 'success' ? 'alert-success' :
                    type === 'error' ? 'alert-danger' :
                    type === 'warning' ? 'alert-warning' : 'alert-info';
  const icon = type === 'success' ? 'fas fa-check' :
               type === 'error' ? 'fas fa-ban' :
               type === 'warning' ? 'fas fa-exclamation-triangle' : 'fas fa-info-circle';
  const alert = `
    <div class="alert \${alertClass} alert-dismissible">
      <button type="button" class="close" data-dismiss="alert">&times;</button>
      <i class="icon \${icon}"></i> \${message}
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
