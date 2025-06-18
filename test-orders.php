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

// Handle form submission for new test order
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_order'])) {
    $patient_id = $_POST['patient_id'];
    $doctor_id = $_POST['doctor_id'];
    $test_name = $_POST['test_name'];
    $priority = $_POST['priority'];
    $sample_type = $_POST['sample_type'] ?? 'Blood';
    $notes = $_POST['notes'] ?? '';
    
    // Generate order ID
    $order_id = 'ORD' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    $stmt = $conn->prepare("INSERT INTO test_orders (order_id, patient_id, doctor_id, test_name, priority, sample_type, notes, order_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, CURDATE(), 'Pending')");
    $stmt->bind_param("siissss", $order_id, $patient_id, $doctor_id, $test_name, $priority, $sample_type, $notes);
    
    if ($stmt->execute()) {
        $message = "Test order created successfully!";
        $messageType = "success";
    } else {
        $message = "Error creating test order: " . $conn->error;
        $messageType = "danger";
    }
    $stmt->close();
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['new_status'];
    
    $stmt = $conn->prepare("UPDATE test_orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $order_id);
    
    if ($stmt->execute()) {
        $message = "Order status updated successfully!";
        $messageType = "success";
    } else {
        $message = "Error updating status: " . $conn->error;
        $messageType = "danger";
    }
    $stmt->close();
}

// Get test orders from database
$query = "SELECT to.*, p.first_name, p.last_name, d.first_name as doctor_first, d.last_name as doctor_last 
          FROM test_orders to 
          LEFT JOIN patients p ON to.patient_id = p.id 
          LEFT JOIN doctors d ON to.doctor_id = d.id 
          ORDER BY to.order_date DESC, to.id DESC";
$result = $conn->query($query);
$test_orders = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $test_orders[] = $row;
    }
}

// Get patients for dropdown
$patients_query = "SELECT id, first_name, last_name FROM patients ORDER BY last_name, first_name";
$patients_result = $conn->query($patients_query);
$patients = [];
if ($patients_result && $patients_result->num_rows > 0) {
    while($row = $patients_result->fetch_assoc()) {
        $patients[] = $row;
    }
}

