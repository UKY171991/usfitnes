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

// Sample test results data
$test_results = [
    ['id' => 1, 'order_id' => 'ORD001', 'patient_name' => 'John Doe', 'test_name' => 'Complete Blood Count', 'result_date' => '2025-06-18', 'status' => 'Final', 'critical' => false],
    ['id' => 2, 'order_id' => 'ORD002', 'patient_name' => 'Jane Smith', 'test_name' => 'Liver Function Test', 'result_date' => '2025-06-18', 'status' => 'Final', 'critical' => true],
    ['id' => 3, 'order_id' => 'ORD003', 'patient_name' => 'Mike Johnson', 'test_name' => 'Blood Glucose', 'result_date' => '2025-06-17', 'status' => 'Preliminary', 'critical' => false],
    ['id' => 4, 'order_id' => 'ORD004', 'patient_name' => 'Sarah Wilson', 'test_name' => 'Urine Analysis', 'result_date' => '2025-06-17', 'status' => 'Final', 'critical' => false],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>PathLab Pro | Test Results</title>

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
            <a href="tests.php" class="nav-link">
              <i class="nav-icon fas fa-flask"></i>
              <p>Lab Tests</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="results.php" class="nav-link active">
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
            <h1 class="m-0">Test Results</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
              <li class="breadcrumb-item active">Test Results</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Laboratory Test Results</h3>
                <div class="card-tools">
                  <button type="button" class="btn btn-warning">
                    <i class="fas fa-exclamation-triangle"></i> Critical Results
                  </button>
                </div>
              </div>
              <div class="card-body">
                <table id="resultsTable" class="table table-bordered table-striped">
                  <thead>
                  <tr>
                    <th>Order ID</th>
                    <th>Patient Name</th>
                    <th>Test Name</th>
                    <th>Result Date</th>
                    <th>Status</th>
                    <th>Critical</th>
                    <th>Actions</th>
                  </tr>
                  </thead>
                  <tbody>
                  <?php foreach($test_results as $result): ?>
                  <tr class="<?php echo $result['critical'] ? 'table-warning' : ''; ?>">
                    <td><span class="badge badge-primary"><?php echo $result['order_id']; ?></span></td>
                    <td><?php echo htmlspecialchars($result['patient_name']); ?></td>
                    <td><?php echo htmlspecialchars($result['test_name']); ?></td>
                    <td><?php echo date('M d, Y H:i', strtotime($result['result_date'])); ?></td>
                    <td>
                      <span class="badge badge-<?php echo $result['status'] == 'Final' ? 'success' : 'warning'; ?>">
                        <?php echo $result['status']; ?>
                      </span>
                    </td>
                    <td>
                      <?php if($result['critical']): ?>
                        <span class="badge badge-danger">
                          <i class="fas fa-exclamation-triangle"></i> Critical
                        </span>
                      <?php else: ?>
                        <span class="badge badge-success">Normal</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <button class="btn btn-info btn-sm" title="View Report">
                        <i class="fas fa-file-alt"></i>
                      </button>
                      <button class="btn btn-primary btn-sm" title="Download PDF">
                        <i class="fas fa-download"></i>
                      </button>
                      <button class="btn btn-success btn-sm" title="Email Report">
                        <i class="fas fa-envelope"></i>
                      </button>
                      <?php if($result['critical']): ?>
                      <button class="btn btn-danger btn-sm" title="Alert Doctor">
                        <i class="fas fa-phone"></i>
                      </button>
                      <?php endif; ?>
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
  $("#resultsTable").DataTable({
    "responsive": true,
    "lengthChange": false,
    "autoWidth": false,
    "order": [[ 3, "desc" ]] // Sort by result date
  });
});
</script>
</body>
</html>
