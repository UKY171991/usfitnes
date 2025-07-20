<?php
// Set page title
$page_title = 'Test Results';

// Include header and sidebar
include 'includes/header.php';
include 'includes/sidebar.php';

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add':
            $test_order_id = $_POST['test_order_id'] ?? '';
            $test_id = $_POST['test_id'] ?? '';
            $patient_id = $_POST['patient_id'] ?? '';
            $result_value = trim($_POST['result_value'] ?? '');
            $status = $_POST['status'] ?? 'pending';
            $comments = trim($_POST['comments'] ?? '') ?: null;
            $reference_range = trim($_POST['reference_range'] ?? '') ?: null;
            $tested_by = $_POST['tested_by'] ?? $_SESSION['user_id'] ?? 1;
            
            if (empty($test_order_id) || empty($test_id) || empty($patient_id)) {
                $response = ['success' => false, 'message' => 'Order ID, Test ID and Patient ID are required'];
                break;
            }
            
            try {
                $stmt = $pdo->prepare("INSERT INTO test_results (test_order_id, test_id, patient_id, result_value, status, comments, reference_range, tested_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                
                if ($stmt->execute([$test_order_id, $test_id, $patient_id, $result_value, $status, $comments, $reference_range, $tested_by])) {
                    // Update test order status
                    $updateStmt = $pdo->prepare("UPDATE test_orders SET status = 'completed', updated_at = NOW() WHERE id = ?");
                    $updateStmt->execute([$test_order_id]);
                    
                    $response = ['success' => true, 'message' => 'Test result added successfully'];
                } else {
                    $response = ['success' => false, 'message' => 'Failed to add test result'];
                }
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
            }
            break;
            
        case 'edit':
            $id = $_POST['id'] ?? '';
            $result_value = trim($_POST['result_value'] ?? '');
            $status = $_POST['status'] ?? 'pending';
            $comments = trim($_POST['comments'] ?? '') ?: null;
            $reference_range = trim($_POST['reference_range'] ?? '') ?: null;
            $verified_by = $_POST['verified_by'] ?? $_SESSION['user_id'] ?? 1;
            
            if (empty($id)) {
                $response = ['success' => false, 'message' => 'Result ID is required'];
                break;
            }
            
            try {
                $stmt = $pdo->prepare("UPDATE test_results SET result_value = ?, status = ?, comments = ?, reference_range = ?, verified_by = ?, updated_at = NOW() WHERE id = ?");
                
                if ($stmt->execute([$result_value, $status, $comments, $reference_range, $verified_by, $id])) {
                    $response = ['success' => true, 'message' => 'Test result updated successfully'];
                } else {
                    $response = ['success' => false, 'message' => 'Failed to update test result'];
                }
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
            }
            break;
            
        case 'delete':
            $id = $_POST['id'] ?? '';
            
            if (empty($id)) {
                $response = ['success' => false, 'message' => 'Result ID is required'];
                break;
            }
            
            try {
                $stmt = $pdo->prepare("DELETE FROM test_results WHERE id = ?");
                
                if ($stmt->execute([$id])) {
                    $response = ['success' => true, 'message' => 'Test result deleted successfully'];
                } else {
                    $response = ['success' => false, 'message' => 'Failed to delete test result'];
                }
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
            }
            break;
            
        case 'get':
            $id = $_POST['id'] ?? '';
            
            if (empty($id)) {
                $response = ['success' => false, 'message' => 'Result ID is required'];
                break;
            }
            
            try {
                $stmt = $pdo->prepare("SELECT tr.*, p.first_name, p.last_name, p.patient_id as patient_code, t.name as test_name, t.test_code, u.full_name as tested_by_name, v.full_name as verified_by_name FROM test_results tr LEFT JOIN patients p ON tr.patient_id = p.id LEFT JOIN tests t ON tr.test_id = t.id LEFT JOIN users u ON tr.tested_by = u.id LEFT JOIN users v ON tr.verified_by = v.id WHERE tr.id = ?");
                $stmt->execute([$id]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result) {
                    $response = ['success' => true, 'data' => $result];
                } else {
                    $response = ['success' => false, 'message' => 'Test result not found'];
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
                $totalRecords = $pdo->query("SELECT COUNT(*) FROM test_results")->fetchColumn();
                
                // Search query
                $searchQuery = "";
                $params = [];
                if (!empty($search)) {
                    $searchQuery = " WHERE p.first_name LIKE ? OR p.last_name LIKE ? OR p.patient_id LIKE ? OR t.name LIKE ? OR t.test_code LIKE ?";
                    $searchTerm = "%$search%";
                    $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm];
                }
                
                // Filtered records count
                $filteredRecords = $pdo->prepare("SELECT COUNT(*) FROM test_results tr LEFT JOIN patients p ON tr.patient_id = p.id LEFT JOIN tests t ON tr.test_id = t.id" . $searchQuery);
                $filteredRecords->execute($params);
                $filteredRecords = $filteredRecords->fetchColumn();
                
                // Get records
                $sql = "SELECT tr.id, tr.result_value, tr.status, tr.created_at, p.first_name, p.last_name, p.patient_id as patient_code, t.name as test_name, t.test_code, t.normal_range FROM test_results tr LEFT JOIN patients p ON tr.patient_id = p.id LEFT JOIN tests t ON tr.test_id = t.id" . $searchQuery . " ORDER BY tr.created_at DESC LIMIT $start, $length";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $data = [];
                foreach ($results as $result) {
                    $statusBadge = '';
                    switch ($result['status']) {
                        case 'pending':
                            $statusBadge = '<span class="badge badge-warning">Pending</span>';
                            break;
                        case 'completed':
                            $statusBadge = '<span class="badge badge-success">Completed</span>';
                            break;
                        case 'verified':
                            $statusBadge = '<span class="badge badge-primary">Verified</span>';
                            break;
                        case 'abnormal':
                            $statusBadge = '<span class="badge badge-danger">Abnormal</span>';
                            break;
                        default:
                            $statusBadge = '<span class="badge badge-secondary">' . ucfirst($result['status']) . '</span>';
                    }
                    
                    $actions = '
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-info" onclick="viewResult(' . $result['id'] . ')" title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-primary" onclick="editResult(' . $result['id'] . ')" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-success" onclick="printResult(' . $result['id'] . ')" title="Print">
                                <i class="fas fa-print"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteResult(' . $result['id'] . ')" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    ';
                    
                    $data[] = [
                        'patient' => '<strong>' . htmlspecialchars($result['first_name'] . ' ' . $result['last_name']) . '</strong><br><small class="text-muted">' . htmlspecialchars($result['patient_code']) . '</small>',
                        'test' => '<strong>' . htmlspecialchars($result['test_name']) . '</strong><br><small class="text-muted">' . htmlspecialchars($result['test_code']) . '</small>',
                        'result' => '<strong>' . htmlspecialchars($result['result_value'] ?: 'Not available') . '</strong>',
                        'normal_range' => $result['normal_range'] ? '<small class="text-muted">' . htmlspecialchars($result['normal_range']) . '</small>' : '<span class="text-muted">-</span>',
                        'status' => $statusBadge,
                        'date' => date('Y-m-d H:i', strtotime($result['created_at'])),
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
            
        case 'get_pending_orders':
            try {
                $stmt = $pdo->prepare("SELECT to.id, to.order_number, p.first_name, p.last_name, t.name as test_name, t.test_code FROM test_orders to LEFT JOIN patients p ON to.patient_id = p.id LEFT JOIN test_order_items toi ON to.id = toi.test_order_id LEFT JOIN tests t ON toi.test_id = t.id WHERE to.status = 'pending' ORDER BY to.created_at DESC");
                $stmt->execute();
                $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $response = ['success' => true, 'data' => $orders];
            } catch (Exception $e) {
                $response = ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
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

// Get summary data
try {
    $totalResults = $pdo->query("SELECT COUNT(*) FROM test_results")->fetchColumn();
    $pendingResults = $pdo->query("SELECT COUNT(*) FROM test_results WHERE status = 'pending'")->fetchColumn();
    $completedResults = $pdo->query("SELECT COUNT(*) FROM test_results WHERE status IN ('completed', 'verified')")->fetchColumn();
    $abnormalResults = $pdo->query("SELECT COUNT(*) FROM test_results WHERE status = 'abnormal'")->fetchColumn();
} catch (Exception $e) {
    $totalResults = $pendingResults = $completedResults = $abnormalResults = 0;
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
.info-box {
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    border-radius: 0.25rem;
    background: #fff;
    display: flex;
    margin-bottom: 1rem;
    min-height: 80px;
    padding: .5rem;
    position: relative;
    width: 100%;
}
.info-box .info-box-icon {
    border-radius: 0.25rem;
    align-items: center;
    display: flex;
    font-size: 1.875rem;
    justify-content: center;
    text-align: center;
    width: 70px;
}
.info-box .info-box-content {
    display: flex;
    flex-direction: column;
    justify-content: center;
    line-height: 1.8;
    flex: 1;
    padding: 0 10px;
}
.info-box .info-box-number {
    display: block;
    margin-top: -.25rem;
    font-size: 1.125rem;
    font-weight: 700;
}
.info-box .info-box-text {
    display: block;
    font-size: .875rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    text-transform: uppercase;
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
                        <i class="fas fa-file-medical mr-2"></i>Test Results Management
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                        <li class="breadcrumb-item active">Test Results</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Summary Cards -->
            <div class="row mb-3">
                <div class="col-lg-3 col-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-info">
                            <i class="fas fa-file-medical"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Results</span>
                            <span class="info-box-number"><?php echo number_format($totalResults); ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning">
                            <i class="fas fa-clock"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Pending</span>
                            <span class="info-box-number"><?php echo number_format($pendingResults); ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-success">
                            <i class="fas fa-check-circle"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Completed</span>
                            <span class="info-box-number"><?php echo number_format($completedResults); ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Abnormal</span>
                            <span class="info-box-number"><?php echo number_format($abnormalResults); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="row mb-3">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body py-2">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-search mr-2 text-muted"></i>
                                <span class="text-muted mr-3">Quick Actions:</span>
                                <button class="btn btn-success btn-sm mr-2" id="addResultBtn">
                                    <i class="fas fa-plus mr-1"></i>Add Result
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
                                <input type="text" class="form-control form-control-sm" id="globalSearch" placeholder="Search results...">
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

            <!-- Results Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-list mr-2"></i>Test Results
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="resultsTable" class="table table-striped table-hover">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Patient</th>
                                            <th>Test</th>
                                            <th>Result Value</th>
                                            <th>Normal Range</th>
                                            <th>Status</th>
                                            <th>Date</th>
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

<!-- Add Result Modal -->
<div class="modal fade" id="addResultModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus mr-2"></i>Add Test Result
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addResultForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="test_order_id">Test Order <span class="required">*</span></label>
                                <select class="form-control" id="test_order_id" name="test_order_id" required>
                                    <option value="">Select Test Order</option>
                                </select>
                                <small class="form-text text-muted">Select from pending test orders</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status">Status <span class="required">*</span></label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="pending">Pending</option>
                                    <option value="completed">Completed</option>
                                    <option value="verified">Verified</option>
                                    <option value="abnormal">Abnormal</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="result_value">Result Value</label>
                                <input type="text" class="form-control" id="result_value" name="result_value" placeholder="Enter test result">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="reference_range">Reference Range</label>
                                <input type="text" class="form-control" id="reference_range" name="reference_range" placeholder="e.g., 4.5-11.0 x10³/μL">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="comments">Comments</label>
                        <textarea class="form-control" id="comments" name="comments" rows="3" placeholder="Additional notes or observations"></textarea>
                    </div>
                    <input type="hidden" id="test_id" name="test_id">
                    <input type="hidden" id="patient_id" name="patient_id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save mr-1"></i>Add Result
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Result Modal -->
<div class="modal fade" id="editResultModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title">
                    <i class="fas fa-edit mr-2"></i>Edit Test Result
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editResultForm">
                <input type="hidden" id="edit_id" name="id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_result_value">Result Value</label>
                                <input type="text" class="form-control" id="edit_result_value" name="result_value">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_status">Status <span class="required">*</span></label>
                                <select class="form-control" id="edit_status" name="status" required>
                                    <option value="pending">Pending</option>
                                    <option value="completed">Completed</option>
                                    <option value="verified">Verified</option>
                                    <option value="abnormal">Abnormal</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_reference_range">Reference Range</label>
                        <input type="text" class="form-control" id="edit_reference_range" name="reference_range">
                    </div>
                    <div class="form-group">
                        <label for="edit_comments">Comments</label>
                        <textarea class="form-control" id="edit_comments" name="comments" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>Update Result
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Result Modal -->
<div class="modal fade" id="viewResultModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title">
                    <i class="fas fa-eye mr-2"></i>Test Result Details
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="resultDetailsContent">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>Close
                </button>
                <button type="button" class="btn btn-primary" onclick="printCurrentResult()">
                    <i class="fas fa-print mr-1"></i>Print Result
                </button>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
let currentResultId = null;

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
    const table = $('#resultsTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "results.php",
            "type": "POST",
            "data": function(d) {
                d.action = 'datatable';
            }
        },
        "columns": [
            { "data": "patient", "width": "20%" },
            { "data": "test", "width": "20%" },
            { "data": "result", "width": "15%" },
            { "data": "normal_range", "width": "15%" },
            { "data": "status", "width": "10%" },
            { "data": "date", "width": "12%" },
            { "data": "actions", "width": "8%", "orderable": false, "searchable": false }
        ],
        "order": [[5, "desc"]],
        "pageLength": 25,
        "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
        "responsive": true,
        "language": {
            "processing": "<i class='fas fa-spinner fa-spin'></i> Loading results...",
            "emptyTable": "No test results found in the system",
            "zeroRecords": "No matching test results found"
        }
    });
    
    // Global search
    $('#globalSearch').on('keyup', function() {
        table.search(this.value).draw();
    });
    
    // Button event handlers
    $('#addResultBtn').click(function() {
        loadPendingOrders();
        $('#addResultModal').modal('show');
    });
    
    $('#refreshBtn').click(function() {
        table.ajax.reload(null, false);
        toastr.info('Table refreshed');
    });
    
    // Load pending orders for add form
    function loadPendingOrders() {
        $.ajax({
            url: 'results.php',
            type: 'POST',
            data: { action: 'get_pending_orders' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const select = $('#test_order_id');
                    select.empty().append('<option value="">Select Test Order</option>');
                    
                    response.data.forEach(function(order) {
                        const option = `<option value="${order.id}" data-test-id="${order.test_id}" data-patient-id="${order.patient_id}">
                            ${order.order_number} - ${order.first_name} ${order.last_name} - ${order.test_name}
                        </option>`;
                        select.append(option);
                    });
                }
            },
            error: function() {
                toastr.error('Failed to load pending orders');
            }
        });
    }
    
    // Handle test order selection
    $('#test_order_id').change(function() {
        const selected = $(this).find(':selected');
        $('#test_id').val(selected.data('test-id') || '');
        $('#patient_id').val(selected.data('patient-id') || '');
    });
    
    // Add Result Form Submission
    $('#addResultForm').submit(function(e) {
        e.preventDefault();
        
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.html('<i class="fas fa-spinner fa-spin mr-1"></i>Adding...').prop('disabled', true);
        
        $.ajax({
            url: 'results.php',
            type: 'POST',
            data: $(this).serialize() + '&action=add',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#addResultModal').modal('hide');
                    $('#addResultForm')[0].reset();
                    table.ajax.reload(null, false);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('An error occurred while adding the result');
            },
            complete: function() {
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });
    
    // Edit Result Form Submission
    $('#editResultForm').submit(function(e) {
        e.preventDefault();
        
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.html('<i class="fas fa-spinner fa-spin mr-1"></i>Updating...').prop('disabled', true);
        
        $.ajax({
            url: 'results.php',
            type: 'POST',
            data: $(this).serialize() + '&action=edit',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#editResultModal').modal('hide');
                    table.ajax.reload(null, false);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('An error occurred while updating the result');
            },
            complete: function() {
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });
    
    // Clear forms when modals are hidden
    $('#addResultModal').on('hidden.bs.modal', function() {
        $('#addResultForm')[0].reset();
    });
    
    $('#editResultModal').on('hidden.bs.modal', function() {
        $('#editResultForm')[0].reset();
    });
});

function viewResult(id) {
    currentResultId = id;
    
    $.ajax({
        url: 'results.php',
        type: 'POST',
        data: { action: 'get', id: id },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const result = response.data;
                
                let statusBadge = '';
                switch (result.status) {
                    case 'pending':
                        statusBadge = '<span class="badge badge-warning">Pending</span>';
                        break;
                    case 'completed':
                        statusBadge = '<span class="badge badge-success">Completed</span>';
                        break;
                    case 'verified':
                        statusBadge = '<span class="badge badge-primary">Verified</span>';
                        break;
                    case 'abnormal':
                        statusBadge = '<span class="badge badge-danger">Abnormal</span>';
                        break;
                }
                
                const content = `
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-user mr-2"></i>Patient Information</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless mb-0">
                                        <tr>
                                            <td class="font-weight-bold" style="width: 40%;">Patient Name:</td>
                                            <td>${result.first_name} ${result.last_name}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">Patient ID:</td>
                                            <td><span class="badge badge-info">${result.patient_code}</span></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fas fa-flask mr-2"></i>Test Information</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless mb-0">
                                        <tr>
                                            <td class="font-weight-bold" style="width: 40%;">Test Name:</td>
                                            <td>${result.test_name}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">Test Code:</td>
                                            <td><span class="badge badge-secondary">${result.test_code}</span></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mt-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-clipboard-list mr-2"></i>Test Results</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="font-weight-bold" style="width: 40%;">Result Value:</td>
                                            <td><strong class="text-primary">${result.result_value || 'Not available'}</strong></td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">Reference Range:</td>
                                            <td>${result.reference_range || '<span class="text-muted">Not specified</span>'}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">Status:</td>
                                            <td>${statusBadge}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="font-weight-bold" style="width: 40%;">Tested By:</td>
                                            <td>${result.tested_by_name || '<span class="text-muted">Not specified</span>'}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">Verified By:</td>
                                            <td>${result.verified_by_name || '<span class="text-muted">Not verified</span>'}</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">Date:</td>
                                            <td>${new Date(result.created_at).toLocaleString()}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            
                            ${result.comments ? `
                            <div class="mt-3">
                                <h6 class="font-weight-bold">Comments:</h6>
                                <div class="bg-light p-3 rounded">
                                    ${result.comments}
                                </div>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                `;
                
                $('#resultDetailsContent').html(content);
                $('#viewResultModal').modal('show');
            } else {
                toastr.error(response.message);
            }
        },
        error: function() {
            toastr.error('An error occurred while loading result details');
        }
    });
}

function editResult(id) {
    $.ajax({
        url: 'results.php',
        type: 'POST',
        data: { action: 'get', id: id },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const result = response.data;
                $('#edit_id').val(result.id);
                $('#edit_result_value').val(result.result_value || '');
                $('#edit_status').val(result.status);
                $('#edit_reference_range').val(result.reference_range || '');
                $('#edit_comments').val(result.comments || '');
                $('#editResultModal').modal('show');
            } else {
                toastr.error(response.message);
            }
        },
        error: function() {
            toastr.error('An error occurred while loading result details');
        }
    });
}

function deleteResult(id) {
    if (confirm('Are you sure you want to delete this test result?\n\nThis action cannot be undone and will permanently remove the result data.')) {
        $.ajax({
            url: 'results.php',
            type: 'POST',
            data: { action: 'delete', id: id },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#resultsTable').DataTable().ajax.reload(null, false);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('An error occurred while deleting the result');
            }
        });
    }
}

function printResult(id) {
    currentResultId = id;
    viewResult(id);
    // The print functionality will be triggered from the view modal
}

function printCurrentResult() {
    if (currentResultId) {
        window.open('print_result.php?id=' + currentResultId, '_blank');
    }
}
</script>
