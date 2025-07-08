<?php
// Set page title
$page_title = 'Database View';

// Include header
include 'includes/header.php';

// Include sidebar with user info
include 'includes/sidebar.php';
?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Database Overview</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
              <li class="breadcrumb-item active">Database View</li>
            </ol>
          </div>
        </div>
      </div>
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <!-- Database Statistics -->
        <div class="row">
          <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
              <div class="inner">
                <h3>47</h3>
                <p>Total Patients</p>
              </div>
              <div class="icon">
                <i class="fas fa-user-injured"></i>
              </div>
              <a href="patients.php" class="small-box-footer">View Patients <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
              <div class="inner">
                <h3>12</h3>
                <p>Total Doctors</p>
              </div>
              <div class="icon">
                <i class="fas fa-user-md"></i>
              </div>
              <a href="doctors.php" class="small-box-footer">View Doctors <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
              <div class="inner">
                <h3>156</h3>
                <p>Total Tests</p>
              </div>
              <div class="icon">
                <i class="fas fa-flask"></i>
              </div>
              <a href="tests.php" class="small-box-footer">View Tests <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
              <div class="inner">
                <h3>8</h3>
                <p>Total Users</p>
              </div>
              <div class="icon">
                <i class="fas fa-users"></i>
              </div>
              <a href="users.php" class="small-box-footer">View Users <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
        </div>

        <!-- Database Tables -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title"><i class="fas fa-database mr-2"></i>Database Tables</h3>
            <div class="card-tools">
              <button type="button" class="btn btn-primary btn-sm" onclick="refreshData()">
                <i class="fas fa-sync"></i> Refresh Data
              </button>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table id="tablesTable" class="table table-bordered table-striped table-hover">
                <thead>
                  <tr>
                    <th>Table Name</th>
                    <th>Description</th>
                    <th>Record Count</th>
                    <th>Last Modified</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <!-- Sample data -->
                  <tr>
                    <td><strong>patients</strong></td>
                    <td>Patient information and medical records</td>
                    <td><span class="badge badge-info">47</span></td>
                    <td>2024-01-15 10:30 AM</td>
                    <td>
                      <div class="btn-group" role="group">
                        <button type="button" class="btn btn-info btn-sm" onclick="viewTableData('patients')" title="View Data">
                          <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="btn btn-success btn-sm" onclick="exportTable('patients')" title="Export">
                          <i class="fas fa-download"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td><strong>doctors</strong></td>
                    <td>Doctor profiles and specializations</td>
                    <td><span class="badge badge-success">12</span></td>
                    <td>2024-01-14 3:45 PM</td>
                    <td>
                      <div class="btn-group" role="group">
                        <button type="button" class="btn btn-info btn-sm" onclick="viewTableData('doctors')" title="View Data">
                          <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="btn btn-success btn-sm" onclick="exportTable('doctors')" title="Export">
                          <i class="fas fa-download"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td><strong>lab_tests</strong></td>
                    <td>Available laboratory test catalog</td>
                    <td><span class="badge badge-warning">23</span></td>
                    <td>2024-01-13 11:20 AM</td>
                    <td>
                      <div class="btn-group" role="group">
                        <button type="button" class="btn btn-info btn-sm" onclick="viewTableData('lab_tests')" title="View Data">
                          <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="btn btn-success btn-sm" onclick="exportTable('lab_tests')" title="Export">
                          <i class="fas fa-download"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td><strong>test_orders</strong></td>
                    <td>Test orders and patient requests</td>
                    <td><span class="badge badge-primary">89</span></td>
                    <td>2024-01-15 9:15 AM</td>
                    <td>
                      <div class="btn-group" role="group">
                        <button type="button" class="btn btn-info btn-sm" onclick="viewTableData('test_orders')" title="View Data">
                          <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="btn btn-success btn-sm" onclick="exportTable('test_orders')" title="Export">
                          <i class="fas fa-download"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td><strong>test_results</strong></td>
                    <td>Laboratory test results and reports</td>
                    <td><span class="badge badge-info">67</span></td>
                    <td>2024-01-15 2:30 PM</td>
                    <td>
                      <div class="btn-group" role="group">
                        <button type="button" class="btn btn-info btn-sm" onclick="viewTableData('test_results')" title="View Data">
                          <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="btn btn-success btn-sm" onclick="exportTable('test_results')" title="Export">
                          <i class="fas fa-download"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td><strong>users</strong></td>
                    <td>System users and authentication</td>
                    <td><span class="badge badge-danger">8</span></td>
                    <td>2024-01-12 4:20 PM</td>
                    <td>
                      <div class="btn-group" role="group">
                        <button type="button" class="btn btn-info btn-sm" onclick="viewTableData('users')" title="View Data">
                          <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="btn btn-success btn-sm" onclick="exportTable('users')" title="Export">
                          <i class="fas fa-download"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title"><i class="fas fa-bolt mr-2"></i>Quick Actions</h3>
          </div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-3">
                <button class="btn btn-primary btn-block" onclick="location.href='patients.php'">
                  <i class="fas fa-user-injured"></i><br>Manage Patients
                </button>
              </div>
              <div class="col-md-3">
                <button class="btn btn-success btn-block" onclick="location.href='doctors.php'">
                  <i class="fas fa-user-md"></i><br>Manage Doctors
                </button>
              </div>
              <div class="col-md-3">
                <button class="btn btn-warning btn-block" onclick="location.href='tests.php'">
                  <i class="fas fa-flask"></i><br>Manage Tests
                </button>
              </div>
              <div class="col-md-3">
                <button class="btn btn-info btn-block" onclick="exportAllData()">
                  <i class="fas fa-download"></i><br>Export All Data
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <!-- View Table Data Modal -->
  <div class="modal fade" id="modal-view-table" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header bg-info">
          <h4 class="modal-title text-white"><i class="fas fa-table mr-2"></i>Table Data: <span id="tableName"></span></h4>
          <button type="button" class="close text-white" data-dismiss="modal">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div id="tableDataContent">
            <!-- Table data will be loaded here -->
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-success" onclick="exportCurrentTable()">
            <i class="fas fa-download mr-1"></i>Export Data
          </button>
        </div>
      </div>
    </div>
  </div>

