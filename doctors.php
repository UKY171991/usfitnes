<?php
// Set page title
$page_title = 'Doctors Management - PathLab Pro';

// Include database connection
require_once 'config.php';

// Get action parameter
$action = $_GET['action'] ?? 'list';
$doctor_id = $_GET['id'] ?? null;

// Get doctors data
$doctors = [];
try {
    $query = "SELECT * FROM doctors WHERE status != 'deleted' ORDER BY created_at DESC";
    $result = mysqli_query($conn, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $doctors[] = $row;
        }
    }
} catch (Exception $e) {
    error_log("Doctors query error: " . $e->getMessage());
}

// Include AdminLTE header and sidebar
include 'includes/adminlte_header.php';
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
            <i class="fas fa-user-md mr-2 text-primary"></i>
            Doctors Management
          </h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item">
              <a href="dashboard.php">
                <i class="fas fa-home"></i> Home
              </a>
            </li>
            <li class="breadcrumb-item active">Doctors</li>
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

<?php include 'includes/adminlte_footer.php'; ?>
