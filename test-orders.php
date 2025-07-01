<?php
// Set page title
$page_title = 'Test Orders';

// Include header
include 'includes/header.php';

// Include sidebar with user info
include 'includes/sidebar.php';

// Sample data for test orders
$test_orders = [
    ['id' => 1, 'order_id' => 'ORD001', 'patient_name' => 'John Doe', 'test_name' => 'Complete Blood Count', 'priority' => 'Normal', 'status' => 'Pending', 'order_date' => '2025-06-18'],
    ['id' => 2, 'order_id' => 'ORD002', 'patient_name' => 'Jane Smith', 'test_name' => 'Liver Function Test', 'priority' => 'Urgent', 'status' => 'Sample_Collected', 'order_date' => '2025-06-18'],
    ['id' => 3, 'order_id' => 'ORD003', 'patient_name' => 'Mike Johnson', 'test_name' => 'Blood Glucose', 'priority' => 'STAT', 'status' => 'In_Progress', 'order_date' => '2025-06-17'],
    ['id' => 4, 'order_id' => 'ORD004', 'patient_name' => 'Sarah Wilson', 'test_name' => 'Urine Analysis', 'priority' => 'Normal', 'status' => 'Completed', 'order_date' => '2025-06-17'],
];
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
              <table id="ordersTable" class="table table-bordered table-striped table-hover">
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
                <tbody>
                  <?php foreach($test_orders as $order): ?>
                  <tr>
                    <td><span class="badge badge-primary"><?php echo htmlspecialchars($order['order_id']); ?></span></td>
                    <td><?php echo htmlspecialchars($order['patient_name']); ?></td>
                    <td><?php echo htmlspecialchars($order['test_name']); ?></td>
                    <td>
                      <?php 
                      $priority_class = $order['priority'] === 'STAT' ? 'badge-danger' : 
                                       ($order['priority'] === 'Urgent' ? 'badge-warning' : 'badge-secondary');
                      ?>
                      <span class="badge <?php echo $priority_class; ?>"><?php echo htmlspecialchars($order['priority']); ?></span>
                    </td>
                    <td>
                      <?php 
                      $status_class = $order['status'] === 'Completed' ? 'badge-success' : 
                                     ($order['status'] === 'In_Progress' ? 'badge-warning' : 
                                     ($order['status'] === 'Sample_Collected' ? 'badge-info' : 'badge-secondary'));
                      ?>
                      <span class="badge <?php echo $status_class; ?>"><?php echo str_replace('_', ' ', $order['status']); ?></span>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                    <td>
                      <div class="btn-group" role="group">
                        <button class="btn btn-sm btn-info btn-view-order" data-id="<?php echo $order['id']; ?>" title="View Order">
                          <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-warning btn-edit-order" data-id="<?php echo $order['id']; ?>" title="Edit Order">
                          <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-success btn-update-status" data-id="<?php echo $order['id']; ?>" title="Update Status">
                          <i class="fas fa-clipboard-check"></i>
                        </button>
                        <button class="btn btn-sm btn-danger btn-delete-order" data-id="<?php echo $order['id']; ?>" title="Delete Order">
                          <i class="fas fa-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
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

