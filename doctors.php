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
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#doctorModal" onclick="openAddModal()">
                  <i class="fas fa-plus mr-1"></i>Add Doctor
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm ml-1" onclick="refreshTable()">
                  <i class="fas fa-sync-alt mr-1"></i>Refresh
                </button>
              </div>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table id="doctorsTable" class="table table-bordered table-striped table-hover">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Name</th>
                      <th>Specialization</th>
                      <th>Phone</th>
                      <th>Email</th>
                      <th>License No.</th>
                      <th>Status</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <!-- Data will be loaded via DataTables AJAX -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<!-- Doctor Modal -->
<div class="modal fade" id="doctorModal" tabindex="-1" aria-labelledby="doctorModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h5 class="modal-title text-white" id="doctorModalLabel">
          <i class="fas fa-user-md mr-2"></i>
          <span id="modalTitle">Add New Doctor</span>
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <form id="doctorForm" novalidate>
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
                  <option value="Other">Other</option>
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
                <label for="phone">Phone Number <span class="text-danger">*</span></label>
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
            <div class="col-md-6">
              <div class="form-group">
                <label for="qualification">Qualification</label>
                <input type="text" class="form-control" id="qualification" name="qualification" placeholder="e.g., MBBS, MD">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="status">Status</label>
                <select class="form-control" id="status" name="status">
                  <option value="Active">Active</option>
                  <option value="Inactive">Inactive</option>
                </select>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save mr-1"></i>Save Doctor
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTable
    initDataTable();
});

function initDataTable() {
    $('#doctorsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: 'ajax/doctors_datatable.php',
            type: 'POST',
            error: function(xhr, error, thrown) {
                console.log('DataTables Error:', error);
                showToast('error', 'Failed to load doctor data. Please check your database connection.');
            }
        },
        columns: [
            { data: 'id', width: '60px' },
            { data: 'full_name' },
            { data: 'specialization' },
            { data: 'phone' },
            { data: 'email' },
            { data: 'license_number' },
            { data: 'status', width: '100px' },
            { data: 'actions', orderable: false, width: '120px' }
        ],
        order: [[0, 'desc']],
        responsive: true,
        language: {
            processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Loading...'
        }
    });
}

function refreshTable() {
    $('#doctorsTable').DataTable().ajax.reload(null, false);
    showToast('success', 'Table refreshed successfully');
}

function openAddModal() {
    $('#doctorModalLabel #modalTitle').text('Add New Doctor');
    $('#doctorForm')[0].reset();
    $('#doctorId').val('');
    $('#doctorForm').removeClass('was-validated');
    $('#status').val('Active');
}

function editDoctor(id) {
    $('#doctorModalLabel #modalTitle').text('Edit Doctor');
    
    $.ajax({
        url: 'api/doctors_api.php',
        type: 'GET',
        data: { action: 'get', id: id },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const doctor = response.data;
                $('#doctorId').val(doctor.id);
                $('#firstName').val(doctor.first_name);
                $('#lastName').val(doctor.last_name);
                $('#specialization').val(doctor.specialization);
                $('#licenseNumber').val(doctor.license_number);
                $('#phone').val(doctor.phone);
                $('#email').val(doctor.email);
                $('#qualification').val(doctor.qualification);
                $('#status').val(doctor.status);
                $('#doctorModal').modal('show');
            } else {
                showToast('error', response.message);
            }
        },
        error: function() {
            showToast('error', 'Failed to load doctor data');
        }
    });
}

function deleteDoctor(id) {
    if (confirm('Are you sure you want to delete this doctor?')) {
        $.ajax({
            url: 'api/doctors_api.php',
            type: 'POST',
            data: { 
                action: 'delete', 
                id: id 
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showToast('success', 'Doctor deleted successfully');
                    refreshTable();
                } else {
                    showToast('error', response.message);
                }
            },
            error: function() {
                showToast('error', 'Failed to delete doctor');
            }
        });
    }
}

// Form submission
$('#doctorForm').on('submit', function(e) {
    e.preventDefault();
    
    if (!this.checkValidity()) {
        e.stopPropagation();
        $(this).addClass('was-validated');
        return;
    }
    
    const formData = new FormData(this);
    const isEdit = $('#doctorId').val() !== '';
    formData.append('action', isEdit ? 'update' : 'create');
    
    $.ajax({
        url: 'api/doctors_api.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showToast('success', isEdit ? 'Doctor updated successfully' : 'Doctor created successfully');
                $('#doctorModal').modal('hide');
                refreshTable();
            } else {
                showToast('error', response.message);
            }
        },
        error: function() {
            showToast('error', 'Failed to save doctor');
        }
    });
});

