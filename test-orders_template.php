<?php
// Set page title
$page_title = 'Test Orders Management - PathLab Pro';

// Include database connection
require_once 'config.php';

// Get action parameter
$action = $_GET['action'] ?? 'list';
$order_id = $_GET['id'] ?? null;

// Get test orders data
$test_orders = [];
try {
    $query = "SELECT t.*, p.first_name, p.last_name, p.phone 
              FROM test_orders t 
              LEFT JOIN patients p ON t.patient_id = p.id 
              WHERE t.status != 'deleted' 
              ORDER BY t.created_at DESC";
    $result = mysqli_query($conn, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $test_orders[] = $row;
        }
    }
} catch (Exception $e) {
    error_log("Test orders query error: " . $e->getMessage());
}

// Include AdminLTE header and sidebar
include 'includes/adminlte_header.php';
include 'includes/adminlte_sidebar.php';
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">
            <i class="fas fa-flask mr-2 text-info"></i>
            Test Orders Management
          </h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item">
              <a href="dashboard.php">
                <i class="fas fa-home"></i> Home
              </a>
            </li>
            <li class="breadcrumb-item active">Test Orders</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      
      <?php if ($action === 'list'): ?>
      <!-- List View -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-list mr-1"></i>
                All Test Orders
              </h3>
              <div class="card-tools">
                <a href="?action=add" class="btn btn-primary btn-sm">
                  <i class="fas fa-plus mr-1"></i>Create New Order
                </a>
              </div>
            </div>
            <div class="card-body">
              <?php if (empty($test_orders)): ?>
                <div class="text-center p-4">
                  <i class="fas fa-flask fa-3x text-muted mb-3"></i>
                  <h5 class="text-muted">No Test Orders Found</h5>
                  <p class="text-muted">Start by creating your first test order.</p>
                  <a href="?action=add" class="btn btn-primary">
                    <i class="fas fa-plus mr-2"></i>Create First Order
                  </a>
                </div>
              <?php else: ?>
                <table id="testOrdersTable" class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th>Order ID</th>
                      <th>Patient</th>
                      <th>Test Type</th>
                      <th>Status</th>
                      <th>Order Date</th>
                      <th>Priority</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($test_orders as $order): ?>
                    <tr>
                      <td><strong>#<?php echo htmlspecialchars($order['id'] ?? ''); ?></strong></td>
                      <td>
                        <div>
                          <strong><?php echo htmlspecialchars(($order['first_name'] ?? '') . ' ' . ($order['last_name'] ?? '')); ?></strong>
                          <br>
                          <small class="text-muted"><?php echo htmlspecialchars($order['phone'] ?? ''); ?></small>
                        </div>
                      </td>
                      <td><?php echo htmlspecialchars($order['test_type'] ?? ''); ?></td>
                      <td>
                        <?php
                        $status = $order['status'] ?? 'pending';
                        $badge_classes = [
                            'pending' => 'badge-warning',
                            'in_progress' => 'badge-info',
                            'completed' => 'badge-success',
                            'cancelled' => 'badge-danger'
                        ];
                        $badge_class = $badge_classes[$status] ?? 'badge-secondary';
                        echo "<span class=\"badge {$badge_class}\">" . ucfirst(str_replace('_', ' ', $status)) . "</span>";
                        ?>
                      </td>
                      <td><?php echo $order['created_at'] ? date('M d, Y H:i', strtotime($order['created_at'])) : ''; ?></td>
                      <td>
                        <?php
                        $priority = $order['priority'] ?? 'normal';
                        $priority_classes = [
                            'urgent' => 'badge-danger',
                            'high' => 'badge-warning',
                            'normal' => 'badge-info',
                            'low' => 'badge-secondary'
                        ];
                        $priority_class = $priority_classes[$priority] ?? 'badge-info';
                        echo "<span class=\"badge {$priority_class}\">" . ucfirst($priority) . "</span>";
                        ?>
                      </td>
                      <td>
                        <div class="btn-group">
                          <a href="?action=view&id=<?php echo $order['id']; ?>" class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i>
                          </a>
                          <a href="?action=edit&id=<?php echo $order['id']; ?>" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i>
                          </a>
                          <button class="btn btn-success btn-sm" onclick="processOrder(<?php echo $order['id']; ?>)">
                            <i class="fas fa-play"></i>
                          </button>
                          <button class="btn btn-danger btn-sm" onclick="cancelOrder(<?php echo $order['id']; ?>)">
                            <i class="fas fa-times"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      
      <?php elseif ($action === 'add' || $action === 'edit'): ?>
      <!-- Add/Edit Form -->
      <div class="row">
        <div class="col-md-10 offset-md-1">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-<?php echo $action === 'add' ? 'plus' : 'edit'; ?> mr-1"></i>
                <?php echo $action === 'add' ? 'Create New' : 'Edit'; ?> Test Order
              </h3>
              <div class="card-tools">
                <a href="?" class="btn btn-secondary btn-sm">
                  <i class="fas fa-arrow-left mr-1"></i>Back to List
                </a>
              </div>
            </div>
            <form id="testOrderForm" method="POST">
              <div class="card-body">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="patient_id">Patient <span class="text-danger">*</span></label>
                      <select class="form-control select2" id="patient_id" name="patient_id" required>
                        <option value="">Select Patient</option>
                        <!-- Options would be populated from database -->
                      </select>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="test_type">Test Type <span class="text-danger">*</span></label>
                      <select class="form-control select2" id="test_type" name="test_type" required>
                        <option value="">Select Test Type</option>
                        <option value="Blood Test">Blood Test</option>
                        <option value="Urine Test">Urine Test</option>
                        <option value="X-Ray">X-Ray</option>
                        <option value="MRI">MRI</option>
                        <option value="CT Scan">CT Scan</option>
                        <option value="ECG">ECG</option>
                        <option value="Ultrasound">Ultrasound</option>
                      </select>
                    </div>
                  </div>
                </div>
                
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="priority">Priority <span class="text-danger">*</span></label>
                      <select class="form-control" id="priority" name="priority" required>
                        <option value="normal">Normal</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                        <option value="low">Low</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="doctor_id">Referring Doctor</label>
                      <select class="form-control select2" id="doctor_id" name="doctor_id">
                        <option value="">Select Doctor</option>
                        <!-- Options would be populated from database -->
                      </select>
                    </div>
                  </div>
                </div>
                
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="test_date">Scheduled Test Date</label>
                      <input type="datetime-local" class="form-control" id="test_date" name="test_date">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="estimated_cost">Estimated Cost</label>
                      <div class="input-group">
                        <div class="input-group-prepend">
                          <span class="input-group-text">$</span>
                        </div>
                        <input type="number" class="form-control" id="estimated_cost" name="estimated_cost" step="0.01">
                      </div>
                    </div>
                  </div>
                </div>
                
                <div class="form-group">
                  <label for="instructions">Special Instructions</label>
                  <textarea class="form-control" id="instructions" name="instructions" rows="3" 
                           placeholder="Any special instructions for the test..."></textarea>
                </div>
                
                <div class="form-group">
                  <label for="notes">Internal Notes</label>
                  <textarea class="form-control" id="notes" name="notes" rows="3" 
                           placeholder="Internal notes for staff..."></textarea>
                </div>
              </div>
              
              <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-save mr-1"></i>
                  <?php echo $action === 'add' ? 'Create' : 'Update'; ?> Order
                </button>
                <a href="?" class="btn btn-secondary">
                  <i class="fas fa-times mr-1"></i>Cancel
                </a>
              </div>
            </form>
          </div>
        </div>
      </div>
      
      <?php elseif ($action === 'view'): ?>
      <!-- View Order Details -->
      <div class="row">
        <div class="col-md-10 offset-md-1">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-flask mr-1"></i>
                Test Order Details - #<?php echo $order_id; ?>
              </h3>
              <div class="card-tools">
                <a href="?" class="btn btn-secondary btn-sm">
                  <i class="fas fa-arrow-left mr-1"></i>Back to List
                </a>
                <a href="?action=edit&id=<?php echo $order_id; ?>" class="btn btn-warning btn-sm">
                  <i class="fas fa-edit mr-1"></i>Edit
                </a>
                <button class="btn btn-primary btn-sm" onclick="printOrder()">
                  <i class="fas fa-print mr-1"></i>Print
                </button>
              </div>
            </div>
            <div class="card-body">
              <!-- Order details would be loaded here -->
              <div class="alert alert-info">
                <i class="fas fa-info-circle mr-2"></i>
                Test order details will be displayed here when integrated with the database.
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>
      
    </div><!-- /.container-fluid -->
  </section>
  <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<script>
