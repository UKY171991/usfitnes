<?php
// Set page title
$page_title = 'Lab Tests';

// Include header and sidebar
include 'includes/header.php';
include 'includes/sidebar.php';

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add':
            $test_code = trim($_POST['test_code'] ?? '');
            $name = trim($_POST['name'] ?? '');
            $category_id = $_POST['category_id'] ?: null;
            $description = trim($_POST['description'] ?? '') ?: null;
            $normal_range = trim($_POST['normal_range'] ?? '') ?: null;
            $unit = trim($_POST['unit'] ?? '') ?: null;
            $price = floatval($_POST['price'] ?? 0);
            $duration_hours = intval($_POST['duration_hours'] ?? 24);
            $sample_type = trim($_POST['sample_type'] ?? 'Blood');
            
            if (empty($test_code) || empty($name)) {
                $response = ['success' => false, 'message' => 'Test code and name are required'];
                break;
            }
            
            try {
                $stmt = $pdo->prepare("INSERT INTO tests (test_code, name, category_id, description, normal_range, unit, price, duration_hours, sample_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                if ($stmt->execute([$test_code, $name, $category_id, $description, $normal_range, $unit, $price, $duration_hours, $sample_type])) {
                    $response = ['success' => true, 'message' => 'Test added successfully'];
                } else {
                    $response = ['success' => false, 'message' => 'Failed to add test'];
                }
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
            }
            break;
            
        case 'edit':
            $id = $_POST['id'] ?? '';
            $test_code = trim($_POST['test_code'] ?? '');
            $name = trim($_POST['name'] ?? '');
            $category_id = $_POST['category_id'] ?: null;
            $description = trim($_POST['description'] ?? '') ?: null;
            $normal_range = trim($_POST['normal_range'] ?? '') ?: null;
            $unit = trim($_POST['unit'] ?? '') ?: null;
            $price = floatval($_POST['price'] ?? 0);
            $duration_hours = intval($_POST['duration_hours'] ?? 24);
            $sample_type = trim($_POST['sample_type'] ?? 'Blood');
            
            if (empty($id) || empty($test_code) || empty($name)) {
                $response = ['success' => false, 'message' => 'Test ID, code and name are required'];
                break;
            }
            
            try {
                $stmt = $pdo->prepare("UPDATE tests SET test_code = ?, name = ?, category_id = ?, description = ?, normal_range = ?, unit = ?, price = ?, duration_hours = ?, sample_type = ?, updated_at = NOW() WHERE id = ?");
                
                if ($stmt->execute([$test_code, $name, $category_id, $description, $normal_range, $unit, $price, $duration_hours, $sample_type, $id])) {
                    $response = ['success' => true, 'message' => 'Test updated successfully'];
                } else {
                    $response = ['success' => false, 'message' => 'Failed to update test'];
                }
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
            }
            break;
            
        case 'delete':
            $id = $_POST['id'] ?? '';
            
            if (empty($id)) {
                $response = ['success' => false, 'message' => 'Test ID is required'];
                break;
            }
            
            try {
                $stmt = $pdo->prepare("DELETE FROM tests WHERE id = ?");
                
                if ($stmt->execute([$id])) {
                    $response = ['success' => true, 'message' => 'Test deleted successfully'];
                } else {
                    $response = ['success' => false, 'message' => 'Failed to delete test'];
                }
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
            }
            break;
            
        case 'get':
            $id = $_POST['id'] ?? '';
            
            if (empty($id)) {
                $response = ['success' => false, 'message' => 'Test ID is required'];
                break;
            }
            
            try {
                $stmt = $pdo->prepare("SELECT t.*, tc.category_name FROM tests t LEFT JOIN test_categories tc ON t.category_id = tc.id WHERE t.id = ?");
                $stmt->execute([$id]);
                $test = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($test) {
                    $response = ['success' => true, 'data' => $test];
                } else {
                    $response = ['success' => false, 'message' => 'Test not found'];
                }
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
            }
            break;
            
        case 'datatable':
            try {
                $draw = intval($_POST['draw']);
                $start = intval($_POST['start']);
                $length = intval($_POST['length']);
                $search = $_POST['search']['value'];
                
                // Total records count
                $totalRecords = $pdo->query("SELECT COUNT(*) FROM tests")->fetchColumn();
                
                // Search query
                $searchQuery = "";
                $params = [];
                if (!empty($search)) {
                    $searchQuery = " WHERE test_code LIKE ? OR name LIKE ? OR sample_type LIKE ?";
                    $searchTerm = "%$search%";
                    $params = [$searchTerm, $searchTerm, $searchTerm];
                }
                
                // Filtered records count
                $filteredRecords = $pdo->prepare("SELECT COUNT(*) FROM tests" . $searchQuery);
                $filteredRecords->execute($params);
                $filteredRecords = $filteredRecords->fetchColumn();
                
                // Get records
                $sql = "SELECT t.id, t.test_code, t.name, t.price, t.sample_type, t.duration_hours, tc.category_name FROM tests t LEFT JOIN test_categories tc ON t.category_id = tc.id" . $searchQuery . " ORDER BY t.created_at DESC LIMIT $start, $length";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $tests = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $data = [];
                foreach ($tests as $test) {
                    $actions = '
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-info" onclick="viewTest(' . $test['id'] . ')" title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-primary" onclick="editTest(' . $test['id'] . ')" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteTest(' . $test['id'] . ')" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    ';
                    
                    $data[] = [
                        'test_code' => '<span class="badge badge-info">' . htmlspecialchars($test['test_code']) . '</span>',
                        'name' => htmlspecialchars($test['name']),
                        'category' => $test['category_name'] ? '<span class="badge badge-secondary">' . htmlspecialchars($test['category_name']) . '</span>' : '<span class="text-muted">None</span>',
                        'price' => '<strong>$' . number_format($test['price'], 2) . '</strong>',
                        'sample_type' => '<span class="badge badge-primary">' . htmlspecialchars($test['sample_type']) . '</span>',
                        'duration' => $test['duration_hours'] . ' hours',
                        'actions' => $actions
                    ];
                }
                
                $response = [
                    'draw' => $draw,
                    'recordsTotal' => $totalRecords,
                    'recordsFiltered' => $filteredRecords,
                    'data' => $data
                ];
            } catch (Exception $e) {
                $response = ['error' => 'Database error: ' . $e->getMessage()];
            }
            break;
            
        default:
            $response = ['success' => false, 'message' => 'Invalid action'];
            break;
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Get test categories for dropdown
try {
    $categories = $pdo->query("SELECT id, category_name FROM test_categories ORDER BY category_name")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $categories = [];
}
?>

