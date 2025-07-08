<?php
$page_title = "Test Orders";
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'add':
                $stmt = $pdo->prepare("INSERT INTO test_orders (patient_id, test_id, doctor_id, priority, status, notes) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['patient_id'],
                    $_POST['test_id'],
                    $_POST['doctor_id'] ?: null,
                    $_POST['priority'],
                    'pending',
                    $_POST['notes'] ?? ''
                ]);
                $_SESSION['success'] = "Test order added successfully!";
                break;
                
            case 'edit':
                $stmt = $pdo->prepare("UPDATE test_orders SET patient_id = ?, test_id = ?, doctor_id = ?, priority = ?, status = ?, notes = ? WHERE id = ?");
                $stmt->execute([
                    $_POST['patient_id'],
                    $_POST['test_id'],
                    $_POST['doctor_id'] ?: null,
                    $_POST['priority'],
                    $_POST['status'],
                    $_POST['notes'] ?? '',
                    $_POST['order_id']
                ]);
                $_SESSION['success'] = "Test order updated successfully!";
                break;
                
            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM test_orders WHERE id = ?");
                $stmt->execute([$_POST['order_id']]);
                $_SESSION['success'] = "Test order deleted successfully!";
                break;
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
    
    header("Location: test-orders.php");
    exit;
}

