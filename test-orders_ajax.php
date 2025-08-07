<?php
require_once 'config.php';
require_once 'includes/adminlte_template.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

renderTemplate('test_orders_ajax', 'Test Orders Management', [
    'page_title' => 'Test Orders Management',
    'breadcrumbs' => [
        ['name' => 'Home', 'url' => 'dashboard_new.php'],
        ['name' => 'Test Orders', 'url' => '']
    ]
]);

function getContent() {
    ob_start();
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Test Orders Management</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="dashboard_new.php">Home</a></li>
                    <li class="breadcrumb-item active">Test Orders</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <!-- Test Orders Table Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-flask mr-2"></i>
                    All Test Orders
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" id="addOrderBtn">
                        <i class="fas fa-plus mr-1"></i>
                        New Order
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
                                <th>Doctor</th>
                                <th>Tests</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- DataTables will populate this -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Add/Edit Order Modal -->
<div class="modal fade" id="orderModal" tabindex="-1" role="dialog" aria-labelledby="orderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderModalLabel">New Test Order</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="orderForm">
                <div class="modal-body">
                    <input type="hidden" id="orderId" name="id">
                    <input type="hidden" name="action" id="formAction" value="add">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="patientId">Patient *</label>
                                <select class="form-control" id="patientId" name="patient_id" required>
                                    <option value="">Select Patient</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="doctorId">Doctor *</label>
                                <select class="form-control" id="doctorId" name="doctor_id" required>
                                    <option value="">Select Doctor</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="testIds">Tests *</label>
                        <select class="form-control" id="testIds" name="test_ids[]" multiple required>
                            <option value="CBC">Complete Blood Count (CBC)</option>
                            <option value="LFT">Liver Function Test (LFT)</option>
                            <option value="KFT">Kidney Function Test (KFT)</option>
                            <option value="LIPID">Lipid Profile</option>
                            <option value="GLUCOSE">Glucose Test</option>
                            <option value="HBA1C">HbA1c Test</option>
                            <option value="THYROID">Thyroid Function Test</option>
                            <option value="URINE">Urine Analysis</option>
                        </select>
                        <small class="text-muted">Hold Ctrl to select multiple tests</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="priority">Priority</label>
                                <select class="form-control" id="priority" name="priority">
                                    <option value="normal">Normal</option>
                                    <option value="urgent">Urgent</option>
                                    <option value="stat">STAT</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="orderDate">Order Date *</label>
                                <input type="date" class="form-control" id="orderDate" name="order_date" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="saveOrderBtn">
                        <i class="fas fa-save mr-1"></i>
                        Save Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Set today's date as default
    $('#orderDate').val(new Date().toISOString().substr(0, 10));
    
    // Initialize DataTable
    let ordersTable = $('#ordersTable').DataTable({
        ajax: {
            url: 'api/test_orders_api.php',
            type: 'GET',
            data: { action: 'list' },
            dataSrc: function(json) {
                if (!json.success) {
                    toastr.error(json.message || 'Failed to load test orders');
                    return [];
                }
                return json.data || [];
            }
        },
        columns: [
            { 
                data: 'order_id',
                defaultContent: 'N/A'
            },
            { 
                data: null,
                render: function(data, type, row) {
                    return `${row.patient_first_name || ''} ${row.patient_last_name || ''}`.trim() || 'N/A';
                }
            },
            { 
                data: null,
                render: function(data, type, row) {
                    return `Dr. ${row.doctor_first_name || ''} ${row.doctor_last_name || ''}`.trim() || 'N/A';
                }
            },
            { 
                data: 'test_names',
                render: function(data, type, row) {
                    if (!data) return 'N/A';
                    let tests = data.split(',').slice(0, 2); // Show first 2 tests
                    let html = tests.join(', ');
                    if (data.split(',').length > 2) {
                        html += ` <small class="text-muted">+${data.split(',').length - 2} more</small>`;
                    }
                    return html;
                }
            },
            { 
                data: 'status',
                render: function(data, type, row) {
                    let badgeClass = 'secondary';
                    if (data === 'pending') badgeClass = 'warning';
                    else if (data === 'processing') badgeClass = 'info';
                    else if (data === 'completed') badgeClass = 'success';
                    else if (data === 'cancelled') badgeClass = 'danger';
                    
                    return `<span class="badge badge-${badgeClass}">${data || 'N/A'}</span>`;
                }
            },
            { 
                data: 'order_date',
                render: function(data, type, row) {
                    if (!data) return 'N/A';
                    return new Date(data).toLocaleDateString();
                }
            },
            {
                data: null,
                orderable: false,
                render: function(data, type, row) {
                    let actions = `
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-info btn-sm edit-order" data-id="${row.id}" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                    `;
                    
                    if (row.status === 'pending') {
                        actions += `
                            <button class="btn btn-success btn-sm process-order" data-id="${row.id}" title="Start Processing">
                                <i class="fas fa-play"></i>
                            </button>
                        `;
                    }
                    
                    actions += `
                            <button class="btn btn-danger btn-sm delete-order" data-id="${row.id}" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                    
                    return actions;
                }
            }
        ],
        responsive: true,
        order: [[0, 'desc']],
        pageLength: 25,
        language: {
            emptyTable: "No test orders found",
            loadingRecords: "Loading orders...",
            processing: "Processing..."
        }
    });

    // Load patients and doctors for dropdowns
    loadDropdownData();
    
    // Add Order Button
    $('#addOrderBtn').click(function() {
        resetForm();
        $('#orderModalLabel').text('New Test Order');
        $('#formAction').val('add');
        $('#orderModal').modal('show');
    });

    // Edit Order
    $(document).on('click', '.edit-order', function() {
        const orderId = $(this).data('id');
        editOrder(orderId);
    });

    // Process Order
    $(document).on('click', '.process-order', function() {
        const orderId = $(this).data('id');
        processOrder(orderId);
    });

    // Delete Order
    $(document).on('click', '.delete-order', function() {
        const orderId = $(this).data('id');
        deleteOrder(orderId);
    });

    // Form Submission
    $('#orderForm').submit(function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        // Show loading state
        $('#saveOrderBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Saving...');
        
        $.ajax({
            url: 'api/test_orders_api.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#orderModal').modal('hide');
                    ordersTable.ajax.reload(null, false);
                } else {
                    toastr.error(response.message || 'Operation failed');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                toastr.error('Network error occurred. Please try again.');
            },
            complete: function() {
                $('#saveOrderBtn').prop('disabled', false).html('<i class="fas fa-save mr-1"></i>Save Order');
            }
        });
    });

    function loadDropdownData() {
        // Load patients
        $.ajax({
            url: 'api/patients_api.php',
            type: 'GET',
            data: { action: 'list' },
            success: function(response) {
                if (response.success && response.data) {
                    let options = '<option value="">Select Patient</option>';
                    response.data.forEach(function(patient) {
                        options += `<option value="${patient.id}">${patient.first_name} ${patient.last_name} (${patient.phone})</option>`;
                    });
                    $('#patientId').html(options);
                }
            }
        });
        
        // Load doctors
        $.ajax({
            url: 'api/doctors_api.php',
            type: 'GET',
            data: { action: 'list' },
            success: function(response) {
                if (response.success && response.data) {
                    let options = '<option value="">Select Doctor</option>';
                    response.data.forEach(function(doctor) {
                        options += `<option value="${doctor.id}">Dr. ${doctor.first_name} ${doctor.last_name} - ${doctor.specialization}</option>`;
                    });
                    $('#doctorId').html(options);
                }
            }
        });
    }

    function editOrder(id) {
        $.ajax({
            url: 'api/test_orders_api.php',
            type: 'GET',
            data: { action: 'get', id: id },
            success: function(response) {
                if (response.success && response.data) {
                    const order = response.data;
                    
                    // Populate form
                    $('#orderId').val(order.id);
                    $('#patientId').val(order.patient_id);
                    $('#doctorId').val(order.doctor_id);
                    $('#testIds').val(order.test_ids ? order.test_ids.split(',') : []);
                    $('#priority').val(order.priority);
                    $('#orderDate').val(order.order_date);
                    $('#notes').val(order.notes);
                    
                    $('#orderModalLabel').text('Edit Test Order');
                    $('#formAction').val('update');
                    $('#orderModal').modal('show');
                } else {
                    toastr.error(response.message || 'Failed to load order data');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                toastr.error('Failed to load order data');
            }
        });
    }

    function processOrder(id) {
        Swal.fire({
            title: 'Start Processing',
            text: 'Mark this order as processing?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#17a2b8',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Start Processing',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'api/test_orders_api.php',
                    type: 'POST',
                    data: { 
                        action: 'update_status', 
                        id: id,
                        status: 'processing'
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success('Order marked as processing');
                            ordersTable.ajax.reload(null, false);
                        } else {
                            toastr.error(response.message || 'Update failed');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', xhr.responseText);
                        toastr.error('Failed to update order status');
                    }
                });
            }
        });
    }

    function deleteOrder(id) {
        Swal.fire({
            title: 'Delete Test Order',
            text: 'Are you sure you want to delete this order? This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, Delete',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'api/test_orders_api.php',
                    type: 'POST',
                    data: { 
                        action: 'delete', 
                        id: id 
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            ordersTable.ajax.reload(null, false);
                        } else {
                            toastr.error(response.message || 'Delete failed');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', xhr.responseText);
                        toastr.error('Failed to delete order');
                    }
                });
            }
        });
    }

    function resetForm() {
        $('#orderForm')[0].reset();
        $('#orderId').val('');
        $('#formAction').val('add');
        $('#orderDate').val(new Date().toISOString().substr(0, 10));
    }

    // Auto-refresh table every 30 seconds
    setInterval(function() {
        if ($('#orderModal').is(':visible') === false) {
            ordersTable.ajax.reload(null, false);
        }
    }, 30000);
});
</script>

<?php
    return ob_get_clean();
}
?>
