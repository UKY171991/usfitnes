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

<!-- Toast container for dynamic messages -->
<div id="toastContainer" class="position-fixed top-0 end-0 p-3" style="z-index: 1055"> 
    <!-- Toasts will be appended here by JavaScript -->
</div>


<?php // Static messages container removed, will rely on dynamic toasts ?>

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
        <div class="col-md-12"> 
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Parameters for: <strong id="selectedTestNameDisplay"></strong></span>
                    <button type="button" id="showAddParameterRowBtn" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add New Parameter
                    </button>
                </div>
                <div class="card-body">
                    <div id="parametersTableContainer">
                        Loading parameters...
                    </div>
                </div>
            </div>
        </div>

        <?php /* Removed the col-md-4 for the "Add New Parameter" form 
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
        */ ?>
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
    const showAddParameterRowBtn = document.getElementById('showAddParameterRowBtn');
    const toastContainer = document.getElementById('toastContainer'); // Define toastContainer

    // Function to load parameters via AJAX
    function loadParameters(testId) {
        if (!testId) {
            parametersSection.style.display = 'none';
            return;
        }

        parametersTableContainer.innerHTML = '<div class=\"text-center\"><div class=\"spinner-border\" role=\"status\"><span class=\"visually-hidden\">Loading...</span></div></div>';
        parametersSection.style.display = 'block';
        // addTestIdField.value = testId; // Set test_id for the add form - no longer needed as field removed

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
            parametersTableContainer.innerHTML = '<p class="text-muted">No parameters defined for this test yet. Click "Add New Parameter" to begin.</p>';
            // Ensure the "Add New Parameter" button is visible even if table is empty, if a test is selected
            if(testSelect.value){
                 showAddParameterRowBtn.style.display = 'block'; // Or 'inline-block'
            } else {
                 showAddParameterRowBtn.style.display = 'none';
            }
            return;
        }
        showAddParameterRowBtn.style.display = 'block'; // Or 'inline-block'


        let tableHtml = '<table class="table table-striped table-hover align-middle" id="parametersEditableTable">';
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

    // REMOVE Handle Add Parameter Form Submission for the old form
    /*
    const addParameterForm = document.getElementById('addParameterForm');
    addParameterForm.addEventListener('submit', function(event) {
        event.preventDefault();
        // ... old logic ...
    });
    */

    // Event listener for the "Add New Parameter" button (to add a row)
    showAddParameterRowBtn.addEventListener('click', function() {
        insertNewEditableParameterRow();
    });

    function insertNewEditableParameterRow() {
        const table = document.getElementById('parametersEditableTable');
        if (!table) {
            // If table doesn't exist (e.g. no params yet), create it first.
            // This case should ideally be handled by renderParametersTable creating an empty table structure.
            // For now, let's assume renderParametersTable creates at least <table><thead></thead><tbody></tbody>
            if(parametersTableContainer.querySelector('table') === null){
                 parametersTableContainer.innerHTML = '<table class="table table-striped table-hover align-middle" id="parametersEditableTable"><thead class="table-light"><tr><th>Parameter Name</th><th>Reference Range</th><th>Unit</th><th>Price</th><th>Description</th><th>Action</th></tr></thead><tbody></tbody></table>';
            }
        }
        
        const tbody = parametersTableContainer.querySelector('#parametersEditableTable tbody');
        if (!tbody) {
            console.error("Table body not found for adding new row.");
            return;
        }

        // Check if an "add new" row already exists
        if (tbody.querySelector('.new-parameter-row')) {
            displayMessage('Please save or cancel the current new parameter first.', 'warning');
            tbody.querySelector('.new-parameter-row input[name="new_parameter_name"]').focus();
            return;
        }

        const newRow = tbody.insertRow(0); // Insert at the top
        newRow.className = 'new-parameter-row table-info'; // Add class for styling and identification

        newRow.innerHTML = `
            <td><input type="text" class="form-control form-control-sm" name="new_parameter_name" placeholder="Parameter Name *" required></td>
            <td><input type="text" class="form-control form-control-sm" name="new_reference_range" placeholder="Reference Range"></td>
            <td><input type="text" class="form-control form-control-sm" name="new_unit" placeholder="Unit"></td>
            <td><input type="number" step="0.01" class="form-control form-control-sm" name="new_price" placeholder="Price"></td>
            <td><textarea class="form-control form-control-sm" name="new_description" rows="1" placeholder="Description"></textarea></td>
            <td>
                <button type="button" class="btn btn-success btn-sm save-new-param-btn" title="Save New Parameter"><i class="fas fa-check"></i></button>
                <button type="button" class="btn btn-warning btn-sm cancel-new-param-btn" title="Cancel Add"><i class="fas fa-times"></i></button>
            </td>
        `;
        newRow.querySelector('input[name="new_parameter_name"]').focus();
    }


    // Event Delegation for Update, Delete, AND NEW Save/Cancel buttons
    parametersTableContainer.addEventListener('click', function(event) {
        const target = event.target;

        // Handle Save New Parameter (from the dynamically added row)
        if (target.closest('.save-new-param-btn')) {
            event.preventDefault();
            const newRow = target.closest('.new-parameter-row');
            if (!newRow) return;

            const parameterName = newRow.querySelector('input[name="new_parameter_name"]').value.trim();
            const referenceRange = newRow.querySelector('input[name="new_reference_range"]').value.trim();
            const unit = newRow.querySelector('input[name="new_unit"]').value.trim();
            const price = newRow.querySelector('input[name="new_price"]').value;
            const description = newRow.querySelector('textarea[name="new_description"]').value.trim();
            const currentTestId = testSelect.value;

            if (!parameterName) {
                displayMessage('Parameter Name is required.', 'danger');
                newRow.querySelector('input[name="new_parameter_name"]').focus();
                return;
            }
            if (!currentTestId) {
                displayMessage('No test selected. Please select a test first.', 'danger');
                return;
            }

            const formData = new FormData();
            formData.append('action', 'add_parameter');
            formData.append('test_id', currentTestId);
            formData.append('parameter_name', parameterName);
            formData.append('reference_range', referenceRange);
            formData.append('unit', unit);
            formData.append('price', price);
            formData.append('description', description);
            
            clearMessages();

            fetch('ajax/handle_test_parameters.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayMessage('Parameter added successfully!', 'success');
                    loadParameters(currentTestId); // Reload to show the new parameter and remove the new row
                } else {
                    displayMessage(`Error: ${data.message || 'Could not add parameter.'}`, 'danger');
                }
            })
            .catch(error => {
                console.error('Error adding parameter:', error);
                displayMessage('An AJAX error occurred while adding the parameter.', 'danger');
            });
            return; // Important: exit after handling this action
        }

        // Handle Cancel New Parameter
        if (target.closest('.cancel-new-param-btn')) {
            event.preventDefault();
            const newRow = target.closest('.new-parameter-row');
            if (newRow) {
                newRow.remove();
                displayMessage('Add parameter cancelled.', 'info');
            }
            return; // Important: exit after handling this action
        }

        // For existing rows, we need paramId
        const paramId = target.closest('tr')?.dataset.paramId; 
        if (!paramId) {
            // If it's not a new row action and no paramId, then it's not a relevant click
            // Or it could be a click on table header/empty space, so we can safely return
            return; 
        }

        // Handle Save (Update) Parameter
        if (target.closest('.save-param-btn')) {
            event.preventDefault();
            const form = target.closest('form.updateParameterForm'); 
            if (!form) return;

            // Correctly get parameter_id from the form\'s hidden input
            const paramIdFromForm = form.querySelector('input[name="parameter_id"]').value;
            if (!paramIdFromForm) {
                displayMessage('Could not find parameter ID for update.', 'danger');
                return;
            }

            const formData = new FormData(form);
            formData.append('action', 'update_parameter');
            // Ensure parameter_id is part of formData if not already (it should be from hidden input)
            if (!formData.has('parameter_id')) { // Should not happen if HTML is correct
                 formData.append('parameter_id', paramIdFromForm);
            }
            
            // Use the correct field names as expected by the backend
            // FormData(form) should already capture these with names:
            // edit_parameter_name, edit_reference_range, edit_unit, edit_price, edit_description

            clearMessages();

            fetch('ajax/handle_test_parameters.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.data && data.data.no_changes) {
                        displayMessage(data.message || 'No changes were made.', 'info');
                    } else {
                        displayMessage(data.message || 'Parameter updated successfully!', 'success');
                    }
                    // Always reload parameters to reflect the current state from the DB
                    loadParameters(testSelect.value); 
                } else {
                    displayMessage(`Error: ${data.message || 'Could not update parameter.'}`, 'danger');
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
        if (!toastContainer) { // Add a guard clause in case toastContainer is still not found
            console.error('Toast container not found in the DOM.');
            alert(message); // Fallback to alert
            return;
        }
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
        if (toastContainer) { // Check if toastContainer exists
            const toasts = toastContainer.querySelectorAll('.alert');
            toasts.forEach(toast => {
                const bsAlert = bootstrap.Alert.getInstance(toast);
                if (bsAlert) {
                    bsAlert.close();
                } else {
                    toast.remove();
                }
            });
        }
        
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