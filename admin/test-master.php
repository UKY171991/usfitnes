<?php
require_once '../inc/config.php';
require_once '../inc/db.php';
require_once '../auth/session-check.php';

checkAdminAccess();

// Handle delete request
if(isset($_POST['delete_test'])) {
    $test_id = $_POST['test_id'] ?? 0;
    try {
        $stmt = $conn->prepare("DELETE FROM tests WHERE id = ?");
        $stmt->execute([$test_id]);
        
        // Log activity
        $activity = "Test deleted: ID $test_id";
        $stmt = $conn->prepare("INSERT INTO activities (user_id, description) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], $activity]);
        
        header("Location: test-master.php?success=2");
        exit();
    } catch(PDOException $e) {
        $error = "Error deleting test: " . $e->getMessage();
    }
}

// Handle edit request
if(isset($_POST['edit_test'])) {
    $test_id = $_POST['test_id'] ?? 0;
    $test_name = $_POST['test_name'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $price = $_POST['price'] ?? '';
    $rate = $_POST['rate'] ?? '';
    $test_code = $_POST['test_code'] ?? '';
    $shortcut = $_POST['shortcut'] ?? '';
    $report_heading = $_POST['report_heading'] ?? '';
    $specimen = $_POST['specimen'] ?? '';
    $default_result = $_POST['default_result'] ?? '';
    $unit = $_POST['unit'] ?? '';
    $min_value = $_POST['min_value'] ?? null;
    $max_value = $_POST['max_value'] ?? null;
    $normal_range = $_POST['normal_range'] ?? '';
    $individual_method = $_POST['individual_method'] ?? '';
    $auto_suggestion = isset($_POST['auto_suggestion']) ? 1 : 0;
    $age_gender_wise_ref = isset($_POST['age_gender_wise_ref']) ? 1 : 0;
    $print_new_page = isset($_POST['print_new_page']) ? 1 : 0;
    $sub_heading = isset($_POST['sub_heading']) ? 1 : 0;
    $description = $_POST['description'] ?? '';
    $sample_type = $_POST['sample_type'] ?? '';
    $method = $_POST['method'] ?? '';
    $preparation = $_POST['preparation'] ?? '';
    $reporting_time = $_POST['reporting_time'] ?? '';
    $status = $_POST['status'] ?? 1;
    
    if(!empty($test_name) && !empty($category_id) && !empty($price) && !empty($rate)) {
        try {
            $stmt = $conn->prepare("
                UPDATE tests 
                SET test_name = ?, test_code = ?, shortcut = ?, category_id = ?, price = ?, rate = ?, 
                    report_heading = ?, specimen = ?, default_result = ?, unit = ?, min_value = ?, 
                    max_value = ?, normal_range = ?, method = ?, individual_method = ?, 
                    auto_suggestion = ?, age_gender_wise_ref = ?, print_new_page = ?, sub_heading = ?, 
                    description = ?, sample_type = ?, preparation = ?, reporting_time = ?, status = ? 
                WHERE id = ?
            ");
            $stmt->execute([
                $test_name, $test_code, $shortcut, $category_id, $price, $rate, $report_heading,
                $specimen, $default_result, $unit, $min_value, $max_value, $normal_range,
                $method, $individual_method, $auto_suggestion, $age_gender_wise_ref,
                $print_new_page, $sub_heading, $description, $sample_type, $preparation,
                $reporting_time, $status, $test_id
            ]);
            
            // Handle test parameters update if provided
            if (isset($_POST['param_name']) && is_array($_POST['param_name'])) {
                // First, delete existing parameters for this test
                $deleteStmt = $conn->prepare("DELETE FROM test_parameters WHERE test_id = ?");
                $deleteStmt->execute([$test_id]);
                
                // Then add the new parameters
                $param_names = $_POST['param_name'];
                $param_specimens = $_POST['param_specimen'] ?? [];
                $param_default_results = $_POST['param_default_result'] ?? [];
                $param_units = $_POST['param_unit'] ?? [];
                $param_ref_ranges = $_POST['param_ref_range'] ?? [];
                $param_mins = $_POST['param_min'] ?? [];
                $param_maxs = $_POST['param_max'] ?? [];
                $param_testcodes = $_POST['param_testcode'] ?? [];
                
                $paramStmt = $conn->prepare("
                    INSERT INTO test_parameters (
                        test_id, parameter_name, specimen, default_result, unit, 
                        reference_range, min_value, max_value, testcode
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                for ($i = 0; $i < count($param_names); $i++) {
                    if (!empty($param_names[$i])) {
                        $paramStmt->execute([
                            $test_id,
                            $param_names[$i] ?? '',
                            $param_specimens[$i] ?? '',
                            $param_default_results[$i] ?? '',
                            $param_units[$i] ?? '',
                            $param_ref_ranges[$i] ?? '',
                            !empty($param_mins[$i]) ? $param_mins[$i] : null,
                            !empty($param_maxs[$i]) ? $param_maxs[$i] : null,
                            $param_testcodes[$i] ?? ''
                        ]);
                    }
                }
            }
            
            // Log activity
            $activity = "Test updated: $test_name";
            $stmt = $conn->prepare("INSERT INTO activities (user_id, description) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], $activity]);
            
            header("Location: test-master.php?success=3");
            exit();
        } catch(PDOException $e) {
            $error = "Error updating test: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all required fields";
    }
}

// Handle form submission for new test
if($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['edit_test']) && !isset($_POST['delete_test'])) {
    $test_name = $_POST['test_name'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $price = $_POST['price'] ?? '';
    $rate = $_POST['rate'] ?? '';
    $test_code = $_POST['test_code'] ?? '';
    $shortcut = $_POST['shortcut'] ?? '';
    $report_heading = $_POST['report_heading'] ?? '';
    $specimen = $_POST['specimen'] ?? '';
    $default_result = $_POST['default_result'] ?? '';
    $unit = $_POST['unit'] ?? '';
    $min_value = $_POST['min_value'] ?? null;
    $max_value = $_POST['max_value'] ?? null;
    $normal_range = $_POST['normal_range'] ?? '';
    $individual_method = $_POST['individual_method'] ?? '';
    $auto_suggestion = isset($_POST['auto_suggestion']) ? 1 : 0;
    $age_gender_wise_ref = isset($_POST['age_gender_wise_ref']) ? 1 : 0;
    $print_new_page = isset($_POST['print_new_page']) ? 1 : 0;
    $sub_heading = isset($_POST['sub_heading']) ? 1 : 0;
    $description = $_POST['description'] ?? '';
    $sample_type = $_POST['sample_type'] ?? '';
    $method = $_POST['method'] ?? '';
    $preparation = $_POST['preparation'] ?? '';
    $reporting_time = $_POST['reporting_time'] ?? '';
    $status = $_POST['status'] ?? 1;
    
    if(!empty($test_name) && !empty($category_id) && !empty($price) && !empty($rate)) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO tests (
                    test_name, test_code, shortcut, category_id, price, rate, report_heading, 
                    specimen, default_result, unit, min_value, max_value, normal_range, 
                    method, individual_method, auto_suggestion, age_gender_wise_ref, 
                    print_new_page, sub_heading, description, sample_type, preparation, 
                    reporting_time, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");            $stmt->execute([
                $test_name, $test_code, $shortcut, $category_id, $price, $rate, $report_heading,
                $specimen, $default_result, $unit, $min_value, $max_value, $normal_range,
                $method, $individual_method, $auto_suggestion, $age_gender_wise_ref,
                $print_new_page, $sub_heading, $description, $sample_type, $preparation,
                $reporting_time, $status
            ]);
            
            $test_id = $conn->lastInsertId();
            
            // Handle test parameters if provided
            if (isset($_POST['param_name']) && is_array($_POST['param_name'])) {
                $param_names = $_POST['param_name'];
                $param_specimens = $_POST['param_specimen'] ?? [];
                $param_default_results = $_POST['param_default_result'] ?? [];
                $param_units = $_POST['param_unit'] ?? [];
                $param_ref_ranges = $_POST['param_ref_range'] ?? [];
                $param_mins = $_POST['param_min'] ?? [];
                $param_maxs = $_POST['param_max'] ?? [];
                $param_testcodes = $_POST['param_testcode'] ?? [];
                
                $paramStmt = $conn->prepare("
                    INSERT INTO test_parameters (
                        test_id, parameter_name, specimen, default_result, unit, 
                        reference_range, min_value, max_value, testcode
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                for ($i = 0; $i < count($param_names); $i++) {
                    if (!empty($param_names[$i])) {
                        $paramStmt->execute([
                            $test_id,
                            $param_names[$i] ?? '',
                            $param_specimens[$i] ?? '',
                            $param_default_results[$i] ?? '',
                            $param_units[$i] ?? '',
                            $param_ref_ranges[$i] ?? '',
                            !empty($param_mins[$i]) ? $param_mins[$i] : null,
                            !empty($param_maxs[$i]) ? $param_maxs[$i] : null,
                            $param_testcodes[$i] ?? ''
                        ]);
                    }
                }
            }
            
            // Log activity
            $activity = "New test added: $test_name";
            $stmt = $conn->prepare("INSERT INTO activities (user_id, description) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], $activity]);
            
            header("Location: test-master.php?success=1");
            exit();
        } catch(PDOException $e) {
            $error = "Error adding test: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all required fields";
    }
}

// Get all categories for dropdown
$categories = $conn->query("SELECT id, category_name FROM test_categories ORDER BY category_name")->fetchAll(PDO::FETCH_ASSOC);

include '../inc/header.php';
?>
<link rel="stylesheet" href="admin-shared.css">
<style>
.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

#searchInput {
    transition: all 0.3s ease;
}

#searchInput:focus {
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    border-color: #80bdff;
}

#clearSearch {
    border-left: 0;
}

.input-group .btn {
    z-index: 2;
}

.search-highlight {
    background-color: #fff3cd;
    padding: 2px 4px;
    border-radius: 3px;
}

/* Parameter table styling */
#parametersTable td, #editParametersTable td {
    vertical-align: middle;
}

#parametersTable input, #editParametersTable input {
    min-width: 80px;
}

.table-responsive {
    max-height: 300px;
    overflow-y: auto;
}

/* Modal improvements */
.modal-xl .modal-body {
    max-height: calc(100vh - 200px);
    overflow-y: auto;
}

/* Form section styling */
.modal-body .card {
    border: 1px solid #e9ecef;
    margin-bottom: 1rem;
}

.modal-body .card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    padding: 0.75rem 1rem;
}

.modal-body .card-body {
    padding: 1rem;
}
</style>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Tests</h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTestModal">
        <i class="fas fa-plus"></i> Add New Test
    </button>
</div>

<?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success">
        <?php 
            switch($_GET['success']) {
                case 1:
                    echo "Test added successfully!";
                    break;
                case 2:
                    echo "Test deleted successfully!";
                    break;
                case 3:
                    echo "Test updated successfully!";
                    break;
            }
        ?>
    </div>
<?php endif; ?>

<?php if(isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<!-- Tests Table -->
<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="card-title mb-0">Tests List</h5>
            </div>
            <div class="col-md-6">
                <div class="input-group">
                    <input type="text" class="form-control" id="searchInput" placeholder="Search tests...">
                    <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Test Details</th>
                        <th>Price</th>
                        <th>Sample</th>
                        <th>Reporting Time</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="tests-table-body">
                    <?php /* if (empty($tests)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-vial fa-2x mb-2"></i>
                                    <p>No tests found</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $sr_no = 1; // Initialize serial number ?>
                        <?php foreach($tests as $test): 
                            $status = $test['status'] ?? 1; // Default to active (1) if missing
                        ?>
                            <tr>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?php echo $sr_no++; // Display and increment serial number ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($test['test_name']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($test['category_name']); ?></small>
                                </td>
                                <td>₹<?php echo number_format($test['price'], 2); ?></td>
                                <td><?php echo htmlspecialchars($test['sample_type'] ?: '-'); ?></td>
                                <td><?php echo htmlspecialchars($test['reporting_time'] ?: '-'); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $status == 1 ? 'success' : 'danger'; ?>">
                                        <?php echo $status == 1 ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-info view-test" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#viewTestModal"
                                                data-test='<?php echo htmlspecialchars(json_encode($test)); ?>'
                                                title="View Test">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-primary edit-test" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editTestModal"
                                                data-test='<?php echo htmlspecialchars(json_encode($test)); ?>'
                                                title="Edit Test">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this test?')">
                                            <input type="hidden" name="test_id" value="<?php echo $test['id']; ?>">
                                            <input type="hidden" name="delete_test" value="1">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete Test">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; */ ?>
                </tbody>
            </table>
        </div>
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center" id="pagination-controls">
                <!-- Pagination controls will be inserted here by JavaScript -->
            </ul>
        </nav>
    </div>
</div>

<!-- Add Test Modal -->
<div class="modal fade" id="addTestModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Test</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="" class="needs-validation" novalidate>
                <div class="modal-body">
                    <!-- Basic Test Information -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label for="category_id" class="form-label">Category *</label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>">
                                        <?php echo htmlspecialchars($category['category_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Please select a category.</div>
                        </div>
                        <div class="col-md-3">
                            <label for="test_name" class="form-label">Test Name *</label>
                            <input type="text" class="form-control" id="test_name" name="test_name" required>
                            <div class="invalid-feedback">Please enter the test name.</div>
                        </div>
                        <div class="col-md-2">
                            <label for="shortcut" class="form-label">Shortcut</label>
                            <input type="text" class="form-control" id="shortcut" name="shortcut" maxlength="10">
                        </div>
                        <div class="col-md-2">
                            <label for="rate" class="form-label">Rate *</label>
                            <input type="number" class="form-control" id="rate" name="rate" step="0.01" min="0" required>
                            <div class="invalid-feedback">Please enter rate.</div>
                        </div>
                        <div class="col-md-2">
                            <label for="price" class="form-label">Price *</label>
                            <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                            <div class="invalid-feedback">Please enter price.</div>
                        </div>
                        <div class="col-12">
                            <label for="report_heading" class="form-label">Report Heading</label>
                            <input type="text" class="form-control" id="report_heading" name="report_heading">
                        </div>
                    </div>

                    <!-- Test Details Section -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">Test Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3 mb-3">
                                <div class="col-md-3">
                                    <label for="specimen" class="form-label">Specimen</label>
                                    <input type="text" class="form-control" id="specimen" name="specimen">
                                </div>
                                <div class="col-md-3">
                                    <label for="default_result" class="form-label">Result (Default)</label>
                                    <input type="text" class="form-control" id="default_result" name="default_result">
                                </div>
                                <div class="col-md-2">
                                    <label for="unit" class="form-label">Unit</label>
                                    <input type="text" class="form-control" id="unit" name="unit">
                                </div>
                                <div class="col-md-2">
                                    <label for="min_value" class="form-label">Min.</label>
                                    <input type="number" class="form-control" id="min_value" name="min_value" step="0.01">
                                </div>
                                <div class="col-md-2">
                                    <label for="max_value" class="form-label">Max.</label>
                                    <input type="number" class="form-control" id="max_value" name="max_value" step="0.01">
                                </div>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="normal_range" class="form-label">Reference Range</label>
                                    <input type="text" class="form-control" id="normal_range" name="normal_range">
                                </div>
                                <div class="col-md-3">
                                    <label for="test_code" class="form-label">Test Code</label>
                                    <input type="text" class="form-control" id="test_code" name="test_code" maxlength="20">
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" id="sub_heading" name="sub_heading" value="1">
                                        <label class="form-check-label" for="sub_heading">
                                            Sub-Heading
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="individual_method" class="form-label">Individual Method</label>
                                    <textarea class="form-control" id="individual_method" name="individual_method" rows="2"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Options</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="auto_suggestion" name="auto_suggestion" value="1">
                                        <label class="form-check-label" for="auto_suggestion">
                                            Auto-suggestion
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="age_gender_wise_ref" name="age_gender_wise_ref" value="1">
                                        <label class="form-check-label" for="age_gender_wise_ref">
                                            Age / Gender Wise Ref. Range
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="print_new_page" name="print_new_page" value="1">
                                        <label class="form-check-label" for="print_new_page">
                                            Print on new page
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Fields -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="sample_type" class="form-label">Sample Type</label>
                            <input type="text" class="form-control" id="sample_type" name="sample_type">
                        </div>
                        <div class="col-md-6">
                            <label for="method" class="form-label">Method</label>
                            <input type="text" class="form-control" id="method" name="method">
                        </div>
                        <div class="col-md-6">
                            <label for="reporting_time" class="form-label">Reporting Time</label>
                            <input type="text" class="form-control" id="reporting_time" name="reporting_time" placeholder="e.g., 24 hours">
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label for="preparation" class="form-label">Preparation Instructions</label>
                            <textarea class="form-control" id="preparation" name="preparation" rows="2"></textarea>
                        </div>
                    </div>

                    <!-- Test Parameters Section -->
                    <div class="card mt-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Test Parameters</h6>
                            <button type="button" class="btn btn-sm btn-primary" id="addParameterBtn">
                                <i class="fas fa-plus"></i> Add Parameter
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm" id="parametersTable">
                                    <thead>
                                        <tr>
                                            <th>Test Name</th>
                                            <th>Specimen</th>
                                            <th>Default Value</th>
                                            <th>Unit</th>
                                            <th>Ref. Range</th>
                                            <th>Min Range</th>
                                            <th>Max Range</th>
                                            <th>Test Code</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="parametersTableBody">
                                        <!-- Parameters will be added dynamically -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Test</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Test Modal -->
<div class="modal fade" id="editTestModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Test</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="" class="needs-validation" novalidate>
                <input type="hidden" name="edit_test" value="1">
                <input type="hidden" name="test_id" id="edit_test_id">
                <div class="modal-body">
                    <!-- Basic Test Information -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label for="edit_category_id" class="form-label">Category *</label>
                            <select class="form-select" id="edit_category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>">
                                        <?php echo htmlspecialchars($category['category_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Please select a category.</div>
                        </div>
                        <div class="col-md-3">
                            <label for="edit_test_name" class="form-label">Test Name *</label>
                            <input type="text" class="form-control" id="edit_test_name" name="test_name" required>
                            <div class="invalid-feedback">Please enter the test name.</div>
                        </div>
                        <div class="col-md-2">
                            <label for="edit_shortcut" class="form-label">Shortcut</label>
                            <input type="text" class="form-control" id="edit_shortcut" name="shortcut" maxlength="10">
                        </div>
                        <div class="col-md-2">
                            <label for="edit_rate" class="form-label">Rate *</label>
                            <input type="number" class="form-control" id="edit_rate" name="rate" step="0.01" min="0" required>
                            <div class="invalid-feedback">Please enter rate.</div>
                        </div>
                        <div class="col-md-2">
                            <label for="edit_price" class="form-label">Price *</label>
                            <input type="number" class="form-control" id="edit_price" name="price" step="0.01" min="0" required>
                            <div class="invalid-feedback">Please enter price.</div>
                        </div>
                        <div class="col-12">
                            <label for="edit_report_heading" class="form-label">Report Heading</label>
                            <input type="text" class="form-control" id="edit_report_heading" name="report_heading">
                        </div>
                    </div>

                    <!-- Test Details Section -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">Test Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3 mb-3">
                                <div class="col-md-3">
                                    <label for="edit_specimen" class="form-label">Specimen</label>
                                    <input type="text" class="form-control" id="edit_specimen" name="specimen">
                                </div>
                                <div class="col-md-3">
                                    <label for="edit_default_result" class="form-label">Result (Default)</label>
                                    <input type="text" class="form-control" id="edit_default_result" name="default_result">
                                </div>
                                <div class="col-md-2">
                                    <label for="edit_unit" class="form-label">Unit</label>
                                    <input type="text" class="form-control" id="edit_unit" name="unit">
                                </div>
                                <div class="col-md-2">
                                    <label for="edit_min_value" class="form-label">Min.</label>
                                    <input type="number" class="form-control" id="edit_min_value" name="min_value" step="0.01">
                                </div>
                                <div class="col-md-2">
                                    <label for="edit_max_value" class="form-label">Max.</label>
                                    <input type="number" class="form-control" id="edit_max_value" name="max_value" step="0.01">
                                </div>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="edit_normal_range" class="form-label">Reference Range</label>
                                    <input type="text" class="form-control" id="edit_normal_range" name="normal_range">
                                </div>
                                <div class="col-md-3">
                                    <label for="edit_test_code" class="form-label">Test Code</label>
                                    <input type="text" class="form-control" id="edit_test_code" name="test_code" maxlength="20">
                                </div>
                                <div class="col-md-3">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" id="edit_sub_heading" name="sub_heading" value="1">
                                        <label class="form-check-label" for="edit_sub_heading">
                                            Sub-Heading
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="edit_individual_method" class="form-label">Individual Method</label>
                                    <textarea class="form-control" id="edit_individual_method" name="individual_method" rows="2"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Options</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="edit_auto_suggestion" name="auto_suggestion" value="1">
                                        <label class="form-check-label" for="edit_auto_suggestion">
                                            Auto-suggestion
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="edit_age_gender_wise_ref" name="age_gender_wise_ref" value="1">
                                        <label class="form-check-label" for="edit_age_gender_wise_ref">
                                            Age / Gender Wise Ref. Range
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="edit_print_new_page" name="print_new_page" value="1">
                                        <label class="form-check-label" for="edit_print_new_page">
                                            Print on new page
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Fields -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="edit_sample_type" class="form-label">Sample Type</label>
                            <input type="text" class="form-control" id="edit_sample_type" name="sample_type">
                        </div>
                        <div class="col-md-6">
                            <label for="edit_method" class="form-label">Method</label>
                            <input type="text" class="form-control" id="edit_method" name="method">
                        </div>
                        <div class="col-md-6">
                            <label for="edit_reporting_time" class="form-label">Reporting Time</label>
                            <input type="text" class="form-control" id="edit_reporting_time" name="reporting_time" placeholder="e.g., 24 hours">
                        </div>
                        <div class="col-md-6">
                            <label for="edit_status" class="form-label">Status</label>
                            <select class="form-select" id="edit_status" name="status">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label for="edit_preparation" class="form-label">Preparation Instructions</label>
                            <textarea class="form-control" id="edit_preparation" name="preparation" rows="2"></textarea>
                        </div>
                    </div>

                    <!-- Test Parameters Section -->
                    <div class="card mt-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Test Parameters</h6>
                            <button type="button" class="btn btn-sm btn-primary" id="editAddParameterBtn">
                                <i class="fas fa-plus"></i> Add Parameter
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm" id="editParametersTable">
                                    <thead>
                                        <tr>
                                            <th>Test Name</th>
                                            <th>Specimen</th>
                                            <th>Default Value</th>
                                            <th>Unit</th>
                                            <th>Ref. Range</th>
                                            <th>Min Range</th>
                                            <th>Max Range</th>
                                            <th>Test Code</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="editParametersTableBody">
                                        <!-- Parameters will be loaded dynamically -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Test Modal -->
<div class="modal fade" id="viewTestModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Test Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title">Basic Information</h6>
                                <dl class="row mb-0">
                                    <dt class="col-sm-4">Test Name</dt>
                                    <dd class="col-sm-8" id="view-test-name">-</dd>
                                    <dt class="col-sm-4">Category</dt>
                                    <dd class="col-sm-8" id="view-test-category">-</dd>
                                    <dt class="col-sm-4">Price</dt>
                                    <dd class="col-sm-8" id="view-test-price">-</dd>
                                    <dt class="col-sm-4">Status</dt>
                                    <dd class="col-sm-8" id="view-test-status">-</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title">Technical Details</h6>
                                <dl class="row mb-0">
                                    <dt class="col-sm-4">Sample Type</dt>
                                    <dd class="col-sm-8" id="view-test-sample">-</dd>
                                    <dt class="col-sm-4">Normal Range</dt>
                                    <dd class="col-sm-8" id="view-test-range">-</dd>
                                    <dt class="col-sm-4">Reporting Time</dt>
                                    <dd class="col-sm-8" id="view-test-reporting">-</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">Additional Information</h6>
                                <dl class="row mb-0">
                                    <dt class="col-sm-3">Description</dt>
                                    <dd class="col-sm-9" id="view-test-description">-</dd>
                                    <dt class="col-sm-3">Preparation</dt>
                                    <dd class="col-sm-9" id="view-test-preparation">-</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Modals
    const addTestModalEl = document.getElementById('addTestModal');
    const editTestModalEl = document.getElementById('editTestModal');
    const viewTestModalEl = document.getElementById('viewTestModal');
    const addTestModal = new bootstrap.Modal(addTestModalEl);
    const editTestModal = new bootstrap.Modal(editTestModalEl);
    const viewTestModal = new bootstrap.Modal(viewTestModalEl);

    let currentPage = 1;
    const itemsPerPage = 10; // Or get from a select input
    let searchTerm = '';
    let searchTimeout = null;

    // Search functionality
    const searchInput = document.getElementById('searchInput');
    const clearSearchBtn = document.getElementById('clearSearch');    // Parameter management
    let parameterCount = 0;
    let editParameterCount = 0;
    const addParameterBtn = document.getElementById('addParameterBtn');
    const editAddParameterBtn = document.getElementById('editAddParameterBtn');
    const parametersTableBody = document.getElementById('parametersTableBody');
    const editParametersTableBody = document.getElementById('editParametersTableBody');

    // Add parameter row functionality for Add modal
    if (addParameterBtn) {
        addParameterBtn.addEventListener('click', function() {
            addParameterRow();
        });
    }

    // Add parameter row functionality for Edit modal
    if (editAddParameterBtn) {
        editAddParameterBtn.addEventListener('click', function() {
            addEditParameterRow();
        });
    }    function addParameterRow() {
        parameterCount++;
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <input type="text" class="form-control form-control-sm" name="param_name[]" placeholder="Parameter name">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" name="param_specimen[]" placeholder="Specimen">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" name="param_default_result[]" placeholder="Default value">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" name="param_unit[]" placeholder="Unit">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" name="param_ref_range[]" placeholder="Reference range">
            </td>
            <td>
                <input type="number" class="form-control form-control-sm" name="param_min[]" step="0.01" placeholder="Min">
            </td>
            <td>
                <input type="number" class="form-control form-control-sm" name="param_max[]" step="0.01" placeholder="Max">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" name="param_testcode[]" placeholder="Test code">
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger remove-parameter" onclick="removeParameterRow(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        parametersTableBody.appendChild(row);
    }

    function addEditParameterRow() {
        editParameterCount++;
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <input type="text" class="form-control form-control-sm" name="param_name[]" placeholder="Parameter name">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" name="param_specimen[]" placeholder="Specimen">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" name="param_default_result[]" placeholder="Default value">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" name="param_unit[]" placeholder="Unit">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" name="param_ref_range[]" placeholder="Reference range">
            </td>
            <td>
                <input type="number" class="form-control form-control-sm" name="param_min[]" step="0.01" placeholder="Min">
            </td>
            <td>
                <input type="number" class="form-control form-control-sm" name="param_max[]" step="0.01" placeholder="Max">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" name="param_testcode[]" placeholder="Test code">
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger remove-parameter" onclick="removeParameterRow(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        editParametersTableBody.appendChild(row);
    }

    // Make removeParameterRow globally available
    window.removeParameterRow = function(button) {
        button.closest('tr').remove();
    };

    // Copy values from main form to parameter fields
    document.getElementById('test_name').addEventListener('input', function() {
        const paramNameInputs = document.querySelectorAll('input[name="param_name[]"]');
        paramNameInputs.forEach(input => {
            if (input.value === '') {
                input.value = this.value;
            }
        });
    });

    document.getElementById('specimen').addEventListener('input', function() {
        const paramSpecimenInputs = document.querySelectorAll('input[name="param_specimen[]"]');
        paramSpecimenInputs.forEach(input => {
            if (input.value === '') {
                input.value = this.value;
            }
        });
    });

    document.getElementById('default_result').addEventListener('input', function() {
        const paramDefaultInputs = document.querySelectorAll('input[name="param_default_result[]"]');
        paramDefaultInputs.forEach(input => {
            if (input.value === '') {
                input.value = this.value;
            }
        });
    });

    document.getElementById('unit').addEventListener('input', function() {
        const paramUnitInputs = document.querySelectorAll('input[name="param_unit[]"]');
        paramUnitInputs.forEach(input => {
            if (input.value === '') {
                input.value = this.value;
            }
        });
    });

    document.getElementById('normal_range').addEventListener('input', function() {
        const paramRefInputs = document.querySelectorAll('input[name="param_ref_range[]"]');
        paramRefInputs.forEach(input => {
            if (input.value === '') {
                input.value = this.value;
            }
        });
    });

    document.getElementById('test_code').addEventListener('input', function() {
        const paramTestcodeInputs = document.querySelectorAll('input[name="param_testcode[]"]');
        paramTestcodeInputs.forEach(input => {
            if (input.value === '') {
                input.value = this.value;
            }
        });
    });

    function fetchTests(page) {
        const searchParam = searchTerm ? `&search=${encodeURIComponent(searchTerm)}` : '';
        fetch(`ajax/get_tests.php?page=${page}&itemsPerPage=${itemsPerPage}${searchParam}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    renderTable(data.tests, (page - 1) * itemsPerPage);
                    renderPagination(data.totalPages, parseInt(data.currentPage));
                    currentPage = parseInt(data.currentPage);
                    attachActionListeners(); // Re-attach event listeners
                } else {
                    console.error('Error fetching tests:', data.message);
                    document.getElementById('tests-table-body').innerHTML = `<tr><td colspan="7" class="text-center py-4"><div class="text-muted"><i class="fas fa-exclamation-triangle fa-2x mb-2"></i><p>Error loading tests: ${data.message}</p></div></td></tr>`;
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                document.getElementById('tests-table-body').innerHTML = `<tr><td colspan="7" class="text-center py-4"><div class="text-muted"><i class="fas fa-exclamation-triangle fa-2x mb-2"></i><p>Could not connect to server.</p></div></td></tr>`;
            });
    }

    function renderTable(tests, offset) {
        const tbody = document.getElementById('tests-table-body');
        tbody.innerHTML = ''; // Clear existing rows
        if (tests.length === 0) {
            const message = searchTerm ? 
                `<div class="text-muted"><i class="fas fa-search fa-2x mb-2"></i><p>No tests found matching "${searchTerm}"</p><p class="small">Try adjusting your search terms</p></div>` :
                '<div class="text-muted"><i class="fas fa-vial fa-2x mb-2"></i><p>No tests found</p></div>';
            tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4">${message}</td></tr>`;
            return;
        }

        tests.forEach((test, index) => {
            const sr_no = offset + index + 1;
            const status = test.status ?? 1;
            const statusBadge = status == 1 ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
            const priceFormatted = test.price ? `₹${parseFloat(test.price).toFixed(2)}` : '-';
            const row = `
                <tr>
                    <td><span class="badge bg-secondary">${sr_no}</span></td>
                    <td>
                        <div class="fw-bold">${escapeHTML(test.test_name)}</div>
                        <small class="text-muted">${escapeHTML(test.category_name)}</small>
                    </td>
                    <td>${priceFormatted}</td>
                    <td>${escapeHTML(test.sample_type || '-')}</td>
                    <td>${escapeHTML(test.reporting_time || '-')}</td>
                    <td>${statusBadge}</td>
                    <td class="text-end">
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-info view-test" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#viewTestModal"
                                    data-test='${escapeHTML(JSON.stringify(test))}'
                                    title="View Test">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-primary edit-test" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editTestModal"
                                    data-test='${escapeHTML(JSON.stringify(test))}'
                                    title="Edit Test">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" action="test-master.php" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this test?')">
                                <input type="hidden" name="test_id" value="${test.id}">
                                <input type="hidden" name="delete_test" value="1">
                                <button type="submit" class="btn btn-sm btn-danger" title="Delete Test">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            `;
            tbody.innerHTML += row;
        });
    }

    function renderPagination(totalPages, currentPage) {
        const paginationControls = document.getElementById('pagination-controls');
        paginationControls.innerHTML = '';

        if (totalPages <= 1) return;

        // Previous button
        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
        const prevA = document.createElement('a');
        prevA.className = 'page-link';
        prevA.href = '#';
        prevA.textContent = 'Previous';
        prevA.addEventListener('click', (e) => {
            e.preventDefault();
            if (currentPage > 1) fetchTests(currentPage - 1);
        });
        prevLi.appendChild(prevA);
        paginationControls.appendChild(prevLi);

        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            const li = document.createElement('li');
            li.className = `page-item ${i === currentPage ? 'active' : ''}`;
            const a = document.createElement('a');
            a.className = 'page-link';
            a.href = '#';
            a.textContent = i;
            a.addEventListener('click', (e) => {
                e.preventDefault();
                fetchTests(i);
            });
            li.appendChild(a);
            paginationControls.appendChild(li);
        }

        // Next button
        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
        const nextA = document.createElement('a');
        nextA.className = 'page-link';
        nextA.href = '#';
        nextA.textContent = 'Next';
        nextA.addEventListener('click', (e) => {
            e.preventDefault();
            if (currentPage < totalPages) fetchTests(currentPage + 1);
        });
        nextLi.appendChild(nextA);
        paginationControls.appendChild(nextLi);
    }

    function escapeHTML(str) {
        if (str === null || str === undefined) return '';
        return str.toString().replace(/[&<>\"\'`]/g, function (match) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;', // Escaping single quote for HTML attributes
                '`': '&#x60;'  // Escaping backtick
            }[match];
        });
    }

    function attachActionListeners() {
        // Handle View Button Clicks
        document.querySelectorAll('.view-test').forEach(button => {
            const newButton = button.cloneNode(true);
            button.parentNode.replaceChild(newButton, button);
            newButton.addEventListener('click', function() {
                const testData = JSON.parse(this.dataset.test);
                const status = testData.status ?? 1;
                document.getElementById('view-test-name').textContent = testData.test_name || '-';
                document.getElementById('view-test-category').textContent = testData.category_name || '-';
                document.getElementById('view-test-price').textContent = testData.price ? `₹${parseFloat(testData.price).toFixed(2)}` : '-';
                document.getElementById('view-test-status').innerHTML = `<span class="badge bg-${status == 1 ? 'success' : 'danger'}">${status == 1 ? 'Active' : 'Inactive'}</span>`;
                document.getElementById('view-test-sample').textContent = testData.sample_type || '-';
                document.getElementById('view-test-range').textContent = testData.normal_range || '-';
                document.getElementById('view-test-reporting').textContent = testData.reporting_time || '-';
                document.getElementById('view-test-description').textContent = testData.description || '-';
                document.getElementById('view-test-preparation').textContent = testData.preparation || '-';
                viewTestModal.show();
            });
        });        // Handle Edit Button Clicks
        document.querySelectorAll('.edit-test').forEach(button => {
            const newButton = button.cloneNode(true);
            button.parentNode.replaceChild(newButton, button);
            newButton.addEventListener('click', function() {
                const testData = JSON.parse(this.dataset.test);
                const status = testData.status ?? 1;
                
                // Populate edit form with all fields
                document.getElementById('edit_test_id').value = testData.id;
                document.getElementById('edit_test_name').value = testData.test_name || '';
                document.getElementById('edit_category_id').value = testData.category_id || '';
                document.getElementById('edit_price').value = testData.price || '';
                document.getElementById('edit_rate').value = testData.rate || '';
                document.getElementById('edit_test_code').value = testData.test_code || '';
                document.getElementById('edit_shortcut').value = testData.shortcut || '';
                document.getElementById('edit_report_heading').value = testData.report_heading || '';
                document.getElementById('edit_specimen').value = testData.specimen || '';
                document.getElementById('edit_default_result').value = testData.default_result || '';
                document.getElementById('edit_unit').value = testData.unit || '';
                document.getElementById('edit_min_value').value = testData.min_value || '';
                document.getElementById('edit_max_value').value = testData.max_value || '';
                document.getElementById('edit_normal_range').value = testData.normal_range || '';
                document.getElementById('edit_individual_method').value = testData.individual_method || '';
                document.getElementById('edit_description').value = testData.description || '';
                document.getElementById('edit_sample_type').value = testData.sample_type || '';
                document.getElementById('edit_method').value = testData.method || '';
                document.getElementById('edit_preparation').value = testData.preparation || '';
                document.getElementById('edit_reporting_time').value = testData.reporting_time || '';
                document.getElementById('edit_status').value = status;
                
                // Handle checkboxes
                document.getElementById('edit_sub_heading').checked = testData.sub_heading == 1;
                document.getElementById('edit_auto_suggestion').checked = testData.auto_suggestion == 1;
                document.getElementById('edit_age_gender_wise_ref').checked = testData.age_gender_wise_ref == 1;
                document.getElementById('edit_print_new_page').checked = testData.print_new_page == 1;
                
                // Clear existing parameters in edit modal
                editParametersTableBody.innerHTML = '';
                editParameterCount = 0;
                
                // Load existing test parameters
                loadTestParameters(testData.id);
                
                // Reset validation state if needed
                const form = editTestModalEl.querySelector('form');
                form.classList.remove('was-validated');
                
                editTestModal.show();
            });
        });
    }    // Load test parameters for edit modal
    function loadTestParameters(testId) {
        fetch(`ajax/get_test_parameters.php?test_id=${testId}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    data.parameters.forEach(param => {
                        addEditParameterRowWithData(param);
                    });
                }
            })
            .catch(error => {
                console.error('Error loading test parameters:', error);
            });
    }

    function addEditParameterRowWithData(paramData = {}) {
        editParameterCount++;
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <input type="text" class="form-control form-control-sm" name="param_name[]" value="${escapeHTML(paramData.parameter_name || '')}" placeholder="Parameter name">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" name="param_specimen[]" value="${escapeHTML(paramData.specimen || '')}" placeholder="Specimen">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" name="param_default_result[]" value="${escapeHTML(paramData.default_result || '')}" placeholder="Default value">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" name="param_unit[]" value="${escapeHTML(paramData.unit || '')}" placeholder="Unit">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" name="param_ref_range[]" value="${escapeHTML(paramData.reference_range || '')}" placeholder="Reference range">
            </td>
            <td>
                <input type="number" class="form-control form-control-sm" name="param_min[]" step="0.01" value="${paramData.min_value || ''}" placeholder="Min">
            </td>
            <td>
                <input type="number" class="form-control form-control-sm" name="param_max[]" step="0.01" value="${paramData.max_value || ''}" placeholder="Max">
            </td>
            <td>
                <input type="text" class="form-control form-control-sm" name="param_testcode[]" value="${escapeHTML(paramData.testcode || '')}" placeholder="Test code">
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger remove-parameter" onclick="removeParameterRow(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        editParametersTableBody.appendChild(row);
    }

    // Initial fetch
    fetchTests(currentPage);

    // Form Validation (already present, ensure it works with dynamic content if needed)
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });    // Reset Add form when modal is opened
    addTestModalEl.addEventListener('show.bs.modal', function () {
        const form = addTestModalEl.querySelector('form');
        form.reset();
        form.classList.remove('was-validated');
        // Clear parameters table
        parametersTableBody.innerHTML = '';
        parameterCount = 0;
    });

    // Reset Edit form when modal is closed
    editTestModalEl.addEventListener('hidden.bs.modal', function () {
        const form = editTestModalEl.querySelector('form');
        form.reset();
        form.classList.remove('was-validated');
        // Clear parameters table
        editParametersTableBody.innerHTML = '';
        editParameterCount = 0;
    });
});
</script>

<?php include '../inc/footer.php'; ?>