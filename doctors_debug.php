<?php
// Set page title and active menu
$page_title = 'Doctors - Debug';
$active_menu = 'doctors';

// Include header and sidebar but with debug info
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
  <!-- Content Header -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">Doctors Management - Debug Mode</h1>
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
      <!-- Debug Info -->
      <div class="row">
        <div class="col-12">
          <div class="card card-info">
            <div class="card-header">
              <h3 class="card-title">Debug Information</h3>
            </div>
            <div class="card-body">
              <div id="debugInfo">
                <div class="alert alert-info">
                  <i class="fas fa-info-circle"></i> Checking system status...
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Alert Messages -->
      <div id="alertContainer"></div>

      <!-- Stats Row -->
      <div class="row">
        <div class="col-lg-3 col-6">
          <div class="small-box bg-info">
            <div class="inner">
              <h3 id="totalDoctors">?</h3>
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
              <h3 id="activeDoctors">?</h3>
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
              <h3 id="apiStatus">?</h3>
              <p>API Status</p>
            </div>
            <div class="icon">
              <i class="fas fa-plug"></i>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-danger">
            <div class="inner">
              <h3 id="jsStatus">?</h3>
              <p>JS Status</p>
            </div>
            <div class="icon">
              <i class="fab fa-js-square"></i>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Card -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Doctors Data</h3>
              <div class="card-tools">
                <button type="button" class="btn btn-primary btn-sm" id="testApiBtn">
                  <i class="fas fa-sync"></i> Test API
                </button>
                <button type="button" class="btn btn-info btn-sm" id="testJSBtn">
                  <i class="fas fa-code"></i> Test JS
                </button>
              </div>
            </div>
            <div class="card-body">
              <div id="doctorsContent">
                <div class="text-center">
                  <i class="fas fa-spinner fa-spin fa-2x"></i>
                  <p>Loading doctors data...</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<?php