<?php
// Additional scripts specific to the database view page
$additional_scripts = <<<EOT
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#tablesTable').DataTable({
        responsive: true,
        lengthChange: false,
        autoWidth: false,
        order: [[3, 'desc']] // Sort by last modified descending
    });
});

var currentTable = '';

// Refresh data function
function refreshData() {
    toastr.info('Refreshing database information...');
    
    // Simulate refresh - in real implementation, make API call to get actual counts
    setTimeout(function() {
        toastr.success('Database information refreshed successfully!');
        location.reload();
    }, 1000);
}

// View table data function
function viewTableData(tableName) {
    currentTable = tableName;
    $('#tableName').text(tableName);
    
    // Sample data based on table name - in real implementation, fetch from API
    var sampleData = '';
    
    switch(tableName) {
        case 'patients':
            sampleData = '<table class="table table-sm table-striped">' +
                '<thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Date Added</th></tr></thead>' +
                '<tbody>' +
                '<tr><td>1</td><td>John Doe</td><td>john@example.com</td><td>555-0123</td><td>2024-01-15</td></tr>' +
                '<tr><td>2</td><td>Jane Smith</td><td>jane@example.com</td><td>555-0124</td><td>2024-01-14</td></tr>' +
                '<tr><td>3</td><td>Mike Johnson</td><td>mike@example.com</td><td>555-0125</td><td>2024-01-13</td></tr>' +
                '</tbody></table>';
            break;
        case 'doctors':
            sampleData = '<table class="table table-sm table-striped">' +
                '<thead><tr><th>ID</th><th>Name</th><th>Specialization</th><th>Email</th><th>Status</th></tr></thead>' +
                '<tbody>' +
                '<tr><td>1</td><td>Dr. Sarah Wilson</td><td>Cardiology</td><td>sarah@example.com</td><td>Active</td></tr>' +
                '<tr><td>2</td><td>Dr. Mark Davis</td><td>Neurology</td><td>mark@example.com</td><td>Active</td></tr>' +
                '</tbody></table>';
            break;
        case 'lab_tests':
            sampleData = '<table class="table table-sm table-striped">' +
                '<thead><tr><th>ID</th><th>Test Code</th><th>Test Name</th><th>Category</th><th>Price</th></tr></thead>' +
                '<tbody>' +
                '<tr><td>1</td><td>CBC001</td><td>Complete Blood Count</td><td>Hematology</td><td>$45.00</td></tr>' +
                '<tr><td>2</td><td>GLUC01</td><td>Glucose Fasting</td><td>Chemistry</td><td>$25.00</td></tr>' +
                '</tbody></table>';
            break;
        default:
            sampleData = '<div class="alert alert-info">Sample data for ' + tableName + ' table would be displayed here.</div>';
    }
    
    $('#tableDataContent').html(sampleData);
    $('#modal-view-table').modal('show');
}

// Export table function
function exportTable(tableName) {
    toastr.info('Exporting ' + tableName + ' data...');
    
    // Simulate export - in real implementation, make API call to generate export
    setTimeout(function() {
        toastr.success(tableName + ' data exported successfully!');
        // In real implementation, trigger file download
    }, 1500);
}

// Export current table function
function exportCurrentTable() {
    if (currentTable) {
        exportTable(currentTable);
        $('#modal-view-table').modal('hide');
    }
}

// Export all data function
function exportAllData() {
    if (confirm('This will export all database tables. Continue?')) {
        toastr.info('Exporting all database data... This may take a few minutes.');
        
        // Simulate full export - in real implementation, make API call
        setTimeout(function() {
            toastr.success('All database data exported successfully!');
            // In real implementation, trigger file download
        }, 3000);
    }
}
</script>
EOT;

// Include footer
include 'includes/footer.php';
?>
