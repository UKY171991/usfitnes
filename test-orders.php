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

        <!-- Quick Stats Row -->
        <div class="row mb-3">
          <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
              <div class="inner">
                <h3>24</h3>
                <p>Pending Orders</p>
              </div>
              <div class="icon">
                <i class="fas fa-clock"></i>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
              <div class="inner">
                <h3>12</h3>
                <p>In Progress</p>
              </div>
              <div class="icon">
                <i class="fas fa-play"></i>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
              <div class="inner">
                <h3>48</h3>
                <p>Completed Today</p>
              </div>
              <div class="icon">
                <i class="fas fa-check"></i>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
              <div class="inner">
                <h3>3</h3>
                <p>STAT Orders</p>
              </div>
              <div class="icon">
                <i class="fas fa-exclamation-triangle"></i>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Controls Row -->
        <div class="row mb-3">
          <div class="col-md-6 mb-2 mb-md-0">
            <div class="input-group">
              <input type="text" class="form-control" id="searchInput" placeholder="Search test orders...">
              <div class="input-group-append">
                <button class="btn btn-outline-secondary" type="button" id="searchBtn" title="Search">
                  <i class="fas fa-search"></i>
                </button>
              </div>
            </div>
          </div>
          <div class="col-md-3 mb-2 mb-md-0">
            <button class="btn btn-info w-100" id="refreshBtn" title="Refresh Table">
              <i class="fas fa-sync-alt"></i> Refresh
            </button>
          </div>
          <div class="col-md-3 text-md-right">
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-add-order" title="Add New Test Order">
              <i class="fas fa-plus"></i> Add New Test Order
            </button>
          </div>
        </div>

        <!-- Filter Controls -->
        <div class="row mb-3">
          <div class="col-md-3">
            <select class="form-control" id="statusFilter">
              <option value="">All Status</option>
              <option value="Pending">Pending</option>
              <option value="Sample_Collected">Sample Collected</option>
              <option value="In_Progress">In Progress</option>
              <option value="Completed">Completed</option>
            </select>
          </div>
          <div class="col-md-3">
            <select class="form-control" id="priorityFilter">
              <option value="">All Priorities</option>
              <option value="Normal">Normal</option>
              <option value="Urgent">Urgent</option>
              <option value="STAT">STAT</option>
            </select>
          </div>
          <div class="col-md-3">
            <input type="date" class="form-control" id="dateFilter" placeholder="Filter by date">
          </div>
          <div class="col-md-3">
            <button class="btn btn-info btn-block" onclick="applyFilters()" title="Apply Filters">
              <i class="fas fa-filter"></i> Apply Filters
            </button>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <h3 class="card-title"><i class="fas fa-clipboard-list mr-2"></i>Test Orders</h3>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table id="ordersTable" class="table table-bordered table-striped table-hover" style="width:100%">
                <thead>
                  <tr>
                    <th>Order ID</th>
                    <th>Patient</th>
                    <th>Test Name</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Order Date</th>
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

  <!-- Add Order Modal -->
  <div class="modal fade" id="modal-add-order" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <h4 class="modal-title text-white"><i class="fas fa-plus mr-2"></i>New Test Order</h4>
          <button type="button" class="close text-white" data-dismiss="modal">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="addOrderForm">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="patientSelect">Patient <span class="text-danger">*</span></label>
                  <select class="form-control select2" id="patientSelect" name="patient_id" required>
                    <option value="">Select Patient</option>
                    <option value="1">John Doe</option>
                    <option value="2">Jane Smith</option>
                    <option value="3">Mike Johnson</option>
                    <option value="4">Sarah Wilson</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="testSelect">Lab Test <span class="text-danger">*</span></label>
                  <select class="form-control select2" id="testSelect" name="test_id" required>
                    <option value="">Select Test</option>
                    <option value="1">Complete Blood Count</option>
                    <option value="2">Liver Function Test</option>
                    <option value="3">Blood Glucose</option>
                    <option value="4">Urine Analysis</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
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
              <div class="col-md-6">
                <div class="form-group">
                  <label for="orderDate">Order Date <span class="text-danger">*</span></label>
                  <input type="date" class="form-control" id="orderDate" name="order_date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label for="instructions">Special Instructions</label>
              <textarea class="form-control" id="instructions" name="instructions" rows="3" placeholder="Any special instructions for the test"></textarea>
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

  <!-- Modals for Add/Edit/View -->
  <div class="modal fade" id="orderModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="orderModalTitle">Order</h5>
          <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body">
          <form id="orderForm">
            <input type="hidden" id="orderId" name="id">
            <div class="form-group">
              <label for="order_id">Order ID</label>
              <input type="text" class="form-control" id="order_id" name="order_id" required>
            </div>
            <div class="form-group">
              <label for="patient_name">Patient Name</label>
              <input type="text" class="form-control" id="patient_name" name="patient_name" required>
            </div>
            <div class="form-group">
              <label for="test_name">Test Name</label>
              <input type="text" class="form-control" id="test_name" name="test_name" required>
            </div>
            <div class="form-group">
              <label for="priority">Priority</label>
              <select class="form-control" id="priority" name="priority" required>
                <option value="Normal">Normal</option>
                <option value="Urgent">Urgent</option>
                <option value="STAT">STAT</option>
              </select>
            </div>
            <div class="form-group">
              <label for="status">Status</label>
              <select class="form-control" id="status" name="status" required>
                <option value="Pending">Pending</option>
                <option value="Sample_Collected">Sample Collected</option>
                <option value="In_Progress">In Progress</option>
                <option value="Completed">Completed</option>
              </select>
            </div>
            <div class="form-group">
              <label for="order_date">Order Date</label>
              <input type="date" class="form-control" id="order_date" name="order_date" required>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" id="saveOrderBtn">Save</button>
        </div>
      </div>
    </div>
  </div>

