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
          <div class="col-12">            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Laboratory Test Results</h3>
                <div class="card-tools">
                  <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addResultModal">
                    <i class="fas fa-plus"></i> Add Result
                  </button>
                  <button type="button" class="btn btn-warning" id="criticalFilter">
                    <i class="fas fa-exclamation-triangle"></i> Critical Results
                  </button>
                </div>
              </div>
              <div class="card-body">
                <!-- Search and Filter Controls -->
                <div class="row mb-3">
                  <div class="col-md-4">
                    <div class="input-group">
                      <input type="text" class="form-control" id="searchInput" placeholder="Search results...">
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
                      <option value="completed">Completed</option>
                      <option value="verified">Verified</option>
                    </select>
                  </div>
                  <div class="col-md-3">
                    <select class="form-control" id="criticalSelect">
                      <option value="">All Results</option>
                      <option value="1">Critical Only</option>
                      <option value="0">Normal Only</option>
                    </select>
                  </div>
                  <div class="col-md-2">
                    <button class="btn btn-secondary btn-block" id="clearFilters">
                      <i class="fas fa-times"></i> Clear
                    </button>
                  </div>
                </div>

                <!-- Results Table -->
                <div class="table-responsive">
                  <table class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th>Test Order</th>
                        <th>Patient</th>
                        <th>Test Name</th>
                        <th>Result Date</th>
                        <th>Status</th>
                        <th>Critical</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody id="resultsTableBody">
                      <!-- Dynamic content loaded via AJAX -->
                    </tbody>
                  </table>
                </div>

                <!-- Pagination -->
                <div class="row mt-3">
                  <div class="col-sm-12 col-md-5">
                    <div class="dataTables_info" id="tableInfo"></div>
                  </div>
                  <div class="col-sm-12 col-md-7">
                    <div class="dataTables_paginate paging_simple_numbers">
                      <ul class="pagination" id="pagination"></ul>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>    </section>
  </div>

  <!-- Add Result Modal -->
  <div class="modal fade" id="addResultModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Add Test Result</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <form id="addResultForm">
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Test Order <span class="text-danger">*</span></label>
                  <select class="form-control" id="add_test_order_id" name="test_order_id" required>
                    <option value="">Select Test Order</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Result Date <span class="text-danger">*</span></label>
                  <input type="date" class="form-control" id="add_result_date" name="result_date" required>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Status <span class="text-danger">*</span></label>
                  <select class="form-control" id="add_status" name="status" required>
                    <option value="pending">Pending</option>
                    <option value="completed">Completed</option>
                    <option value="verified">Verified</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Critical Result</label>
                  <select class="form-control" id="add_is_critical" name="is_critical">
                    <option value="0">Normal</option>
                    <option value="1">Critical</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label>Result Values</label>
              <textarea class="form-control" id="add_result_values" name="result_values" rows="4" placeholder="Enter test result values..."></textarea>
            </div>
            <div class="form-group">
              <label>Notes</label>
              <textarea class="form-control" id="add_notes" name="notes" rows="3" placeholder="Additional notes..."></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save"></i> Save Result
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Edit Result Modal -->
  <div class="modal fade" id="editResultModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Edit Test Result</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <form id="editResultForm">
          <input type="hidden" id="edit_result_id" name="id">
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Test Order <span class="text-danger">*</span></label>
                  <select class="form-control" id="edit_test_order_id" name="test_order_id" required>
                    <option value="">Select Test Order</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Result Date <span class="text-danger">*</span></label>
                  <input type="date" class="form-control" id="edit_result_date" name="result_date" required>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label>Status <span class="text-danger">*</span></label>
                  <select class="form-control" id="edit_status" name="status" required>
                    <option value="pending">Pending</option>
                    <option value="completed">Completed</option>
                    <option value="verified">Verified</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label>Critical Result</label>
                  <select class="form-control" id="edit_is_critical" name="is_critical">
                    <option value="0">Normal</option>
                    <option value="1">Critical</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label>Result Values</label>
              <textarea class="form-control" id="edit_result_values" name="result_values" rows="4" placeholder="Enter test result values..."></textarea>
            </div>
            <div class="form-group">
              <label>Notes</label>
              <textarea class="form-control" id="edit_notes" name="notes" rows="3" placeholder="Additional notes..."></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <i class="fas fa-save"></i> Update Result
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- View Result Modal -->
  <div class="modal fade" id="viewResultModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Test Result Details</h4>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body" id="viewResultContent">
          <!-- Dynamic content loaded via AJAX -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" onclick="downloadResultPDF()">
            <i class="fas fa-download"></i> Download PDF
          </button>
        </div>
      </div>
    </div>
  </div>

  <footer class="main-footer">
    <strong>Copyright &copy; 2024 <a href="#">PathLab Pro</a>.</strong> All rights reserved.
  </footer>
