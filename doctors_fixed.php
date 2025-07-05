<?php
// Start session and check authentication before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in - redirect immediately if not
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Set page title and active menu
$page_title = 'Doctors';
$active_menu = 'doctors';
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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables.net-bs4/1.12.1/dataTables.bootstrap4.min.css">
  
  <style>
    .preloader {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 999999;
      background: rgba(0,0,0,0.9);
      color: white;
      display: flex;
      justify-content: center;
      align-items: center;
      flex-direction: column;
    }
    .preloader.hidden {
      display: none !important;
    }
  </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">

<!-- Preloader -->
<div class="preloader" id="preloader">
  <div class="text-center">
    <img src="img/logo.svg" alt="PathLab Pro Logo" height="60" width="60" class="animation__shake">
    <h2 class="animation__shake text-primary font-weight-bold mt-3">PathLab Pro</h2>
    <p class="mt-2">Loading doctors page...</p>
    <div class="spinner-border text-primary" role="status">
      <span class="sr-only">Loading...</span>
    </div>
  </div>
</div>

<div class="wrapper">
  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="dashboard.php" class="nav-link">Home</a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="doctors.php" class="nav-link">Doctors</a>
      </li>
    </ul>
    <ul class="navbar-nav ml-auto">
      <li class="nav-item">
        <a href="logout.php" class="nav-link">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      </li>
    </ul>
  </nav>

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="dashboard.php" class="brand-link">
      <img src="img/logo.svg" alt="PathLab Pro Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light">PathLab Pro</span>
    </a>
    <div class="sidebar">
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <li class="nav-item">
            <a href="dashboard.php" class="nav-link">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Dashboard</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="doctors.php" class="nav-link active">
              <i class="nav-icon fas fa-user-md"></i>
              <p>Doctors</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="patients.php" class="nav-link">
              <i class="nav-icon fas fa-users"></i>
              <p>Patients</p>
            </a>
          </li>
        </ul>
      </nav>
    </div>
  </aside>

  <!-- Content Wrapper -->
  <div class="content-wrapper">
    <!-- Content Header -->
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

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <!-- Alert Messages -->
        <div id="alertContainer"></div>

        <!-- Stats Row -->
        <div class="row">
          <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
              <div class="inner">
                <h3 id="totalDoctors">0</h3>
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
                <h3 id="activeDoctors">0</h3>
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
                <h3 id="specializations">0</h3>
                <p>Specializations</p>
              </div>
              <div class="icon">
                <i class="fas fa-stethoscope"></i>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
              <div class="inner">
                <h3 id="referralsToday">0</h3>
                <p>Referrals Today</p>
              </div>
              <div class="icon">
                <i class="fas fa-paper-plane"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Main Card -->
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Registered Doctors</h3>
                <div class="card-tools">
                  <button type="button" class="btn btn-primary btn-sm" id="addDoctorBtn">
                    <i class="fas fa-plus"></i> Add Doctor
                  </button>
                  <button type="button" class="btn btn-info btn-sm" id="refreshBtn">
                    <i class="fas fa-refresh"></i> Refresh
                  </button>
                </div>
              </div>
              <div class="card-body">
                <!-- Doctors Table -->
                <div class="table-responsive">
                  <table id="doctorsTable" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th>Doctor Name</th>
                        <th>Hospital</th>
                        <th>Contact No.</th>
                        <th>Address</th>
                        <th>Ref. %</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody id="doctorsTableBody">
                      <tr>
                        <td colspan="6" class="text-center">
                          <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                          </div>
                          <p class="mt-2">Loading doctors...</p>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <footer class="main-footer">
    <div class="float-right d-none d-sm-block">
      <b>Version</b> 1.0.0
    </div>
    <strong>Copyright &copy; 2023-2025 <a href="#" class="text-primary">PathLab Pro</a>.</strong> All rights reserved.
  </footer>
</div>

<!-- jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
<!-- DataTables -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables.net/1.12.1/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables.net-bs4/1.12.1/dataTables.bootstrap4.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>