// Get doctors for dropdown
$doctors_query = "SELECT id, first_name, last_name FROM doctors ORDER BY last_name, first_name";
$doctors_result = $conn->query($doctors_query);
$doctors = [];
if ($doctors_result && $doctors_result->num_rows > 0) {
    while($row = $doctors_result->fetch_assoc()) {
        $doctors[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>PathLab Pro | Test Orders</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables.net-bs4/1.11.3/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables.net-responsive-bs4/2.2.9/responsive.bootstrap4.min.css">
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
            <a href="test-orders.php" class="nav-link active">
              <i class="nav-icon fas fa-clipboard-list"></i>
              <p>Test Orders</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="tests.php" class="nav-link">
              <i class="nav-icon fas fa-flask"></i>
              <p>Lab Tests</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="results.php" class="nav-link">
              <i class="nav-icon fas fa-file-medical"></i>
              <p>Test Results</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="doctors.php" class="nav-link">
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
            <h1 class="m-0">Test Orders Management</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
              <li class="breadcrumb-item active">Test Orders</li>
            </ol>
          </div>
        </div>
      </div>
    </div>    <section class="content">
      <div class="container-fluid">
        <?php if($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible">
          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
          <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>
        
        <!-- Add New Test Order Card -->
        <div class="row">
          <div class="col-12">
            <div class="card collapsed-card">
              <div class="card-header">
                <h3 class="card-title">Add New Test Order</h3>
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
                        <label for="patient_id">Patient <span class="text-danger">*</span></label>
                        <select class="form-control" id="patient_id" name="patient_id" required>
                          <option value="">Select Patient</option>
                          <?php foreach($patients as $patient): ?>
                          <option value="<?php echo $patient['id']; ?>">
                            <?php echo htmlspecialchars($patient['last_name'] . ', ' . $patient['first_name']); ?>
                          </option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="doctor_id">Ordering Doctor <span class="text-danger">*</span></label>
                        <select class="form-control" id="doctor_id" name="doctor_id" required>
                          <option value="">Select Doctor</option>
                          <?php foreach($doctors as $doctor): ?>
                          <option value="<?php echo $doctor['id']; ?>">
                            Dr. <?php echo htmlspecialchars($doctor['last_name'] . ', ' . $doctor['first_name']); ?>
                          </option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="test_name">Test Name <span class="text-danger">*</span></label>
                        <select class="form-control" id="test_name" name="test_name" required>
                          <option value="">Select Test</option>
                          <option value="Complete Blood Count">Complete Blood Count (CBC)</option>
                          <option value="Basic Metabolic Panel">Basic Metabolic Panel (BMP)</option>
                          <option value="Comprehensive Metabolic Panel">Comprehensive Metabolic Panel (CMP)</option>
                          <option value="Liver Function Test">Liver Function Test (LFT)</option>
                          <option value="Lipid Panel">Lipid Panel</option>
                          <option value="Thyroid Function Test">Thyroid Function Test (TFT)</option>
                          <option value="Hemoglobin A1C">Hemoglobin A1C</option>
                          <option value="Blood Glucose">Blood Glucose</option>
                          <option value="Urine Analysis">Urine Analysis</option>
                          <option value="Blood Culture">Blood Culture</option>
                          <option value="PT/INR">Prothrombin Time (PT/INR)</option>
                          <option value="D-Dimer">D-Dimer</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="form-group">
                        <label for="priority">Priority <span class="text-danger">*</span></label>
                        <select class="form-control" id="priority" name="priority" required>
                          <option value="Normal">Normal</option>
                          <option value="Urgent">Urgent</option>
                          <option value="STAT">STAT</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-md-3">
                      <div class="form-group">
                        <label for="sample_type">Sample Type</label>
                        <select class="form-control" id="sample_type" name="sample_type">
                          <option value="Blood">Blood</option>
                          <option value="Urine">Urine</option>
                          <option value="Stool">Stool</option>
                          <option value="Swab">Swab</option>
                          <option value="Tissue">Tissue</option>
                          <option value="Other">Other</option>
                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-12">
                      <div class="form-group">
                        <label for="notes">Clinical Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Clinical history, symptoms, or special instructions..."></textarea>
                      </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-12">
                      <button type="submit" name="add_order" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Test Order
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

        <!-- Test Orders List -->
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Laboratory Test Orders</h3>
                <div class="card-tools">
                  <span class="badge badge-primary"><?php echo count($test_orders); ?> Total Orders</span>
                </div>
              </div>
              <div class="card-body">
                <table id="ordersTable" class="table table-bordered table-striped">
                  <thead>
                  <tr>
                    <th>Order ID</th>
                    <th>Patient Name</th>
                    <th>Doctor</th>
                    <th>Test Name</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Order Date</th>
                    <th>Actions</th>
                  </tr>
                  </thead>
                  <tbody>
                  <?php foreach($test_orders as $order): ?>
                  <tr>
                    <td><span class="badge badge-info"><?php echo htmlspecialchars($order['order_id']); ?></span></td>
                    <td><?php echo htmlspecialchars(($order['last_name'] ?? '') . ', ' . ($order['first_name'] ?? '')); ?></td>
                    <td><?php echo htmlspecialchars('Dr. ' . ($order['doctor_last'] ?? '') . ', ' . ($order['doctor_first'] ?? '')); ?></td>
                    <td><?php echo htmlspecialchars($order['test_name']); ?></td>
                    <td>
                      <span class="badge badge-<?php echo $order['priority'] == 'STAT' ? 'danger' : ($order['priority'] == 'Urgent' ? 'warning' : 'success'); ?>">
                        <?php echo htmlspecialchars($order['priority']); ?>
                      </span>
                    </td>
                    <td>
                      <span class="badge badge-<?php 
                        echo $order['status'] == 'Completed' ? 'success' : 
                             ($order['status'] == 'In_Progress' ? 'warning' : 
                             ($order['status'] == 'Sample_Collected' ? 'info' : 'secondary')); ?>">
                        <?php echo str_replace('_', ' ', htmlspecialchars($order['status'])); ?>
                      </span>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                    <td>
                      <div class="btn-group">
                        <button type="button" class="btn btn-info btn-sm" title="View Details" onclick="viewOrder(<?php echo $order['id']; ?>)">
                          <i class="fas fa-eye"></i>
                        </button>
                        <?php if($order['status'] != 'Completed'): ?>
                        <div class="btn-group">
                          <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown" title="Update Status">
                            <i class="fas fa-edit"></i>
                          </button>
                          <div class="dropdown-menu">
                            <form method="post" style="display: inline;">
                              <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                              <button type="submit" name="update_status" value="Sample_Collected" class="dropdown-item">
                                <i class="fas fa-vial text-info"></i> Sample Collected
                              </button>
                              <button type="submit" name="update_status" value="In_Progress" class="dropdown-item">
                                <i class="fas fa-play text-warning"></i> In Progress
                              </button>
                              <button type="submit" name="update_status" value="Completed" class="dropdown-item">
                                <i class="fas fa-check text-success"></i> Completed
                              </button>
                              <input type="hidden" name="new_status" value="">
                            </form>
                          </div>
                        </div>
                        <?php endif; ?>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables.net-responsive/2.2.9/dataTables.responsive.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables.net-responsive-bs4/2.2.9/responsive.bootstrap4.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>

<script>
$(function () {
  $("#ordersTable").DataTable({
    "responsive": true,
    "lengthChange": false,
    "autoWidth": false,
    "order": [[ 6, "desc" ]] // Sort by order date
  });
  
  // Handle status update dropdown
  $('.dropdown-menu button[name="update_status"]').click(function(e) {
    e.preventDefault();
    var form = $(this).closest('form');
    var status = $(this).val();
    form.find('input[name="new_status"]').val(status);
    form.submit();
  });
});

function viewOrder(orderId) {
  // Placeholder for view order details functionality
  alert('View order details for Order ID: ' + orderId + '\n\nThis feature will show detailed information about the test order.');
}
</script>
</body>
</html>
