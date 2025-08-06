<?php
// Set page title
$page_title = 'Doctors - PathLab Pro';

// Include header and session handling
include 'includes/adminlte_header.php';
include 'includes/adminlte_sidebar.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add':
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $specialization = trim($_POST['specialization'] ?? '');
            $license_number = trim($_POST['license_number'] ?? '');
            $hospital = trim($_POST['hospital'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $notes = trim($_POST['notes'] ?? '');
            
            if ($name && $phone && $specialization) {
                // Generate doctor ID
                $stmt = $conn->query("SELECT COUNT(*) as count FROM doctors");
                $count = $stmt->fetch()['count'] + 1;
                $doctor_id = 'DOC' . str_pad($count, 3, '0', STR_PAD_LEFT);
                
                $stmt = $conn->prepare("INSERT INTO doctors (doctor_id, name, email, phone, specialization, license_number, hospital, address, notes, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                if ($stmt->execute([$doctor_id, $name, $email, $phone, $specialization, $license_number, $hospital, $address, $notes])) {
                    $response = ['success' => true, 'message' => 'Doctor added successfully'];
                } else {
                    $response = ['success' => false, 'message' => 'Error adding doctor'];
                }
            } else {
                $response = ['success' => false, 'message' => 'Name, phone and specialization are required'];
            }
            break;
            
        case 'edit':
            $id = $_POST['id'] ?? '';
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $specialization = trim($_POST['specialization'] ?? '');
            $license_number = trim($_POST['license_number'] ?? '');
            $hospital = trim($_POST['hospital'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $notes = trim($_POST['notes'] ?? '');
            $status = $_POST['status'] ?? 'active';
            
            if ($id && $name && $phone && $specialization) {
                $stmt = $conn->prepare("UPDATE doctors SET name = ?, email = ?, phone = ?, specialization = ?, license_number = ?, hospital = ?, address = ?, notes = ?, status = ? WHERE id = ?");
                if ($stmt->execute([$name, $email, $phone, $specialization, $license_number, $hospital, $address, $notes, $status, $id])) {
                    $response = ['success' => true, 'message' => 'Doctor updated successfully'];
                } else {
                    $response = ['success' => false, 'message' => 'Error updating doctor'];
                }
            } else {
                $response = ['success' => false, 'message' => 'ID, name, phone and specialization are required'];
            }
            break;
            
        case 'delete':
            $id = $_POST['id'] ?? '';
            
            if ($id) {
                $stmt = $conn->prepare("DELETE FROM doctors WHERE id = ?");
                if ($stmt->execute([$id])) {
                    $response = ['success' => true, 'message' => 'Doctor deleted successfully'];
                } else {
                    $response = ['success' => false, 'message' => 'Error deleting doctor'];
                }
            }
            break;
            
        case 'get':
            $id = $_POST['id'] ?? '';
            
            if ($id) {
                $stmt = $conn->prepare("SELECT * FROM doctors WHERE id = ?");
                $stmt->execute([$id]);
                $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($doctor) {
                    $response = ['success' => true, 'data' => $doctor];
                } else {
                    $response = ['success' => false, 'message' => 'Doctor not found'];
                }
            }
            break;
    }
    
    if (isset($response)) {
        echo json_encode($response);
        exit;
    }
}

// Get all doctors with stats
$stmt = $conn->query("SELECT * FROM doctors ORDER BY created_at DESC");
$doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_doctors = count($doctors);
$active_doctors = count(array_filter($doctors, function($d) { return $d['status'] == 'active'; }));
$inactive_doctors = $total_doctors - $active_doctors;
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
              <h3><?php echo $total_doctors; ?></h3>
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
              <h3><?php echo $active_doctors; ?></h3>
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
              <h3><?php echo $inactive_doctors; ?></h3>
              <p>Inactive Doctors</p>
            </div>
            <div class="icon">
              <i class="fas fa-pause-circle"></i>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-secondary">
            <div class="inner">
              <h3><?php echo count(array_unique(array_column($doctors, 'specialization'))); ?></h3>
              <p>Specializations</p>
            </div>
            <div class="icon">
              <i class="fas fa-stethoscope"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="row mb-3">
        <div class="col-md-8">
          <div class="input-group">
            <input type="text" class="form-control" id="searchInput" placeholder="Search doctors by name, specialization, or hospital...">
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
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-list mr-2"></i>Doctors List</h3>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table id="doctorsTable" class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th>Doctor ID</th>
                  <th>Name</th>
                  <th>Specialization</th>
                  <th>Phone</th>
                  <th>Email</th>
                  <th>Hospital</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($doctors as $doctor): ?>
                <tr>
                  <td><strong><?php echo htmlspecialchars($doctor['doctor_id']); ?></strong></td>
                  <td><?php echo htmlspecialchars($doctor['name']); ?></td>
                  <td>
                    <span class="badge badge-primary"><?php echo htmlspecialchars($doctor['specialization']); ?></span>
                  </td>
                  <td><?php echo htmlspecialchars($doctor['phone']); ?></td>
                  <td><?php echo htmlspecialchars($doctor['email'] ?? 'N/A'); ?></td>
                  <td><?php echo htmlspecialchars($doctor['hospital'] ?? 'N/A'); ?></td>
                  <td>
                    <span class="badge badge-<?php echo $doctor['status'] == 'active' ? 'success' : 'secondary'; ?>">
                      <?php echo ucfirst(htmlspecialchars($doctor['status'])); ?>
                    </span>
                  </td>
                  <td>
                    <button class="btn btn-sm btn-info" onclick="viewDoctor(<?php echo $doctor['id']; ?>)">
                      <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-primary" onclick="editDoctor(<?php echo $doctor['id']; ?>)">
                      <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteDoctor(<?php echo $doctor['id']; ?>)">
                      <i class="fas fa-trash"></i>
                    </button>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<!-- Add Doctor Modal -->
<div class="modal fade" id="addDoctorModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title"><i class="fas fa-plus mr-2"></i>Add New Doctor</h4>
        <button type="button" class="close" data-dismiss="modal">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="addDoctorForm">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="name">Full Name *</label>
                <input type="text" class="form-control" id="name" name="name" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="phone">Phone Number *</label>
                <input type="tel" class="form-control" id="phone" name="phone" required>
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
                <label for="specialization">Specialization *</label>
                <input type="text" class="form-control" id="specialization" name="specialization" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="license_number">License Number</label>
                <input type="text" class="form-control" id="license_number" name="license_number">
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
            <label for="address">Address</label>
            <textarea class="form-control" id="address" name="address" rows="2"></textarea>
          </div>
          <div class="form-group">
            <label for="notes">Notes</label>
            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Any additional notes about the doctor..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">
            <i class="fas fa-save mr-2"></i>Add Doctor
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Doctor Modal -->
<div class="modal fade" id="editDoctorModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title"><i class="fas fa-edit mr-2"></i>Edit Doctor</h4>
        <button type="button" class="close" data-dismiss="modal">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="editDoctorForm">
        <input type="hidden" id="edit_id" name="id">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_name">Full Name *</label>
                <input type="text" class="form-control" id="edit_name" name="name" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_phone">Phone Number *</label>
                <input type="tel" class="form-control" id="edit_phone" name="phone" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_email">Email Address</label>
                <input type="email" class="form-control" id="edit_email" name="email">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_specialization">Specialization *</label>
                <input type="text" class="form-control" id="edit_specialization" name="specialization" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_license_number">License Number</label>
                <input type="text" class="form-control" id="edit_license_number" name="license_number">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_hospital">Hospital/Clinic</label>
                <input type="text" class="form-control" id="edit_hospital" name="hospital">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_status">Status</label>
                <select class="form-control" id="edit_status" name="status">
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label for="edit_address">Address</label>
            <textarea class="form-control" id="edit_address" name="address" rows="2"></textarea>
          </div>
          <div class="form-group">
            <label for="edit_notes">Notes</label>
            <textarea class="form-control" id="edit_notes" name="notes" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save mr-2"></i>Update Doctor
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- View Doctor Modal -->
<div class="modal fade" id="viewDoctorModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title"><i class="fas fa-eye mr-2"></i>Doctor Details</h4>
        <button type="button" class="close" data-dismiss="modal">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="doctorDetailsContent">
        <!-- Content will be loaded dynamically -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/adminlte_footer.php'; ?>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#doctorsTable').DataTable({
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        "order": [[0, "desc"]],
        "pageLength": 25
    });

    // Add Doctor Form Submission
    $('#addDoctorForm').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'doctors.php',
            type: 'POST',
            data: $(this).serialize() + '&action=add',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#addDoctorModal').modal('hide');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('An error occurred while adding the doctor');
            }
        });
    });

    // Edit Doctor Form Submission
    $('#editDoctorForm').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'doctors.php',
            type: 'POST',
            data: $(this).serialize() + '&action=edit',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#editDoctorModal').modal('hide');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('An error occurred while updating the doctor');
            }
        });
    });

    // Search functionality
    $('#searchInput').on('keyup', function() {
        $('#doctorsTable').DataTable().search(this.value).draw();
    });

    // Clear forms when modals are hidden
    $('#addDoctorModal').on('hidden.bs.modal', function() {
        $('#addDoctorForm')[0].reset();
    });
});