// Fetch test orders with related data
try {
    $query = "SELECT 
        to.id,
        to.patient_id,
        to.test_id,
        to.doctor_id,
        to.priority,
        to.status,
        to.notes,
        to.order_date,
        p.name as patient_name,
        p.patient_id as patient_mrn,
        t.name as test_name,
        t.type as test_type,
        d.name as doctor_name
    FROM test_orders to
    LEFT JOIN patients p ON to.patient_id = p.id
    LEFT JOIN tests t ON to.test_id = t.id
    LEFT JOIN doctors d ON to.doctor_id = d.id
    ORDER BY to.order_date DESC";
    $test_orders = $pdo->query($query)->fetchAll();
    
    // Fetch patients for dropdown
    $patients = $pdo->query("SELECT id, name, patient_id as mrn FROM patients WHERE status = 'active' ORDER BY name")->fetchAll();
    
    // Fetch tests for dropdown
    $tests = $pdo->query("SELECT id, name, type, price FROM tests WHERE status = 'active' ORDER BY name")->fetchAll();
    
    // Fetch doctors for dropdown
    $doctors = $pdo->query("SELECT id, name, specialization FROM doctors WHERE status = 'active' ORDER BY name")->fetchAll();
    
} catch (Exception $e) {
    $error_message = "Database error: " . $e->getMessage();
    $test_orders = [];
    $patients = [];
    $tests = [];
    $doctors = [];
}
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-flask mr-2"></i>Test Orders</h1>
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

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <i class="icon fas fa-check"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <i class="icon fas fa-ban"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger">
                    <i class="icon fas fa-ban"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <!-- Main row -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-list mr-1"></i>
                                Test Orders Management
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addOrderModal">
                                    <i class="fas fa-plus"></i> Add New Order
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="ordersTable" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Patient</th>
                                            <th>Test</th>
                                            <th>Doctor</th>
                                            <th>Priority</th>
                                            <th>Status</th>
                                            <th>Order Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($test_orders as $order): ?>
                                        <tr>
                                            <td><?php echo sprintf("TO-%05d", $order['id']); ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($order['patient_name'] ?? 'N/A'); ?></strong><br>
                                                <small class="text-muted">MRN: <?php echo htmlspecialchars($order['patient_mrn'] ?? 'N/A'); ?></small>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($order['test_name'] ?? 'N/A'); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($order['test_type'] ?? ''); ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($order['doctor_name'] ?? 'Not Assigned'); ?></td>
                                            <td>
                                                <?php
                                                $priority_class = '';
                                                switch ($order['priority']) {
                                                    case 'urgent': $priority_class = 'badge-danger'; break;
                                                    case 'high': $priority_class = 'badge-warning'; break;
                                                    default: $priority_class = 'badge-info'; break;
                                                }
                                                ?>
                                                <span class="badge <?php echo $priority_class; ?>">
                                                    <?php echo ucfirst($order['priority']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                $status_class = '';
                                                switch ($order['status']) {
                                                    case 'completed': $status_class = 'badge-success'; break;
                                                    case 'processing': $status_class = 'badge-primary'; break;
                                                    case 'cancelled': $status_class = 'badge-secondary'; break;
                                                    default: $status_class = 'badge-warning'; break;
                                                }
                                                ?>
                                                <span class="badge <?php echo $status_class; ?>">
                                                    <?php echo ucfirst($order['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M d, Y H:i', strtotime($order['order_date'])); ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-info btn-sm" onclick="viewOrder(<?php echo $order['id']; ?>)" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-primary btn-sm" onclick="editOrder(<?php echo htmlspecialchars(json_encode($order)); ?>)" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteOrder(<?php echo $order['id']; ?>)" title="Delete">
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
            </div>
        </div>
    </section>
</div>

<!-- Add Order Modal -->
<div class="modal fade" id="addOrderModal" tabindex="-1" aria-labelledby="addOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="addOrderModalLabel">
                    <i class="fas fa-plus mr-2"></i>Add New Test Order
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="patient_id">Patient <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="patient_id" name="patient_id" required>
                                    <option value="">Select Patient</option>
                                    <?php foreach ($patients as $patient): ?>
                                        <option value="<?php echo $patient['id']; ?>">
                                            <?php echo htmlspecialchars($patient['name'] . ' (MRN: ' . $patient['mrn'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="test_id">Test <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="test_id" name="test_id" required>
                                    <option value="">Select Test</option>
                                    <?php foreach ($tests as $test): ?>
                                        <option value="<?php echo $test['id']; ?>">
                                            <?php echo htmlspecialchars($test['name'] . ' (' . $test['type'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="doctor_id">Referring Doctor</label>
                                <select class="form-control select2" id="doctor_id" name="doctor_id">
                                    <option value="">Select Doctor (Optional)</option>
                                    <?php foreach ($doctors as $doctor): ?>
                                        <option value="<?php echo $doctor['id']; ?>">
                                            <?php echo htmlspecialchars($doctor['name'] . ' - ' . $doctor['specialization']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="priority">Priority <span class="text-danger">*</span></label>
                                <select class="form-control" id="priority" name="priority" required>
                                    <option value="normal">Normal</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Additional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Order Modal -->
<div class="modal fade" id="editOrderModal" tabindex="-1" aria-labelledby="editOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="editOrderModalLabel">
                    <i class="fas fa-edit mr-2"></i>Edit Test Order
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="order_id" id="edit_order_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_patient_id">Patient <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="edit_patient_id" name="patient_id" required>
                                    <option value="">Select Patient</option>
                                    <?php foreach ($patients as $patient): ?>
                                        <option value="<?php echo $patient['id']; ?>">
                                            <?php echo htmlspecialchars($patient['name'] . ' (MRN: ' . $patient['mrn'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_test_id">Test <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="edit_test_id" name="test_id" required>
                                    <option value="">Select Test</option>
                                    <?php foreach ($tests as $test): ?>
                                        <option value="<?php echo $test['id']; ?>">
                                            <?php echo htmlspecialchars($test['name'] . ' (' . $test['type'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_doctor_id">Referring Doctor</label>
                                <select class="form-control select2" id="edit_doctor_id" name="doctor_id">
                                    <option value="">Select Doctor (Optional)</option>
                                    <?php foreach ($doctors as $doctor): ?>
                                        <option value="<?php echo $doctor['id']; ?>">
                                            <?php echo htmlspecialchars($doctor['name'] . ' - ' . $doctor['specialization']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_priority">Priority <span class="text-danger">*</span></label>
                                <select class="form-control" id="edit_priority" name="priority" required>
                                    <option value="normal">Normal</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_status">Status <span class="text-danger">*</span></label>
                                <select class="form-control" id="edit_status" name="status" required>
                                    <option value="pending">Pending</option>
                                    <option value="processing">Processing</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_notes">Notes</label>
                        <textarea class="form-control" id="edit_notes" name="notes" rows="3" placeholder="Additional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<script>
$(function() {
    // Initialize DataTable
    $('#ordersTable').DataTable({
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        "pageLength": 25,
        "order": [[6, "desc"]], // Order by date descending
        "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
    }).buttons().container().appendTo('#ordersTable_wrapper .col-md-6:eq(0)');
    
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });
});

function editOrder(order) {
    // Populate edit modal with order data
    $('#edit_order_id').val(order.id);
    $('#edit_patient_id').val(order.patient_id).trigger('change');
    $('#edit_test_id').val(order.test_id).trigger('change');
    $('#edit_doctor_id').val(order.doctor_id).trigger('change');
    $('#edit_priority').val(order.priority);
    $('#edit_status').val(order.status);
    $('#edit_notes').val(order.notes);
    
    // Show modal
    $('#editOrderModal').modal('show');
}

function deleteOrder(orderId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Create form to submit delete request
            var form = $('<form method="POST" action=""><input type="hidden" name="action" value="delete"><input type="hidden" name="order_id" value="' + orderId + '"></form>');
            $('body').append(form);
            form.submit();
        }
    });
}

function viewOrder(orderId) {
    // You can implement a view modal or redirect to a detail page
    window.location.href = 'view-test-order.php?id=' + orderId;
}
</script>
