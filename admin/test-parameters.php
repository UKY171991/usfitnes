<?php
require_once '../inc/config.php';
require_once '../inc/db.php';
require_once '../auth/session-check.php'; // Corrected filename

checkAdminAccess(); // Added admin access check function call

$selected_test_id = $_GET['test_id'] ?? null;
$parameters = [];
$message = '';
$message_type = ''; // 'success' or 'danger'

// Fetch all tests for the dropdown
try {
    $tests_stmt = $conn->query("SELECT id, test_name FROM tests ORDER BY test_name ASC");
    $all_tests = $tests_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $all_tests = [];
    $message = "Error fetching tests: " . $e->getMessage();
    $message_type = 'danger';
}

// Handle adding a parameter
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_parameter'])) {
    $add_test_id = $_POST['add_test_id'] ?? null;
    $parameter_name = trim($_POST['parameter_name'] ?? '');
    $default_unit = trim($_POST['default_unit'] ?? '');
    $price = trim($_POST['price'] ?? '');

    if ($add_test_id && !empty($parameter_name)) {
        try {
            // Check if parameter already exists for this test
            $check_stmt = $conn->prepare("SELECT COUNT(*) FROM test_parameters WHERE test_id = ? AND parameter_name = ?");
            $check_stmt->execute([$add_test_id, $parameter_name]);
            if ($check_stmt->fetchColumn() > 0) {
                $message = "Parameter '" . htmlspecialchars($parameter_name) . "' already exists for this test.";
                $message_type = 'warning'; // Use warning for existing
            } else {
                $insert_stmt = $conn->prepare("INSERT INTO test_parameters (test_id, parameter_name, default_unit, price) VALUES (?, ?, ?, ?)");
                if ($insert_stmt->execute([$add_test_id, $parameter_name, $default_unit, $price])) {
                    $message = "Parameter '" . htmlspecialchars($parameter_name) . "' added successfully.";
                    $message_type = 'success';
                    // Keep the same test selected after adding
                    $selected_test_id = $add_test_id;
                } else {
                    $message = "Error adding parameter.";
                    $message_type = 'danger';
                }
            }
        } catch (PDOException $e) {
            $message = "Database error adding parameter: " . $e->getMessage();
            $message_type = 'danger';
        }
    } else {
        $message = "Test ID and Parameter Name are required to add a parameter.";
        $message_type = 'danger';
    }
    // Ensure the correct test_id is kept in the URL after POST
    if ($add_test_id && !$selected_test_id) {
         $selected_test_id = $add_test_id;
    }
}

// Handle deleting a parameter
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_parameter'])) {
    $param_id_to_delete = $_POST['parameter_id'] ?? null;
    $delete_test_id = $_POST['delete_test_id'] ?? null; // Get test_id to reselect

    if ($param_id_to_delete) {
        try {
            $delete_stmt = $conn->prepare("DELETE FROM test_parameters WHERE id = ?");
            if ($delete_stmt->execute([$param_id_to_delete])) {
                $message = "Parameter deleted successfully.";
                $message_type = 'success';
            } else {
                $message = "Error deleting parameter.";
                $message_type = 'danger';
            }
        } catch (PDOException $e) {
            $message = "Database error deleting parameter: " . $e->getMessage();
            $message_type = 'danger';
        }
        // Keep the same test selected after delete
        $selected_test_id = $delete_test_id;
    } else {
        $message = "Parameter ID not provided for deletion.";
        $message_type = 'danger';
    }
}

// Handle update parameter
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_parameter'])) {
    $edit_param_id = $_POST['edit_param_id'] ?? null;
    $edit_parameter_name = trim($_POST['edit_parameter_name'] ?? '');
    $edit_default_unit = trim($_POST['edit_default_unit'] ?? '');
    $edit_price = trim($_POST['edit_price'] ?? '');
    if ($edit_param_id && $edit_parameter_name !== '') {
        try {
            $update_stmt = $conn->prepare("UPDATE test_parameters SET parameter_name = ?, default_unit = ?, price = ? WHERE id = ?");
            if ($update_stmt->execute([$edit_parameter_name, $edit_default_unit, $edit_price, $edit_param_id])) {
                $message = "Parameter updated successfully.";
                $message_type = 'success';
            } else {
                $message = "Error updating parameter.";
                $message_type = 'danger';
            }
        } catch (PDOException $e) {
            $message = "Database error updating parameter: " . $e->getMessage();
            $message_type = 'danger';
        }
    } else {
        $message = "Parameter name is required.";
        $message_type = 'danger';
    }
}

