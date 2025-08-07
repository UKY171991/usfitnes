<?php
require_once 'config.php';
require_once 'includes/init.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pageTitle = 'Test Orders Management';
$pageIcon = 'fas fa-flask';
$breadcrumbs = ['Test Orders'];

include 'includes/adminlte_template_header_modern.php';
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <i class="<?php echo $pageIcon; ?> mr-2 text-primary"></i>
                        <?php echo $pageTitle; ?>
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="dashboard_modern.php">Home</a></li>
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
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list mr-1"></i>
                        All Test Orders
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-light btn-sm" onclick="openTestOrderModal()">
                            <i class="fas fa-plus"></i> New Test Order
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <table id="testOrdersTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Patient</th>
                                <th>Test Type</th>
                                <th>Doctor</th>
                                <th>Status</th>
                                <th>Urgency</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>
    </section>
</div>

<!-- Test Order Modal -->
<div class="modal fade" id="testOrderModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h4 class="modal-title">
                    <i class="fas fa-flask"></i>
                    <span id="testOrderModalTitle">Create Test Order</span>
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="testOrderForm">
                <input type="hidden" id="orderId" name="order_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="patientSelect">Patient <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="patientSelect" name="patient_id" required>
                                    <option value="">Select Patient</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="doctorSelect">Doctor <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="doctorSelect" name="doctor_id" required>
                                    <option value="">Select Doctor</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="testType">Test Type <span class="text-danger">*</span></label>
                                <select class="form-control" id="testType" name="test_type" required>
                                    <option value="">Select Test</option>
                                    <option value="Complete Blood Count (CBC)">Complete Blood Count (CBC)</option>
                                    <option value="Blood Glucose">Blood Glucose</option>
                                    <option value="Lipid Profile">Lipid Profile</option>
                                    <option value="Liver Function Test">Liver Function Test</option>
                                    <option value="Kidney Function Test">Kidney Function Test</option>
                                    <option value="Thyroid Function Test">Thyroid Function Test</option>
                                    <option value="Urine Analysis">Urine Analysis</option>
                                    <option value="X-Ray Chest">X-Ray Chest</option>
                                    <option value="ECG">ECG</option>
                                    <option value="Echocardiogram">Echocardiogram</option>
                                    <option value="CT Scan">CT Scan</option>
                                    <option value="MRI">MRI</option>
                                    <option value="Ultrasound">Ultrasound</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="urgency">Urgency <span class="text-danger">*</span></label>
                                <select class="form-control" id="urgency" name="urgency" required>
                                    <option value="normal">Normal</option>
                                    <option value="urgent">Urgent</option>
                                    <option value="emergency">Emergency</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Any special instructions or notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Order Modal -->
<div class="modal fade" id="viewOrderModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h4 class="modal-title text-white">
                    <i class="fas fa-eye"></i>
                    Test Order Details
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="orderDetails">
                    <!-- Order details loaded via AJAX -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Close
                </button>
                <button type="button" class="btn btn-primary" onclick="printOrder()">
                    <i class="fas fa-print"></i> Print Order
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h4 class="modal-title text-white">
                    <i class="fas fa-edit"></i>
                    Update Order Status
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="statusForm">
                <input type="hidden" id="statusOrderId" name="order_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="newStatus">Status <span class="text-danger">*</span></label>
                        <select class="form-control" id="newStatus" name="status" required>
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="statusNotes">Notes</label>
                        <textarea class="form-control" id="statusNotes" name="notes" rows="3" placeholder="Add notes about status change..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let testOrdersTable;
let currentOrderId;

$(document).ready(function() {
    // Initialize DataTable
    testOrdersTable = $('#testOrdersTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "ajax/test_orders_datatable.php",
            "type": "POST"
        },
        "columns": [
            { "data": "id" },
            { "data": "patient_name" },
            { "data": "test_type" },
            { "data": "doctor_name" },
            { 
                "data": "status_badge",
                "orderable": false
            },
            { 
                "data": "urgency",
                "render": function(data) {
                    const badges = {
                        'normal': '<span class="badge badge-success">Normal</span>',
                        'urgent': '<span class="badge badge-warning">Urgent</span>',
                        'emergency': '<span class="badge badge-danger">Emergency</span>'
                    };
                    return badges[data] || data;
                }
            },
            { "data": "created_date" },
            { 
                "data": "actions",
                "orderable": false,
                "searchable": false
            }
        ],
        "order": [[0, "desc"]],
        "pageLength": 25,
        "responsive": true,
        "dom": 'Bfrtip',
        "buttons": [
            {
                text: '<i class="fas fa-plus"></i> New Order',
                className: 'btn btn-primary btn-sm',
                action: function() {
                    openTestOrderModal();
                }
            },
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Export',
                className: 'btn btn-success btn-sm'
            }
        ],
        "language": {
            "processing": '<div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>',
            "emptyTable": "No test orders found",
            "zeroRecords": "No matching test orders found"
        }
    });
    
    // Load dropdown options
    loadPatientOptions();
    loadDoctorOptions();
    
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });
});

