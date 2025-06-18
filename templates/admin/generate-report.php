<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';

// Check if user is branch admin or master admin
if (!isLoggedIn() || ($_SESSION['user_role'] !== 'branch_admin' && $_SESSION['user_role'] !== 'master_admin')) {
    header('Location: ../patient/login');
    exit;
}

$title = 'Generate Report - US Fitness Lab';
$additionalCSS = [BASE_URL . 'assets/css/admin.css'];
$additionalJS = [BASE_URL . 'assets/js/admin.js'];
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-file-medical text-primary me-2"></i>
                    Generate Test Report
                </h2>
                <button type="button" class="btn btn-outline-secondary" onclick="window.history.back()">
                    <i class="fas fa-arrow-left me-1"></i>Back
                </button>
            </div>
        </div>
    </div>
    
    <form id="generateReportForm" class="ajax-form">
        <input type="hidden" name="action" value="generateReport">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        
        <div class="row">
            <!-- Left Panel - Patient & Billing Information -->
            <div class="col-lg-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-user-injured me-2"></i>
                            Patient Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="patient_name" class="form-label">Patient Name</label>
                                <input type="text" class="form-control" id="patient_name" name="patient_name" required>
                            </div>
                            <div class="col-md-3">
                                <label for="fn_hn" class="form-label">FN/HN</label>
                                <input type="text" class="form-control" id="fn_hn" name="fn_hn">
                            </div>
                            <div class="col-md-3">
                                <label for="gender" class="form-label">Gender</label>
                                <select class="form-select" id="gender" name="gender" required>
                                    <option value="">Select</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="age" class="form-label">Age</label>
                                <input type="number" class="form-control" id="age" name="age" min="0" max="150" required>
                            </div>
                            <div class="col-md-9">
                                <label for="mobile" class="form-label">Mobile</label>
                                <input type="tel" class="form-control" id="mobile" name="mobile" pattern="[0-9]{10}" required>
                            </div>
                            
                            <div class="col-12">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="ref_by" class="form-label">Referred By</label>
                                <input type="text" class="form-control" id="ref_by" name="ref_by">
                            </div>
                            <div class="col-md-6">
                                <label for="test_name" class="form-label">Test Name</label>
                                <select class="form-select" id="test_name" name="test_id" required>
                                    <option value="">Select Test</option>
                                    <!-- Options loaded via AJAX -->
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="test_date" class="form-label">Test Date</label>
                                <input type="date" class="form-control" id="test_date" name="test_date" required>
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="pending">Pending</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Billing Information -->
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-rupee-sign me-2"></i>
                            Billing Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="subtotal" class="form-label">Subtotal</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" class="form-control" id="subtotal" name="subtotal" step="0.01" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="discount" class="form-label">Discount</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" class="form-control" id="discount" name="discount" step="0.01" value="0">
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="total_amount" class="form-label">Total Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" class="form-control" id="total_amount" name="total_amount" step="0.01" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="paid_amount" class="form-label">Paid Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" class="form-control" id="paid_amount" name="paid_amount" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="balance" class="form-label">Balance</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" class="form-control" id="balance" name="balance" step="0.01" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Panel - Test Parameters & Results -->
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-vials me-2"></i>
                            Test Parameters & Results
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="test-parameters">
                            <!-- Dynamic test parameters loaded via AJAX -->
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-vial fa-3x mb-3"></i>
                                <p>Select a test to load parameters</p>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="reporting_date" class="form-label">Reporting Date</label>
                                    <input type="date" class="form-control" id="reporting_date" name="reporting_date" value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="report_comments" class="form-label">Report Comments</label>
                                    <textarea class="form-control" id="report_comments" name="report_comments" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12">
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                        <i class="fas fa-undo me-1"></i>Reset
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Generate Report
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Load tests on page load
$(document).ready(function() {
    loadTests();
    setupBillingCalculations();
    setupTestParameterLoading();
});

function loadTests() {
    $.ajax({
        url: AJAX_URL,
        type: 'POST',
        data: {action: 'getTests'},
        success: function(response) {
            if (response.success) {
                var select = $('#test_name');
                select.empty().append('<option value="">Select Test</option>');
                
                response.data.forEach(function(test) {
                    select.append('<option value="' + test.id + '" data-price="' + test.price + '">' + test.name + '</option>');
                });
            }
        }
    });
}

function setupBillingCalculations() {
    $('#subtotal, #discount, #paid_amount').on('input', function() {
        calculateTotals();
    });
    
    $('#test_name').on('change', function() {
        var selectedOption = $(this).find(':selected');
        var price = selectedOption.data('price');
        if (price) {
            $('#subtotal').val(price);
            calculateTotals();
        }
    });
}

function calculateTotals() {
    var subtotal = parseFloat($('#subtotal').val()) || 0;
    var discount = parseFloat($('#discount').val()) || 0;
    var paidAmount = parseFloat($('#paid_amount').val()) || 0;
    
    var totalAmount = subtotal - discount;
    var balance = totalAmount - paidAmount;
    
    $('#total_amount').val(totalAmount.toFixed(2));
    $('#balance').val(balance.toFixed(2));
}

function setupTestParameterLoading() {
    $('#test_name').on('change', function() {
        var testId = $(this).val();
        if (testId) {
            loadTestParameters(testId);
        } else {
            $('#test-parameters').html('<div class="text-center text-muted py-4"><i class="fas fa-vial fa-3x mb-3"></i><p>Select a test to load parameters</p></div>');
        }
    });
}

function loadTestParameters(testId) {
    $.ajax({
        url: AJAX_URL,
        type: 'POST',
        data: {action: 'getTestParameters', test_id: testId},
        success: function(response) {
            if (response.success) {
                renderTestParameters(response.data);
            } else {
                $('#test-parameters').html('<div class="alert alert-warning">No parameters found for this test</div>');
            }
        }
    });
}

function renderTestParameters(parameters) {
    var html = '';
    
    parameters.forEach(function(param, index) {
        html += `
            <div class="parameter-group mb-3 p-3 border rounded">
                <div class="row g-2">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Parameter</label>
                        <input type="text" class="form-control" name="parameters[${index}][name]" value="${param.name}" readonly>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Specimen</label>
                        <input type="text" class="form-control" name="parameters[${index}][specimen]" value="${param.specimen || ''}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Result</label>
                        <input type="text" class="form-control" name="parameters[${index}][result]" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Unit</label>
                        <input type="text" class="form-control" name="parameters[${index}][unit]" value="${param.unit || ''}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Reference Range</label>
                        <input type="text" class="form-control" name="parameters[${index}][ref_range]" value="${param.ref_range || ''}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Method</label>
                        <input type="text" class="form-control" name="parameters[${index}][method]" value="${param.method || ''}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Comments</label>
                        <input type="text" class="form-control" name="parameters[${index}][comments]">
                    </div>
                </div>
            </div>
        `;
    });
    
    $('#test-parameters').html(html);
}

function resetForm() {
    $('#generateReportForm')[0].reset();
    $('#test-parameters').html('<div class="text-center text-muted py-4"><i class="fas fa-vial fa-3x mb-3"></i><p>Select a test to load parameters</p></div>');
    calculateTotals();
}
</script>