</div>

<!-- jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>

<script>
let currentPage = 1;
let currentSearch = '';
let currentStatus = '';
let currentCritical = '';

$(document).ready(function() {
    loadResults();
    loadTestOrders();
    
    // Set today's date as default
    $('#add_result_date, #edit_result_date').val(new Date().toISOString().split('T')[0]);
});

// Search functionality
$('#searchBtn').click(function() {
    currentSearch = $('#searchInput').val();
    loadResults(1, currentSearch, currentStatus, currentCritical);
});

$('#searchInput').keypress(function(e) {
    if(e.which == 13) {
        $('#searchBtn').click();
    }
});

// Filter functionality
$('#statusFilter').change(function() {
    currentStatus = $(this).val();
    loadResults(1, currentSearch, currentStatus, currentCritical);
});

$('#criticalSelect').change(function() {
    currentCritical = $(this).val();
    loadResults(1, currentSearch, currentStatus, currentCritical);
});

$('#criticalFilter').click(function() {
    $('#criticalSelect').val('1').trigger('change');
});

$('#clearFilters').click(function() {
    $('#searchInput').val('');
    $('#statusFilter').val('');
    $('#criticalSelect').val('');
    currentSearch = '';
    currentStatus = '';
    currentCritical = '';
    loadResults();
});

// Load results function
function loadResults(page = 1, search = '', status = '', critical = '') {
    currentPage = page;
    currentSearch = search;
    currentStatus = status;
    currentCritical = critical;
    
    $.ajax({
        url: 'api/results_api.php',
        method: 'GET',
        data: {
            action: 'read',
            page: page,
            search: search,
            status: status,
            is_critical: critical
        },
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                displayResults(response.data);
                updatePagination(response.pagination);
                updateTableInfo(response.pagination);
            } else {
                showAlert('Error loading results: ' + response.message, 'danger');
            }
        },
        error: function() {
            showAlert('Error loading results. Please try again.', 'danger');
        }
    });
}