// Fetch parameters if a test is selected
if ($selected_test_id) {
    try {
        $params_stmt = $conn->prepare("SELECT id, parameter_name, default_unit, price FROM test_parameters WHERE test_id = ? ORDER BY parameter_name ASC");
        $params_stmt->execute([$selected_test_id]);
        $parameters = $params_stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $parameters = [];
        $message = "Error fetching parameters: " . $e->getMessage();
        $message_type = 'danger';
    }
}

include '../inc/header.php';
?>
<link rel="stylesheet" href="admin-shared.css">

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <div>
        <h1 class="h2">Test Parameters</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Test Parameters</li>
            </ol>
        </nav>
    </div>
</div>

<?php if ($message): ?>
<div class="alert alert-<?php echo $message_type === 'success' ? 'success' : ($message_type === 'warning' ? 'warning' : 'danger'); ?> alert-dismissible fade show" role="alert">
    <?php echo htmlspecialchars($message); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" id="selectTestForm">
            <div class="row align-items-end">
                <div class="col-md-6">
                    <label for="test_id" class="form-label">Select Test to Manage Parameters</label>
                    <select class="form-select" id="test_id" name="test_id" required>
                        <option value="">-- Select a Test --</option>
                        <?php foreach ($all_tests as $test): ?>
                            <option value="<?php echo $test['id']; ?>" <?php echo $selected_test_id == $test['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($test['test_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                     <button type="submit" class="btn btn-primary">Load Parameters</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if ($selected_test_id): ?>
<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                Parameters for: <strong><?php 
                    $selected_test_name = '';
                    foreach($all_tests as $t) {
                        if($t['id'] == $selected_test_id) {
                            $selected_test_name = $t['test_name'];
                            break;
                        }
                    }
                    echo htmlspecialchars($selected_test_name); 
                ?></strong>
            </div>
            <div class="card-body">
                <?php if (empty($parameters)): ?>
                    <p class="text-muted">No parameters defined for this test yet.</p>
                <?php else: ?>
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Parameter Name</th>
                                <th>Default Unit</th>
                                <th>Price</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($parameters as $param): ?>
                            <tr>
                                <form method="POST" action="test-parameters.php?test_id=<?php echo $selected_test_id; ?>">
                                    <input type="hidden" name="edit_param_id" value="<?php echo $param['id']; ?>">
                                    <td>
                                        <input type="text" class="form-control form-control-sm" name="edit_parameter_name" value="<?php echo htmlspecialchars($param['parameter_name']); ?>" required>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" name="edit_default_unit" value="<?php echo htmlspecialchars($param['default_unit']); ?>">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" class="form-control form-control-sm" name="edit_price" value="<?php echo htmlspecialchars($param['price']); ?>">
                                    </td>
                                    <td>
                                        <button type="submit" name="update_parameter" class="btn btn-success btn-sm" title="Save"><i class="fas fa-save"></i></button>
                                        <form method="POST" action="test-parameters.php?test_id=<?php echo $selected_test_id; ?>" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this parameter?');">
                                            <input type="hidden" name="parameter_id" value="<?php echo $param['id']; ?>">
                                            <input type="hidden" name="delete_test_id" value="<?php echo $selected_test_id; ?>">
                                            <button type="submit" name="delete_parameter" class="btn btn-danger btn-sm" title="Delete Parameter">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </form>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">Add New Parameter</div>
            <div class="card-body">
                <form method="POST" action="test-parameters.php?test_id=<?php echo $selected_test_id; ?>">
                     <input type="hidden" name="add_test_id" value="<?php echo $selected_test_id; ?>">
                    <div class="mb-3">
                        <label for="parameter_name" class="form-label">Parameter Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="parameter_name" name="parameter_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="default_unit" class="form-label">Default Unit (Optional)</label>
                        <input type="text" class="form-control" id="default_unit" name="default_unit">
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Price (Optional)</label>
                        <input type="number" step="0.01" class="form-control" id="price" name="price">
                    </div>
                    <button type="submit" name="add_parameter" class="btn btn-success">Add Parameter</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectTestForm = document.getElementById('selectTestForm');
    const testSelect = document.getElementById('test_id');

    // Optional: Auto-submit the form when the select changes
    // if (testSelect) {
    //     testSelect.addEventListener('change', function() {
    //         if (this.value) { // Only submit if a test is selected
    //             selectTestForm.submit();
    //         }
    //     });
    // }
});
</script>

<?php include '../inc/footer.php'; ?>