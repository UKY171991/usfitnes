<?php
$page_title = "Test Orders";
require_once 'includes/adminlte_header.php';
require_once 'includes/adminlte_sidebar.php';
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="test-orders-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="mb-0"><i class="fas fa-vials"></i> Test Orders Management</h1>
                        <p class="mb-0 text-light">Manage laboratory test orders and track their status</p>
                    </div>
                    <div class="col-md-4 text-right">
                        <button class="btn btn-light btn-lg shadow-sm" onclick="addOrder()">
                            <i class="fas fa-plus"></i> New Test Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Quick Stats -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0" id="pendingCount">0</h4>
                                    <small>Pending Orders</small>
                                </div>
                                <i class="fas fa-clock fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0" id="processingCount">0</h4>
                                    <small>Processing</small>
                                </div>
                                <i class="fas fa-spinner fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0" id="completedCount">0</h4>
                                    <small>Completed</small>
                                </div>
                                <i class="fas fa-check fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0" id="urgentCount">0</h4>
                                    <small>Urgent</small>
                                </div>
                                <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Test Orders Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list"></i> Test Orders</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm" onclick="loadOrders()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Loading State -->
                    <div id="loading" class="text-center py-5" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading test orders...</p>
                    </div>

                    <!-- No Data State -->
                    <div id="no-data" class="text-center py-5" style="display: none;">
                        <i class="fas fa-vials fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No test orders found</h5>
                        <p class="text-muted">Create your first test order to get started.</p>
                        <button class="btn btn-primary" onclick="addOrder()">
                            <i class="fas fa-plus"></i> Add Test Order
                        </button>
                    </div>

                    <!-- Orders Table -->
                    <div id="orders-table-container" style="display: none;">
                        <div class="table-responsive">
                            <table id="ordersTable" class="table table-striped table-hover">
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
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<!-- Test Order Modal -->
<div class="modal fade" id="orderModal" tabindex="-1" aria-labelledby="orderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderModalLabel">
                    <i class="fas fa-vials"></i> <span id="modalTitle">Add Test Order</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="orderForm">
                <div class="modal-body">
                    <input type="hidden" id="orderId" name="id">
                    
                    <!-- Patient Information -->
                    <div class="card-section">
                        <h6 class="section-title mb-3">
                            <i class="fas fa-user"></i> Patient Information
                        </h6>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="patientId">Patient <span class="text-danger">*</span></label>
                                    <select class="form-control" id="patientId" name="patient_id" required>
                                        <option value="">Select Patient</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Test Information -->
                    <div class="card-section">
                        <h6 class="section-title mb-3">
                            <i class="fas fa-flask"></i> Test Details
                        </h6>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="testId">Test <span class="text-danger">*</span></label>
                                    <select class="form-control" id="testId" name="test_id" required>
                                        <option value="">Select Test</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
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
                    </div>

                    <!-- Doctor & Additional Info -->
                    <div class="card-section">
                        <h6 class="section-title mb-3">
                            <i class="fas fa-user-md"></i> Additional Information
                        </h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="doctorId">Referring Doctor</label>
                                    <select class="form-control" id="doctorId" name="doctor_id">
                                        <option value="">Select Doctor (Optional)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6" id="statusGroup" style="display: none;">
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="pending">Pending</option>
                                        <option value="processing">Processing</option>
                                        <option value="completed">Completed</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="notes">Notes</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Additional notes or instructions..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> <span id="submitText">Save Order</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/adminlte_footer.php'; ?>

    <!-- Custom CSS for enhanced styling -->
    <style>
        .test-orders-header {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
        }
        
        .modal-header {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
            border-radius: 8px 8px 0 0;
        }
        
        .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }
        
        .card-section {
            background: #f8f9fc;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            border-left: 4px solid #17a2b8;
        }
        
        .section-title {
            color: #495057;
            font-weight: 600;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 8px;
        }
        
        .form-group label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
        }
        
        .form-control {
            border-radius: 6px;
            border: 1px solid #e3e6f0;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #17a2b8;
            box-shadow: 0 0 0 0.2rem rgba(23, 162, 184, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            border: none;
            border-radius: 6px;
            padding: 10px 30px;
            font-weight: 600;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #148a9b 0%, #117a88 100%);
            transform: translateY(-1px);
        }
        
        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .table thead th {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
            border: none;
            font-weight: 600;
            padding: 15px 12px;
        }
        
        .table tbody tr:hover {
            background-color: #f8f9fc;
        }
        
        .badge {
            padding: 8px 12px;
            font-weight: 500;
            border-radius: 20px;
        }
        
        .btn-group .btn {
            margin: 0 2px;
            border-radius: 4px !important;
        }
        
        .opacity-75 {
            opacity: 0.75;
        }
        
        .is-invalid {
            border-color: #e74a3b !important;
            box-shadow: 0 0 0 0.2rem rgba(231, 74, 59, 0.25) !important;
        }
        
        @media (max-width: 768px) {
            .test-orders-header h1 {
                font-size: 1.5rem;
            }
            
            .card-section {
                padding: 15px;
            }
            
            .btn-group .btn {
                padding: 5px 8px;
                font-size: 0.875rem;
            }
        }
    </style>

// PathLab Pro utilities for Test Orders
const PathLabPro = {
    notifications: {
        success: function(message) {
            if (typeof toastr !== 'undefined') {
                toastr.success(message);
            } else {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: message,
                    timer: 3000,
                    showConfirmButton: false
                });
            }
        },
        error: function(message) {
            if (typeof toastr !== 'undefined') {
                toastr.error(message);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: message
                });
            }
        },
        warning: function(message) {
            if (typeof toastr !== 'undefined') {
                toastr.warning(message);
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Warning!',
                    text: message
                });
            }
        }
    },
    modal: {
        confirm: function(options) {
            Swal.fire({
                title: options.title || 'Are you sure?',
                text: options.text || 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: options.confirmText || 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed && options.callback) {
                    options.callback();
                }
            });
        }
    },
    formatDate: function(dateStr) {
        if (!dateStr) return '';
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
};

