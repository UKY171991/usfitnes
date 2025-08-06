<?php
// Set page title
$page_title = 'Patients Management - PathLab Pro';

// Include database connection
require_once 'config.php';

// Get action parameter
$action = $_GET['action'] ?? 'list';
$patient_id = $_GET['id'] ?? null;

// Handle form submissions
if ($_POST) {
    // Handle patient creation/update
    // This would typically be in a separate handler file
}

// Get patients data
$patients = [];
try {
    $query = "SELECT * FROM patients WHERE status != 'deleted' ORDER BY created_at DESC";
    $result = mysqli_query($conn, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $patients[] = $row;
        }
    }
} catch (Exception $e) {
    error_log("Patients query error: " . $e->getMessage());
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
            <i class="fas fa-user-injured mr-2 text-primary"></i>
            Patients Management
          </h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item">
              <a href="dashboard.php">
                <i class="fas fa-home"></i> Home
              </a>
            </li>
            <li class="breadcrumb-item active">Patients</li>
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
                All Patients
              </h3>
              <div class="card-tools">
                <a href="?action=add" class="btn btn-primary btn-sm">
                  <i class="fas fa-plus mr-1"></i>Add New Patient
                </a>
              </div>
            </div>
            <div class="card-body">
              <?php if (empty($patients)): ?>
                <div class="text-center p-4">
                  <i class="fas fa-user-injured fa-3x text-muted mb-3"></i>
                  <h5 class="text-muted">No Patients Found</h5>
                  <p class="text-muted">Start by adding your first patient to the system.</p>
                  <a href="?action=add" class="btn btn-primary">
                    <i class="fas fa-plus mr-2"></i>Add First Patient
                  </a>
                </div>
              <?php else: ?>
                <table id="patientsTable" class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Name</th>
                      <th>Email</th>
                      <th>Phone</th>
                      <th>Date of Birth</th>
                      <th>Status</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($patients as $patient): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($patient['id'] ?? ''); ?></td>
                      <td>
                        <strong><?php echo htmlspecialchars(($patient['first_name'] ?? '') . ' ' . ($patient['last_name'] ?? '')); ?></strong>
                      </td>
                      <td><?php echo htmlspecialchars($patient['email'] ?? ''); ?></td>
                      <td><?php echo htmlspecialchars($patient['phone'] ?? ''); ?></td>
                      <td><?php echo $patient['date_of_birth'] ? date('M d, Y', strtotime($patient['date_of_birth'])) : ''; ?></td>
                      <td>
                        <?php
                        $status = $patient['status'] ?? 'active';
                        $badge_class = $status === 'active' ? 'badge-success' : 'badge-secondary';
                        echo "<span class=\"badge {$badge_class}\">" . ucfirst($status) . "</span>";
                        ?>
                      </td>
                      <td>
                        <div class="btn-group">
                          <a href="?action=view&id=<?php echo $patient['id']; ?>" class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i>
                          </a>
                          <a href="?action=edit&id=<?php echo $patient['id']; ?>" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i>
                          </a>
                          <button class="btn btn-danger btn-sm" onclick="deletePatient(<?php echo $patient['id']; ?>)">
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
                <?php echo $action === 'add' ? 'Add New' : 'Edit'; ?> Patient
              </h3>
              <div class="card-tools">
                <a href="?" class="btn btn-secondary btn-sm">
                  <i class="fas fa-arrow-left mr-1"></i>Back to List
                </a>
              </div>
            </div>
            <form id="patientForm" method="POST">
              <div class="card-body">
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
                      <label for="email">Email Address</label>
                      <input type="email" class="form-control" id="email" name="email">
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
                      <label for="date_of_birth">Date of Birth <span class="text-danger">*</span></label>
                      <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" required>
                    </div>
                  </div>
                  <div class="col-md-6">
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
                </div>
                
                <div class="form-group">
                  <label for="address">Address</label>
                  <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                </div>
                
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="emergency_contact_name">Emergency Contact Name</label>
                      <input type="text" class="form-control" id="emergency_contact_name" name="emergency_contact_name">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="emergency_contact_phone">Emergency Contact Phone</label>
                      <input type="tel" class="form-control" id="emergency_contact_phone" name="emergency_contact_phone">
                    </div>
                  </div>
                </div>
                
                <div class="form-group">
                  <label for="medical_history">Medical History</label>
                  <textarea class="form-control" id="medical_history" name="medical_history" rows="4"></textarea>
                </div>
              </div>
              
              <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-save mr-1"></i>
                  <?php echo $action === 'add' ? 'Add' : 'Update'; ?> Patient
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
      <!-- View Patient Details -->
      <div class="row">
        <div class="col-md-8 offset-md-2">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-user mr-1"></i>
                Patient Details
              </h3>
              <div class="card-tools">
                <a href="?" class="btn btn-secondary btn-sm">
                  <i class="fas fa-arrow-left mr-1"></i>Back to List
                </a>
                <a href="?action=edit&id=<?php echo $patient_id; ?>" class="btn btn-warning btn-sm">
                  <i class="fas fa-edit mr-1"></i>Edit
                </a>
              </div>
            </div>
            <div class="card-body">
              <div class="text-center mb-4">
                <div class="user-image bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" 
                     style="width: 80px; height: 80px; font-size: 2rem; font-weight: bold; color: white;">
                  P
                </div>
                <h4 class="mt-2">Patient Name</h4>
                <p class="text-muted">Patient ID: #<?php echo $patient_id; ?></p>
              </div>
              
              <!-- Patient information would be loaded here -->
              <div class="alert alert-info">
                <i class="fas fa-info-circle mr-2"></i>
                Patient details will be displayed here when integrated with the database.
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
    // Initialize DataTable for patients list
    if ($('#patientsTable').length) {
        $('#patientsTable').DataTable({
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
    
    // Form validation
    $('#patientForm').on('submit', function(e) {
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
            PathLabPro.notifications.success('Patient saved successfully!');
            // window.location.href = '?';
        } else {
            PathLabPro.notifications.error('Please fill in all required fields.');
        }
    });
});

function deletePatient(id) {
    PathLabPro.modal.confirm({
        title: 'Delete Patient',
        text: 'Are you sure you want to delete this patient? This action cannot be undone.',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Delete patient logic here
            PathLabPro.notifications.success('Patient deleted successfully!');
            // Reload page or remove row
        }
    });
}
</script>

<?php include 'includes/adminlte_footer.php'; ?>