<style>
.content-wrapper {
    background-color: #f4f6f9;
}
.card {
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    border: none;
}
.btn-group .btn {
    margin-right: 2px;
}
.btn-group .btn:last-child {
    margin-right: 0;
}
.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}
.modal-header {
    background-color: #007bff;
    color: white;
}
.modal-header .close {
    color: white;
    opacity: 0.8;
}
.modal-header .close:hover {
    opacity: 1;
}
.form-group label {
    font-weight: 600;
    color: #495057;
}
.required {
    color: #dc3545;
}
</style>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <i class="fas fa-flask mr-2"></i>Lab Tests Management
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                        <li class="breadcrumb-item active">Lab Tests</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Action Buttons -->
            <div class="row mb-3">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body py-2">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-search mr-2 text-muted"></i>
                                <span class="text-muted mr-3">Quick Actions:</span>
                                <button class="btn btn-success btn-sm mr-2" id="addTestBtn">
                                    <i class="fas fa-plus mr-1"></i>Add Test
                                </button>
                                <button class="btn btn-info btn-sm mr-2" id="refreshBtn">
                                    <i class="fas fa-sync-alt mr-1"></i>Refresh
                                </button>
                                <button class="btn btn-secondary btn-sm" id="exportBtn">
                                    <i class="fas fa-download mr-1"></i>Export
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body py-2">
                            <div class="input-group">
                                <input type="text" class="form-control form-control-sm" id="globalSearch" placeholder="Search tests...">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary btn-sm" type="button">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tests Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-list mr-2"></i>Available Lab Tests
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="testsTable" class="table table-striped table-hover">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Test Code</th>
                                            <th>Test Name</th>
                                            <th>Category</th>
                                            <th>Price</th>
                                            <th>Sample Type</th>
                                            <th>Duration</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Data will be loaded via AJAX -->
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