// Modal Functions
function openTestOrderModal(id = null) {
    if (id) {
        // Edit mode
        loadTestOrderData(id);
        $('#testOrderModalTitle').text('Edit Test Order');
    } else {
        // Add mode
        $('#testOrderForm')[0].reset();
        $('#orderId').val('');
        $('#testOrderModalTitle').text('Create Test Order');
    }
    $('#testOrderModal').modal('show');
}

function loadTestOrderData(id) {
    $.ajax({
        url: 'ajax/test_order_get.php',
        method: 'GET',
        data: { id: id },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const order = response.data;
                $('#orderId').val(order.id);
                $('#patientSelect').val(order.patient_id).trigger('change');
                $('#doctorSelect').val(order.doctor_id).trigger('change');
                $('#testType').val(order.test_type);
                $('#urgency').val(order.urgency);
                $('#notes').val(order.notes);
            } else {
                showToast('error', response.message);
            }
        },
        error: function() {
            showToast('error', 'Failed to load test order data');
        }
    });
}

function viewTestOrder(id) {
    $.ajax({
        url: 'ajax/test_order_get.php',
        method: 'GET',
        data: { id: id },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displayOrderDetails(response.data);
                $('#viewOrderModal').modal('show');
            } else {
                showToast('error', response.message);
            }
        },
        error: function() {
            showToast('error', 'Failed to load test order details');
        }
    });
}

function displayOrderDetails(order) {
    const urgencyBadge = {
        'normal': '<span class="badge badge-success">Normal</span>',
        'urgent': '<span class="badge badge-warning">Urgent</span>',
        'emergency': '<span class="badge badge-danger">Emergency</span>'
    }[order.urgency] || order.urgency;
    
    const html = `
        <div class="row">
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr><th>Order #:</th><td>#${order.id}</td></tr>
                    <tr><th>Patient:</th><td>${order.patient_name}</td></tr>
                    <tr><th>Doctor:</th><td>${order.doctor_name}</td></tr>
                    <tr><th>Test Type:</th><td>${order.test_type}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr><th>Status:</th><td>${order.status_badge}</td></tr>
                    <tr><th>Urgency:</th><td>${urgencyBadge}</td></tr>
                    <tr><th>Created:</th><td>${order.created_date}</td></tr>
                    <tr><th>Updated:</th><td>${order.updated_date}</td></tr>
                </table>
            </div>
        </div>
        ${order.notes ? `
        <div class="row">
            <div class="col-12">
                <h6>Notes:</h6>
                <div class="alert alert-info">${order.notes}</div>
            </div>
        </div>
        ` : ''}
    `;
    
    $('#orderDetails').html(html);
}

function updateOrderStatus(id) {
    currentOrderId = id;
    $('#statusOrderId').val(id);
    $('#statusModal').modal('show');
}

function deleteTestOrder(id) {
    if (confirm('Are you sure you want to delete this test order? This action cannot be undone.')) {
        $.ajax({
            url: 'ajax/test_order_delete.php',
            method: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showToast('success', response.message);
                    testOrdersTable.ajax.reload();
                } else {
                    showToast('error', response.message);
                }
            },
            error: function() {
                showToast('error', 'Failed to delete test order');
            }
        });
    }
}

// Form Submissions
$('#testOrderForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    $.ajax({
        url: 'ajax/test_order_save.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#testOrderModal').modal('hide');
                showToast('success', response.message);
                testOrdersTable.ajax.reload();
            } else {
                showToast('error', response.message);
            }
        },
        error: function() {
            showToast('error', 'Failed to save test order');
        }
    });
});

$('#statusForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    $.ajax({
        url: 'ajax/test_order_status.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#statusModal').modal('hide');
                showToast('success', response.message);
                testOrdersTable.ajax.reload();
            } else {
                showToast('error', response.message);
            }
        },
        error: function() {
            showToast('error', 'Failed to update status');
        }
    });
});

// Load dropdown options
function loadPatientOptions() {
    $.ajax({
        url: 'ajax/get_patients.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let options = '<option value="">Select Patient</option>';
                response.data.forEach(patient => {
                    options += `<option value="${patient.id}">${patient.name}</option>`;
                });
                $('#patientSelect').html(options);
            }
        }
    });
}

function loadDoctorOptions() {
    $.ajax({
        url: 'ajax/get_doctors.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let options = '<option value="">Select Doctor</option>';
                response.data.forEach(doctor => {
                    options += `<option value="${doctor.id">${doctor.name}</option>`;
                });
                $('#doctorSelect').html(options);
            }
        }
    });
}

function printOrder() {
    if (currentOrderId) {
        window.open('print_order.php?id=' + currentOrderId, '_blank');
    }
}

// Global functions for table actions
window.editTestOrder = function(id) {
    openTestOrderModal(id);
};

window.viewTestOrder = viewTestOrder;
window.updateOrderStatus = updateOrderStatus;
window.deleteTestOrder = deleteTestOrder;
</script>

<?php include 'includes/adminlte_template_footer_modern.php'; ?>
