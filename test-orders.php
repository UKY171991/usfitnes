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

// Get test orders (sample data for now)
$test_orders = [
    ['id' => 1, 'order_id' => 'ORD001', 'patient_name' => 'John Doe', 'test_name' => 'Complete Blood Count', 'priority' => 'Normal', 'status' => 'Pending', 'order_date' => '2025-06-18'],
    ['id' => 2, 'order_id' => 'ORD002', 'patient_name' => 'Jane Smith', 'test_name' => 'Liver Function Test', 'priority' => 'Urgent', 'status' => 'Sample_Collected', 'order_date' => '2025-06-18'],
    ['id' => 3, 'order_id' => 'ORD003', 'patient_name' => 'Mike Johnson', 'test_name' => 'Blood Glucose', 'priority' => 'STAT', 'status' => 'In_Progress', 'order_date' => '2025-06-17'],
    ['id' => 4, 'order_id' => 'ORD004', 'patient_name' => 'Sarah Wilson', 'test_name' => 'Urine Analysis', 'priority' => 'Normal', 'status' => 'Completed', 'order_date' => '2025-06-17'],
];
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
        <!-- Alert Messages -->
        <div id="alertContainer"></div>
        
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Laboratory Test Orders</h3>
                <div class="card-tools">
                  <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addOrderModal">
                    <i class="fas fa-plus"></i> New Test Order
                  </button>
                </div>
              </div>
              <div class="card-body">
                <div class="row mb-3">
                  <div class="col-md-4">
                    <div class="input-group">
                      <input type="text" class="form-control" id="searchInput" placeholder="Search orders...">
                      <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                          <i class="fas fa-search"></i>
                        </button>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <select class="form-control" id="statusFilter">
                      <option value="">All Status</option>
                      <option value="pending">Pending</option>
                      <option value="in_progress">In Progress</option>
                      <option value="completed">Completed</option>
                      <option value="cancelled">Cancelled</option>
                    </select>
                  </div>
                  <div class="col-md-3">
                    <button class="btn btn-info" id="refreshBtn">
                      <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                  </div>
                </div>
                
                <div id="loadingIndicator" class="text-center" style="display: none;">
                  <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                  </div>
                  <p>Loading test orders...</p>
                </div>
                
                <table id="ordersTable" class="table table-bordered table-striped" style="display: none;">
                  <thead>
                  <tr>
                    <th>Order ID</th>
                    <th>Patient Name</th>
                    <th>Test Name</th>
                    <th>Test Type</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Order Date</th>
                    <th>Actions</th>
                  </tr>
                  </thead>
                  <tbody id="ordersTableBody">
                  </tbody>
                </table>
                
                <!-- Pagination -->
                <nav aria-label="Order pagination" id="paginationContainer" style="display: none;">
                  <ul class="pagination justify-content-center" id="pagination">
                  </ul>
                </nav>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <!-- Add Test Order Modal -->
  <div class="modal fade" id="addOrderModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">New Test Order</h4>
          <button type="button" class="close" data-dismiss="modal">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="addOrderForm">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="patientSelect">Patient <span class="text-danger">*</span></label>
                  <select class="form-control" id="patientSelect" name="patient_id" required>
                    <option value="">Select Patient</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="doctorSelect">Referring Doctor</label>
                  <select class="form-control" id="doctorSelect" name="doctor_id">
                    <option value="">Select Doctor</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="testName">Test Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="testName" name="test_name" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="testType">Test Type <span class="text-danger">*</span></label>
                  <select class="form-control" id="testType" name="test_type" required>
                    <option value="">Select Type</option>
                    <option value="Blood Test">Blood Test</option>
                    <option value="Urine Test">Urine Test</option>
                    <option value="Imaging">Imaging</option>
                    <option value="Biopsy">Biopsy</option>
                    <option value="Microbiology">Microbiology</option>
                    <option value="Chemistry">Chemistry</option>
                    <option value="Immunology">Immunology</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label for="priority">Priority</label>
                  <select class="form-control" id="priority" name="priority">
                    <option value="normal">Normal</option>
                    <option value="urgent">Urgent</option>
                    <option value="stat">STAT</option>
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="testCost">Test Cost</label>
                  <input type="number" class="form-control" id="testCost" name="test_cost" step="0.01" min="0">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="totalAmount">Total Amount</label>
                  <input type="number" class="form-control" id="totalAmount" name="total_amount" step="0.01" min="0">
                </div>
              </div>
            </div>
            <div class="form-group">
              <label for="notes">Notes</label>
              <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" id="saveOrderBtn">
            <i class="fas fa-save"></i> Create Order
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Test Order Modal -->
  <div class="modal fade" id="editOrderModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Edit Test Order</h4>
          <button type="button" class="close" data-dismiss="modal">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="editOrderForm">
            <input type="hidden" id="editOrderId" name="order_id">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="editPatientSelect">Patient <span class="text-danger">*</span></label>
                  <select class="form-control" id="editPatientSelect" name="patient_id" required>
                    <option value="">Select Patient</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="editDoctorSelect">Referring Doctor</label>
                  <select class="form-control" id="editDoctorSelect" name="doctor_id">
                    <option value="">Select Doctor</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="editTestName">Test Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="editTestName" name="test_name" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="editTestType">Test Type <span class="text-danger">*</span></label>
                  <select class="form-control" id="editTestType" name="test_type" required>
                    <option value="">Select Type</option>
                    <option value="Blood Test">Blood Test</option>
                    <option value="Urine Test">Urine Test</option>
                    <option value="Imaging">Imaging</option>
                    <option value="Biopsy">Biopsy</option>
                    <option value="Microbiology">Microbiology</option>
                    <option value="Chemistry">Chemistry</option>
                    <option value="Immunology">Immunology</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label for="editPriority">Priority</label>
                  <select class="form-control" id="editPriority" name="priority">
                    <option value="normal">Normal</option>
                    <option value="urgent">Urgent</option>
                    <option value="stat">STAT</option>
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="editStatus">Status</label>
                  <select class="form-control" id="editStatus" name="status">
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                  </select>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="editTestCost">Test Cost</label>
                  <input type="number" class="form-control" id="editTestCost" name="test_cost" step="0.01" min="0">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="editTotalAmount">Total Amount</label>
                  <input type="number" class="form-control" id="editTotalAmount" name="total_amount" step="0.01" min="0">
                </div>
              </div>
            </div>
            <div class="form-group">
              <label for="editNotes">Notes</label>
              <textarea class="form-control" id="editNotes" name="notes" rows="3"></textarea>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" id="updateOrderBtn">
            <i class="fas fa-save"></i> Update Order
          </button>
        </div>
      </div>
    </div>
  </div>
                    <th>Actions</th>
                  </tr>
                  </thead>
                  <tbody>
                  <?php foreach($test_orders as $order): ?>
                  <tr>
                    <td><span class="badge badge-info"><?php echo $order['order_id']; ?></span></td>
                    <td><?php echo htmlspecialchars($order['patient_name']); ?></td>
                    <td><?php echo htmlspecialchars($order['test_name']); ?></td>
                    <td>
                      <span class="badge badge-<?php echo $order['priority'] == 'STAT' ? 'danger' : ($order['priority'] == 'Urgent' ? 'warning' : 'success'); ?>">
                        <?php echo $order['priority']; ?>
                      </span>
                    </td>
                    <td>
                      <span class="badge badge-<?php 
                        echo $order['status'] == 'Completed' ? 'success' : 
                             ($order['status'] == 'In_Progress' ? 'warning' : 
                             ($order['status'] == 'Sample_Collected' ? 'info' : 'secondary')); ?>">
                        <?php echo str_replace('_', ' ', $order['status']); ?>
                      </span>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                    <td>
                      <button class="btn btn-info btn-sm" title="View Details">
                        <i class="fas fa-eye"></i>
                      </button>
                      <button class="btn btn-primary btn-sm" title="Process">
                        <i class="fas fa-play"></i>
                      </button>
                      <button class="btn btn-success btn-sm" title="Complete">
                        <i class="fas fa-check"></i>
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
// Global variables
let currentPage = 1;
let currentSearch = '';
let currentStatus = '';
const recordsPerPage = 10;