<?php
// Additional scripts specific to the test orders page
$additional_scripts = <<<EOT
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#ordersTable').DataTable({
        responsive: true,
        lengthChange: false,
        autoWidth: false,
        order: [[5, 'desc']], // Sort by order date descending
        buttons: [
            {
                extend: 'copy',
                className: 'btn btn-sm btn-secondary'
            },
            {
                extend: 'csv',
                className: 'btn btn-sm btn-secondary'
            },
            {
                extend: 'excel',
                className: 'btn btn-sm btn-secondary'
            },
            {
                extend: 'pdf',
                className: 'btn btn-sm btn-secondary'
            },
            {
                extend: 'print',
                className: 'btn btn-sm btn-secondary'
            }
        ]
    }).buttons().container().appendTo('#ordersTable_wrapper .col-md-6:eq(0)');

    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });    // Handle form submission
    $('#saveOrderBtn').on('click', function() {
        var formData = {
            action: 'create',
            patient_id: $('#patientSelect').val(),
            test_id: $('#testSelect').val(),
            priority: $('#priority').val(),
            order_date: $('#orderDate').val(),
            instructions: $('#instructions').val()
        };

        // Basic validation
        if (!formData.patient_id || !formData.test_id || !formData.priority) {
            showToaster('danger', 'Please fill in all required fields.');
            return;
        }

        // Send data to API
        $.ajax({
            url: 'api/test_orders_api.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showToaster('success', 'Test order created successfully!');
                    $('#modal-add-order').modal('hide');
                    $('#addOrderForm')[0].reset();
                    $('.select2').val(null).trigger('change');
                    loadOrders(); // Reload the orders table
                } else {
                    showToaster('danger', response.message || 'Failed to create test order');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error creating order:', error);
                showToaster('danger', 'Failed to create test order. Please try again.');
            }
        });
    });

    // Handle button clicks
    $(document).on('click', '.btn-view-order', function() {
        var orderId = $(this).data('id');
        // TODO: Implement view functionality
        showToaster('info', 'View order functionality for order ' + orderId + ' will be implemented.');
    });

    $(document).on('click', '.btn-edit-order', function() {
        var orderId = $(this).data('id');
        // TODO: Implement edit functionality
        showToaster('info', 'Edit order functionality for order ' + orderId + ' will be implemented.');
    });

    $(document).on('click', '.btn-update-status', function() {
        var orderId = $(this).data('id');
        // TODO: Implement status update functionality
        showToaster('info', 'Update status functionality for order ' + orderId + ' will be implemented.');
    });

    $(document).on('click', '.btn-delete-order', function() {
        var orderId = $(this).data('id');
        if (confirm('Are you sure you want to delete this order?')) {
            $.ajax({
                url: 'api/test_orders_api.php',
                method: 'POST',
                data: {
                    action: 'delete',
                    id: orderId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showToaster('success', 'Order deleted successfully!');
                        loadOrders(); // Reload the orders table
                    } else {
                        showToaster('danger', response.message || 'Failed to delete order');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error deleting order:', error);
                    showToaster('danger', 'Failed to delete order. Please try again.');
                }
            });
        }
    });

    // Load orders function
    function loadOrders() {
        $.ajax({
            url: 'api/test_orders_api.php?action=list',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Update the orders table with new data
                    // This would need to be implemented based on your table structure
                    console.log('Orders loaded:', response.data);
                } else {
                    showToaster('danger', 'Failed to load orders');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading orders:', error);
                showToaster('danger', 'Failed to load orders');
            }
        });
    }
});

function refreshTable() {
    $('#ordersTable').DataTable().ajax.reload();
    showToaster('info', 'Table refreshed successfully!');
}

function applyFilters() {
    var status = $('#statusFilter').val();
    var priority = $('#priorityFilter').val();
    var date = $('#dateFilter').val();
      // Here you would apply the filters to your DataTable
    showToaster('info', 'Filters applied successfully!');
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
  document.getElementById('alertContainer').innerHTML = alert;
  setTimeout(() => {
    const el = document.querySelector('#alertContainer .alert');
    if (el) el.style.display = 'none';
  }, 5000);
}

document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.btn-view-order').forEach(btn => {
    btn.addEventListener('click', function() {
      showAlert('info', 'View Order: ' + this.dataset.id);
    });
  });
  document.querySelectorAll('.btn-edit-order').forEach(btn => {
    btn.addEventListener('click', function() {
      showAlert('warning', 'Edit Order: ' + this.dataset.id);
    });
  });
  document.querySelectorAll('.btn-update-status').forEach(btn => {
    btn.addEventListener('click', function() {
      showAlert('success', 'Update Status for Order: ' + this.dataset.id);
    });
  });
  document.querySelectorAll('.btn-delete-order').forEach(btn => {
    btn.addEventListener('click', function() {
      if (confirm('Are you sure you want to delete this order?')) {
        showAlert('error', 'Order deleted: ' + this.dataset.id);
      }
    });
  });
  document.getElementById('refreshBtn').addEventListener('click', function() {
    showAlert('info', 'Table refreshed!');
  });
  document.getElementById('searchBtn').addEventListener('click', function() {
    showAlert('info', 'Search: ' + document.getElementById('searchInput').value);
  });
});
</script>
EOT;

// Include footer
include 'includes/footer.php';
?>
