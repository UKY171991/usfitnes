<?php
// Set page title
$page_title = 'Test Orders';

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
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <!-- Alert Messages -->
        <div id="alertContainer"></div>
        
        <!-- Controls Row -->
        <div class="row mb-3">
          <div class="col-md-6">
            <div class="input-group">
              <input type="text" class="form-control" id="searchInput" placeholder="Search test orders...">
              <div class="input-group-append">
                <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                  <i class="fas fa-search"></i>
                </button>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <button class="btn btn-info w-100" id="refreshBtn">
              <i class="fas fa-sync-alt"></i> Refresh
            </button>
          </div>
          <div class="col-md-3">
            <button type="button" class="btn btn-primary w-100" id="addOrderBtn">
              <i class="fas fa-plus"></i> Add New Order
            </button>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <h3 class="card-title"><i class="fas fa-clipboard-list mr-2"></i>Test Orders</h3>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table id="ordersTable" class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>Order ID</th>
                    <th>Patient</th>
                    <th>Tests</th>
                    <th>Date</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <!-- Add/Edit Order Modal -->
  <div class="modal fade" id="orderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <h4 class="modal-title text-white" id="orderModalTitle">
            <i class="fas fa-plus mr-2"></i>Add Test Order
          </h4>
          <button type="button" class="close text-white" data-dismiss="modal">
            <span>&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="orderForm">
            <input type="hidden" id="orderId" name="id">
            
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
                  <label for="orderDate">Order Date <span class="text-danger">*</span></label>
                  <input type="date" class="form-control" id="orderDate" name="order_date" required>
                </div>
              </div>
            </div>
            
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="testSelect">Lab Tests <span class="text-danger">*</span></label>
                  <select class="form-control" id="testSelect" name="test_ids" multiple required>
                    <option value="">Select Tests</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="priority">Priority <span class="text-danger">*</span></label>
                  <select class="form-control" id="priority" name="priority" required>
                    <option value="">Select Priority</option>
                    <option value="Normal">Normal</option>
                    <option value="Urgent">Urgent</option>
                    <option value="STAT">STAT</option>
                  </select>
                </div>
              </div>
            </div>
            
            <div class="form-group">
              <label for="instructions">Instructions</label>
              <textarea class="form-control" id="instructions" name="instructions" rows="3" placeholder="Any special instructions"></textarea>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="saveOrderBtn">
            <i class="fas fa-save mr-1"></i>Save Order
          </button>
        </div>
      </div>
    </div>
  </div>