// Display results in table
function displayResults(results) {
    let html = '';
    
    if(results.length === 0) {
        html = '<tr><td colspan="7" class="text-center">No results found</td></tr>';
    } else {
        results.forEach(function(result) {
            const criticalClass = result.is_critical == '1' ? 'table-warning' : '';
            const statusBadge = getStatusBadge(result.status);
            const criticalBadge = result.is_critical == '1' ? 
                '<span class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i> Critical</span>' :
                '<span class="badge badge-success">Normal</span>';
            
            html += `
                <tr class="${criticalClass}">
                    <td><span class="badge badge-primary">${result.order_id || 'N/A'}</span></td>
                    <td>${result.patient_name || 'Unknown'}</td>
                    <td>${result.test_name || 'Unknown'}</td>
                    <td>${formatDate(result.result_date)}</td>
                    <td>${statusBadge}</td>
                    <td>${criticalBadge}</td>
                    <td>
                        <button class="btn btn-info btn-sm" onclick="viewResult(${result.id})" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-warning btn-sm" onclick="editResult(${result.id})" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-primary btn-sm" onclick="downloadResultPDF(${result.id})" title="Download PDF">
                            <i class="fas fa-download"></i>
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="deleteResult(${result.id})" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
    }
    
    $('#resultsTableBody').html(html);
}

// Load test orders for dropdowns
function loadTestOrders() {
    $.ajax({
        url: 'api/test_orders_api.php',
        method: 'GET',
        data: { action: 'read', page: 1, limit: 1000 },
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                let options = '<option value="">Select Test Order</option>';
                response.data.forEach(function(order) {
                    options += `<option value="${order.id}">${order.order_id} - ${order.patient_name} (${order.test_name})</option>`;
                });
                $('#add_test_order_id, #edit_test_order_id').html(options);
            }
        }
    });
}

// Add result form submission
$('#addResultForm').submit(function(e) {
    e.preventDefault();
    
    $.ajax({
        url: 'api/results_api.php',
        method: 'POST',
        data: $(this).serialize() + '&action=create',
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                $('#addResultModal').modal('hide');
                $('#addResultForm')[0].reset();
                $('#add_result_date').val(new Date().toISOString().split('T')[0]);
                loadResults(currentPage, currentSearch, currentStatus, currentCritical);
                showAlert('Result added successfully!', 'success');
            } else {
                showAlert('Error adding result: ' + response.message, 'danger');
            }
        },
        error: function() {
            showAlert('Error adding result. Please try again.', 'danger');
        }
    });
});

// Edit result
function editResult(id) {
    $.ajax({
        url: 'api/results_api.php',
        method: 'GET',
        data: { action: 'read', id: id },
        dataType: 'json',
        success: function(response) {
            if(response.success && response.data.length > 0) {
                const result = response.data[0];
                $('#edit_result_id').val(result.id);
                $('#edit_test_order_id').val(result.test_order_id);
                $('#edit_result_date').val(result.result_date);
                $('#edit_status').val(result.status);
                $('#edit_is_critical').val(result.is_critical);
                $('#edit_result_values').val(result.result_values);
                $('#edit_notes').val(result.notes);
                $('#editResultModal').modal('show');
            }
        }
    });
}

// Edit result form submission
$('#editResultForm').submit(function(e) {
    e.preventDefault();
    
    $.ajax({
        url: 'api/results_api.php',
        method: 'POST',
        data: $(this).serialize() + '&action=update',
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                $('#editResultModal').modal('hide');
                loadResults(currentPage, currentSearch, currentStatus, currentCritical);
                showAlert('Result updated successfully!', 'success');
            } else {
                showAlert('Error updating result: ' + response.message, 'danger');
            }
        },
        error: function() {
            showAlert('Error updating result. Please try again.', 'danger');
        }
    });
});

// View result details
function viewResult(id) {
    $.ajax({
        url: 'api/results_api.php',
        method: 'GET',
        data: { action: 'read', id: id },
        dataType: 'json',
        success: function(response) {
            if(response.success && response.data.length > 0) {
                const result = response.data[0];
                const criticalBadge = result.is_critical == '1' ? 
                    '<span class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i> Critical</span>' :
                    '<span class="badge badge-success">Normal</span>';
                
                let html = `
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Test Information</h5>
                            <table class="table table-sm">
                                <tr><td><strong>Order ID:</strong></td><td>${result.order_id || 'N/A'}</td></tr>
                                <tr><td><strong>Patient:</strong></td><td>${result.patient_name || 'Unknown'}</td></tr>
                                <tr><td><strong>Test Name:</strong></td><td>${result.test_name || 'Unknown'}</td></tr>
                                <tr><td><strong>Result Date:</strong></td><td>${formatDate(result.result_date)}</td></tr>
                                <tr><td><strong>Status:</strong></td><td>${getStatusBadge(result.status)}</td></tr>
                                <tr><td><strong>Critical:</strong></td><td>${criticalBadge}</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Result Values</h5>
                            <div class="form-group">
                                <textarea class="form-control" rows="6" readonly>${result.result_values || 'No values recorded'}</textarea>
                            </div>
                        </div>
                    </div>
                `;
                
                if(result.notes) {
                    html += `
                        <div class="row">
                            <div class="col-12">
                                <h5>Notes</h5>
                                <div class="alert alert-info">${result.notes}</div>
                            </div>
                        </div>
                    `;
                }
                
                $('#viewResultContent').html(html);
                $('#viewResultModal').modal('show');
            }
        }
    });
}

// Delete result
function deleteResult(id) {
    if(confirm('Are you sure you want to delete this result? This action cannot be undone.')) {
        $.ajax({
            url: 'api/results_api.php',
            method: 'POST',
            data: { action: 'delete', id: id },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    loadResults(currentPage, currentSearch, currentStatus, currentCritical);
                    showAlert('Result deleted successfully!', 'success');
                } else {
                    showAlert('Error deleting result: ' + response.message, 'danger');
                }
            },
            error: function() {
                showAlert('Error deleting result. Please try again.', 'danger');
            }
        });
    }
}

// Download PDF function
function downloadResultPDF(id) {
    if(id) {
        window.open(`api/results_api.php?action=download_pdf&id=${id}`, '_blank');
    } else {
        showAlert('Please select a result to download.', 'warning');
    }
}

// Utility functions
function getStatusBadge(status) {
    const badges = {
        'pending': 'badge-warning',
        'completed': 'badge-info', 
        'verified': 'badge-success'
    };
    const badgeClass = badges[status] || 'badge-secondary';
    return `<span class="badge ${badgeClass}">${status.charAt(0).toUpperCase() + status.slice(1)}</span>`;
}

function formatDate(dateStr) {
    if(!dateStr) return 'N/A';
    const date = new Date(dateStr);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
}

function updatePagination(pagination) {
    let container = $('#pagination');
    container.empty();
    
    if(pagination.pages <= 1) return;
    
    // Previous button
    if(pagination.page > 1) {
        container.append(`
            <li class="page-item">
                <a class="page-link" href="#" onclick="loadResults(${pagination.page - 1}, '${currentSearch}', '${currentStatus}', '${currentCritical}')">Previous</a>
            </li>
        `);
    }
    
    // Page numbers
    let startPage = Math.max(1, pagination.page - 2);
    let endPage = Math.min(pagination.pages, pagination.page + 2);
    
    if(startPage > 1) {
        container.append('<li class="page-item"><a class="page-link" href="#" onclick="loadResults(1, \'' + currentSearch + '\', \'' + currentStatus + '\', \'' + currentCritical + '\')">1</a></li>');
        if(startPage > 2) {
            container.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
        }
    }
    
    for(let i = startPage; i <= endPage; i++) {
        const activeClass = i === pagination.page ? 'active' : '';
        container.append(`
            <li class="page-item ${activeClass}">
                <a class="page-link" href="#" onclick="loadResults(${i}, '${currentSearch}', '${currentStatus}', '${currentCritical}')">${i}</a>
            </li>
        `);
    }
    
    if(endPage < pagination.pages) {
        if(endPage < pagination.pages - 1) {
            container.append('<li class="page-item disabled"><span class="page-link">...</span></li>');
        }
        container.append(`<li class="page-item"><a class="page-link" href="#" onclick="loadResults(${pagination.pages}, '${currentSearch}', '${currentStatus}', '${currentCritical}')">${pagination.pages}</a></li>`);
    }
    
    // Next button
    if(pagination.page < pagination.pages) {
        container.append(`
            <li class="page-item">
                <a class="page-link" href="#" onclick="loadResults(${pagination.page + 1}, '${currentSearch}', '${currentStatus}', '${currentCritical}')">Next</a>
            </li>
        `);
    }
}

function updateTableInfo(pagination) {
    const start = (pagination.page - 1) * pagination.limit + 1;
    const end = Math.min(pagination.page * pagination.limit, pagination.total);
    $('#tableInfo').text(`Showing ${start} to ${end} of ${pagination.total} entries`);
}

function showAlert(message, type) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    `;
    
    // Remove existing alerts
    $('.alert').remove();
    
    // Add new alert at the top of content
    $('.content-wrapper .content').prepend(alertHtml);
      // Auto dismiss after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
}

// Load test orders for dropdown
function loadTestOrders() {
    $.ajax({
        url: 'api/test_orders_api.php',
        method: 'GET',
        data: { action: 'read' },
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                const addSelect = $('#add_test_order_id');
                const editSelect = $('#edit_test_order_id');
                addSelect.empty().append('<option value="">Select Test Order</option>');
                editSelect.empty().append('<option value="">Select Test Order</option>');
                
                response.data.forEach(function(order) {
                    const option = `<option value="${order.id}">${order.order_id} - ${order.patient_name} (${order.test_name})</option>`;
                    addSelect.append(option);
                    editSelect.append(option);
                });
            }
        }
    });
}

// Add result form submission
$('#addResultForm').submit(function(e) {
    e.preventDefault();
    
    $.ajax({
        url: 'api/results_api.php',
        method: 'POST',
        data: $(this).serialize() + '&action=create',
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                $('#addResultModal').modal('hide');
                $('#addResultForm')[0].reset();
                loadResults(currentPage, currentSearch, currentStatus, currentCritical);
                showAlert('Result added successfully!', 'success');
            } else {
                showAlert('Error adding result: ' + response.message, 'danger');
            }
        },
        error: function() {
            showAlert('Error adding result. Please try again.', 'danger');
        }
    });
});

