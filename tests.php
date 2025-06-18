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
$user_type = $_SESSION['user_type'] ?? 'user';
$page_title = 'Lab Tests';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>PathLab Pro | Lab Tests</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/css/dataTables.bootstrap4.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <!-- Preloader -->
  <div class="preloader flex-column justify-content-center align-items-center">
    <img class="animation__shake" src="img/logo.svg" alt="PathLab Pro Logo" height="60" width="60">
  </div>

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="dashboard.php" class="nav-link">Home</a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="#" class="nav-link">Contact</a>
      </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      <!-- User Menu -->
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="far fa-user"></i>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <span class="dropdown-item dropdown-header"><?php echo htmlspecialchars($username); ?></span>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <i class="fas fa-user-cog mr-2"></i> Profile
          </a>
          <div class="dropdown-divider"></div>
          <a href="logout.php" class="dropdown-item">
            <i class="fas fa-sign-out-alt mr-2"></i> Logout
          </a>
        </div>
      </li>
    </ul>
  </nav>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="dashboard.php" class="brand-link">
      <img src="img/logo.svg" alt="PathLab Pro Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">PathLab Pro</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($username); ?>&background=random" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a href="#" class="d-block"><?php echo htmlspecialchars($username); ?> <span class="badge badge-success"><?php echo htmlspecialchars(ucfirst($user_type)); ?></span></a>
        </div>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <li class="nav-item">
            <a href="dashboard.php" class="nav-link">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Dashboard</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="patients.php" class="nav-link">
              <i class="nav-icon fas fa-user-injured"></i>
              <p>
                Patients
                <span class="right badge badge-info">New</span>
              </p>
            </a>
          </li>
          <li class="nav-item">
            <a href="test-orders.php" class="nav-link">
              <i class="nav-icon fas fa-clipboard-list"></i>
              <p>Test Orders</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="tests.php" class="nav-link active">
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
            <a href="reports.php" class="nav-link">
              <i class="nav-icon fas fa-chart-line"></i>
              <p>Reports</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="equipment.php" class="nav-link">
              <i class="nav-icon fas fa-microscope"></i>
              <p>Equipment</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="settings.php" class="nav-link">
              <i class="nav-icon fas fa-cog"></i>
              <p>Settings</p>
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
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Lab Tests Management</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
              <li class="breadcrumb-item active">Lab Tests</li>
            </ol>
          </div>
        </div>
      </div>
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <!-- Lab Tests DataTable -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Available Lab Tests</h3>
            <div class="card-tools">
              <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-add-test">
                <i class="fas fa-plus"></i> Add Test
              </button>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table id="testsTable" class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>Test ID</th>
                    <th>Test Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Turnaround Time</th>
                    <th>Sample Type</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="testsTableBody">
                  <tr>
                    <td>CBC001</td>
                    <td>Complete Blood Count</td>
                    <td>Hematology</td>
                    <td>$25.00</td>
                    <td>24 hours</td>
                    <td>Blood</td>
                    <td>
                      <button class="btn btn-sm btn-info btn-edit-test" data-id="CBC001">
                        <i class="fas fa-edit"></i>
                      </button>
                      <button class="btn btn-sm btn-danger btn-delete-test" data-id="CBC001">
                        <i class="fas fa-trash"></i>
                      </button>
                    </td>
                  </tr>
                  <tr>
                    <td>GLU001</td>
                    <td>Glucose Test</td>
                    <td>Chemistry</td>
                    <td>$15.00</td>
                    <td>12 hours</td>
                    <td>Blood</td>
                    <td>
                      <button class="btn btn-sm btn-info btn-edit-test" data-id="GLU001">
                        <i class="fas fa-edit"></i>
                      </button>
                      <button class="btn btn-sm btn-danger btn-delete-test" data-id="GLU001">
                        <i class="fas fa-trash"></i>
                      </button>
                    </td>
                  </tr>
                  <tr>
                    <td>LFT001</td>
                    <td>Liver Function Test</td>
                    <td>Chemistry</td>
                    <td>$45.00</td>
                    <td>24 hours</td>
                    <td>Blood</td>
                    <td>
                      <button class="btn btn-sm btn-info btn-edit-test" data-id="LFT001">
                        <i class="fas fa-edit"></i>
                      </button>
                      <button class="btn btn-sm btn-danger btn-delete-test" data-id="LFT001">
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
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <!-- Add Test Modal -->
  <div class="modal fade" id="modal-add-test">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Add New Test</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="addTestForm">
            <div class="form-group">
              <label for="testId">Test ID</label>
              <input type="text" class="form-control" id="testId" placeholder="Enter test ID" required>
            </div>
            <div class="form-group">
              <label for="testName">Test Name</label>
              <input type="text" class="form-control" id="testName" placeholder="Enter test name" required>
            </div>
            <div class="form-group">
              <label for="category">Category</label>
              <select class="form-control" id="category" required>
                <option value="">Select Category</option>
                <option value="Hematology">Hematology</option>
                <option value="Chemistry">Chemistry</option>
                <option value="Microbiology">Microbiology</option>
                <option value="Immunology">Immunology</option>
                <option value="Pathology">Pathology</option>
              </select>
            </div>
            <div class="form-group">
              <label for="price">Price</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">$</span>
                </div>
                <input type="number" step="0.01" class="form-control" id="price" placeholder="Enter price" required>
              </div>
            </div>
            <div class="form-group">
              <label for="turnaround">Turnaround Time</label>
              <input type="text" class="form-control" id="turnaround" placeholder="e.g. 24 hours" required>
            </div>
            <div class="form-group">
              <label for="sampleType">Sample Type</label>
              <input type="text" class="form-control" id="sampleType" placeholder="e.g. Blood, Urine" required>
            </div>
          </form>
        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" id="saveTestBtn">Save</button>
        </div>
      </div>
    </div>
  </div>

  <footer class="main-footer">
    <strong>Copyright &copy; 2025 <a href="#">PathLab Pro</a>.</strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
      <b>Version</b> 1.0.0
    </div>
  </footer>
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>
<!-- DataTables -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/dataTables.bootstrap4.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>

