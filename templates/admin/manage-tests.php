<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';

// Check if user is admin
if (!isLoggedIn() || ($_SESSION['user_role'] !== 'master_admin' && $_SESSION['user_role'] !== 'branch_admin')) {
    header('Location: ../patient/login');
    exit;
}

$title = 'Manage Tests - US Fitness Lab';
$additionalCSS = [BASE_URL . 'assets/css/admin.css'];
$additionalJS = [
    BASE_URL . 'assets/js/admin.js',
    'https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js',
    'https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js'
];
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-vials text-primary me-2"></i>
                    Manage Tests
                </h2>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#testModal">
                        <i class="fas fa-plus me-1"></i>Add New Test
                    </button>
                    <button type="button" class="btn btn-outline-secondary refresh-stats">
                        <i class="fas fa-sync-alt me-1"></i>Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tests Table -->
    <div class="row">
        <div class="col-12">
            <div class="card admin-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2"></i>
                        Tests List
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="testsTable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Test Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Parameters</th>
                                    <th>Status</th>
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
        </div>
    </div>
</div>

<!-- Test Modal -->
<div class="modal fade" id="testModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-vial me-2"></i>
                    <span id="modalTitle">Add New Test</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="testForm" class="ajax-form">
                <div class="modal-body">
                    <input type="hidden" name="action" value="saveTest">
                    <input type="hidden" name="test_id" id="test_id">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="test_name" class="form-label">Test Name *</label>
                            <input type="text" class="form-control" id="test_name" name="test_name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="test_category" class="form-label">Category *</label>
                            <select class="form-select" id="test_category" name="category" required>
                                <option value="">Select Category</option>
                                <option value="blood">Blood Tests</option>
                                <option value="urine">Urine Tests</option>
                                <option value="stool">Stool Tests</option>
                                <option value="imaging">Imaging Tests</option>
                                <option value="cardiac">Cardiac Tests</option>
                                <option value="hormone">Hormone Tests</option>
                                <option value="cancer">Cancer Screening</option>
                                <option value="diabetes">Diabetes Tests</option>
                                <option value="liver">Liver Function Tests</option>
                                <option value="kidney">Kidney Function Tests</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="test_price" class="form-label">Price (₹) *</label>
                            <input type="number" class="form-control" id="test_price" name="price" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label for="test_duration" class="form-label">Duration (hours)</label>
                            <input type="number" class="form-control" id="test_duration" name="duration" min="1" value="24">
                        </div>
                        
                        <div class="col-12">
                            <label for="test_description" class="form-label">Description</label>
                            <textarea class="form-control" id="test_description" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="col-12">
                            <label for="preparation_instructions" class="form-label">Preparation Instructions</label>
                            <textarea class="form-control" id="preparation_instructions" name="preparation_instructions" rows="2" placeholder="Instructions for patient preparation..."></textarea>
                        </div>
                    </div>
                    
                    <!-- Test Parameters Section -->
                    <div class="mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Test Parameters</h6>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addParameter()">
                                <i class="fas fa-plus me-1"></i>Add Parameter
                            </button>
                        </div>
                        <div id="parametersContainer">
                            <!-- Parameters will be added dynamically -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Save Test
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Parameter Template (hidden) -->
<div id="parameterTemplate" style="display: none;">
    <div class="parameter-item border rounded p-3 mb-3">
        <div class="row g-2">
            <div class="col-md-3">
                <label class="form-label">Parameter Name *</label>
                <input type="text" class="form-control" name="parameters[][name]" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Unit</label>
                <input type="text" class="form-control" name="parameters[][unit]" placeholder="mg/dL">
            </div>
            <div class="col-md-3">
                <label class="form-label">Reference Range</label>
                <input type="text" class="form-control" name="parameters[][ref_range]" placeholder="0-100">
            </div>
            <div class="col-md-3">
                <label class="form-label">Method</label>
                <input type="text" class="form-control" name="parameters[][method]" placeholder="ELISA">
            </div>
            <div class="col-md-11">
                <label class="form-label">Specimen</label>
                <input type="text" class="form-control" name="parameters[][specimen]" placeholder="Blood, Urine, etc.">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeParameter(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    loadTestsTable();
    setupTestManagement();
});

function loadTestsTable() {
    if ($.fn.DataTable.isDataTable('#testsTable')) {
        $('#testsTable').DataTable().destroy();
    }
    
    $('#testsTable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: AJAX_URL,
            type: 'POST',
            data: {action: 'getTests'},
            dataSrc: function(json) {
                return json.success ? json.data : [];
            }
        },
        columns: [
            {data: 'id'},
            {data: 'name'},
            {data: 'category'},
            {
                data: 'price',
                render: function(data) {
                    return '₹' + parseFloat(data).toFixed(2);
                }
            },
            {
                data: 'parameter_count',
                render: function(data) {
                    return data || 0;
                }
            },
            {
                data: 'status',
                render: function(data) {
                    var badgeClass = data === 'active' ? 'bg-success' : 'bg-secondary';
                    return '<span class="badge ' + badgeClass + '">' + data.toUpperCase() + '</span>';
                }
            },
            {
                data: null,
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary edit-test" 
                                    data-test-id="${row.id}" 
                                    title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-outline-info view-parameters" 
                                    data-test-id="${row.id}" 
                                    title="View Parameters">
                                <i class="fas fa-list"></i>
                            </button>
                            <button class="btn btn-outline-danger delete-test" 
                                    data-test-id="${row.id}" 
                                    data-test-name="${row.name}" 
                                    title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true
    });
}

