<?php
// Set page title
$page_title = 'AdminLTE3 Test Page - PathLab Pro';

// Include AdminLTE header and sidebar
include 'includes/adminlte_header.php';
include 'includes/adminlte_sidebar.php';
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
  <!-- Content Header -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">
            <i class="fas fa-flask mr-2 text-primary"></i>AdminLTE3 Test Page
          </h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
            <li class="breadcrumb-item active">Test Page</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      
      <!-- Alert Example -->
      <div class="row">
        <div class="col-12">
          <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-check"></i> Success!</h5>
            AdminLTE3 layout has been successfully implemented for PathLab Pro.
          </div>
        </div>
      </div>

      <!-- Info Boxes Row -->
      <div class="row">
        <div class="col-lg-3 col-6">
          <div class="info-box">
            <span class="info-box-icon bg-info"><i class="fas fa-cog"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Components</span>
              <span class="info-box-number">25+</span>
            </div>
          </div>
        </div>
        
        <div class="col-lg-3 col-6">
          <div class="info-box">
            <span class="info-box-icon bg-success"><i class="far fa-flag"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Features</span>
              <span class="info-box-number">100%</span>
            </div>
          </div>
        </div>
        
        <div class="col-lg-3 col-6">
          <div class="info-box">
            <span class="info-box-icon bg-warning"><i class="far fa-copy"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Pages</span>
              <span class="info-box-number">8</span>
            </div>
          </div>
        </div>
        
        <div class="col-lg-3 col-6">
          <div class="info-box">
            <span class="info-box-icon bg-danger"><i class="far fa-star"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Rating</span>
              <span class="info-box-number">5.0</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Cards Row -->
      <div class="row">
        <!-- Button Examples -->
        <div class="col-md-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-mouse mr-1"></i>
                Button Examples
              </h3>
            </div>
            <div class="card-body">
              <button type="button" class="btn btn-primary">Primary</button>
              <button type="button" class="btn btn-secondary">Secondary</button>
              <button type="button" class="btn btn-success">Success</button>
              <button type="button" class="btn btn-info">Info</button>
              <button type="button" class="btn btn-warning">Warning</button>
              <button type="button" class="btn btn-danger">Danger</button>
              
              <hr>
              
              <button type="button" class="btn btn-outline-primary">Outline Primary</button>
              <button type="button" class="btn btn-outline-success">Outline Success</button>
              <button type="button" class="btn btn-outline-danger">Outline Danger</button>
            </div>
          </div>
        </div>

        <!-- Form Examples -->
        <div class="col-md-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-edit mr-1"></i>
                Form Examples
              </h3>
            </div>
            <div class="card-body">
              <form>
                <div class="form-group">
                  <label for="testInput">Text Input</label>
                  <input type="text" class="form-control" id="testInput" placeholder="Enter text">
                </div>
                
                <div class="form-group">
                  <label for="testSelect">Select Dropdown</label>
                  <select class="form-control select2" id="testSelect">
                    <option>Option 1</option>
                    <option>Option 2</option>
                    <option>Option 3</option>
                  </select>
                </div>
                
                <div class="form-group">
                  <label for="testDate">Date Picker</label>
                  <input type="text" class="form-control datepicker" id="testDate">
                </div>
                
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" id="testCheck">
                  <label class="form-check-label" for="testCheck">
                    Check me out
                  </label>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- Notification Test Row -->
      <div class="row">
        <div class="col-md-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-bell mr-1"></i>
                Notification Tests
              </h3>
            </div>
            <div class="card-body">
              <button type="button" class="btn btn-success" onclick="PathLabPro.notifications.success('Success notification!')">
                Test Success
              </button>
              <button type="button" class="btn btn-info" onclick="PathLabPro.notifications.info('Info notification!')">
                Test Info
              </button>
              <button type="button" class="btn btn-warning" onclick="PathLabPro.notifications.warning('Warning notification!')">
                Test Warning
              </button>
              <button type="button" class="btn btn-danger" onclick="PathLabPro.notifications.error('Error notification!')">
                Test Error
              </button>
            </div>
          </div>
        </div>

        <!-- Modal Test -->
        <div class="col-md-6">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-window-maximize mr-1"></i>
                Modal Tests
              </h3>
            </div>
            <div class="card-body">
              <button type="button" class="btn btn-primary" onclick="testAlert()">
                Test Alert
              </button>
              <button type="button" class="btn btn-warning" onclick="testConfirm()">
                Test Confirm
              </button>
              <button type="button" class="btn btn-info" onclick="testLoading()">
                Test Loading
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- DataTable Example -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-table mr-1"></i>
                DataTable Example
              </h3>
            </div>
            <div class="card-body">
              <table class="table table-bordered table-striped datatable">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>1</td>
                    <td>John Doe</td>
                    <td>john@example.com</td>
                    <td><span class="badge badge-primary">Admin</span></td>
                    <td><span class="badge badge-success">Active</span></td>
                    <td>
                      <button class="btn btn-sm btn-info"><i class="fas fa-eye"></i></button>
                      <button class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></button>
                      <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                    </td>
                  </tr>
                  <tr>
                    <td>2</td>
                    <td>Jane Smith</td>
                    <td>jane@example.com</td>
                    <td><span class="badge badge-info">Technician</span></td>
                    <td><span class="badge badge-success">Active</span></td>
                    <td>
                      <button class="btn btn-sm btn-info"><i class="fas fa-eye"></i></button>
                      <button class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></button>
                      <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                    </td>
                  </tr>
                  <tr>
                    <td>3</td>
                    <td>Mike Johnson</td>
                    <td>mike@example.com</td>
                    <td><span class="badge badge-secondary">Doctor</span></td>
                    <td><span class="badge badge-warning">Pending</span></td>
                    <td>
                      <button class="btn btn-sm btn-info"><i class="fas fa-eye"></i></button>
                      <button class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></button>
                      <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

    </div><!-- /.container-fluid -->
  </section>
  <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<script>
// Test functions for modals
function testAlert() {
  PathLabPro.modal.alert({
    title: 'Test Alert',
    text: 'This is a test alert modal using SweetAlert2!',
    icon: 'info'
  });
}

function testConfirm() {
  PathLabPro.modal.confirm({
    title: 'Test Confirmation',
    text: 'Do you want to proceed with this test?',
    confirmButtonText: 'Yes, proceed!'
  }).then((result) => {
    if (result.isConfirmed) {
      PathLabPro.notifications.success('You confirmed the action!');
    }
  });
}

function testLoading() {
  PathLabPro.modal.loading('Processing test...');
  
  // Simulate some processing time
  setTimeout(() => {
    Swal.close();
    PathLabPro.notifications.success('Loading test completed!');
  }, 3000);
}

// Document ready functions
$(document).ready(function() {
  // Test API call
  console.log('Testing API connection...');
  PathLabPro.api.get('get_counts.php')
    .then(data => {
      console.log('API test successful:', data);
    })
    .catch(error => {
      console.log('API test failed:', error);
    });
});
</script>

<?php include 'includes/adminlte_footer.php'; ?>