$additional_scripts = <<<'EOT'
<script>
$(document).ready(function() {
  console.log('Document ready - starting diagnostics...');
  
  let debugMessages = [];
  
  function addDebugMessage(type, message) {
    const timestamp = new Date().toLocaleTimeString();
    debugMessages.push(`[${timestamp}] ${type.toUpperCase()}: ${message}`);
    updateDebugDisplay();
    console.log(`[DEBUG] ${type}: ${message}`);
  }
  
  function updateDebugDisplay() {
    const debugHtml = debugMessages.map(msg => `<div style="font-family: monospace; font-size: 12px; margin: 2px 0;">${msg}</div>`).join('');
    $('#debugInfo').html(debugHtml);
  }
  
  // Check if jQuery is working
  addDebugMessage('info', 'jQuery loaded successfully');
  
  // Check if DataTables is available
  if (typeof $.fn.DataTable !== 'undefined') {
    addDebugMessage('success', 'DataTables library is available');
    $('#jsStatus').text('OK').parent().removeClass('bg-danger').addClass('bg-success');
  } else {
    addDebugMessage('error', 'DataTables library is NOT available');
    $('#jsStatus').text('FAIL').parent().removeClass('bg-danger').addClass('bg-danger');
  }
  
  // Test API connection
  function testAPI() {
    addDebugMessage('info', 'Testing doctors API...');
    
    $.ajax({
      url: 'api/doctors_api.php',
      type: 'GET',
      dataType: 'json',
      timeout: 10000,
      success: function(response) {
        addDebugMessage('success', 'API response received');
        console.log('API Response:', response);
        
        if (response.success) {
          addDebugMessage('success', `API returned ${response.data.length} doctors`);
          $('#totalDoctors').text(response.data.length);
          $('#activeDoctors').text(response.data.filter(d => d.status === 'active').length);
          $('#apiStatus').text('OK').parent().removeClass('bg-warning').addClass('bg-success');
          
          // Display doctors data
          displayDoctorsData(response.data);
        } else {
          addDebugMessage('error', `API error: ${response.message}`);
          $('#apiStatus').text('ERROR').parent().removeClass('bg-warning').addClass('bg-danger');
        }
      },
      error: function(xhr, status, error) {
        addDebugMessage('error', `API request failed: ${status} - ${error}`);
        addDebugMessage('error', `HTTP Status: ${xhr.status}`);
        addDebugMessage('error', `Response: ${xhr.responseText}`);
        $('#apiStatus').text('FAIL').parent().removeClass('bg-warning').addClass('bg-danger');
        
        // Show error details
        $('#doctorsContent').html(`
          <div class="alert alert-danger">
            <h5><i class="fas fa-exclamation-triangle"></i> API Connection Failed</h5>
            <p><strong>Status:</strong> ${status}</p>
            <p><strong>Error:</strong> ${error}</p>
            <p><strong>HTTP Status:</strong> ${xhr.status}</p>
            <p><strong>Response:</strong> ${xhr.responseText}</p>
          </div>
        `);
      }
    });
  }
  
  function displayDoctorsData(doctors) {
    if (doctors.length === 0) {
      $('#doctorsContent').html('<div class="alert alert-warning">No doctors found in database.</div>');
      return;
    }
    
    let html = '<div class="table-responsive"><table class="table table-bordered table-striped"><thead><tr>';
    html += '<th>ID</th><th>Name</th><th>Hospital</th><th>Phone</th><th>Status</th>';
    html += '</tr></thead><tbody>';
    
    doctors.forEach(doctor => {
      html += `<tr>
        <td>${doctor.id || doctor.doctor_id || 'N/A'}</td>
        <td>${escapeHtml((doctor.first_name || '') + ' ' + (doctor.last_name || ''))}</td>
        <td>${escapeHtml(doctor.hospital || 'N/A')}</td>
        <td>${escapeHtml(doctor.phone || 'N/A')}</td>
        <td><span class="badge badge-${doctor.status === 'active' ? 'success' : 'secondary'}">${doctor.status || 'unknown'}</span></td>
      </tr>`;
    });
    
    html += '</tbody></table></div>';
    $('#doctorsContent').html(html);
  }
  
  function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }
  
  // Test API button
  $('#testApiBtn').click(function() {
    testAPI();
  });
  
  // Test JS button
  $('#testJSBtn').click(function() {
    addDebugMessage('info', 'Running JavaScript tests...');
    
    // Test various JS libraries
    const tests = [
      { name: 'jQuery', test: () => typeof $ !== 'undefined' },
      { name: 'Bootstrap', test: () => typeof $.fn.modal !== 'undefined' },
      { name: 'DataTables', test: () => typeof $.fn.DataTable !== 'undefined' },
      { name: 'Chart.js', test: () => typeof Chart !== 'undefined' },
      { name: 'SweetAlert2', test: () => typeof Swal !== 'undefined' },
      { name: 'Toastr', test: () => typeof toastr !== 'undefined' }
    ];
    
    tests.forEach(test => {
      try {
        if (test.test()) {
          addDebugMessage('success', `${test.name} is available`);
        } else {
          addDebugMessage('error', `${test.name} is NOT available`);
        }
      } catch (e) {
        addDebugMessage('error', `${test.name} test failed: ${e.message}`);
      }
    });
  });
  
  // Auto-run tests
  setTimeout(() => {
    addDebugMessage('info', 'Auto-running tests...');
    testAPI();
    $('#testJSBtn').click();
  }, 1000);
  
  // Show alert function
  function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' :
                      type === 'error' ? 'alert-danger' :
                      type === 'warning' ? 'alert-warning' : 'alert-info';
    const icon = type === 'success' ? 'fas fa-check' :
                 type === 'error' ? 'fas fa-ban' :
                 type === 'warning' ? 'fas fa-exclamation-triangle' : 'fas fa-info-circle';
    const alert = `
      <div class="alert ${alertClass} alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="icon ${icon}"></i> ${message}
      </div>
    `;
    $('#alertContainer').html(alert);
    setTimeout(() => {
      $('#alertContainer .alert').fadeOut();
    }, 5000);
  }
  
  // Global error handler
  window.onerror = function(msg, url, lineNo, columnNo, error) {
    addDebugMessage('error', `JavaScript error: ${msg} at ${url}:${lineNo}:${columnNo}`);
    return false;
  };
  
  addDebugMessage('info', 'Diagnostics setup complete');
});
</script>
EOT;

include 'includes/footer.php';
?>