$(document).ready(function() {
    // Initialize page
    loadTestOrders();
    loadPatients();
    loadDoctors();
    
    // Event listeners
    $('#saveOrderBtn').click(saveTestOrder);
    $('#updateOrderBtn').click(updateTestOrder);
    $('#searchBtn').click(searchOrders);
    $('#refreshBtn').click(function() {
        currentSearch = '';
        currentStatus = '';
        $('#searchInput').val('');
        $('#statusFilter').val('');
        loadTestOrders();
    });
    
    // Status filter change
    $('#statusFilter').change(function() {
        currentStatus = $(this).val();
        loadTestOrders(1, currentSearch, currentStatus);
    });
    
    // Search on Enter key
    $('#searchInput').keypress(function(e) {
        if (e.which == 13) {
            searchOrders();
        }
    });
    
    // Auto-calculate total amount when test cost changes
    $('#testCost').on('input', function() {
        $('#totalAmount').val($(this).val());
    });
    
    $('#editTestCost').on('input', function() {
        $('#editTotalAmount').val($(this).val());
    });
    
    // Clear forms when modals close
    $('#addOrderModal').on('hidden.bs.modal', function() {
        $('#addOrderForm')[0].reset();
    });
    
    $('#editOrderModal').on('hidden.bs.modal', function() {
        $('#editOrderForm')[0].reset();
    });
});

