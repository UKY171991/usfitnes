<?php
// Set page title
$page_title = 'Settings';

// Include header
include 'includes/header.php';

// Include sidebar with user info
include 'includes/sidebar.php';
?>
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
            <a href="equipment.php" class="nav-link">
              <i class="nav-icon fas fa-microscope"></i>
              <p>Equipment</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="settings.php" class="nav-link active">
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
            <h1 class="m-0">System Settings</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
              <li class="breadcrumb-item active">Settings</li>
            </ol>
          </div>
        </div>
      </div>
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-3">
            <!-- Settings navigation menu -->
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Settings Menu</h3>
              </div>
              <div class="card-body p-0">
                <ul class="nav nav-pills flex-column">
                  <li class="nav-item">
                    <a href="#general" class="nav-link active" data-toggle="tab">
                      <i class="fas fa-cog mr-2"></i> General Settings
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#users" class="nav-link" data-toggle="tab">
                      <i class="fas fa-users mr-2"></i> User Management
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#notifications" class="nav-link" data-toggle="tab">
                      <i class="fas fa-bell mr-2"></i> Notifications
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#backup" class="nav-link" data-toggle="tab">
                      <i class="fas fa-database mr-2"></i> Backup & Restore
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#appearance" class="nav-link" data-toggle="tab">
                      <i class="fas fa-palette mr-2"></i> Appearance
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#system" class="nav-link" data-toggle="tab">
                      <i class="fas fa-server mr-2"></i> System Information
                    </a>
                  </li>
                </ul>
              </div>
            </div>
          </div>
          
          <div class="col-md-9">
            <!-- Settings content -->
            <div class="tab-content">
              <!-- General Settings -->
              <div class="tab-pane active" id="general">
                <div class="card">
                  <div class="card-header">
                    <h3 class="card-title">General Settings</h3>
                  </div>
                  <div class="card-body">
                    <form id="generalSettingsForm">                      <div class="form-group">
                        <label for="laboratory_name">Laboratory Name</label>
                        <input type="text" class="form-control" id="laboratory_name" placeholder="Laboratory Name" required>
                      </div>
                      <div class="form-group">
                        <label for="laboratory_address">Laboratory Address</label>
                        <textarea class="form-control" id="laboratory_address" rows="3" placeholder="Enter full address" required></textarea>
                      </div>
                      <div class="form-group">
                        <label for="contact_phone">Contact Phone</label>
                        <input type="tel" class="form-control" id="contact_phone" placeholder="Contact Phone" required>
                      </div>
                      <div class="form-group">
                        <label for="contact_email">Contact Email</label>
                        <input type="email" class="form-control" id="contact_email" placeholder="Contact Email" required>
                      </div>
                      <div class="form-group">
                        <label for="timezone">Timezone</label>
                        <select class="form-control" id="timezone" required>
                          <option value="UTC-8">Pacific Time (UTC-8)</option>
                          <option value="UTC-7">Mountain Time (UTC-7)</option>
                          <option value="UTC-6">Central Time (UTC-6)</option>
                          <option value="UTC-5" selected>Eastern Time (UTC-5)</option>
                          <option value="UTC-4">Atlantic Time (UTC-4)</option>
                          <option value="UTC">UTC</option>
                          <option value="UTC+1">Central European Time (UTC+1)</option>
                          <option value="UTC+2">Eastern European Time (UTC+2)</option>
                        </select>
                      </div>
                      <div class="form-group">
                        <label for="dateFormat">Date Format</label>
                        <select class="form-control" id="dateFormat" required>
                          <option value="MM/DD/YYYY" selected>MM/DD/YYYY</option>
                          <option value="DD/MM/YYYY">DD/MM/YYYY</option>
                          <option value="YYYY-MM-DD">YYYY-MM-DD</option>
                        </select>
                      </div>
                      <div class="form-group">
                        <label for="currency">Currency</label>
                        <select class="form-control" id="currency" required>
                          <option value="USD" selected>USD ($)</option>
                          <option value="EUR">EUR (€)</option>
                          <option value="GBP">GBP (£)</option>
                          <option value="CAD">CAD (C$)</option>
                        </select>
                      </div>
                      <div class="form-group">
                        <div class="custom-control custom-switch">
                          <input type="checkbox" class="custom-control-input" id="smsNotifications" checked>
                          <label class="custom-control-label" for="smsNotifications">Enable SMS Notifications</label>
                        </div>
                      </div>
                      <div class="form-group">
                        <div class="custom-control custom-switch">
                          <input type="checkbox" class="custom-control-input" id="emailNotifications" checked>
                          <label class="custom-control-label" for="emailNotifications">Enable Email Notifications</label>
                        </div>
                      </div>
                      <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                  </div>
                </div>
              </div>
              
              <!-- User Management -->
              <div class="tab-pane" id="users">
                <div class="card">
                  <div class="card-header">
                    <h3 class="card-title">User Management</h3>
                    <div class="card-tools">
                      <button type="button" class="btn btn-primary btn-sm">
                        <i class="fas fa-user-plus"></i> Add New User
                      </button>
                    </div>
                  </div>
                  <div class="card-body">
                    <table class="table table-bordered table-striped">
                      <thead>
                        <tr>
                          <th style="width: 10px">#</th>
                          <th>Username</th>
                          <th>Full Name</th>
                          <th>Role</th>
                          <th>Status</th>
                          <th>Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td>1</td>
                          <td>admin</td>
                          <td>System Administrator</td>
                          <td><span class="badge bg-danger">Admin</span></td>
                          <td><span class="badge bg-success">Active</span></td>
                          <td>
                            <button class="btn btn-sm btn-info"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-sm btn-warning"><i class="fas fa-key"></i></button>
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                          </td>
                        </tr>
                        <tr>
                          <td>2</td>
                          <td>technician1</td>
                          <td>John Doe</td>
                          <td><span class="badge bg-primary">Lab Technician</span></td>
                          <td><span class="badge bg-success">Active</span></td>
                          <td>
                            <button class="btn btn-sm btn-info"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-sm btn-warning"><i class="fas fa-key"></i></button>
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                          </td>
                        </tr>
                        <tr>
                          <td>3</td>
                          <td>doctor1</td>
                          <td>Jane Smith</td>
                          <td><span class="badge bg-info">Doctor</span></td>
                          <td><span class="badge bg-success">Active</span></td>
                          <td>
                            <button class="btn btn-sm btn-info"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-sm btn-warning"><i class="fas fa-key"></i></button>
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                          </td>
                        </tr>
                        <tr>
                          <td>4</td>
                          <td>receptionist1</td>
                          <td>Robert Brown</td>
                          <td><span class="badge bg-success">Receptionist</span></td>
                          <td><span class="badge bg-warning">Inactive</span></td>
                          <td>
                            <button class="btn btn-sm btn-info"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-sm btn-warning"><i class="fas fa-key"></i></button>
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
              
              <!-- Notifications -->
              <div class="tab-pane" id="notifications">
                <div class="card">
                  <div class="card-header">
                    <h3 class="card-title">Notification Settings</h3>
                  </div>
                  <div class="card-body">
                    <form id="notificationSettingsForm">
                      <h5>Email Notifications</h5>
                      <div class="form-group">
                        <div class="custom-control custom-switch">
                          <input type="checkbox" class="custom-control-input" id="newOrder" checked>
                          <label class="custom-control-label" for="newOrder">New Test Order</label>
                        </div>
                      </div>
                      <div class="form-group">
                        <div class="custom-control custom-switch">
                          <input type="checkbox" class="custom-control-input" id="resultReady" checked>
                          <label class="custom-control-label" for="resultReady">Test Result Ready</label>
                        </div>
                      </div>
                      <div class="form-group">
                        <div class="custom-control custom-switch">
                          <input type="checkbox" class="custom-control-input" id="criticalResult" checked>
                          <label class="custom-control-label" for="criticalResult">Critical Result Alert</label>
                        </div>
                      </div>
                      <div class="form-group">
                        <div class="custom-control custom-switch">
                          <input type="checkbox" class="custom-control-input" id="newPatient">
                          <label class="custom-control-label" for="newPatient">New Patient Registration</label>
                        </div>
                      </div>
                      <div class="form-group">
                        <div class="custom-control custom-switch">
                          <input type="checkbox" class="custom-control-input" id="systemBackup" checked>
                          <label class="custom-control-label" for="systemBackup">System Backup Status</label>
                        </div>
                      </div>
                      
                      <h5 class="mt-4">SMS Notifications</h5>
                      <div class="form-group">
                        <div class="custom-control custom-switch">
                          <input type="checkbox" class="custom-control-input" id="smsResultReady" checked>
                          <label class="custom-control-label" for="smsResultReady">Test Result Ready (to Patients)</label>
                        </div>
                      </div>
                      <div class="form-group">
                        <div class="custom-control custom-switch">
                          <input type="checkbox" class="custom-control-input" id="smsCritical" checked>
                          <label class="custom-control-label" for="smsCritical">Critical Result Alert (to Doctors)</label>
                        </div>
                      </div>
                      <div class="form-group">
                        <div class="custom-control custom-switch">
                          <input type="checkbox" class="custom-control-input" id="smsAppointment">
                          <label class="custom-control-label" for="smsAppointment">Appointment Reminders</label>
                        </div>
                      </div>
                      
                      <h5 class="mt-4">System Notifications</h5>
                      <div class="form-group">
                        <div class="custom-control custom-switch">
                          <input type="checkbox" class="custom-control-input" id="lowInventory" checked>
                          <label class="custom-control-label" for="lowInventory">Low Inventory Alert</label>
                        </div>
                      </div>
                      <div class="form-group">
                        <div class="custom-control custom-switch">
                          <input type="checkbox" class="custom-control-input" id="maintenanceReminder" checked>
                          <label class="custom-control-label" for="maintenanceReminder">Equipment Maintenance Reminder</label>
                        </div>
                      </div>
                      <div class="form-group">
                        <div class="custom-control custom-switch">
                          <input type="checkbox" class="custom-control-input" id="systemUpdates" checked>
                          <label class="custom-control-label" for="systemUpdates">System Updates Available</label>
                        </div>
                      </div>
                      
                      <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                  </div>
                </div>
              </div>
              
              <!-- Backup & Restore -->
              <div class="tab-pane" id="backup">
                <div class="card">
                  <div class="card-header">
                    <h3 class="card-title">Backup & Restore</h3>
                  </div>
                  <div class="card-body">
                    <div class="row">
                      <div class="col-md-6">
                        <div class="card">
                          <div class="card-header">
                            <h3 class="card-title">Backup Database</h3>
                          </div>
                          <div class="card-body">
                            <p>Last backup: <strong>June 17, 2025 09:45 AM</strong></p>
                            <p>Backup schedule: <strong>Daily at 2:00 AM</strong></p>
                            <button class="btn btn-primary">Create Manual Backup</button>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="card">
                          <div class="card-header">
                            <h3 class="card-title">Restore Database</h3>
                          </div>
                          <div class="card-body">
                            <p class="text-danger"><strong>Warning:</strong> Restoring a backup will overwrite all current data.</p>
                            <div class="form-group">
                              <label for="backupFile">Select Backup File</label>
                              <div class="input-group">
                                <div class="custom-file">
                                  <input type="file" class="custom-file-input" id="backupFile">
                                  <label class="custom-file-label" for="backupFile">Choose file</label>
                                </div>
                              </div>
                            </div>
                            <button class="btn btn-warning">Restore Selected Backup</button>
                          </div>
                        </div>
                      </div>
                    </div>
                    
                    <div class="card mt-4">
                      <div class="card-header">
                        <h3 class="card-title">Backup History</h3>
                      </div>
                      <div class="card-body">
                        <table class="table table-bordered table-striped">
                          <thead>
                            <tr>
                              <th>Date & Time</th>
                              <th>Type</th>
                              <th>Size</th>
                              <th>Status</th>
                              <th>Actions</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr>
                              <td>Jun 17, 2025 09:45 AM</td>
                              <td>Manual</td>
                              <td>24.6 MB</td>
                              <td><span class="badge bg-success">Success</span></td>
                              <td>
                                <button class="btn btn-sm btn-info"><i class="fas fa-download"></i></button>
                                <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                              </td>
                            </tr>
                            <tr>
                              <td>Jun 17, 2025 02:00 AM</td>
                              <td>Automatic</td>
                              <td>24.5 MB</td>
                              <td><span class="badge bg-success">Success</span></td>
                              <td>
                                <button class="btn btn-sm btn-info"><i class="fas fa-download"></i></button>
                                <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                              </td>
                            </tr>
                            <tr>
                              <td>Jun 16, 2025 02:00 AM</td>
                              <td>Automatic</td>
                              <td>24.3 MB</td>
                              <td><span class="badge bg-success">Success</span></td>
                              <td>
                                <button class="btn btn-sm btn-info"><i class="fas fa-download"></i></button>
                                <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                              </td>
                            </tr>
                            <tr>
                              <td>Jun 15, 2025 02:00 AM</td>
                              <td>Automatic</td>
                              <td>24.2 MB</td>
                              <td><span class="badge bg-success">Success</span></td>
                              <td>
                                <button class="btn btn-sm btn-info"><i class="fas fa-download"></i></button>
                                <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Appearance -->
              <div class="tab-pane" id="appearance">
                <div class="card">
                  <div class="card-header">
                    <h3 class="card-title">Appearance Settings</h3>
                  </div>
                  <div class="card-body">
                    <form id="appearanceSettingsForm">
                      <div class="form-group">
                        <label for="theme">Theme</label>
                        <select class="form-control" id="theme">
                          <option value="light" selected>Light</option>
                          <option value="dark">Dark</option>
                          <option value="blue">Blue</option>
                          <option value="green">Green</option>
                        </select>
                      </div>
                      <div class="form-group">
                        <label for="accentColor">Accent Color</label>
                        <input type="color" class="form-control" id="accentColor" value="#3c8dbc">
                      </div>
                      <div class="form-group">
                        <label for="fontSize">Font Size</label>
                        <select class="form-control" id="fontSize">
                          <option value="small">Small</option>
                          <option value="medium" selected>Medium</option>
                          <option value="large">Large</option>
                        </select>
                      </div>
                      <div class="form-group">
                        <label for="sidebarMode">Sidebar Mode</label>
                        <select class="form-control" id="sidebarMode">
                          <option value="expanded" selected>Expanded</option>
                          <option value="collapsed">Collapsed</option>
                          <option value="hover">Hover</option>
                        </select>
                      </div>
                      <div class="form-group">
                        <div class="custom-control custom-switch">
                          <input type="checkbox" class="custom-control-input" id="animations" checked>
                          <label class="custom-control-label" for="animations">Enable Animations</label>
                        </div>
                      </div>
                      <div class="form-group">
                        <label for="logoUpload">Upload Logo</label>
                        <div class="input-group">
                          <div class="custom-file">
                            <input type="file" class="custom-file-input" id="logoUpload">
                            <label class="custom-file-label" for="logoUpload">Choose file</label>
                          </div>
                        </div>
                        <small class="form-text text-muted">Recommended size: 200x50 pixels</small>
                      </div>
                      <div class="form-group">
                        <div class="custom-control custom-switch">
                          <input type="checkbox" class="custom-control-input" id="showAvatar" checked>
                          <label class="custom-control-label" for="showAvatar">Show User Avatars</label>
                        </div>
                      </div>
                      <button type="submit" class="btn btn-primary">Save Changes</button>
                      <button type="button" class="btn btn-default">Reset to Default</button>
                    </form>
                  </div>
                </div>
              </div>
              
              <!-- System Information -->
              <div class="tab-pane" id="system">
                <div class="card">
                  <div class="card-header">
                    <h3 class="card-title">System Information</h3>
                  </div>
                  <div class="card-body">
                    <table class="table table-bordered">
                      <tr>
                        <th style="width: 200px">Application Version</th>
                        <td>PathLab Pro 1.0.0</td>
                      </tr>
                      <tr>
                        <th>PHP Version</th>
                        <td>8.1.2</td>
                      </tr>
                      <tr>
                        <th>MySQL Version</th>
                        <td>8.0.28</td>
                      </tr>
                      <tr>
                        <th>Server OS</th>
                        <td>Windows Server 2019</td>
                      </tr>
                      <tr>
                        <th>Web Server</th>
                        <td>Apache 2.4.52</td>
                      </tr>
                      <tr>
                        <th>Memory Usage</th>
                        <td>2.4 GB / 8 GB (30%)</td>
                      </tr>
                      <tr>
                        <th>Disk Usage</th>
                        <td>86.2 GB / 500 GB (17.2%)</td>
                      </tr>
                      <tr>
                        <th>Database Size</th>
                        <td>24.6 MB</td>
                      </tr>
                      <tr>
                        <th>Last Update</th>
                        <td>June 16, 2025</td>
                      </tr>
                      <tr>
                        <th>License</th>
                        <td>Professional Edition (Valid until: Dec 31, 2025)</td>
                      </tr>
                    </table>
                    <div class="mt-4">
                      <button class="btn btn-info">Check for Updates</button>
                      <button class="btn btn-warning">System Diagnostics</button>
                      <button class="btn btn-danger">Clear Cache</button>
                    </div>
                  </div>
                </div>
                <div class="card mt-4">
                  <div class="card-header">
                    <h3 class="card-title">Server Status</h3>
                  </div>
                  <div class="card-body">
                    <div class="row">
                      <div class="col-md-6">
                        <h5>CPU Usage</h5>
                        <div class="progress">
                          <div class="progress-bar bg-primary" role="progressbar" style="width: 15%" aria-valuenow="15" aria-valuemin="0" aria-valuemax="100">15%</div>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <h5>Memory Usage</h5>
                        <div class="progress">
                          <div class="progress-bar bg-success" role="progressbar" style="width: 30%" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100">30%</div>
                        </div>
                      </div>
                    </div>
                    <div class="row mt-4">
                      <div class="col-md-6">
                        <h5>Disk Usage</h5>
                        <div class="progress">
                          <div class="progress-bar bg-warning" role="progressbar" style="width: 17.2%" aria-valuenow="17.2" aria-valuemin="0" aria-valuemax="100">17.2%</div>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <h5>Network Usage</h5>
                        <div class="progress">
                          <div class="progress-bar bg-info" role="progressbar" style="width: 5%" aria-valuenow="5" aria-valuemin="0" aria-valuemax="100">5%</div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- /.content -->  </div>
  <!-- /.content-wrapper -->