function setupTestManagement() {
    // Handle test form submission
    $('#testForm').on('submit', function(e) {
        e.preventDefault();
        saveTest();
    });
    
    // Handle edit test
    $(document).on('click', '.edit-test', function() {
        var testId = $(this).data('test-id');
        editTest(testId);
    });
    
    // Handle delete test
    $(document).on('click', '.delete-test', function() {
        var testId = $(this).data('test-id');
        var testName = $(this).data('test-name');
        deleteTest(testId, testName);
    });
    
    // Handle view parameters
    $(document).on('click', '.view-parameters', function() {
        var testId = $(this).data('test-id');
        viewTestParameters(testId);
    });
    
    // Reset form when modal is hidden
    $('#testModal').on('hidden.bs.modal', function() {
        resetTestForm();
    });
}

function addParameter() {
    var template = $('#parameterTemplate').html();
    $('#parametersContainer').append(template);
}

function removeParameter(button) {
    $(button).closest('.parameter-item').remove();
}

function saveTest() {
    var form = $('#testForm');
    var formData = new FormData(form[0]);
    
    $.ajax({
        url: AJAX_URL,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                showToast('Test saved successfully', 'success');
                $('#testModal').modal('hide');
                loadTestsTable();
            } else {
                showToast(response.message || 'Failed to save test', 'error');
            }
        }
    });
}

function editTest(testId) {
    $.ajax({
        url: AJAX_URL,
        type: 'POST',
        data: {action: 'getTest', test_id: testId},
        success: function(response) {
            if (response.success) {
                populateTestForm(response.data);
                $('#modalTitle').text('Edit Test');
                $('#testModal').modal('show');
            }
        }
    });
}

function populateTestForm(testData) {
    $('#test_id').val(testData.id);
    $('#test_name').val(testData.name);
    $('#test_category').val(testData.category);
    $('#test_price').val(testData.price);
    $('#test_duration').val(testData.duration);
    $('#test_description').val(testData.description);
    $('#preparation_instructions').val(testData.preparation_instructions);
    
    // Load parameters
    $('#parametersContainer').empty();
    if (testData.parameters && testData.parameters.length > 0) {
        testData.parameters.forEach(function(param) {
            addParameterWithData(param);
        });
    }
}

function addParameterWithData(param) {
    var template = $('#parameterTemplate').html();
    var $param = $(template);
    
    $param.find('input[name="parameters[][name]"]').val(param.name);
    $param.find('input[name="parameters[][unit]"]').val(param.unit);
    $param.find('input[name="parameters[][ref_range]"]').val(param.ref_range);
    $param.find('input[name="parameters[][method]"]').val(param.method);
    $param.find('input[name="parameters[][specimen]"]').val(param.specimen);
    
    $('#parametersContainer').append($param);
}

function deleteTest(testId, testName) {
    if (confirm('Are you sure you want to delete "' + testName + '"? This action cannot be undone.')) {
        $.ajax({
            url: AJAX_URL,
            type: 'POST',
            data: {action: 'deleteTest', test_id: testId},
            success: function(response) {
                if (response.success) {
                    showToast('Test deleted successfully', 'success');
                    loadTestsTable();
                } else {
                    showToast(response.message || 'Failed to delete test', 'error');
                }
            }
        });
    }
}

function viewTestParameters(testId) {
    $.ajax({
        url: AJAX_URL,
        type: 'POST',
        data: {action: 'getTestParameters', test_id: testId},
        success: function(response) {
            if (response.success) {
                showParametersModal(response.data);
            }
        }
    });
}

function showParametersModal(parameters) {
    var html = '<div class="table-responsive"><table class="table table-sm">';
    html += '<thead><tr><th>Parameter</th><th>Unit</th><th>Reference Range</th><th>Method</th><th>Specimen</th></tr></thead><tbody>';
    
    parameters.forEach(function(param) {
        html += '<tr>';
        html += '<td>' + param.name + '</td>';
        html += '<td>' + (param.unit || '-') + '</td>';
        html += '<td>' + (param.ref_range || '-') + '</td>';
        html += '<td>' + (param.method || '-') + '</td>';
        html += '<td>' + (param.specimen || '-') + '</td>';
        html += '</tr>';
    });
    
    html += '</tbody></table></div>';
    
    var modal = `
        <div class="modal fade" id="parametersModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Test Parameters</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">${html}</div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    $('body').append(modal);
    var modalElement = new bootstrap.Modal(document.getElementById('parametersModal'));
    modalElement.show();
    
    $('#parametersModal').on('hidden.bs.modal', function() {
        $(this).remove();
    });
}

function resetTestForm() {
    $('#testForm')[0].reset();
    $('#test_id').val('');
    $('#parametersContainer').empty();
    $('#modalTitle').text('Add New Test');
}
</script>