function viewDoctor(id) {
    $.ajax({
        url: 'doctors.php',
        type: 'POST',
        data: { action: 'get', id: id },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                var doctor = response.data;
                
                var content = `
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr><td><strong>Doctor ID:</strong></td><td>${doctor.doctor_id}</td></tr>
                                <tr><td><strong>Name:</strong></td><td>${doctor.name}</td></tr>
                                <tr><td><strong>Specialization:</strong></td><td><span class="badge badge-primary">${doctor.specialization}</span></td></tr>
                                <tr><td><strong>Phone:</strong></td><td>${doctor.phone}</td></tr>
                                <tr><td><strong>Email:</strong></td><td>${doctor.email || 'N/A'}</td></tr>
                                <tr><td><strong>Status:</strong></td><td><span class="badge badge-${doctor.status == 'active' ? 'success' : 'secondary'}">${doctor.status.charAt(0).toUpperCase() + doctor.status.slice(1)}</span></td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr><td><strong>License Number:</strong></td><td>${doctor.license_number || 'N/A'}</td></tr>
                                <tr><td><strong>Hospital/Clinic:</strong></td><td>${doctor.hospital || 'N/A'}</td></tr>
                                <tr><td><strong>Registration Date:</strong></td><td>${new Date(doctor.created_at).toLocaleDateString()}</td></tr>
                            </table>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <h6><strong>Address:</strong></h6>
                            <p>${doctor.address || 'No address provided'}</p>
                            <h6><strong>Notes:</strong></h6>
                            <p>${doctor.notes || 'No notes recorded'}</p>
                        </div>
                    </div>
                `;
                
                $('#doctorDetailsContent').html(content);
                $('#viewDoctorModal').modal('show');
            } else {
                toastr.error(response.message);
            }
        },
        error: function() {
            toastr.error('An error occurred while loading doctor details');
        }
    });
}

function editDoctor(id) {
    $.ajax({
        url: 'doctors.php',
        type: 'POST',
        data: { action: 'get', id: id },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                var doctor = response.data;
                $('#edit_id').val(doctor.id);
                $('#edit_name').val(doctor.name);
                $('#edit_phone').val(doctor.phone);
                $('#edit_email').val(doctor.email || '');
                $('#edit_specialization').val(doctor.specialization);
                $('#edit_license_number').val(doctor.license_number || '');
                $('#edit_hospital').val(doctor.hospital || '');
                $('#edit_status').val(doctor.status);
                $('#edit_address').val(doctor.address || '');
                $('#edit_notes').val(doctor.notes || '');
                $('#editDoctorModal').modal('show');
            } else {
                toastr.error(response.message);
            }
        },
        error: function() {
            toastr.error('An error occurred while loading doctor details');
        }
    });
}

function deleteDoctor(id) {
    if (confirm('Are you sure you want to delete this doctor? This action cannot be undone.')) {
        $.ajax({
            url: 'doctors.php',
            type: 'POST',
            data: { action: 'delete', id: id },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('An error occurred while deleting the doctor');
            }
        });
    }
}

function refreshTable() {
    location.reload();
}
</script>
