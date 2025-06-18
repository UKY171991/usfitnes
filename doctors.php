<?php
session_start();

// Include database configuration
require_once 'config.php';

// Check if user is logged in
if(!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'User';
$message = '';
$messageType = '';

// Handle form submission for new doctor
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_doctor'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $specialization = trim($_POST['specialization']);
    $license_number = trim($_POST['license_number']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $hospital_affiliation = trim($_POST['hospital_affiliation']);
    
    // Check if license number already exists
    $check_stmt = $conn->prepare("SELECT id FROM doctors WHERE license_number = ?");
    $check_stmt->bind_param("s", $license_number);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $message = "A doctor with this license number already exists.";
        $messageType = "danger";
    } else {
        $stmt = $conn->prepare("INSERT INTO doctors (first_name, last_name, specialization, license_number, phone, email, hospital_affiliation) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $first_name, $last_name, $specialization, $license_number, $phone, $email, $hospital_affiliation);
        
        if ($stmt->execute()) {
            $message = "Doctor added successfully!";
            $messageType = "success";
        } else {
            $message = "Error adding doctor: " . $conn->error;
            $messageType = "danger";
        }
        $stmt->close();
    }
    $check_stmt->close();
}

// Get doctors from database
$query = "SELECT * FROM doctors ORDER BY last_name, first_name";
$result = $conn->query($query);
$doctors = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $doctors[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>PathLab Pro | Doctors</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables.net-bs4/1.11.3/dataTables.bootstrap4.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">

  <style>
    .content-wrapper {
      background: linear-gradient(135deg, #f8f9fa 0%, #e3f2fd 100%);
    }
    .main-header {
      background: linear-gradient(135deg, #2c5aa0 0%, #1e3c72 100%);
    }
    .main-sidebar {
      background: #263238;
    }
    .card {
      border-radius: 15px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars text-white"></i></a>
      </li>
    </ul>
    <ul class="navbar-nav ml-auto">
      <li class="nav-item">
        <a class="nav-link" href="logout.php" role="button">
          <i class="fas fa-sign-out-alt text-white"></i>
        </a>
      </li>
    </ul>
  </nav>

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="dashboard.php" class="brand-link">
      <img src="https://via.placeholder.com/33x33/2c5aa0/ffffff?text=PL" alt="PathLab Pro Logo" class="brand-image img-circle elevation-3">
      <span class="brand-text font-weight-light">PathLab Pro</span>
    </a>

    <div class="sidebar">
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="https://via.placeholder.com/160x160/2c5aa0/ffffff?text=<?php echo strtoupper(substr($username, 0, 1)); ?>" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a href="#" class="d-block"><?php echo htmlspecialchars($username); ?></a>
        </div>
      </div>

      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
          <li class="nav-item">
            <a href="dashboard.php" class="nav-link">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Dashboard</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="patients.php" class="nav-link">
              <i class="nav-icon fas fa-user-injured"></i>
              <p>Patients</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="test-orders.php" class="nav-link">
              <i class="nav-icon fas fa-clipboard-list"></i>
              <p>Test Orders</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="results.php" class="nav-link">
              <i class="nav-icon fas fa-file-medical"></i>
              <p>Test Results</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="doctors.php" class="nav-link active">
              <i class="nav-icon fas fa-user-md"></i>
              <p>Doctors</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="logout.php" class="nav-link">
              <i class="nav-icon fas fa-sign-out-alt"></i>
              <p>Logout</p>
            </a>
          </li>
        </ul>
      </nav>
    </div>
  </aside>

  <!-- Content Wrapper -->
  <div class="content-wrapper">
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

    <section class="content">
      <div class="container-fluid">
        <?php if($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible">
          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
          <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>
        
        <!-- Add New Doctor Card -->
        <div class="row">
          <div class="col-12">
            <div class="card collapsed-card">
              <div class="card-header">
                <h3 class="card-title">Add New Doctor</h3>
                <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-plus"></i>
                  </button>
                </div>
              </div>
              <div class="card-body" style="display: none;">
                <form method="post">
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
                        <label for="specialization">Specialization <span class="text-danger">*</span></label>
                        <select class="form-control" id="specialization" name="specialization" required>
                          <option value="">Select Specialization</option>
                          <option value="Internal Medicine">Internal Medicine</option>
                          <option value="Family Medicine">Family Medicine</option>
                          <option value="Cardiology">Cardiology</option>
                          <option value="Endocrinology">Endocrinology</option>
                          <option value="Hematology">Hematology</option>
                          <option value="Oncology">Oncology</option>
                          <option value="Gastroenterology">Gastroenterology</option>
                          <option value="Nephrology">Nephrology</option>
                          <option value="Pulmonology">Pulmonology</option>
                          <option value="Emergency Medicine">Emergency Medicine</option>
                          <option value="Surgery">Surgery</option>
                          <option value="Pediatrics">Pediatrics</option>
                          <option value="Obstetrics and Gynecology">Obstetrics and Gynecology</option>
                          <option value="General Practice">General Practice</option>
                          <option value="Other">Other</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="license_number">License Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="license_number" name="license_number" required>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone">
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
                    <div class="col-12">
                      <div class="form-group">
                        <label for="hospital_affiliation">Hospital/Clinic Affiliation</label>
                        <input type="text" class="form-control" id="hospital_affiliation" name="hospital_affiliation" placeholder="Hospital or clinic name">
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-12">
                      <button type="submit" name="add_doctor" class="btn btn-primary">
                        <i class="fas fa-save"></i> Add Doctor
                      </button>
                      <button type="button" class="btn btn-secondary" data-card-widget="collapse">
                        <i class="fas fa-times"></i> Cancel
                      </button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>

        <!-- Doctors List -->
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Registered Doctors</h3>
                <div class="card-tools">
                  <span class="badge badge-primary"><?php echo count($doctors); ?> Total Doctors</span>
                </div>
              </div>
              <div class="card-body">
                <table id="doctorsTable" class="table table-bordered table-striped">
                  <thead>
                  <tr>
                    <th>Name</th>
                    <th>Specialization</th>
                    <th>License #</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Hospital</th>
                    <th>Actions</th>
                  </tr>
                  </thead>
                  <tbody>
                  <?php foreach($doctors as $doctor): ?>
                  <tr>
                    <td>
                      <strong>Dr. <?php echo htmlspecialchars($doctor['last_name'] . ', ' . $doctor['first_name']); ?></strong>
                    </td>
                    <td>
                      <span class="badge badge-info"><?php echo htmlspecialchars($doctor['specialization']); ?></span>
                    </td>
                    <td><?php echo htmlspecialchars($doctor['license_number']); ?></td>
                    <td><?php echo htmlspecialchars($doctor['phone'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($doctor['email'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($doctor['hospital_affiliation'] ?? 'N/A'); ?></td>
                    <td>
                      <div class="btn-group">
                        <button type="button" class="btn btn-info btn-sm" title="View Details">
                          <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" title="Edit">
                          <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-success btn-sm" title="Contact">
                          <i class="fas fa-phone"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <footer class="main-footer">
    <strong>Copyright &copy; 2024 <a href="#">PathLab Pro</a>.</strong> All rights reserved.
  </footer>
</div>

<!-- jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>
<!-- DataTables & Plugins -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables.net/1.11.3/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables.net-bs4/1.11.3/dataTables.bootstrap4.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>

<script>
$(function () {
  $("#doctorsTable").DataTable({
    "responsive": true,
    "lengthChange": false,
    "autoWidth": false,
    "order": [[ 0, "asc" ]] // Sort by name
  });
});
</script>
</body>
</html>