$(document).ready(function() {
    // Initialize DataTable for test orders list
    if ($('#testOrdersTable').length) {
        $('#testOrdersTable').DataTable({
            responsive: true,
            autoWidth: false,
            pageLength: 25,
            order: [[0, 'desc']], // Order by ID descending
            columnDefs: [
                { 
                    targets: -1, // Last column (Actions)
                    orderable: false,
                    searchable: false
                }
            ]
        });
    }
    
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });
    
    // Form validation
    $('#testOrderForm').on('submit', function(e) {
        e.preventDefault();
        
        // Basic validation
        let isValid = true;
        $(this).find('[required]').each(function() {
            if (!$(this).val()) {
                isValid = false;
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        if (isValid) {
            // Submit form (you would typically send this to a handler)
            PathLabPro.notifications.success('Test order saved successfully!');
            // window.location.href = '?';
        } else {
            PathLabPro.notifications.error('Please fill in all required fields.');
        }
    });
});

function processOrder(id) {
    PathLabPro.modal.confirm({
        title: 'Process Order',
        text: 'Mark this order as in progress?',
        confirmButtonText: 'Yes, process it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Process order logic here
            PathLabPro.notifications.success('Order marked as in progress!');
        }
    });
}

function cancelOrder(id) {
    PathLabPro.modal.confirm({
        title: 'Cancel Order',
        text: 'Are you sure you want to cancel this order?',
        confirmButtonText: 'Yes, cancel it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Cancel order logic here
            PathLabPro.notifications.success('Order cancelled successfully!');
        }
    });
}

function printOrder() {
    // Print functionality
    window.print();
}
</script>

<?php include 'includes/adminlte_footer.php'; ?>