let ordersTable;
let currentOrderId = null;

$(document).ready(function() {
    // Initialize DataTable
    ordersTable = $('#ordersTable').DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 25,
        order: [[0, 'desc']],
        columnDefs: [
            { 
                targets: -1,
                orderable: false,
                searchable: false
            }
        ],
        language: {
            emptyTable: "No test orders found",
            search: "Search orders:",
            lengthMenu: "Show _MENU_ orders per page",
            info: "Showing _START_ to _END_ of _TOTAL_ orders",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        }
    });
    
    // Load initial data
    loadOrders();
    loadDropdownData();
    
    // Form submission with validation
    $('#orderForm').on('submit', function(e) {
        e.preventDefault();
        
        // Clear previous validation
        $(this).find('.is-invalid').removeClass('is-invalid');
        
        // Validate required fields
        let isValid = true;
        $(this).find('[required]').each(function() {
            if (!$(this).val().trim()) {
                isValid = false;
                $(this).addClass('is-invalid');
            }
        });
        
        if (isValid) {
            saveOrder();
        } else {
            PathLabPro.notifications.error('Please fill in all required fields.');
        }
    });
    
    // Reset modal on close
    $('#orderModal').on('hidden.bs.modal', function() {
        resetForm();
    });
});

// Load test orders via AJAX
function loadOrders() {
    $('#loading').show();
    $('#orders-table-container').hide();
    $('#no-data').hide();
    
    $.ajax({
        url: 'api/test_orders_api.php',
        method: 'GET',
        data: { action: 'list' },
        dataType: 'json',
        success: function(response) {
            $('#loading').hide();
            
            if (response.success && response.data && response.data.length > 0) {
                populateOrdersTable(response.data);
                updateStats(response.data);
                $('#orders-table-container').show();
            } else {
                $('#no-data').show();
                resetStats();
            }
        },
        error: function(xhr, status, error) {
            $('#loading').hide();
            console.error('Error loading orders:', error);
            PathLabPro.notifications.error('Failed to load test orders');
        }
    });
}