<script>
$(document).ready(function() {
  console.log('Page loaded, hiding preloader...');
  
  // Hide preloader immediately
  $('#preloader').fadeOut(500);
  
  // Initialize page
  setTimeout(function() {
    initializePage();
  }, 600);
  
  function initializePage() {
    console.log('Initializing doctors page...');
    
    // Load doctors data
    loadDoctors();
    
    // Refresh button
    $('#refreshBtn').click(function() {
      loadDoctors();
    });
  }
  
  function loadDoctors() {
    console.log('Loading doctors data...');
    
    $.ajax({
      url: 'api/doctors_api.php',
      type: 'GET',
      dataType: 'json',
      timeout: 10000,
      success: function(response) {
        console.log('API Response:', response);
        
        if (response.success) {
          displayDoctors(response.data);
          updateStats(response.data);
        } else {
          showError('API Error: ' + response.message);
        }
      },
      error: function(xhr, status, error) {
        console.error('API Error:', status, error);
        showError('Failed to load doctors: ' + error);
        
        // Show fallback message
        $('#doctorsTableBody').html(`
          <tr>
            <td colspan="6" class="text-center text-danger">
              <i class="fas fa-exclamation-triangle"></i>
              <p>Failed to load doctors data.</p>
              <p><strong>Error:</strong> ${error}</p>
              <p><strong>Status:</strong> ${status}</p>
              <button class="btn btn-primary btn-sm" onclick="loadDoctors()">
                <i class="fas fa-retry"></i> Try Again
              </button>
            </td>
          </tr>
        `);
      }
    });
  }
  
  function displayDoctors(doctors) {
    console.log('Displaying doctors:', doctors.length);
    
    if (doctors.length === 0) {
      $('#doctorsTableBody').html(`
        <tr>
          <td colspan="6" class="text-center">
            <div class="alert alert-info">
              <i class="fas fa-info-circle"></i>
              No doctors found. <a href="#" id="addFirstDoctor">Add the first doctor</a>
            </div>
          </td>
        </tr>
      `);
      return;
    }
    
    let html = '';
    doctors.forEach(function(doctor) {
      html += `
        <tr>
          <td>${escapeHtml((doctor.first_name || '') + ' ' + (doctor.last_name || ''))}</td>
          <td>${escapeHtml(doctor.hospital || 'N/A')}</td>
          <td>${escapeHtml(doctor.phone || 'N/A')}</td>
          <td>${escapeHtml(doctor.address || 'N/A')}</td>
          <td>${escapeHtml(doctor.referral_percentage || '0')}%</td>
          <td>
            <div class="btn-group">
              <button class="btn btn-sm btn-info" title="View">
                <i class="fas fa-eye"></i>
              </button>
              <button class="btn btn-sm btn-warning" title="Edit">
                <i class="fas fa-edit"></i>
              </button>
              <button class="btn btn-sm btn-danger" title="Delete">
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </td>
        </tr>
      `;
    });
    
    $('#doctorsTableBody').html(html);
    
    // Initialize DataTable if not already initialized
    if (!$.fn.DataTable.isDataTable('#doctorsTable')) {
      $('#doctorsTable').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[0, 'asc']]
      });
    }
  }
  
  function updateStats(doctors) {
    $('#totalDoctors').text(doctors.length);
    $('#activeDoctors').text(doctors.filter(d => d.status === 'active').length);
    $('#specializations').text([...new Set(doctors.map(d => d.specialization))].length);
    $('#referralsToday').text('0'); // This would need actual referral data
  }
  
  function showError(message) {
    $('#alertContainer').html(`
      <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-exclamation-triangle"></i> ${message}
      </div>
    `);
  }
  
  function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }
  
  // Global error handler
  window.onerror = function(msg, url, lineNo, columnNo, error) {
    console.error('JavaScript error:', msg, 'at', url + ':' + lineNo + ':' + columnNo);
    return false;
  };
});
</script>

</body>
</html>