<?php
// Additional scripts specific to the settings page
$additional_scripts = <<<EOT
<script>
$(document).ready(function() {
  // Load settings from the server when page loads
  loadGeneralSettings();
  
  // Form submission handlers
  $('#generalSettingsForm').on('submit', function(e) {
    e.preventDefault();
    
    // Get form data
    const formData = {
      laboratory_name: $('#laboratory_name').val(),
      laboratory_address: $('#laboratory_address').val(),
      contact_phone: $('#contact_phone').val(),
      contact_email: $('#contact_email').val()
    };
    
    // Send AJAX request to save settings
    $.ajax({
      url: 'api/settings_api.php',
      type: 'POST',
      data: {
        action: 'save_general_settings',
        settings: formData
      },
      dataType: 'json',
      success: function(response) {
        if (response.success) {
          showAlert('General settings saved successfully!', 'success');
        } else {
          showAlert('Error: ' + response.message, 'danger');
        }
      },
      error: function() {
        showAlert('Server error while saving settings.', 'danger');
      }
    });
  });
  
  $('#notificationSettingsForm').on('submit', function(e) {
    e.preventDefault();
    showAlert('Notification settings saved successfully!', 'success');
  });
  
  $('#appearanceSettingsForm').on('submit', function(e) {
    e.preventDefault();
    showAlert('Appearance settings saved successfully!', 'success');
  });
  
  // File input label update
  $('.custom-file-input').on('change', function() {
    var fileName = $(this).val().split('\\\\').pop();
    $(this).next('.custom-file-label').addClass('selected').html(fileName);
  });
  
  // Helper function to show alerts
  function showAlert(message, type) {
    const alertHtml = `
      <div class="alert alert-\${type} alert-dismissible fade show" role="alert">
        \${message}
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
  
  // Function to load general settings from the server
  function loadGeneralSettings() {
    $.ajax({
      url: 'api/settings_api.php',
      type: 'GET',
      data: { action: 'get_general_settings' },
      dataType: 'json',
      success: function(response) {
        if (response.success) {
          const settings = response.data;
          
          // Populate form fields with settings from server
          $('#laboratory_name').val(settings.laboratory_name || '');
          $('#laboratory_address').val(settings.laboratory_address || '');
          $('#contact_phone').val(settings.contact_phone || '');
          $('#contact_email').val(settings.contact_email || '');
        } else {
          console.error('Error loading settings:', response.message);
        }
      },
      error: function() {
        console.error('Server error while loading settings.');
      }
    });
  }
});
</script>
EOT;

// Include footer with all the necessary scripts
include 'includes/footer.php';
?>