// Edit result
function editResult(id) {
    $.ajax({
        url: 'api/results_api.php',
        method: 'GET',
        data: { action: 'read', id: id },
        dataType: 'json',
        success: function(response) {
            if(response.success && response.data.length > 0) {
                const result = response.data[0];
                $('#edit_result_id').val(result.id);
                $('#edit_test_order_id').val(result.test_order_id);
                $('#edit_result_date').val(result.result_date);
                $('#edit_status').val(result.status);
                $('#edit_is_critical').val(result.is_critical);
                $('#edit_result_values').val(result.result_values);
                $('#edit_notes').val(result.notes);
                $('#editResultModal').modal('show');
            }
        }
    });
}

// Edit result form submission
$('#editResultForm').submit(function(e) {
    e.preventDefault();
    
    $.ajax({
        url: 'api/results_api.php',
        method: 'POST',
        data: $(this).serialize() + '&action=update',
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                $('#editResultModal').modal('hide');
                loadResults(currentPage, currentSearch, currentStatus, currentCritical);
                showAlert('Result updated successfully!', 'success');
            } else {
                showAlert('Error updating result: ' + response.message, 'danger');
            }
        },
        error: function() {
            showAlert('Error updating result. Please try again.', 'danger');
        }
    });
});

// View result details
function viewResult(id) {
    $.ajax({
        url: 'api/results_api.php',
        method: 'GET',
        data: { action: 'read', id: id },
        dataType: 'json',
        success: function(response) {
            if(response.success && response.data.length > 0) {
                const result = response.data[0];
                let html = `
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Test Information</h5>
                            <table class="table table-sm">
                                <tr><td><strong>Order ID:</strong></td><td>${result.order_id}</td></tr>
                                <tr><td><strong>Patient:</strong></td><td>${result.patient_name}</td></tr>
                                <tr><td><strong>Test:</strong></td><td>${result.test_name}</td></tr>
                                <tr><td><strong>Date:</strong></td><td>${formatDate(result.result_date)}</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5>Result Status</h5>
                            <table class="table table-sm">
                                <tr><td><strong>Status:</strong></td><td><span class="badge badge-info">${result.status}</span></td></tr>
                                <tr><td><strong>Critical:</strong></td><td>${result.is_critical == 1 ? '<span class="badge badge-danger">Yes</span>' : '<span class="badge badge-success">No</span>'}</td></tr>
                            </table>
                        </div>
                    </div>
                `;
                
                if(result.result_values) {
                    html += `
                        <div class="row">
                            <div class="col-12">
                                <h5>Result Values</h5>
                                <div class="alert alert-info">${result.result_values}</div>
                            </div>
                        </div>
                    `;
                }
                
                if(result.notes) {
                    html += `
                        <div class="row">
                            <div class="col-12">
                                <h5>Notes</h5>
                                <div class="alert alert-secondary">${result.notes}</div>
                            </div>
                        </div>
                    `;
                }
                
                $('#viewResultContent').html(html);
                $('#viewResultModal').modal('show');
            }
        }
    });
}

// Delete result
function deleteResult(id) {
    if(confirm('Are you sure you want to delete this result? This action cannot be undone.')) {
        $.ajax({
            url: 'api/results_api.php',
            method: 'POST',
            data: { action: 'delete', id: id },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    loadResults(currentPage, currentSearch, currentStatus, currentCritical);
                    showAlert('Result deleted successfully!', 'success');
                } else {
                    showAlert('Error deleting result: ' + response.message, 'danger');
                }
            },
            error: function() {
                showAlert('Error deleting result. Please try again.', 'danger');
            }
        });
    }
}

// Download result PDF
function downloadResultPDF() {
    showAlert('PDF download feature will be implemented soon.', 'info');
}

// Utility function to format date
function formatDate(dateStr) {
    if(!dateStr) return 'N/A';
    const date = new Date(dateStr);
    return date.toLocaleDateString();
}
</script>
</body>
</html>