function showToast(type, message) {
    const toast = $(`
        <div class="toast toast-${type}" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
            <div class="toast-header">
                <i class="fas fa-${type === 'success' ? 'check-circle text-success' : 'exclamation-circle text-danger'} mr-2"></i>
                <strong class="mr-auto">${type === 'success' ? 'Success' : 'Error'}</strong>
                <button type="button" class="ml-2 mb-1 close" data-dismiss="toast">
                    <span>&times;</span>
                </button>
            </div>
            <div class="toast-body">${message}</div>
        </div>
    `);
    
    $('body').append(toast);
    toast.toast({ delay: 3000 });
    toast.toast('show');
    
    toast.on('hidden.bs.toast', function() {
        $(this).remove();
    });
}
</script>

<?php include 'includes/adminlte_template_footer.php'; ?>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      
      <?php if ($action === 'list'): ?>
      <!-- List View -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-list mr-1"></i>
                All Doctors
              </h3>
              <div class="card-tools">
                <a href="?action=add" class="btn btn-primary btn-sm">
                  <i class="fas fa-plus mr-1"></i>Add New Doctor
                </a>
              </div>
            </div>
            <div class="card-body">
              <?php if (empty($doctors)): ?>
                <div class="text-center p-4">
                  <i class="fas fa-user-md fa-3x text-muted mb-3"></i>
                  <h5 class="text-muted">No Doctors Found</h5>
                  <p class="text-muted">Start by adding doctors to the system.</p>
                  <a href="?action=add" class="btn btn-primary">
                    <i class="fas fa-plus mr-2"></i>Add First Doctor
                  </a>
                </div>
              <?php else: ?>
                <table id="doctorsTable" class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Name</th>
                      <th>Specialization</th>
                      <th>Email</th>
                      <th>Phone</th>
                      <th>Status</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($doctors as $doctor): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($doctor['id'] ?? ''); ?></td>
                      <td>
                        <div>
                          <strong>Dr. <?php echo htmlspecialchars(($doctor['first_name'] ?? '') . ' ' . ($doctor['last_name'] ?? '')); ?></strong>
                          <br>
                          <small class="text-muted"><?php echo htmlspecialchars($doctor['title'] ?? ''); ?></small>
                        </div>
                      </td>
                      <td><?php echo htmlspecialchars($doctor['specialization'] ?? ''); ?></td>
                      <td><?php echo htmlspecialchars($doctor['email'] ?? ''); ?></td>
                      <td><?php echo htmlspecialchars($doctor['phone'] ?? ''); ?></td>
                      <td>
                        <?php
                        $status = $doctor['status'] ?? 'active';
                        $badge_class = $status === 'active' ? 'badge-success' : 'badge-secondary';
                        echo "<span class=\"badge {$badge_class}\">" . ucfirst($status) . "</span>";
                        ?>
                      </td>
                      <td>
                        <div class="btn-group">
                          <a href="?action=view&id=<?php echo $doctor['id']; ?>" class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i>
                          </a>
                          <a href="?action=edit&id=<?php echo $doctor['id']; ?>" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i>
                          </a>
                          <button class="btn btn-danger btn-sm" onclick="deleteDoctor(<?php echo $doctor['id']; ?>)">
                            <i class="fas fa-trash"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      
      <?php elseif ($action === 'add' || $action === 'edit'): ?>
      <!-- Add/Edit Form -->
      <div class="row">
        <div class="col-md-8 offset-md-2">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-<?php echo $action === 'add' ? 'plus' : 'edit'; ?> mr-1"></i>
                <?php echo $action === 'add' ? 'Add New' : 'Edit'; ?> Doctor
              </h3>
              <div class="card-tools">
                <a href="?" class="btn btn-secondary btn-sm">
                  <i class="fas fa-arrow-left mr-1"></i>Back to List
                </a>
              </div>
            </div>
            <form id="doctorForm" method="POST">
              <div class="card-body">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="title">Title</label>
                      <select class="form-control" id="title" name="title">
                        <option value="Dr.">Dr.</option>
                        <option value="Prof.">Prof.</option>
                        <option value="Asst. Prof.">Asst. Prof.</option>
                        <option value="Assoc. Prof.">Assoc. Prof.</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="specialization">Specialization <span class="text-danger">*</span></label>
                      <select class="form-control select2" id="specialization" name="specialization" required>
                        <option value="">Select Specialization</option>
                        <option value="Pathology">Pathology</option>
                        <option value="Clinical Pathology">Clinical Pathology</option>
                        <option value="Microbiology">Microbiology</option>
                        <option value="Hematology">Hematology</option>
                        <option value="Biochemistry">Biochemistry</option>
                        <option value="Radiology">Radiology</option>
                        <option value="Cardiology">Cardiology</option>
                        <option value="Internal Medicine">Internal Medicine</option>
                        <option value="General Practice">General Practice</option>
                      </select>
                    </div>
                  </div>
                </div>
                
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="first_name">First Name <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="first_name" name="first_name" required>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="last_name">Last Name <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="last_name" name="last_name" required>
                    </div>
                  </div>
                </div>
                
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="email">Email Address <span class="text-danger">*</span></label>
                      <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="phone">Phone Number <span class="text-danger">*</span></label>
                      <input type="tel" class="form-control" id="phone" name="phone" required>
                    </div>
                  </div>
                </div>
                
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="license_number">Medical License Number</label>
                      <input type="text" class="form-control" id="license_number" name="license_number">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="years_experience">Years of Experience</label>
                      <input type="number" class="form-control" id="years_experience" name="years_experience" min="0">
                    </div>
                  </div>
                </div>
                
                <div class="form-group">
                  <label for="hospital_clinic">Hospital/Clinic Affiliation</label>
                  <input type="text" class="form-control" id="hospital_clinic" name="hospital_clinic">
                </div>
                
                <div class="form-group">
                  <label for="address">Address</label>
                  <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                </div>
                
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="consultation_fee">Consultation Fee</label>
                      <div class="input-group">
                        <div class="input-group-prepend">
                          <span class="input-group-text">$</span>
                        </div>
                        <input type="number" class="form-control" id="consultation_fee" name="consultation_fee" step="0.01">
                      </div>
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
                
                <div class="form-group">
                  <label for="qualifications">Qualifications</label>
                  <textarea class="form-control" id="qualifications" name="qualifications" rows="3" 
                           placeholder="Medical degrees, certifications, awards..."></textarea>
                </div>
                
                <div class="form-group">
                  <label for="notes">Notes</label>
                  <textarea class="form-control" id="notes" name="notes" rows="3" 
                           placeholder="Additional notes about the doctor..."></textarea>
                </div>
              </div>
              
              <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-save mr-1"></i>
                  <?php echo $action === 'add' ? 'Add' : 'Update'; ?> Doctor
                </button>
                <a href="?" class="btn btn-secondary">
                  <i class="fas fa-times mr-1"></i>Cancel
                </a>
              </div>
            </form>
          </div>
        </div>
      </div>
      
      <?php elseif ($action === 'view'): ?>
      <!-- View Doctor Details -->
      <div class="row">
        <div class="col-md-8 offset-md-2">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-user-md mr-1"></i>
                Doctor Details
              </h3>
              <div class="card-tools">
                <a href="?" class="btn btn-secondary btn-sm">
                  <i class="fas fa-arrow-left mr-1"></i>Back to List
                </a>
                <a href="?action=edit&id=<?php echo $doctor_id; ?>" class="btn btn-warning btn-sm">
                  <i class="fas fa-edit mr-1"></i>Edit
                </a>
              </div>
            </div>
            <div class="card-body">
              <div class="text-center mb-4">
                <div class="user-image bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" 
                     style="width: 80px; height: 80px; font-size: 2rem; font-weight: bold; color: white;">
                  D
                </div>
                <h4 class="mt-2">Dr. Doctor Name</h4>
                <p class="text-muted">Specialization</p>
              </div>
              
              <!-- Doctor information would be loaded here -->
              <div class="alert alert-info">
                <i class="fas fa-info-circle mr-2"></i>
                Doctor details will be displayed here when integrated with the database.
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>
      
    </div><!-- /.container-fluid -->
  </section>
  <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<script>