// Load test orders with AJAX
function loadTestOrders(page = 1, search = '', status = '') {
    currentPage = page;
    currentSearch = search;
    currentStatus = status;
    
    showLoading();
    
    $.ajax({
        url: 'api/test_orders_api.php',
        method: 'GET',
        data: {
            page: page,
            limit: recordsPerPage,
            search: search,
            status: status
        },
        dataType: 'json',
        success: function(response) {
            hideLoading();
            if (response.success) {
                displayOrders(response.data);
                displayPagination(response.pagination);
            } else {
                showAlert('error', 'Error loading test orders: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            hideLoading();
            console.error('AJAX Error:', error);
            showAlert('error', 'Failed to load test orders. Please try again.');
        }
    });
}

// Display test orders in table
function displayOrders(orders) {
    const tbody = $('#ordersTableBody');
    tbody.empty();
    
    if (orders.length === 0) {
        tbody.append('<tr><td colspan="8" class="text-center">No test orders found</td></tr>');
        $('#ordersTable').show();
        return;
    }
    
    orders.forEach(function(order) {
        const statusClass = getStatusClass(order.status);
        const priorityClass = getPriorityClass(order.priority);
        
        const row = `
            <tr>
                <td><span class="badge badge-secondary">${escapeHtml(order.order_id || 'N/A')}</span></td>
                <td>${escapeHtml(order.patient_name || 'Unknown')}</td>
                <td>${escapeHtml(order.test_name || '')}</td>
                <td>${escapeHtml(order.test_type || '')}</td>
                <td><span class="badge badge-${priorityClass}">${escapeHtml(order.priority || 'normal')}</span></td>
                <td><span class="badge badge-${statusClass}">${escapeHtml(order.status || 'pending')}</span></td>
                <td>${formatDateTime(order.order_date)}</td>
                <td>
                    <button class="btn btn-info btn-sm" onclick="viewOrder(${order.order_id})" title="View Details">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-primary btn-sm" onclick="editOrder(${order.order_id})" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-success btn-sm" onclick="addResult(${order.order_id})" title="Add Result">
                        <i class="fas fa-flask"></i>
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="deleteOrder(${order.order_id})" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
    
    $('#ordersTable').show();
}

// Load patients for dropdowns
function loadPatients() {
    $.ajax({
        url: 'api/patients_api.php',
        method: 'GET',
        data: { limit: 100 }, // Get more patients for dropdown
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const patients = response.data;
                const patientOptions = patients.map(patient => 
                    `<option value="${patient.patient_id}">${escapeHtml(patient.first_name + ' ' + patient.last_name)}</option>`
                ).join('');
                
                $('#patientSelect, #editPatientSelect').html('<option value="">Select Patient</option>' + patientOptions);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading patients:', error);
        }
    });
}

// Load doctors for dropdowns
function loadDoctors() {
    $.ajax({
        url: 'api/doctors_api.php',
        method: 'GET',
        data: { limit: 100 }, // Get more doctors for dropdown
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const doctors = response.data;
                const doctorOptions = doctors.map(doctor => 
                    `<option value="${doctor.doctor_id}">${escapeHtml(doctor.first_name + ' ' + doctor.last_name)} - ${escapeHtml(doctor.specialization)}</option>`
                ).join('');
                
                $('#doctorSelect, #editDoctorSelect').html('<option value="">Select Doctor</option>' + doctorOptions);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading doctors:', error);
        }
    });
}

// Search orders
function searchOrders() {
    const search = $('#searchInput').val().trim();
    const status = $('#statusFilter').val();
    loadTestOrders(1, search, status);
}

// Save new test order
function saveTestOrder() {
    const form = $('#addOrderForm')[0];
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const formData = new FormData(form);
    const orderData = {};
    formData.forEach((value, key) => {
        orderData[key] = value;
    });
    
    $('#saveOrderBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creating...');
    
    $.ajax({
        url: 'api/test_orders_api.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(orderData),
        dataType: 'json',
        success: function(response) {
            $('#saveOrderBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Create Order');
            
            if (response.success) {
                showAlert('success', response.message);
                $('#addOrderModal').modal('hide');
                loadTestOrders(currentPage, currentSearch, currentStatus);
            } else {
                showAlert('error', response.message);
            }
        },
        error: function(xhr, status, error) {
            $('#saveOrderBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Create Order');
            console.error('AJAX Error:', error);
            
            let message = 'Failed to create test order. Please try again.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            showAlert('error', message);
        }
    });
}

// Edit test order
function editOrder(orderId) {
    // Get order data
    $.ajax({
        url: 'api/test_orders_api.php',
        method: 'GET',
        data: { id: orderId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const order = response.data;
                
                // Populate edit form
                $('#editOrderId').val(order.order_id);
                $('#editPatientSelect').val(order.patient_id);
                $('#editDoctorSelect').val(order.doctor_id || '');
                $('#editTestName').val(order.test_name);
                $('#editTestType').val(order.test_type);
                $('#editPriority').val(order.priority);
                $('#editStatus').val(order.status);
                $('#editTestCost').val(order.test_cost || '');
                $('#editTotalAmount').val(order.total_amount || '');
                $('#editNotes').val(order.notes || '');
                
                $('#editOrderModal').modal('show');
            } else {
                showAlert('error', 'Failed to load test order data: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            showAlert('error', 'Failed to load test order data. Please try again.');
        }
    });
}

// Update test order
function updateTestOrder() {
    const form = $('#editOrderForm')[0];
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const formData = new FormData(form);
    const orderData = {};
    formData.forEach((value, key) => {
        orderData[key] = value;
    });
    
    $('#updateOrderBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');
    
    $.ajax({
        url: 'api/test_orders_api.php',
        method: 'PUT',
        contentType: 'application/json',
        data: JSON.stringify(orderData),
        dataType: 'json',
        success: function(response) {
            $('#updateOrderBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Update Order');
            
            if (response.success) {
                showAlert('success', response.message);
                $('#editOrderModal').modal('hide');
                loadTestOrders(currentPage, currentSearch, currentStatus);
            } else {
                showAlert('error', response.message);
            }
        },
        error: function(xhr, status, error) {
            $('#updateOrderBtn').prop('disabled', false).html('<i class="fas fa-save"></i> Update Order');
            console.error('AJAX Error:', error);
            
            let message = 'Failed to update test order. Please try again.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            showAlert('error', message);
        }
    });
}

// Delete test order
function deleteOrder(orderId) {
    if (!confirm('Are you sure you want to delete this test order? This action cannot be undone.')) {
        return;
    }
    
    $.ajax({
        url: 'api/test_orders_api.php',
        method: 'DELETE',
        contentType: 'application/json',
        data: JSON.stringify({ order_id: orderId }),
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showAlert('success', response.message);
                loadTestOrders(currentPage, currentSearch, currentStatus);
            } else {
                showAlert('error', response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            
            let message = 'Failed to delete test order. Please try again.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            showAlert('error', message);
        }
    });
}

// View order details
function viewOrder(orderId) {
    // TODO: Implement order detail view
    showAlert('info', 'Order detail view will be implemented in the next phase.');
}

// Add result for order
function addResult(orderId) {
    // Redirect to results page with order ID
    window.location.href = `results.php?order_id=${orderId}`;
}

// Display pagination (reuse from patients page)
function displayPagination(pagination) {
    const container = $('#pagination');
    container.empty();
    
    if (pagination.pages <= 1) {
        $('#paginationContainer').hide();
        return;
    }
    
    // Previous button
    const prevDisabled = pagination.page <= 1 ? 'disabled' : '';
    container.append(`
        <li class="page-item ${prevDisabled}">
            <a class="page-link" href="#" onclick="loadTestOrders(${pagination.page - 1}, '${currentSearch}', '${currentStatus}')">Previous</a>
        </li>
    `);
    
    // Page numbers
    const startPage = Math.max(1, pagination.page - 2);
    const endPage = Math.min(pagination.pages, pagination.page + 2);
    
    if (startPage > 1) {
        container.append('<li class="page-item"><a class="page-link" href="#" onclick="loadTestOrders(1, \'' + currentSearch + '\', \'' + currentStatus + '\')">1</a></li>');
        if (startPage > 2) {
            container.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
        }
    }
    
    for (let i = startPage; i <= endPage; i++) {
        const active = i === pagination.page ? 'active' : '';
        container.append(`
            <li class="page-item ${active}">
                <a class="page-link" href="#" onclick="loadTestOrders(${i}, '${currentSearch}', '${currentStatus}')">${i}</a>
            </li>
        `);
    }
    
    if (endPage < pagination.pages) {
        if (endPage < pagination.pages - 1) {
            container.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
        }
        container.append(`<li class="page-item"><a class="page-link" href="#" onclick="loadTestOrders(${pagination.pages}, '${currentSearch}', '${currentStatus}')">${pagination.pages}</a></li>`);
    }
    
    // Next button
    const nextDisabled = pagination.page >= pagination.pages ? 'disabled' : '';
    container.append(`
        <li class="page-item ${nextDisabled}">
            <a class="page-link" href="#" onclick="loadTestOrders(${pagination.page + 1}, '${currentSearch}', '${currentStatus}')">Next</a>
        </li>
    `);
    
    $('#paginationContainer').show();
}

// Utility functions
function showLoading() {
    $('#loadingIndicator').show();
    $('#ordersTable').hide();
    $('#paginationContainer').hide();
}

function hideLoading() {
    $('#loadingIndicator').hide();
}

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 
                     type === 'error' ? 'alert-danger' : 
                     type === 'warning' ? 'alert-warning' : 'alert-info';
    
    const icon = type === 'success' ? 'fas fa-check' : 
                type === 'error' ? 'fas fa-ban' : 
                type === 'warning' ? 'fas fa-exclamation-triangle' : 'fas fa-info-circle';
    
    const alert = `
        <div class="alert ${alertClass} alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <i class="icon ${icon}"></i> ${message}
        </div>
    `;
    
    $('#alertContainer').html(alert);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        $('#alertContainer .alert').fadeOut();
    }, 5000);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDateTime(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function getStatusClass(status) {
    switch(status?.toLowerCase()) {
        case 'pending': return 'warning';
        case 'in_progress': return 'info';
        case 'completed': return 'success';
        case 'cancelled': return 'danger';
        default: return 'secondary';
    }
}

function getPriorityClass(priority) {
    switch(priority?.toLowerCase()) {
        case 'stat': return 'danger';
        case 'urgent': return 'warning';
        case 'normal': return 'primary';
        default: return 'secondary';
    }
}
</script>
</body>
</html>
