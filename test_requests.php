<?php
require_once 'includes/config.php';
require_once 'includes/db_connect.php';

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

try {
    $db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch all tests for dropdown
    $tests_stmt = $db->query("
        SELECT test_id, test_name, category_name 
        FROM Tests_Catalog tc
        JOIN Test_Categories tcat ON tc.category_id = tcat.category_id
        ORDER BY category_name, test_name
    ");
    $tests = $tests_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all patients for dropdown
    $patients_stmt = $db->query("
        SELECT patient_id, CONCAT(first_name, ' ', last_name) as patient_name 
        FROM Patients 
        ORDER BY first_name, last_name
    ");
    $patients = $patients_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    die("A database error occurred. Please try again later.");
}

include 'includes/header.php';
?>

<div class="container-fluid px-4">
    <div id="alertContainer"></div>
    
    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Test Requests</h5>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRequestModal">
                <i class="bi bi-plus"></i> Add New Request
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
                <button type="submit" form="addRequestForm" class="btn btn-primary">Submit</button>
            </div>
        </div>
    </div>
</div>

<!-- Update Request Modal -->
<div class="modal fade" id="updateRequestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Test Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="updateRequestForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="request_id" id="updateRequestId">
                    
                    <div class="mb-3">
                        <label class="form-label">Patient</label>
                        <p class="form-control-static" id="updatePatientName"></p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Test</label>
                        <p class="form-control-static" id="updateTestName"></p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" name="status" id="updateStatus" required>
                            <option value="Pending">Pending</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Completed">Completed</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Priority <span class="text-danger">*</span></label>
                        <select class="form-select" name="priority" id="updatePriority" required>
                            <option value="Normal">Normal</option>
                            <option value="Urgent">Urgent</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="updateRequestForm" class="btn btn-primary">Update</button>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<!-- Initialize DataTable and handle form submissions -->
<script src="assets/js/test_requests.js"></script>
<script>
    // Make CSRF token available to JavaScript
    const csrfToken = <?php echo json_encode($_SESSION['csrf_token']); ?>;
</script>