$(document).ready(function() {
    // Initialize DataTable for doctors list
    if ($('#doctorsTable').length) {
        $('#doctorsTable').DataTable({
            responsive: true,
            autoWidth: false,
            pageLength: 25,
            order: [[0, 'desc']], // Order by ID descending
            columnDefs: [
                { 
                    targets: -1, // Last column (Actions)
                    orderable: false,
                    searchable: false
                }
            ]
        });
    }
    
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });
    
    // Form validation
    $('#doctorForm').on('submit', function(e) {
        e.preventDefault();
        
        // Basic validation
        let isValid = true;
        $(this).find('[required]').each(function() {
            if (!$(this).val().trim()) {
                isValid = false;
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        if (isValid) {
            // Submit form (you would typically send this to a handler)
            PathLabPro.notifications.success('Doctor saved successfully!');
            // window.location.href = '?';
        } else {
            PathLabPro.notifications.error('Please fill in all required fields.');
        }
    });
});

function deleteDoctor(id) {
    PathLabPro.modal.confirm({
        title: 'Delete Doctor',
        text: 'Are you sure you want to delete this doctor? This action cannot be undone.',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Delete doctor logic here
            PathLabPro.notifications.success('Doctor deleted successfully!');
            // Reload page or remove row
        }
    });
}
</script>

<?php include 'includes/adminlte_template_footer.php'; ?>
