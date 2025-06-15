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
    $reference_range = trim($_POST['reference_range'] ?? '');
    $unit = trim($_POST['unit'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($add_test_id && !empty($parameter_name)) {
        try {
            // Check if parameter already exists for this test
            $check_stmt = $conn->prepare("SELECT COUNT(*) FROM test_parameters WHERE test_id = ? AND parameter_name = ?");
            $check_stmt->execute([$add_test_id, $parameter_name]);
            if ($check_stmt->fetchColumn() > 0) {
                $message = "Parameter '" . htmlspecialchars($parameter_name) . "' already exists for this test.";
                $message_type = 'warning'; // Use warning for existing
            } else {
                $insert_stmt = $conn->prepare("INSERT INTO test_parameters (test_id, parameter_name, reference_range, unit, price, description) VALUES (?, ?, ?, ?, ?, ?)");
                if ($insert_stmt->execute([$add_test_id, $parameter_name, $reference_range, $unit, $price, $description])) {
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
    $edit_reference_range = trim($_POST['edit_reference_range'] ?? '');
    $edit_unit = trim($_POST['edit_unit'] ?? '');
    $edit_price = trim($_POST['edit_price'] ?? '');
    $edit_description = trim($_POST['edit_description'] ?? '');
    if ($edit_param_id && $edit_parameter_name !== '') {
        try {
            $update_stmt = $conn->prepare("UPDATE test_parameters SET parameter_name = ?, reference_range = ?, unit = ?, price = ?, description = ? WHERE id = ?");
            if ($update_stmt->execute([$edit_parameter_name, $edit_reference_range, $edit_unit, $edit_price, $edit_description, $edit_param_id])) {
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

// Fetch parameters if a test is selected (This will be replaced by AJAX)
/*
if ($selected_test_id) {
    try {
        $params_stmt = $conn->prepare(\"SELECT id, parameter_name, default_unit, price FROM test_parameters WHERE test_id = ? ORDER BY parameter_name ASC\");
        $params_stmt->execute([$selected_test_id]);
        $parameters = $params_stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $parameters = [];
        $message = \"Error fetching parameters: \" . $e->getMessage();
        $message_type = \'danger\';
    }
}
*/

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
                     <button type="button" id="loadParametersBtn" class="btn btn-primary">Load Parameters</button> 
                </div>
            </div>
        </form>
    </div>
</div>

<?php // if ($selected_test_id): // This condition will be handled by JS ?>
<div id="parametersSection" style="display: none;"> 
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    Parameters for: <strong id="selectedTestNameDisplay"></strong>
                </div>
                <div class="card-body">
                    <div id="parametersTableContainer">
                        Loading parameters...
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">Add New Parameter</div>
                <div class="card-body">
                    <form id="addParameterForm"> 
                         <input type="hidden" name="add_test_id" id="add_test_id_field" value="<?php echo $selected_test_id; ?>">
                        <div class="mb-3">
                            <label for="parameter_name" class="form-label">Parameter Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="parameter_name" name="parameter_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="reference_range" class="form-label">Reference Range (Optional)</label>
                            <input type="text" class="form-control" id="reference_range" name="reference_range">
                        </div>
                        <div class="mb-3">
                            <label for="unit" class="form-label">Unit (Optional)</label> 
                            <input type="text" class="form-control" id="unit" name="unit">
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">Price (Optional)</label>
                            <input type="number" step="0.01" class="form-control" id="price" name="price">
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description (Optional)</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-success">Add Parameter</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php // endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectTestForm = document.getElementById('selectTestForm');
    const testSelect = document.getElementById('test_id');
    const loadParametersBtn = document.getElementById('loadParametersBtn');
    const parametersSection = document.getElementById('parametersSection');
    const parametersTableContainer = document.getElementById('parametersTableContainer');
    const selectedTestNameDisplay = document.getElementById('selectedTestNameDisplay');
    const addTestIdField = document.getElementById('add_test_id_field');

    // Function to load parameters via AJAX
    function loadParameters(testId) {
        if (!testId) {
            parametersSection.style.display = 'none';
            return;
        }

        parametersTableContainer.innerHTML = '<div class=\"text-center\"><div class=\"spinner-border\" role=\"status\"><span class=\"visually-hidden\">Loading...</span></div></div>';
        parametersSection.style.display = 'block';
        addTestIdField.value = testId; // Set test_id for the add form

        fetch(`ajax/handle_test_parameters.php?action=load_parameters&test_id=${testId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    selectedTestNameDisplay.textContent = data.data.test_name || 'Selected Test';
                    renderParametersTable(data.data.parameters);
                } else {
                    parametersTableContainer.innerHTML = `<p class="text-danger">Error: ${data.message || 'Could not load parameters.'}</p>`;
                    selectedTestNameDisplay.textContent = 'Error'; // Clear or set to error
                }
            })
            .catch(error => {
                console.error('Error loading parameters:', error);
                parametersTableContainer.innerHTML = '<p class=\"text-danger\">An error occurred while loading parameters.</p>';
            });
    }

    // Function to render the parameters table
    function renderParametersTable(parameters) {
        if (!parameters || parameters.length === 0) {
            parametersTableContainer.innerHTML = '<p class=\"text-muted\">No parameters defined for this test yet.</p>';
            return;
        }

        let tableHtml = '<table class=\"table table-striped table-hover align-middle\">';
        tableHtml += '<thead class="table-light"><tr><th>Parameter Name</th><th>Reference Range</th><th>Unit</th><th>Price</th><th>Description</th><th>Action</th></tr></thead><tbody>';

        parameters.forEach(param => {
            // Ensure values are not null before assigning to value attribute
            const paramName = param.parameter_name || '';
            const refRange = param.reference_range || '';
            const unit = param.unit || ''; // Changed from default_unit
            const price = param.price !== null ? parseFloat(param.price).toFixed(2) : '';
            const description = param.description || '';

            tableHtml += `<tr data-param-id="${param.id}">
                <form class="updateParameterForm" data-param-id="${param.id}"> 
                    <td><input type="text" class="form-control form-control-sm" name="edit_parameter_name" value="${paramName}" required></td>
                    <td><input type="text" class="form-control form-control-sm" name="edit_reference_range" value="${refRange}"></td>
                    <td><input type="text" class="form-control form-control-sm" name="edit_unit" value="${unit}"></td>
                    <td><input type="number" step="0.01" class="form-control form-control-sm" name="edit_price" value="${price}"></td>
                    <td><textarea class="form-control form-control-sm" name="edit_description" rows="1">${description}</textarea></td>
                    <td>
                        <input type="hidden" name="parameter_id" value="${param.id}">
                        <button type="submit" class="btn btn-success btn-sm save-param-btn" title="Save Changes"><i class="fas fa-save"></i></button>
                        <button type="button" class="btn btn-danger btn-sm delete-param-btn" data-param-id="${param.id}" title="Delete Parameter"><i class="fas fa-trash-alt"></i></button>
                    </td>
                </form>
            </tr>`;
        });

        tableHtml += '</tbody></table>';
        parametersTableContainer.innerHTML = tableHtml;
    }

    loadParametersBtn.addEventListener('click', function() {
        const selectedTestId = testSelect.value;
        loadParameters(selectedTestId);
    });

    // Load parameters if a test is already selected on page load (e.g., from query param)
    const initialTestId = testSelect.value;
    if (initialTestId) {
        loadParameters(initialTestId);
    }

    // Create a container for toast messages
    const toastContainer = document.createElement('div');
    toastContainer.id = 'toastContainer';
    toastContainer.style.position = 'fixed';
    toastContainer.style.top = '20px';
    toastContainer.style.right = '20px';
    toastContainer.style.zIndex = '1055'; // Ensure it's above other elements
    document.body.appendChild(toastContainer);

    // Handle Add Parameter Form Submission
    const addParameterForm = document.getElementById('addParameterForm');
    addParameterForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const formData = new FormData(addParameterForm);
        formData.append('action', 'add_parameter');
        const testId = document.getElementById('add_test_id_field').value;
        formData.set('test_id', testId); // Ensure test_id is correctly set from the hidden field

        // Clear previous messages
        clearMessages(); 

        fetch('ajax/handle_test_parameters.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayMessage('Parameter added successfully!', 'success');
                addParameterForm.reset();
                if (data.data && data.data.parameter) {
                    // Option 1: Reload all parameters for simplicity
                    loadParameters(testId);
                    // Option 2: Or dynamically add the new row (more complex)
                    // appendParameterToTable(data.data.parameter);
                } else {
                     loadParameters(testId); // Fallback if specific parameter data not returned
                }
            } else {
                displayMessage(`Error: ${data.message || 'Could not add parameter.'}`, 'danger');
            }
        })
        .catch(error => {
            console.error('Error adding parameter:', error);
            displayMessage('An AJAX error occurred while adding the parameter.', 'danger');
        });
    });

    // Event Delegation for Update and Delete buttons
    parametersTableContainer.addEventListener('click', function(event) {
        const target = event.target;
        const paramId = target.closest('tr')?.dataset.paramId; // Get paramId from parent <tr>

        if (!paramId) return; // Click was not on a relevant element within a row

        // Handle Save (Update) Parameter
        if (target.closest('.save-param-btn')) {
            event.preventDefault();
            const form = target.closest('.updateParameterForm');
            if (!form) return;

            const formData = new FormData(form);
            formData.append('action', 'update_parameter');
            // parameter_id is already in the form as a hidden input
            
            clearMessages();

            fetch('ajax/handle_test_parameters.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayMessage('Parameter updated successfully!', 'success');
                    if (data.data && data.data.parameter) {
                        // Optionally, update just the row if you implement that
                        // updateParameterInTable(data.data.parameter);
                        // For now, reload all for simplicity and to reflect changes
                        loadParameters(testSelect.value);
                    }
                } else {
                    // Check for specific "no changes" message from backend
                    if (data.message && data.message.toLowerCase().includes('no changes made')) {
                        displayMessage(data.message, 'info'); // Show as info toast
                    } else {
                        displayMessage(`Error: ${data.message || 'Could not update parameter.'}`, 'danger');
                    }
                }
            })
            .catch(error => {
                console.error('Error updating parameter:', error);
                displayMessage('An AJAX error occurred while updating the parameter.', 'danger');
            });
        }

        // Handle Delete Parameter
        if (target.closest('.delete-param-btn')) {
            event.preventDefault();
            if (!confirm('Are you sure you want to delete this parameter?')) {
                return;
            }
            clearMessages();

            const formData = new FormData();
            formData.append('action', 'delete_parameter');
            formData.append('parameter_id', paramId);

            fetch('ajax/handle_test_parameters.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayMessage('Parameter deleted successfully!', 'success');
                    // Remove the row from the table or reload
                    // document.querySelector(`tr[data-param-id=\"${paramId}\"]`).remove(); 
                    loadParameters(testSelect.value); // Reload for simplicity
                } else {
                    displayMessage(`Error: ${data.message || 'Could not delete parameter.'}`, 'danger');
                }
            })
            .catch(error => {
                console.error('Error deleting parameter:', error);
                displayMessage('An AJAX error occurred while deleting the parameter.', 'danger');
            });
        }
    });

    // Helper function to display messages as toasts
    function displayMessage(message, type = 'info', duration = 5000) {
        const toastId = 'toast-' + Date.now();
        const toastElement = document.createElement('div');
        toastElement.id = toastId;
        toastElement.className = `alert alert-${type} alert-dismissible fade show`;
        toastElement.setAttribute('role', 'alert');
        toastElement.style.minWidth = '250px'; // Optional: set a min width
        toastElement.style.marginBottom = '10px'; // Space between toasts

        toastElement.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

        toastContainer.appendChild(toastElement);

        // Auto-dismiss the toast
        setTimeout(() => {
            const currentToast = document.getElementById(toastId);
            if (currentToast) {
                // Use Bootstrap's alert close method if available, otherwise just remove
                const bsAlert = bootstrap.Alert.getInstance(currentToast);
                if (bsAlert) {
                    bsAlert.close();
                } else {
                    currentToast.remove();
                }
            }
        }, duration);

        // Allow manual dismissal
        toastElement.querySelector('.btn-close').addEventListener('click', () => {
            const currentToast = document.getElementById(toastId);
             if (currentToast) {
                const bsAlert = bootstrap.Alert.getInstance(currentToast);
                if (bsAlert) {
                    bsAlert.close();
                } else {
                    currentToast.remove();
                }
            }
        });
    }

    function clearMessages() {
        // This function might not be needed if toasts auto-dismiss and are manually closable.
        // If you want a way to clear all visible toasts:
        // const toasts = toastContainer.querySelectorAll('.alert');
        // toasts.forEach(toast => toast.remove());
        
        // Remove the old static message container if it exists and was created by previous versions
        const oldMessageContainer = document.getElementById('dynamicMessageContainer');
        if (oldMessageContainer) {
            oldMessageContainer.innerHTML = '';
        }
        const staticMessage = document.querySelector('.alert.alert-dismissible');
        if (staticMessage && !staticMessage.closest('#toastContainer') && !staticMessage.closest('#dynamicMessageContainer')) {
             // Check if it's not part of a toast or the old dynamic container before removing
            staticMessage.remove();
        }
    }

});
</script>

<?php include '../inc/footer.php'; ?>