<?php
$additional_scripts = <<<EOT
<script>
$(document).ready(function() {
  let ordersTable;
  let patients = [];
  let tests = [];
  
  // Initialize DataTable
  function initTable() {
    ordersTable = $('#ordersTable').DataTable({
      ajax: {
        url: 'api/test_orders_api.php',
        dataSrc: function(json) {
          if (json.success) {
            return json.data;
          } else {
            showAlert('error', 'Failed to load orders: ' + json.message);
            return [];
          }
        },
        error: function(xhr, error, thrown) {
          showAlert('error', 'Failed to load orders: ' + error);
          console.error('DataTables error:', error);
        }
      },
      columns: [
        { data: 'order_id' },
        { data: 'patient_name' },
        { 
          data: 'items',
          render: function(data) {
            if (data && data.length > 0) {
              return data.map(item => item.test_name).join(', ');
            }
            return 'No tests';
          }
        },
        { 
          data: 'order_date',
          render: function(data) {
            return new Date(data).toLocaleDateString();
          }
        },
        { 
          data: 'priority',
          render: function(data) {
            let badgeClass = data === 'STAT' ? 'danger' : (data === 'Urgent' ? 'warning' : 'info');
            return `<span class="badge badge-\${badgeClass}">\${data}</span>`;
          }
        },
        { 
          data: 'status',
          render: function(data) {
            let badgeClass = data === 'Completed' ? 'success' : 
                           data === 'In Progress' ? 'warning' : 'secondary';
            return `<span class="badge badge-\${badgeClass}">\${data}</span>`;
          }
        },
        {
          data: null,
          orderable: false,
          render: function(data, type, row) {
            return `
              <div class="btn-group">
                <button class="btn btn-sm btn-warning btn-edit" data-id="\${row.id}" title="Edit">
                  <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger btn-delete" data-id="\${row.id}" title="Delete">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
            `;
          }
        }
      ],
      responsive: true,
      pageLength: 25
    });
  }

  // Load patients and tests
  function loadOptions() {
    // Load patients
    $.get('api/patients_api.php', function(response) {
      if (response.success) {
        patients = response.data;
        let options = '<option value="">Select Patient</option>';
        patients.forEach(patient => {
          options += `<option value="\${patient.id}">\${patient.full_name}</option>`;
        });
        $('#patientSelect').html(options);
      }
    });

    // Load tests
    $.get('api/tests_api.php', function(response) {
      if (response.success) {
        tests = response.data;
        let options = '<option value="">Select Tests</option>';
        tests.forEach(test => {
          options += `<option value="\${test.id}">\${test.test_name} - $\${test.price}</option>`;
        });
        $('#testSelect').html(options);
      }
    });
  }

  // Initialize
  initTable();
  loadOptions();

  // Set default date
  $('#orderDate').val(new Date().toISOString().split('T')[0]);

  // Add new order
  $('#addOrderBtn').click(function() {
    $('#orderModalTitle').html('<i class="fas fa-plus mr-2"></i>Add Test Order');
    $('#orderForm')[0].reset();
    $('#orderId').val('');
    $('#orderDate').val(new Date().toISOString().split('T')[0]);
    $('#orderModal').modal('show');
  });

  // Save order
  $('#saveOrderBtn').click(function() {
    if (!$('#orderForm')[0].checkValidity()) {
      $('#orderForm')[0].reportValidity();
      return;
    }

    let formData = new FormData($('#orderForm')[0]);
    let data = Object.fromEntries(formData.entries());
    
    // Handle multiple test selection
    let selectedTests = Array.from($('#testSelect')[0].selectedOptions).map(option => option.value);
    data.test_ids = selectedTests;

    let isEdit = !!data.id;
    
    $.ajax({
      url: 'api/test_orders_api.php',
      type: isEdit ? 'PUT' : 'POST',
      contentType: 'application/json',
      data: JSON.stringify(data),
      success: function(response) {
        if (response.success) {
          showAlert('success', response.message);
          $('#orderModal').modal('hide');
          ordersTable.ajax.reload();
        } else {
          showAlert('error', response.message);
        }
      },
      error: function(xhr) {
        showAlert('error', 'Error saving order: ' + xhr.responseText);
      }
    });
  });

  // Edit order
  $('#ordersTable').on('click', '.btn-edit', function() {
    let id = $(this).data('id');
    
    // Find the order data in the table
    let rowData = ordersTable.row($(this).closest('tr')).data();
    
    $('#orderModalTitle').html('<i class="fas fa-edit mr-2"></i>Edit Test Order');
    $('#orderId').val(rowData.id);
    $('#patientSelect').val(rowData.patient_id);
    $('#orderDate').val(rowData.order_date);
    $('#priority').val(rowData.priority);
    $('#instructions').val(rowData.instructions);
    
    // Set selected tests
    if (rowData.items && rowData.items.length > 0) {
      let testIds = rowData.items.map(item => item.test_id);
      $('#testSelect').val(testIds);
    }
    
    $('#orderModal').modal('show');
  });

  // Delete order
  $('#ordersTable').on('click', '.btn-delete', function() {
    let id = $(this).data('id');
    
    if (confirm('Are you sure you want to delete this order?')) {
      $.ajax({
        url: 'api/test_orders_api.php',
        type: 'DELETE',
        contentType: 'application/json',
        data: JSON.stringify({ id: id }),
        success: function(response) {
          if (response.success) {
            showAlert('success', response.message);
            ordersTable.ajax.reload();
          } else {
            showAlert('error', response.message);
          }
        },
        error: function(xhr) {
          showAlert('error', 'Error deleting order: ' + xhr.responseText);
        }
      });
    }
  });

  // Refresh table
  $('#refreshBtn').click(function() {
    ordersTable.ajax.reload();
  });

  // Search functionality
  $('#searchInput').on('keyup', function() {
    ordersTable.search(this.value).draw();
  });
});

function showAlert(type, message) {
  const alertClass = type === 'success' ? 'alert-success' :
                    type === 'error' ? 'alert-danger' :
                    type === 'warning' ? 'alert-warning' : 'alert-info';
  const icon = type === 'success' ? 'fas fa-check' :
               type === 'error' ? 'fas fa-ban' :
               type === 'warning' ? 'fas fa-exclamation-triangle' : 'fas fa-info-circle';
  const alert = `
    <div class="alert \${alertClass} alert-dismissible">
      <button type="button" class="close" data-dismiss="alert">&times;</button>
      <i class="icon \${icon}"></i> \${message}
    </div>
  `;
  $('#alertContainer').html(alert);
  setTimeout(() => {
    $('#alertContainer .alert').fadeOut();
  }, 5000);
}
</script>
EOT;

// Include footer
include 'includes/footer.php';
?>