<?php
// Additional scripts specific to the test orders page
$additional_scripts = <<<EOT
<script>
$(document).ready(function() {
  const table = $('#ordersTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: 'api/test_orders_api.php',
      type: 'GET',
      dataType: 'json',
      dataSrc: function(json) {
        if (json.data !== undefined) {
          return json.data;
        } else {
          showAlert('error', 'Error loading orders: ' + (json.message || 'Unknown error'));
          return [];
        }
      },
      error: function(xhr, error, thrown) {
        let msg = 'Unknown error';
        try { msg = xhr.responseText ? xhr.responseText : error; } catch (e) {}
        showAlert('error', 'Error loading orders: ' + msg);
        console.error('DataTables AJAX error:', msg);
      }
    },
    columns: [
      { data: 'order_id', render: data => `<span class="badge badge-primary">${data}</span>` },
      { data: 'patient_name' },
      { data: 'test_name' },
      { data: 'priority', render: data => `<span class="badge badge-${data === 'STAT' ? 'danger' : (data === 'Urgent' ? 'warning' : 'secondary')}">${data}</span>` },
      { data: 'status', render: data => `<span class="badge badge-${data === 'Completed' ? 'success' : (data === 'In_Progress' ? 'warning' : (data === 'Sample_Collected' ? 'info' : 'secondary'))}">${data.replace('_', ' ')}</span>` },
      { data: 'order_date', render: data => new Date(data).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) },
      { data: null, orderable: false, searchable: false, render: function(data, type, row) {
        return `
          <div class="btn-group" role="group">
            <button class="btn btn-sm btn-info btn-view-order" data-id="${row.id}" title="View Order"><i class="fas fa-eye"></i></button>
            <button class="btn btn-sm btn-warning btn-edit-order" data-id="${row.id}" title="Edit Order"><i class="fas fa-edit"></i></button>
            <button class="btn btn-sm btn-success btn-update-status" data-id="${row.id}" title="Update Status"><i class="fas fa-clipboard-check"></i></button>
            <button class="btn btn-sm btn-danger btn-delete-order" data-id="${row.id}" title="Delete Order"><i class="fas fa-trash"></i></button>
          </div>`;
      }}
    ]
  });

  // Add Order
  $('#saveOrderBtn').click(function() {
    const form = $('#orderForm')[0];
    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }
    const data = Object.fromEntries(new FormData(form).entries());
    const isEdit = !!data.id;
    $.ajax({
      url: 'api/test_orders_api.php',
      type: isEdit ? 'PUT' : 'POST',
      contentType: 'application/json',
      data: JSON.stringify(data),
      dataType: 'json',
      success: function(response) {
        if (response.success) {
          showAlert('success', response.message);
          $('#orderModal').modal('hide');
          table.ajax.reload();
        } else {
          showAlert('error', response.message);
        }
      },
      error: function(xhr) {
        showAlert('error', xhr.responseText || 'Unknown error');
      }
    });
  });

  // Open Add Modal
  $(document).on('click', '[data-target="#modal-add-order"]', function() {
    $('#orderModalTitle').text('Add Test Order');
    $('#orderForm')[0].reset();
    $('#orderId').val('');
    $('#orderModal').modal('show');
  });

  // Edit Order
  $('#ordersTable').on('click', '.btn-edit-order', function() {
    const id = $(this).data('id');
    $.get('api/test_orders_api.php', { id }, function(response) {
      if (response.success) {
        const d = response.data;
        $('#orderModalTitle').text('Edit Test Order');
        $('#orderId').val(d.id);
        $('#order_id').val(d.order_id);
        $('#patient_name').val(d.patient_name);
        $('#test_name').val(d.test_name);
        $('#priority').val(d.priority);
        $('#status').val(d.status);
        $('#order_date').val(d.order_date);
        $('#orderModal').modal('show');
      } else {
        showAlert('error', response.message);
      }
    }, 'json');
  });

  // Delete Order
  $('#ordersTable').on('click', '.btn-delete-order', function() {
    const id = $(this).data('id');
    if (!confirm('Are you sure you want to delete this order?')) return;
    $.ajax({
      url: 'api/test_orders_api.php',
      type: 'DELETE',
      contentType: 'application/json',
      data: JSON.stringify({ id }),
      dataType: 'json',
      success: function(response) {
        if (response.success) {
          showAlert('success', response.message);
          table.ajax.reload();
        } else {
          showAlert('error', response.message);
        }
      },
      error: function(xhr) {
        showAlert('error', xhr.responseText || 'Unknown error');
      }
    });
  });

  // View Order
  $('#ordersTable').on('click', '.btn-view-order', function() {
    const id = $(this).data('id');
    $.get('api/test_orders_api.php', { id }, function(response) {
      if (response.success) {
        const d = response.data;
        let html = `<strong>Order ID:</strong> ${d.order_id}<br>
                    <strong>Patient:</strong> ${d.patient_name}<br>
                    <strong>Test Name:</strong> ${d.test_name}<br>
                    <strong>Priority:</strong> ${d.priority}<br>
                    <strong>Status:</strong> ${d.status}<br>
                    <strong>Order Date:</strong> ${d.order_date}`;
        $('#orderModalTitle').text('View Test Order');
        $('#orderForm')[0].reset();
        $('#orderForm input, #orderForm select').prop('disabled', true);
        $('#orderForm').html(html);
        $('#saveOrderBtn').hide();
        $('#orderModal').modal('show');
      } else {
        showAlert('error', response.message);
      }
    }, 'json');
  });

  // Reset modal on close
  $('#orderModal').on('hidden.bs.modal', function() {
    $('#orderForm input, #orderForm select').prop('disabled', false);
    $('#saveOrderBtn').show();
    // Restore form fields if needed
    $('#orderForm').html(`
      <input type="hidden" id="orderId" name="id">
      <div class="form-group">
        <label for="order_id">Order ID</label>
        <input type="text" class="form-control" id="order_id" name="order_id" required>
      </div>
      <div class="form-group">
        <label for="patient_name">Patient Name</label>
        <input type="text" class="form-control" id="patient_name" name="patient_name" required>
      </div>
      <div class="form-group">
        <label for="test_name">Test Name</label>
        <input type="text" class="form-control" id="test_name" name="test_name" required>
      </div>
      <div class="form-group">
        <label for="priority">Priority</label>
        <select class="form-control" id="priority" name="priority" required>
          <option value="Normal">Normal</option>
          <option value="Urgent">Urgent</option>
          <option value="STAT">STAT</option>
        </select>
      </div>
      <div class="form-group">
        <label for="status">Status</label>
        <select class="form-control" id="status" name="status" required>
          <option value="Pending">Pending</option>
          <option value="Sample_Collected">Sample Collected</option>
          <option value="In_Progress">In Progress</option>
          <option value="Completed">Completed</option>
        </select>
      </div>
      <div class="form-group">
        <label for="order_date">Order Date</label>
        <input type="date" class="form-control" id="order_date" name="order_date" required>
      </div>
    `);
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
    <div class="alert ${alertClass} alert-dismissible">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
      <i class="icon ${icon}"></i> ${message}
    </div>
  `;
  document.getElementById('alertContainer').innerHTML = alert;
  setTimeout(() => {
    const el = document.querySelector('#alertContainer .alert');
    if (el) el.style.display = 'none';
  }, 5000);
}
</script>
EOT;

// Include footer
include 'includes/footer.php';
?>