// Populate DataTable with orders data
function populateOrdersTable(orders) {
    ordersTable.clear();
    
    orders.forEach(function(order) {
        const priorityBadge = getPriorityBadge(order.priority);
        const statusBadge = getStatusBadge(order.status);
        
        const actions = `
            <div class="btn-group">
                <button class="btn btn-info btn-sm" onclick="viewOrder(${order.id})" title="View Details">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="btn btn-warning btn-sm" onclick="editOrder(${order.id})" title="Edit Order">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-danger btn-sm" onclick="deleteOrder(${order.id})" title="Delete Order">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        
        ordersTable.row.add([
            order.id,
            `<strong>${order.patient_name || 'N/A'}</strong><br><small class="text-muted">${order.patient_mrn || ''}</small>`,
            `<strong>${order.test_name || 'N/A'}</strong><br><small class="text-muted">${order.test_type || ''}</small>`,
            order.doctor_name || '<span class="text-muted">Not assigned</span>',
            priorityBadge,
            statusBadge,
            PathLabPro.formatDate(order.order_date),
            actions
        ]);
    });
    
    ordersTable.draw();
}

// Get priority badge HTML
function getPriorityBadge(priority) {
    const badges = {
        'normal': '<span class="badge badge-info">Normal</span>',
        'high': '<span class="badge badge-warning">High</span>',
        'urgent': '<span class="badge badge-danger">Urgent</span>'
    };
    return badges[priority] || '<span class="badge badge-secondary">Unknown</span>';
}

// Get status badge HTML
function getStatusBadge(status) {
    const badges = {
        'pending': '<span class="badge badge-warning">Pending</span>',
        'processing': '<span class="badge badge-info">Processing</span>',
        'completed': '<span class="badge badge-success">Completed</span>',
        'cancelled': '<span class="badge badge-danger">Cancelled</span>'
    };
    return badges[status] || '<span class="badge badge-secondary">Unknown</span>';
}

// Update statistics cards
function updateStats(orders) {
    const stats = {
        pending: 0,
        processing: 0,
        completed: 0,
        urgent: 0
    };
    
    orders.forEach(function(order) {
        if (order.status === 'pending') stats.pending++;
        if (order.status === 'processing') stats.processing++;
        if (order.status === 'completed') stats.completed++;
        if (order.priority === 'urgent') stats.urgent++;
    });
    
    $('#pendingCount').text(stats.pending);
    $('#processingCount').text(stats.processing);
    $('#completedCount').text(stats.completed);
    $('#urgentCount').text(stats.urgent);
}

// Reset statistics
function resetStats() {
    $('#pendingCount').text('0');
    $('#processingCount').text('0');
    $('#completedCount').text('0');
    $('#urgentCount').text('0');
}

// Load dropdown data (patients, tests, doctors)
function loadDropdownData() {
    // Load patients
    $.ajax({
        url: 'api/patients_api.php',
        method: 'GET',
        data: { action: 'list' },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                const patientSelect = $('#patientId');
                patientSelect.empty().append('<option value="">Select Patient</option>');
                response.data.forEach(function(patient) {
                    patientSelect.append(`<option value="${patient.id}">${patient.name}</option>`);
                });
            }
        }
    });
    
    // Load tests
    $.ajax({
        url: 'api/test_orders_api.php',
        method: 'GET',
        data: { action: 'get_tests' },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                const testSelect = $('#testId');
                testSelect.empty().append('<option value="">Select Test</option>');
                response.data.forEach(function(test) {
                    testSelect.append(`<option value="${test.id}">${test.name} (${test.type})</option>`);
                });
            }
        }
    });
    
    // Load doctors
    $.ajax({
        url: 'api/test_orders_api.php',
        method: 'GET',
        data: { action: 'get_doctors' },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                const doctorSelect = $('#doctorId');
                doctorSelect.empty().append('<option value="">Select Doctor (Optional)</option>');
                response.data.forEach(function(doctor) {
                    doctorSelect.append(`<option value="${doctor.id}">${doctor.name}</option>`);
                });
            }
        }
    });
}

// Add new order
function addOrder() {
    resetForm();
    $('#modalTitle').text('Add Test Order');
    $('#submitText').text('Save Order');
    $('#statusGroup').hide();
    $('#orderModal').modal('show');
}

// Edit order
function editOrder(orderId) {
    currentOrderId = orderId;
    $('#modalTitle').text('Edit Test Order');
    $('#submitText').text('Update Order');
    $('#statusGroup').show();
    
    // Load order data
    $.ajax({
        url: 'api/test_orders_api.php',
        method: 'GET',
        data: { 
            action: 'get',
            id: orderId 
        },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                const order = response.data;
                $('#orderId').val(order.id);
                $('#patientId').val(order.patient_id);
                $('#testId').val(order.test_id);
                $('#doctorId').val(order.doctor_id || '');
                $('#priority').val(order.priority);
                $('#status').val(order.status);
                $('#notes').val(order.notes || '');
                
                $('#orderModal').modal('show');
            } else {
                PathLabPro.notifications.error('Failed to load order data');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading order:', error);
            PathLabPro.notifications.error('Failed to load order data');
        }
    });
}

// View order details
function viewOrder(orderId) {
    // For now, just edit - could be expanded to a read-only view
    editOrder(orderId);
}

// Delete order
function deleteOrder(orderId) {
    PathLabPro.modal.confirm({
        title: 'Delete Test Order',
        text: 'Are you sure you want to delete this test order? This action cannot be undone.',
        confirmText: 'Yes, delete it!',
        callback: function() {
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
                        PathLabPro.notifications.success(response.message);
                        loadOrders();
                    } else {
                        PathLabPro.notifications.error(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error deleting order:', error);
                    PathLabPro.notifications.error('Failed to delete test order');
                }
            });
        }
    });
}

// Save order
function saveOrder() {
    const formData = {
        action: currentOrderId ? 'update' : 'create',
        id: currentOrderId,
        patient_id: $('#patientId').val(),
        test_id: $('#testId').val(),
        doctor_id: $('#doctorId').val() || null,
        priority: $('#priority').val(),
        status: $('#status').val() || 'pending',
        notes: $('#notes').val()
    };
    
    $.ajax({
        url: 'api/test_orders_api.php',
        method: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                PathLabPro.notifications.success(response.message);
                $('#orderModal').modal('hide');
                loadOrders();
            } else {
                PathLabPro.notifications.error(response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error saving order:', error);
            PathLabPro.notifications.error('Failed to save test order');
        }
    });
}

// Reset form
function resetForm() {
    $('#orderForm')[0].reset();
    $('#orderForm').find('.is-invalid').removeClass('is-invalid');
    currentOrderId = null;
    $('#orderId').val('');
}
</script>

</body>
</html>

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

<?php if ($database_available): ?>
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
<?php endif; ?>

function viewOrder(orderId) {
    // Show order details in a modal or redirect to a detail page
    alert('View Order #' + orderId + ' - Feature would redirect to details page or show modal with order information.');
}
</script>
