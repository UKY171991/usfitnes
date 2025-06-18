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
$page_title = 'Equipment Management';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>PathLab Pro | Equipment Management</title>

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
            <a href="reports.php" class="nav-link">
              <i class="nav-icon fas fa-chart-line"></i>
              <p>Reports</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="equipment.php" class="nav-link active">
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
            <h1 class="m-0">Equipment Management</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
              <li class="breadcrumb-item active">Equipment</li>
            </ol>
          </div>
        </div>
      </div>
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <!-- Equipment stats cards -->
        <div class="row">
          <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
              <div class="inner">
                <h3>12</h3>
                <p>Total Equipment</p>
              </div>
              <div class="icon">
                <i class="fas fa-microscope"></i>
              </div>
              <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
              <div class="inner">
                <h3>9</h3>
                <p>Operational Equipment</p>
              </div>
              <div class="icon">
                <i class="fas fa-check-circle"></i>
              </div>
              <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
              <div class="inner">
                <h3>2</h3>
                <p>Under Maintenance</p>
              </div>
              <div class="icon">
                <i class="fas fa-tools"></i>
              </div>
              <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
              <div class="inner">
                <h3>1</h3>
                <p>Out of Service</p>
              </div>
              <div class="icon">
                <i class="fas fa-exclamation-triangle"></i>
              </div>
              <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
        </div>

        <!-- Equipment DataTable -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Laboratory Equipment</h3>
            <div class="card-tools">
              <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-add-equipment">
                <i class="fas fa-plus"></i> Add Equipment
              </button>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table id="equipmentTable" class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>Equipment ID</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Model</th>
                    <th>Serial Number</th>
                    <th>Purchase Date</th>
                    <th>Last Maintenance</th>
                    <th>Next Maintenance</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>EQ001</td>
                    <td>Automated Hematology Analyzer</td>
                    <td>Hematology</td>
                    <td>XS-1000i</td>
                    <td>SN12345678</td>
                    <td>2023-01-15</td>
                    <td>2025-01-20</td>
                    <td>2025-07-20</td>
                    <td><span class="badge badge-success">Operational</span></td>
                    <td>
                      <div class="btn-group">
                        <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#modal-view-equipment"><i class="fas fa-eye"></i></button>
                        <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#modal-maintenance"><i class="fas fa-tools"></i></button>
                        <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td>EQ002</td>
                    <td>Clinical Chemistry Analyzer</td>
                    <td>Chemistry</td>
                    <td>CA-6000</td>
                    <td>SN87654321</td>
                    <td>2023-03-22</td>
                    <td>2025-02-15</td>
                    <td>2025-08-15</td>
                    <td><span class="badge badge-success">Operational</span></td>
                    <td>
                      <div class="btn-group">
                        <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#modal-view-equipment"><i class="fas fa-eye"></i></button>
                        <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#modal-maintenance"><i class="fas fa-tools"></i></button>
                        <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td>EQ003</td>
                    <td>Microbiology Analyzer</td>
                    <td>Microbiology</td>
                    <td>MB-5000</td>
                    <td>SN98765432</td>
                    <td>2023-07-08</td>
                    <td>2025-01-10</td>
                    <td>2025-07-10</td>
                    <td><span class="badge badge-warning">Under Maintenance</span></td>
                    <td>
                      <div class="btn-group">
                        <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#modal-view-equipment"><i class="fas fa-eye"></i></button>
                        <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#modal-maintenance"><i class="fas fa-tools"></i></button>
                        <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td>EQ004</td>
                    <td>PCR Machine</td>
                    <td>Molecular</td>
                    <td>PCR-8000</td>
                    <td>SN45678901</td>
                    <td>2022-11-30</td>
                    <td>2025-02-25</td>
                    <td>2025-08-25</td>
                    <td><span class="badge badge-danger">Out of Service</span></td>
                    <td>
                      <div class="btn-group">
                        <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#modal-view-equipment"><i class="fas fa-eye"></i></button>
                        <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#modal-maintenance"><i class="fas fa-tools"></i></button>
                        <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <!-- /.card -->

        <!-- Equipment Maintenance Schedule -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Maintenance Schedule</h3>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th>Equipment</th>
                    <th>Scheduled Date</th>
                    <th>Type</th>
                    <th>Technician</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Automated Hematology Analyzer</td>
                    <td>2025-07-20</td>
                    <td>Routine</td>
                    <td>John Smith</td>
                    <td><span class="badge badge-primary">Scheduled</span></td>
                  </tr>
                  <tr>
                    <td>Clinical Chemistry Analyzer</td>
                    <td>2025-08-15</td>
                    <td>Routine</td>
                    <td>Jane Doe</td>
                    <td><span class="badge badge-primary">Scheduled</span></td>
                  </tr>
                  <tr>
                    <td>Microbiology Analyzer</td>
                    <td>2025-06-18</td>
                    <td>Emergency</td>
                    <td>David Wilson</td>
                    <td><span class="badge badge-warning">In Progress</span></td>
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

  <!-- Add Equipment Modal -->
  <div class="modal fade" id="modal-add-equipment">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Add New Equipment</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="addEquipmentForm">
            <div class="form-group">
              <label for="equipmentName">Equipment Name</label>
              <input type="text" class="form-control" id="equipmentName" placeholder="Enter equipment name" required>
            </div>
            <div class="form-group">
              <label for="equipmentType">Type</label>
              <select class="form-control" id="equipmentType" required>
                <option value="">Select Type</option>
                <option value="Hematology">Hematology</option>
                <option value="Chemistry">Chemistry</option>
                <option value="Microbiology">Microbiology</option>
                <option value="Molecular">Molecular</option>
                <option value="Immunology">Immunology</option>
                <option value="Pathology">Pathology</option>
                <option value="Other">Other</option>
              </select>
            </div>
            <div class="form-group">
              <label for="model">Model</label>
              <input type="text" class="form-control" id="model" placeholder="Enter model number">
            </div>
            <div class="form-group">
              <label for="serialNumber">Serial Number</label>
              <input type="text" class="form-control" id="serialNumber" placeholder="Enter serial number" required>
            </div>
            <div class="form-group">
              <label for="purchaseDate">Purchase Date</label>
              <input type="date" class="form-control" id="purchaseDate" required>
            </div>
            <div class="form-group">
              <label for="warrantyEnd">Warranty End Date</label>
              <input type="date" class="form-control" id="warrantyEnd">
            </div>
            <div class="form-group">
              <label for="status">Status</label>
              <select class="form-control" id="status" required>
                <option value="operational">Operational</option>
                <option value="maintenance">Under Maintenance</option>
                <option value="out-of-service">Out of Service</option>
              </select>
            </div>
          </form>
        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" id="saveEquipmentBtn">Save</button>
        </div>
      </div>
    </div>
  </div>

  <!-- View Equipment Modal -->
  <div class="modal fade" id="modal-view-equipment">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Equipment Details</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <table class="table table-bordered">
            <tr>
              <th style="width: 150px">Equipment ID</th>
              <td>EQ001</td>
            </tr>
            <tr>
              <th>Name</th>
              <td>Automated Hematology Analyzer</td>
            </tr>
            <tr>
              <th>Type</th>
              <td>Hematology</td>
            </tr>
            <tr>
              <th>Model</th>
              <td>XS-1000i</td>
            </tr>
            <tr>
              <th>Serial Number</th>
              <td>SN12345678</td>
            </tr>
            <tr>
              <th>Purchase Date</th>
              <td>2023-01-15</td>
            </tr>
            <tr>
              <th>Warranty End</th>
              <td>2025-01-15</td>
            </tr>
            <tr>
              <th>Last Maintenance</th>
              <td>2025-01-20</td>
            </tr>
            <tr>
              <th>Next Maintenance</th>
              <td>2025-07-20</td>
            </tr>
            <tr>
              <th>Status</th>
              <td><span class="badge badge-success">Operational</span></td>
            </tr>
            <tr>
              <th>Notes</th>
              <td>Regular calibration required every 6 months.</td>
            </tr>
          </table>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary">Edit</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Maintenance Modal -->
  <div class="modal fade" id="modal-maintenance">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Schedule Maintenance</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="maintenanceForm">
            <div class="form-group">
              <label for="equipmentId">Equipment</label>
              <select class="form-control" id="equipmentId" disabled>
                <option value="EQ001">EQ001 - Automated Hematology Analyzer</option>
              </select>
            </div>
            <div class="form-group">
              <label for="maintenanceType">Maintenance Type</label>
              <select class="form-control" id="maintenanceType" required>
                <option value="routine">Routine</option>
                <option value="calibration">Calibration</option>
                <option value="repair">Repair</option>
                <option value="emergency">Emergency</option>
              </select>
            </div>
            <div class="form-group">
              <label for="maintenanceDate">Scheduled Date</label>
              <input type="date" class="form-control" id="maintenanceDate" required>
            </div>
            <div class="form-group">
              <label for="technician">Technician</label>
              <input type="text" class="form-control" id="technician" placeholder="Technician name">
            </div>
            <div class="form-group">
              <label for="maintenanceNotes">Notes</label>
              <textarea class="form-control" id="maintenanceNotes" rows="3" placeholder="Enter maintenance notes"></textarea>
            </div>
          </form>
        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" id="saveMaintenanceBtn">Schedule</button>
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
  $('#equipmentTable').DataTable({
    "paging": true,
    "lengthChange": true,
    "searching": true,
    "ordering": true,
    "info": true,
    "autoWidth": false,
    "responsive": true
  });
  
  // Save Equipment button click handler
  $('#saveEquipmentBtn').on('click', function() {
    const form = document.getElementById('addEquipmentForm');
    
    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }
    
    // Get form data (in a real app, this would be sent to the server)
    const equipmentData = {
      name: $('#equipmentName').val(),
      type: $('#equipmentType').val(),
      model: $('#model').val(),
      serialNumber: $('#serialNumber').val(),
      purchaseDate: $('#purchaseDate').val(),
      warrantyEnd: $('#warrantyEnd').val(),
      status: $('#status').val()
    };
    
    console.log('Equipment data to save:', equipmentData);
    
    // Reset form and close modal
    form.reset();
    $('#modal-add-equipment').modal('hide');
    
    // Show success message
    alert('Equipment added successfully!');
  });
  
  // Schedule Maintenance button click handler
  $('#saveMaintenanceBtn').on('click', function() {
    const form = document.getElementById('maintenanceForm');
    
    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }
    
    // Get form data (in a real app, this would be sent to the server)
    const maintenanceData = {
      equipmentId: $('#equipmentId').val(),
      maintenanceType: $('#maintenanceType').val(),
      maintenanceDate: $('#maintenanceDate').val(),
      technician: $('#technician').val(),
      notes: $('#maintenanceNotes').val()
    };
    
    console.log('Maintenance data to save:', maintenanceData);
    
    // Reset form and close modal
    form.reset();
    $('#modal-maintenance').modal('hide');
    
    // Show success message
    alert('Maintenance scheduled successfully!');
  });
});
</script>
</body>
</html>
