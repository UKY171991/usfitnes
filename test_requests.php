<?php
require_once 'config.php';
require_once 'db_connect.php';

// Start session with secure settings
session_start([
    'cookie_httponly' => true,
    'cookie_secure' => true,
    'cookie_samesite' => 'Strict'
]);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Generate CSRF token if it doesn't exist
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$db = Database::getInstance();

// Fetch all tests for dropdown
$tests_stmt = $db->query("
    SELECT test_id, test_name, category_name 
    FROM Tests_Catalog tc
    JOIN Test_Categories tcat ON tc.category_id = tcat.category_id
    ORDER BY category_name, test_name
");
$tests = $tests_stmt->fetchAll();

// Fetch all patients for dropdown
$patients_stmt = $db->query("
    SELECT patient_id, CONCAT(first_name, ' ', last_name) as patient_name 
    FROM Patients 
    ORDER BY first_name, last_name
");
$patients = $patients_stmt->fetchAll();

include 'includes/head.php';
?>

<div class="container-fluid px-4">
    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Test Requests</h5>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRequestModal">
                <i class="fas fa-plus me-2"></i>Add New Request
            </button>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" id="searchInput" placeholder="Search requests...">
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover" id="requestsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Patient Name</th>
                            <th>Test Type</th>
                            <th>Ordered By</th>
                            <th>Request Date</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be loaded dynamically -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Request Modal -->
<div class="modal fade" id="addRequestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Test Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addRequestForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label class="form-label">Patient <span class="text-danger">*</span></label>
                        <select class="form-select" name="patient_id" required>
                            <option value="">Select Patient</option>
                            <?php foreach ($patients as $patient): ?>
                                <option value="<?php echo htmlspecialchars($patient['patient_id']); ?>">
                                    <?php echo htmlspecialchars($patient['patient_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Test <span class="text-danger">*</span></label>
                        <select class="form-select" name="test_id" required>
                            <option value="">Select Test</option>
                            <?php 
                            $current_category = '';
                            foreach ($tests as $test):
                                if ($current_category !== $test['category_name']):
                                    if ($current_category !== '') echo '</optgroup>';
                                    $current_category = $test['category_name'];
                                    echo '<optgroup label="' . htmlspecialchars($current_category) . '">';
                                endif;
                            ?>
                                <option value="<?php echo htmlspecialchars($test['test_id']); ?>">
                                    <?php echo htmlspecialchars($test['test_name']); ?>
                                </option>
                            <?php 
                            endforeach;
                            if ($current_category !== '') echo '</optgroup>';
                            ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Ordered By <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="ordered_by" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Priority <span class="text-danger">*</span></label>
                        <select class="form-select" name="priority" required>
                            <option value="Normal">Normal</option>
                            <option value="Urgent">Urgent</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="submitRequest">Submit</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Status Modal -->
<div class="modal fade" id="editStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Request Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="updateStatusForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="request_id" id="editRequestId">
                    
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="Pending">Pending</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Completed">Completed</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="updateStatus">Update</button>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
$(document).ready(function() {
    // Initialize DataTable
    const table = $('#requestsTable').DataTable({
        ajax: {
            url: 'includes/fetch_requests.php',
            dataSrc: ''
        },
        columns: [
            { data: 'request_id' },
            { data: 'patient_name' },
            { data: 'test_name' },
            { data: 'ordered_by' },
            { 
                data: 'request_date',
                render: function(data) {
                    return moment(data).format('MMM D, YYYY h:mm A');
                }
            },
            { 
                data: 'status',
                render: function(data) {
                    const badges = {
                        'Pending': 'bg-warning',
                        'In Progress': 'bg-info',
                        'Completed': 'bg-success'
                    };
                    return `<span class="badge ${badges[data]}">${data}</span>`;
                }
            },
            {
                data: 'priority',
                render: function(data) {
                    const badges = {
                        'Normal': 'bg-secondary',
                        'Urgent': 'bg-danger'
                    };
                    return `<span class="badge ${badges[data]}">${data}</span>`;
                }
            },
            {
                data: null,
                render: function(data, type, row) {
                    let buttons = `
                        <button class="btn btn-sm btn-info me-1 edit-status" data-id="${row.request_id}">
                            <i class="fas fa-edit"></i>
                        </button>`;
                    
                    if (data.can_delete) {
                        buttons += `
                            <button class="btn btn-sm btn-danger delete-request" data-id="${row.request_id}">
                                <i class="fas fa-trash"></i>
                            </button>`;
                    }
                    
                    return buttons;
                }
            }
        ],
        order: [[0, 'desc']],
        pageLength: 10,
        responsive: true
    });
    
    // Handle form submission
    $('#submitRequest').click(function() {
        const form = $('#addRequestForm');
        
        if (!form[0].checkValidity()) {
            form[0].reportValidity();
            return;
        }
        
        $.ajax({
            url: 'includes/process_request.php',
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                const data = JSON.parse(response);
                if (data.success) {
                    $('#addRequestModal').modal('hide');
                    form[0].reset();
                    table.ajax.reload();
                    showAlert('success', data.message);
                } else {
                    showAlert('danger', data.message);
                }
            },
            error: function() {
                showAlert('danger', 'An error occurred while processing your request');
            }
        });
    });
    
    // Handle status update
    $('#updateStatus').click(function() {
        const form = $('#updateStatusForm');
        
        $.ajax({
            url: 'includes/process_request.php',
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                const data = JSON.parse(response);
                if (data.success) {
                    $('#editStatusModal').modal('hide');
                    table.ajax.reload();
                    showAlert('success', data.message);
                } else {
                    showAlert('danger', data.message);
                }
            },
            error: function() {
                showAlert('danger', 'An error occurred while updating the status');
            }
        });
    });
    
    // Handle edit status button click
    $('#requestsTable').on('click', '.edit-status', function() {
        $('#editRequestId').val($(this).data('id'));
        $('#editStatusModal').modal('show');
    });
    
    // Handle delete button click
    $('#requestsTable').on('click', '.delete-request', function() {
        const requestId = $(this).data('id');
        
        if (confirm('Are you sure you want to delete this request?')) {
            $.ajax({
                url: 'includes/process_request.php',
                method: 'POST',
                data: {
                    action: 'delete',
                    request_id: requestId,
                    csrf_token: $('input[name="csrf_token"]').val()
                },
                success: function(response) {
                    const data = JSON.parse(response);
                    if (data.success) {
                        table.ajax.reload();
                        showAlert('success', data.message);
                    } else {
                        showAlert('danger', data.message);
                    }
                },
                error: function() {
                    showAlert('danger', 'An error occurred while deleting the request');
                }
            });
        }
    });
    
    // Search functionality
    $('#searchInput').on('keyup', function() {
        table.search(this.value).draw();
    });
});

function showAlert(type, message) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;
    
    $('.container-fluid').prepend(alertHtml);
    
    setTimeout(function() {
        $('.alert').alert('close');
    }, 5000);
}
</script>