<!-- Add Test Modal -->
<div class="modal fade" id="addTestModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus mr-2"></i>Add New Test
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addTestForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="test_code">Test Code <span class="required">*</span></label>
                                <input type="text" class="form-control" id="test_code" name="test_code" required>
                                <small class="form-text text-muted">Unique identifier for the test</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Test Name <span class="required">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required>
                                <small class="form-text text-muted">Full name of the test</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="category_id">Category</label>
                                <select class="form-control" id="category_id" name="category_id">
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['category_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sample_type">Sample Type</label>
                                <select class="form-control" id="sample_type" name="sample_type">
                                    <option value="Blood">Blood</option>
                                    <option value="Urine">Urine</option>
                                    <option value="Stool">Stool</option>
                                    <option value="Saliva">Saliva</option>
                                    <option value="Tissue">Tissue</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="price">Price ($)</label>
                                <input type="number" class="form-control" id="price" name="price" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="duration_hours">Duration (Hours)</label>
                                <input type="number" class="form-control" id="duration_hours" name="duration_hours" value="24" min="1">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="normal_range">Normal Range</label>
                                <input type="text" class="form-control" id="normal_range" name="normal_range" placeholder="e.g., 4.5-11.0 x10³/μL">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="unit">Unit</label>
                                <input type="text" class="form-control" id="unit" name="unit" placeholder="e.g., mg/dL, μL, %">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Test description and purpose"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save mr-1"></i>Add Test
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Test Modal -->
<div class="modal fade" id="editTestModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title">
                    <i class="fas fa-edit mr-2"></i>Edit Test
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editTestForm">
                <input type="hidden" id="edit_id" name="id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_test_code">Test Code <span class="required">*</span></label>
                                <input type="text" class="form-control" id="edit_test_code" name="test_code" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_name">Test Name <span class="required">*</span></label>
                                <input type="text" class="form-control" id="edit_name" name="name" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_category_id">Category</label>
                                <select class="form-control" id="edit_category_id" name="category_id">
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['category_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_sample_type">Sample Type</label>
                                <select class="form-control" id="edit_sample_type" name="sample_type">
                                    <option value="Blood">Blood</option>
                                    <option value="Urine">Urine</option>
                                    <option value="Stool">Stool</option>
                                    <option value="Saliva">Saliva</option>
                                    <option value="Tissue">Tissue</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_price">Price ($)</label>
                                <input type="number" class="form-control" id="edit_price" name="price" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_duration_hours">Duration (Hours)</label>
                                <input type="number" class="form-control" id="edit_duration_hours" name="duration_hours" min="1">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_normal_range">Normal Range</label>
                                <input type="text" class="form-control" id="edit_normal_range" name="normal_range">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_unit">Unit</label>
                                <input type="text" class="form-control" id="edit_unit" name="unit">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_description">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>Update Test
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Test Modal -->
<div class="modal fade" id="viewTestModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title">
                    <i class="fas fa-eye mr-2"></i>Test Details
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="testDetailsContent">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
$(document).ready(function() {
    // Configure Toastr
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };
    
    // Initialize DataTable with server-side processing
    const table = $('#testsTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "tests.php",
            "type": "POST",
            "data": function(d) {
                d.action = 'datatable';
            }
        },
        "columns": [
            { "data": "test_code", "width": "12%" },
            { "data": "name", "width": "25%" },
            { "data": "category", "width": "15%" },
            { "data": "price", "width": "10%" },
            { "data": "sample_type", "width": "12%" },
            { "data": "duration", "width": "10%" },
            { "data": "actions", "width": "16%", "orderable": false, "searchable": false }
        ],
        "order": [[1, "asc"]],
        "pageLength": 25,
        "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
        "responsive": true,
        "language": {
            "processing": "<i class='fas fa-spinner fa-spin'></i> Loading tests...",
            "emptyTable": "No tests found in the system",
            "zeroRecords": "No matching tests found"
        }
    });
    
    // Global search
    $('#globalSearch').on('keyup', function() {
        table.search(this.value).draw();
    });
    
    // Button event handlers
    $('#addTestBtn').click(function() {
        $('#addTestModal').modal('show');
    });
    
    $('#refreshBtn').click(function() {
        table.ajax.reload(null, false);
        toastr.info('Table refreshed');
    });
    
    // Add Test Form Submission
    $('#addTestForm').submit(function(e) {
        e.preventDefault();
        
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.html('<i class="fas fa-spinner fa-spin mr-1"></i>Adding...').prop('disabled', true);
        
        $.ajax({
            url: 'tests.php',
            type: 'POST',
            data: $(this).serialize() + '&action=add',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#addTestModal').modal('hide');
                    $('#addTestForm')[0].reset();
                    table.ajax.reload(null, false);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('An error occurred while adding the test');
            },
            complete: function() {
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });
    
    // Edit Test Form Submission
    $('#editTestForm').submit(function(e) {
        e.preventDefault();
        
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.html('<i class="fas fa-spinner fa-spin mr-1"></i>Updating...').prop('disabled', true);
        
        $.ajax({
            url: 'tests.php',
            type: 'POST',
            data: $(this).serialize() + '&action=edit',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#editTestModal').modal('hide');
                    table.ajax.reload(null, false);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('An error occurred while updating the test');
            },
            complete: function() {
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });
    
    // Clear forms when modals are hidden
    $('#addTestModal').on('hidden.bs.modal', function() {
        $('#addTestForm')[0].reset();
    });
    
    $('#editTestModal').on('hidden.bs.modal', function() {
        $('#editTestForm')[0].reset();
    });
});

function viewTest(id) {
    $.ajax({
        url: 'tests.php',
        type: 'POST',
        data: { action: 'get', id: id },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const test = response.data;
                
                const content = `
                    <div class="row">
                        <div class="col-md-8">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="font-weight-bold" style="width: 30%;">Test Code:</td>
                                    <td><span class="badge badge-info">${test.test_code}</span></td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Test Name:</td>
                                    <td>${test.name}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Category:</td>
                                    <td>${test.category_name || '<span class="text-muted">None</span>'}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Sample Type:</td>
                                    <td><span class="badge badge-primary">${test.sample_type}</span></td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Price:</td>
                                    <td><strong>$${parseFloat(test.price).toFixed(2)}</strong></td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Duration:</td>
                                    <td>${test.duration_hours} hours</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Normal Range:</td>
                                    <td>${test.normal_range || '<span class="text-muted">Not specified</span>'}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Unit:</td>
                                    <td>${test.unit || '<span class="text-muted">Not specified</span>'}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-4">
                            <h6 class="font-weight-bold mb-3">Description:</h6>
                            <div class="bg-light p-3 rounded">
                                ${test.description || '<span class="text-muted">No description provided</span>'}
                            </div>
                        </div>
                    </div>
                `;
                
                $('#testDetailsContent').html(content);
                $('#viewTestModal').modal('show');
            } else {
                toastr.error(response.message);
            }
        },
        error: function() {
            toastr.error('An error occurred while loading test details');
        }
    });
}

function editTest(id) {
    $.ajax({
        url: 'tests.php',
        type: 'POST',
        data: { action: 'get', id: id },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const test = response.data;
                $('#edit_id').val(test.id);
                $('#edit_test_code').val(test.test_code);
                $('#edit_name').val(test.name);
                $('#edit_category_id').val(test.category_id || '');
                $('#edit_sample_type').val(test.sample_type);
                $('#edit_price').val(test.price);
                $('#edit_duration_hours').val(test.duration_hours);
                $('#edit_normal_range').val(test.normal_range || '');
                $('#edit_unit').val(test.unit || '');
                $('#edit_description').val(test.description || '');
                $('#editTestModal').modal('show');
            } else {
                toastr.error(response.message);
            }
        },
        error: function() {
            toastr.error('An error occurred while loading test details');
        }
    });
}

function deleteTest(id) {
    if (confirm('Are you sure you want to delete this test?\n\nThis action cannot be undone and will permanently remove the test data.')) {
        $.ajax({
            url: 'tests.php',
            type: 'POST',
            data: { action: 'delete', id: id },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#testsTable').DataTable().ajax.reload(null, false);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('An error occurred while deleting the test');
            }
        });
    }
}
</script>