<script>
$(document).ready(function() {
  // Initialize DataTable
  $('#testsTable').DataTable({
    "paging": true,
    "lengthChange": true,
    "searching": true,
    "ordering": true,
    "info": true,
    "autoWidth": false,
    "responsive": true
  });
  
  // Save Test button click handler
  $('#saveTestBtn').on('click', function() {
    const form = document.getElementById('addTestForm');
    
    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }
    
    // Get form data (in a real app, this would be sent to the server)
    const testData = {
      testId: $('#testId').val(),
      testName: $('#testName').val(),
      category: $('#category').val(),
      price: $('#price').val(),
      turnaround: $('#turnaround').val(),
      sampleType: $('#sampleType').val()
    };
    
    console.log('Test data to save:', testData);
    
    // Add a new row to the table (in a real app, this would happen after successful API call)
    const newRow = `
      <tr>
        <td>${testData.testId}</td>
        <td>${testData.testName}</td>
        <td>${testData.category}</td>
        <td>$${testData.price}</td>
        <td>${testData.turnaround}</td>
        <td>${testData.sampleType}</td>
        <td>
          <button class="btn btn-sm btn-info btn-edit-test" data-id="${testData.testId}">
            <i class="fas fa-edit"></i>
          </button>
          <button class="btn btn-sm btn-danger btn-delete-test" data-id="${testData.testId}">
            <i class="fas fa-trash"></i>
          </button>
        </td>
      </tr>
    `;
    
    $('#testsTableBody').append(newRow);
    
    // Reset form and close modal
    form.reset();
    $('#modal-add-test').modal('hide');
    
    // Show success message
    alert('Test added successfully!');
  });
});
</script>
</body>
</html>
