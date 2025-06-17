<?php
require_once '../inc/config.php';
require_once '../inc/db.php';
require_once '../auth/branch-admin-check.php';

$branch_id = $_SESSION['branch_id'] ?? 0; // Ensure branch_id is set

// --- Fetch Patients ---
$patients = [];
try {
    $stmt = $conn->prepare("SELECT id, name, phone, uhid, fn_hn, gender, age, age_unit, address, referred_by_text FROM patients WHERE branch_id = :branch_id ORDER BY name ASC");
    $stmt->bindParam(':branch_id', $branch_id, PDO::PARAM_INT);
    $stmt->execute();
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching patients for add-report: " . $e->getMessage());
    // Handle error appropriately, maybe set a message for the user
}

// --- Fetch Tests (for test name dropdown) ---
$tests_for_selection = [];
try {
    // Assuming branch_tests links active tests for the branch
    $stmt = $conn->prepare("
        SELECT t.id, t.test_name, bt.price as branch_price, t.price as master_price,
               COALESCE(t.method, NULL) as method, 
               COALESCE(t.default_report_heading, t.test_name) as default_report_heading
        FROM tests t
        JOIN branch_tests bt ON t.id = bt.test_id AND bt.branch_id = :branch_id AND bt.status = 1
        WHERE t.status = 1
        ORDER BY t.test_name ASC
    ");
    $stmt->bindParam(':branch_id', $branch_id, PDO::PARAM_INT);
    $stmt->execute();
    $tests_for_selection = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching tests for add-report: " . $e->getMessage());
}

// --- Fetch Test Packages (Placeholder) ---
$packages = []; 
// Example: $packages_stmt = $conn->query("SELECT id, package_name FROM test_packages WHERE status=1"); $packages = $packages_stmt->fetchAll(PDO::FETCH_ASSOC);


// --- Generate S.No (Placeholder - implement actual logic) ---
// This should ideally be generated upon successful save, or fetched if editing an existing report.
$s_no = ""; // Will be set by backend or if editing

// --- Default Date ---
$current_date_time = date('d-m-Y H:i');


include '../inc/branch-header.php';
?>
<link rel="stylesheet" href="add-report.css">
<style>
    /* Additional styles specific to this page if needed, or move all to add-report.css */
    .form-control-sm { height: auto; padding: .2rem .4rem; font-size: .8rem; }
    .input-group-text-sm { padding: .2rem .4rem; font-size: .8rem; }
    label { margin-bottom: .1rem; }
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.7);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5em;
    }
</style>

<div id="loadingOverlay" class="loading-overlay" style="display: none;">Loading...</div>

<div class="container-fluid report-container mt-2">
    <form id="testBillingForm">
        <input type="hidden" id="report_id" name="report_id" value=""> <!-- For editing existing reports -->
        <input type="hidden" id="branch_id" name="branch_id" value="<?php echo $branch_id; ?>">
        <div class="row">
            <!-- Left Column -->
            <div class="col-md-5">
                <div class="form-section-left patient-info-group">
                    <div class="row">
                        <div class="col-md-8">
                            <label for="patient_id" class="form-label">Patient <span class="required-asterisk">*</span></label>
                            <select id="patient_id" name="patient_id" class="form-select form-control-sm" required>
                                <option value="">Select Patient</option>
                                <?php foreach ($patients as $patient): ?>
                                    <option value="<?php echo $patient['id']; ?>"
                                            data-uhid="<?php echo htmlspecialchars($patient['uhid'] ?? ''); ?>"
                                            data-fn_hn="<?php echo htmlspecialchars($patient['fn_hn'] ?? ''); ?>"
                                            data-gender="<?php echo htmlspecialchars($patient['gender'] ?? ''); ?>"
                                            data-age="<?php echo htmlspecialchars($patient['age'] ?? ''); ?>"
                                            data-age_unit="<?php echo htmlspecialchars($patient['age_unit'] ?? 'Yrs'); ?>"
                                            data-mobile="<?php echo htmlspecialchars($patient['phone'] ?? ''); ?>"
                                            data-address="<?php echo htmlspecialchars($patient['address'] ?? ''); ?>"
                                            data-ref_by="<?php echo htmlspecialchars($patient['referred_by_text'] ?? ''); ?>">
                                        <?php echo htmlspecialchars($patient['name'] . ($patient['phone'] ? ' - ' . $patient['phone'] : '')); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="s_no" class="form-label">S. No.</label>
                            <input type="text" id="s_no" name="s_no" class="form-control form-control-sm" value="<?php echo htmlspecialchars($s_no); ?>" readonly>
                        </div>
                    </div>
                    <div class="row mt-1">
                        <div class="col-md-8">
                            <label for="fn_hn" class="form-label">FN / HN</label>
                            <input type="text" id="fn_hn" name="fn_hn" class="form-control form-control-sm">
                        </div>
                         <div class="col-md-4">
                            <label for="date_display" class="form-label">Date</label>
                            <input type="text" id="date_display" name="date_display" class="form-control form-control-sm" value="<?php echo $current_date_time; ?>" readonly>
                        </div>
                    </div>
                    <div class="row mt-1">
                        <div class="col-md-4">
                            <label for="gender" class="form-label">Gender <span class="required-asterisk">*</span></label>
                            <select id="gender" name="gender" class="form-select form-control-sm">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="age" class="form-label">Age <span class="required-asterisk">*</span></label>
                            <div class="input-group input-group-sm">
                                <input type="number" id="age" name="age" class="form-control form-control-sm" min="0">
                                <select id="age_unit" name="age_unit" class="form-select form-control-sm" style="max-width: 60px;">
                                    <option value="Yrs">Yrs</option>
                                    <option value="Mths">Mths</option>
                                    <option value="Days">Days</option>
                                </select>
                            </div>
                        </div>
                         <div class="col-md-4">
                            <label for="uhid_display" class="form-label">UHID</label>
                            <input type="text" id="uhid_display" name="uhid_display" class="form-control form-control-sm">
                        </div>
                    </div>
                     <div class="row mt-1">
                        <div class="col-md-6">
                            <label for="mobile" class="form-label">Mobile</label>
                            <input type="text" id="mobile" name="mobile" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                             <button type="button" id="edit_patient_btn" class="btn btn-sm btn-secondary me-1">Edit</button>
                             <button type="button" id="refresh_patient_list_btn" class="btn btn-sm btn-secondary">Refresh</button>
                        </div>
                    </div>
                    <div class="row mt-1">
                        <div class="col-md-12">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" id="address" name="address" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="row mt-1">
                        <div class="col-md-12">
                            <label for="ref_by" class="form-label">Ref By. <span class="required-asterisk">*</span></label>
                            <input type="text" id="ref_by" name="ref_by" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>

                <div class="form-section-left test-selection-group">
                    <div class="row">
                        <div class="col-md-9">
                            <label for="test_name_select" class="form-label">Test Name <span class="required-asterisk">*</span></label>
                            <select id="test_name_select" class="form-select form-control-sm">
                                <option value="">Select Test to Add</option>
                                <?php foreach($tests_for_selection as $test): ?>
                                    <option value="<?php echo $test['id']; ?>" 
                                            data-price="<?php echo htmlspecialchars($test['branch_price'] ?? $test['master_price'] ?? '0'); ?>"
                                            data-method="<?php echo htmlspecialchars($test['method'] ?? ''); ?>"
                                            data-heading="<?php echo htmlspecialchars($test['default_report_heading'] ?? $test['test_name']); ?>">
                                        <?php echo htmlspecialchars($test['test_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="button" id="select_package_btn" class="btn btn-sm btn-primary w-100">Select Package</button>
                        </div>
                    </div>
                </div>
                
                <div class="action-buttons-left button-bar-top">
                    <button type="button" id="delete_selected_param_btn" class="btn btn-sm btn-danger">Delete</button>
                    <button type="button" id="report_param_btn" class="btn btn-sm">Report</button> <!-- Functionality unclear -->
                    <button type="button" id="print_param_btn" class="btn btn-sm">Print</button> <!-- Functionality unclear -->
                    <button type="button" id="test_name_param_btn" class="btn btn-sm">Test Name</button> <!-- Functionality unclear -->
                    <button type="button" id="price_param_btn" class="btn btn-sm">Price</button> <!-- Functionality unclear -->
                </div>

                <div class="billing-details mt-2">
                     <div class="row mb-1 align-items-center">
                        <div class="col-md-4">
                            <button type="button" id="check_all_params_billing" class="btn btn-sm btn-outline-primary">Check All</button>
                            <button type="button" id="uncheck_all_params_billing" class="btn btn-sm btn-outline-secondary ms-1">Uncheck All</button>
                        </div>
                        <label for="subtotal" class="col-md-4 col-form-label text-end">Subtotal</label>
                        <div class="col-md-4">
                            <input type="text" id="subtotal" name="subtotal" class="form-control form-control-sm text-end" value="0.00" readonly>
                        </div>
                    </div>
                    <div class="row mb-1 align-items-center">
                        <label for="out_s_test_label" class="col-md-4 col-form-label">Out S. Test</label>
                        <div class="col-md-4">
                             <input type="text" id="out_s_test_label" name="out_s_test_label" class="form-control form-control-sm text-end" value="0.00" readonly> <!-- This seems like a label from screenshot -->
                        </div>
                        <label for="discount_amount" class="col-md-1 col-form-label text-end">(-) Discount</label>
                        <div class="col-md-3">
                            <div class="input-group input-group-sm">
                                <input type="number" id="discount_percentage" name="discount_percentage" class="form-control form-control-sm text-end" placeholder="%" min="0" max="100" step="0.01">
                                <input type="number" id="discount_amount" name="discount_amount" class="form-control form-control-sm text-end" placeholder="Amt" min="0" step="0.01">
                            </div>
                        </div>
                    </div>
                     <div class="row mb-1 align-items-center">
                        <label for="status_payment" class="col-md-2 col-form-label">Status</label>
                        <div class="col-md-3">
                            <select id="status_payment" name="status_payment" class="form-select form-control-sm">
                                <option value="Pending">Pending</option>
                                <option value="Paid">Paid</option>
                                <option value="Partial">Partial</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>
                         <div class="col-md-1">
                            <button type="button" id="add_payment_btn" class="btn btn-sm btn-primary">Add Payment</button>
                        </div>
                        <label for="total_amount" class="col-md-3 col-form-label text-end">Total Amount</label>
                        <div class="col-md-3">
                            <input type="text" id="total_amount" name="total_amount" class="form-control form-control-sm text-end" value="0.00" readonly>
                        </div>
                    </div>
                    <div class="row mb-1 align-items-center">
                        <label for="note_billing" class="col-md-2 col-form-label">Note</label>
                        <div class="col-md-4">
                            <input type="text" id="note_billing" name="note_billing" class="form-control form-control-sm">
                        </div>
                        <label for="paid_amount" class="col-md-3 col-form-label text-end">Paid Amount</label>
                        <div class="col-md-3">
                            <input type="number" id="paid_amount" name="paid_amount" class="form-control form-control-sm text-end" value="0.00" min="0" step="0.01">
                        </div>
                    </div>
                     <div class="row align-items-center">
                        <div class="col-md-6">
                            <!-- Spacer -->
                        </div>
                        <label for="balance_amount" class="col-md-3 col-form-label text-end">Balance</label>
                        <div class="col-md-3">
                            <input type="text" id="balance_amount" name="balance_amount" class="form-control form-control-sm text-end" value="0.00" readonly>
                        </div>
                    </div>
                </div>

                <div class="action-buttons-bottom-left mt-2">
                    <button type="button" id="btn_new" class="btn btn-primary">New</button>
                    <button type="submit" id="btn_save" class="btn btn-success">Save</button>
                    <button type="button" id="btn_print_bill" class="btn btn-info">Print Bill</button>
                    <button type="button" id="btn_print_multiple_reports" class="btn btn-info">Print Multiple Reports</button>
                    <button type="button" id="btn_add_new_doctor" class="btn btn-secondary">Add New Doctor</button>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-md-7">
                <div class="form-section-right">
                    <div class="header-title">Test Parameters</div>
                    <div class="table-responsive" style="max-height: 300px; overflow-y: auto; "> <!-- Added scroll for parameter table -->
                        <table class="table table-bordered table-report-parameters" id="reportParametersTable">
                            <thead>
                                <tr>
                                    <th style="width: 5%;"><input type="checkbox" id="check_all_table_params" class="form-check-input"></th>
                                    <th style="width: 5%;">Bold</th>
                                    <th style="width: 25%;">Parameter</th>
                                    <th style="width: 10%;">Specimen</th>
                                    <th style="width: 15%;">Result</th>
                                    <th style="width: 10%;">Unit</th>
                                    <th style="width: 20%;">Ref. Range</th>
                                    <th style="width: 5%;">Min</th>
                                    <th style="width: 5%;">Max</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Rows will be added here by JavaScript -->
                            </tbody>
                        </table>
                        
                    </div>
                    <button type="button" id="add_parameter_row" class="btn btn-sm btn-outline-success mb-2 mt-1">+ Add Row</button>

                    <div class="mt-2">
                        <div class="row">
                            <div class="col-md-8">
                                <label for="report_heading" class="form-label">Heading</label>
                                <input type="text" id="report_heading" name="report_heading" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-4">
                                <label for="reporting_date_display" class="form-label">Reporting Date</label>
                                <input type="datetime-local" id="reporting_date_display" name="reporting_date_display" class="form-control form-control-sm" value="<?php echo date('Y-m-d\\TH:i'); ?>">
                            </div>
                        </div>
                         <div class="row mt-1">
                            <div class="col-md-12">
                                <label for="report_method" class="form-label">Method</label>
                                <input type="text" id="report_method" name="report_method" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="row mt-1">
                            <div class="col-md-12">
                                <label for="report_comments" class="form-label">Comments</label>
                                <textarea id="report_comments" name="report_comments" class="form-control form-control-sm" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="action-buttons-bottom-right mt-2">
                    <button type="button" id="btn_save_next" class="btn btn-success">Save & Next Report >></button>
                    <button type="button" id="btn_print_report" class="btn btn-primary">Print Report</button>
                    <div class="form-check form-check-inline ms-2">
                        <input class="form-check-input" type="checkbox" id="new_page_checkbox" name="new_page_checkbox" value="1">
                        <label class="form-check-label" for="new_page_checkbox">New Page</label>
                    </div>
                    <button type="button" id="btn_close_esc" class="btn btn-danger float-end">Close (Esc)</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const loadingOverlay = document.getElementById('loadingOverlay');
    const patientSelect = document.getElementById('patient_id');
    const testNameSelect = document.getElementById('test_name_select');
    const reportParametersTableBody = document.querySelector('#reportParametersTable tbody');
    const addParameterRowButton = document.getElementById('add_parameter_row');
    const subtotalInput = document.getElementById('subtotal');
    const discountPercentageInput = document.getElementById('discount_percentage');
    const discountAmountInput = document.getElementById('discount_amount');
    const totalAmountInput = document.getElementById('total_amount');
    const paidAmountInput = document.getElementById('paid_amount');
    const balanceAmountInput = document.getElementById('balance_amount');
    const billingForm = document.getElementById('testBillingForm');
    const reportHeadingInput = document.getElementById('report_heading');
    const reportMethodInput = document.getElementById('report_method');
    const sNoInput = document.getElementById('s_no');
    const reportIdInput = document.getElementById('report_id');

    let selectedTests = {}; // To keep track of tests added and their parameters

    function showLoading(show) {
        loadingOverlay.style.display = show ? 'flex' : 'none';
    }

    // Patient selection updates fields
    if(patientSelect) {
        patientSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            document.getElementById('fn_hn').value = selectedOption.dataset.fn_hn || '';
            document.getElementById('gender').value = selectedOption.dataset.gender || 'Male';
            document.getElementById('age').value = selectedOption.dataset.age || '';
            document.getElementById('age_unit').value = selectedOption.dataset.age_unit || 'Yrs';
            document.getElementById('uhid_display').value = selectedOption.dataset.uhid || '';
            document.getElementById('mobile').value = selectedOption.dataset.mobile || '';
            document.getElementById('address').value = selectedOption.dataset.address || '';
            document.getElementById('ref_by').value = selectedOption.dataset.ref_by || '';
        });
    }

    // Test selection - Fetches parameters and adds to table
    if(testNameSelect && reportParametersTableBody) {
        testNameSelect.addEventListener('change', function() {
            const selectedTestOption = this.options[this.selectedIndex];
            if (!selectedTestOption.value) return;

            const testId = selectedTestOption.value;
            const testName = selectedTestOption.text;
            const testPrice = parseFloat(selectedTestOption.dataset.price || 0);
            const defaultMethod = selectedTestOption.dataset.method || '';
            const defaultHeading = selectedTestOption.dataset.heading || testName;

            if (selectedTests[testId]) {
                alert('This test has already been added.');
                this.value = ''; // Reset select
                return;
            }

            showLoading(true);
            fetch(`ajax/get-test-parameters.php?test_id=${testId}`)
                .then(response => response.json())
                .then(data => {
                    showLoading(false);
                    if (data.success && data.parameters) {
                        selectedTests[testId] = { name: testName, price: testPrice, parameters: data.parameters };
                        
                        // Set report heading and method if they are empty or from a previous test
                        if (!reportHeadingInput.value || (Object.keys(selectedTests).length === 1)) {
                            reportHeadingInput.value = data.test_details?.default_report_heading || defaultHeading;
                        }
                        if (!reportMethodInput.value || (Object.keys(selectedTests).length === 1)) {
                            reportMethodInput.value = data.test_details?.method || defaultMethod;
                        }

                        data.parameters.forEach(param => {
                            addParameterEntry(param, testId, testName);
                        });
                        updateSubtotal(testPrice);
                    } else {
                        alert('Error fetching parameters: ' + (data.message || 'Unknown error'));
                        // Add a single row for the test itself if no params, allowing manual entry
                        addParameterEntry({ parameter_name: testName, is_bold_default: true }, testId, testName, true);
                        updateSubtotal(testPrice);
                    }
                    this.value = ''; // Reset select
                })
                .catch(error => {
                    showLoading(false);
                    console.error('Fetch Error:', error);
                    alert('Could not fetch test parameters. Adding test name as a parameter.');
                    addParameterEntry({ parameter_name: testName, is_bold_default: true }, testId, testName, true);
                    updateSubtotal(testPrice);
                    this.value = ''; // Reset select
                });
        });
    }
    
    if(addParameterRowButton && reportParametersTableBody){
        addParameterRowButton.addEventListener('click', function(){
            addParameterEntry({}, null, 'Manual Entry'); // Add a blank row, not associated with a specific test_id initially
        });
    }

    function addParameterEntry(param, testId = null, testName = 'N/A', isTestHeader = false) {
        const newRow = reportParametersTableBody.insertRow();
        newRow.dataset.testId = testId; // Store testId for potential grouping or deletion
        newRow.dataset.testParameterId = param.test_parameter_id || ''; // Store original parameter ID
        
        let paramName = param.parameter_name || '';
        if (isTestHeader) {
            // For test headers, make the parameter name bold and read-only if desired
            // This is a simple way to denote the test itself in the parameters list
        }

        newRow.innerHTML = `
            <td class="checkbox-cell"><input type="checkbox" name="param_selected[]" class="form-check-input param-select-checkbox" value="${param.test_parameter_id || 'new_'+Date.now()}" checked></td>
            <td class="checkbox-cell"><input type="checkbox" name="param_is_bold[]" class="form-check-input" ${param.is_bold_default ? 'checked' : ''}></td>
            <td><input type="text" name="param_name[]" class="form-control form-control-sm" value="${paramName}" ${isTestHeader ? 'readonly style="font-weight:bold;"' : ''}></td>
            <td><input type="text" name="param_specimen[]" class="form-control form-control-sm" value="${param.specimen || ''}"></td>
            <td><input type="text" name="param_result[]" class="form-control form-control-sm"></td>
            <td><input type="text" name="param_unit[]" class="form-control form-control-sm" value="${param.default_unit || ''}"></td>
            <td><input type="text" name="param_ref_range[]" class="form-control form-control-sm" value="${param.ref_range || ''}"></td>
            <td><input type="text" name="param_min_value[]" class="form-control form-control-sm" value="${param.min_value || ''}"></td>
            <td><input type="text" name="param_max_value[]" class="form-control form-control-sm" value="${param.max_value || ''}"></td>
            <input type="hidden" name="param_test_id[]" value="${testId || ''}">
            <input type="hidden" name="param_test_parameter_id[]" value="${param.test_parameter_id || ''}">
        `;
    }

    // Check/Uncheck all for table parameters
    const checkAllTableParams = document.getElementById('check_all_table_params');
    if(checkAllTableParams) {
        checkAllTableParams.addEventListener('change', function() {
            document.querySelectorAll('#reportParametersTable tbody .param-select-checkbox').forEach(cb => {
                cb.checked = this.checked;
            });
        });
    }
    
    // Delete selected parameters from table
    const deleteSelectedParamBtn = document.getElementById('delete_selected_param_btn');
    if(deleteSelectedParamBtn){
        deleteSelectedParamBtn.addEventListener('click', function(){
            const selectedCheckboxes = document.querySelectorAll('#reportParametersTable tbody .param-select-checkbox:checked');
            if(selectedCheckboxes.length === 0){
                alert("Please select parameters to delete.");
                return;
            }
            if(confirm("Are you sure you want to delete the selected parameters?")){
                selectedCheckboxes.forEach(cb => {
                    const row = cb.closest('tr');
                    const testId = row.dataset.testId;
                    // If this is the last parameter for a test, or a test header itself, remove test from subtotal and selectedTests
                    // This logic needs refinement if tests can have zero parameters initially
                    // For now, we assume deletion of any row means we might need to adjust subtotal if it was a test header or only param.
                    // A more robust way would be to sum prices of *remaining* tests.
                    row.remove();
                });
                recalculateSubtotalAndAll(); // Recalculate subtotal based on remaining tests
            }
        });
    }

    function recalculateSubtotalAndAll(){
        let newSubtotal = 0;
        const currentTestIdsInTable = new Set();
        document.querySelectorAll('#reportParametersTable tbody tr').forEach(row => {
            if(row.dataset.testId) currentTestIdsInTable.add(row.dataset.testId);
        });

        let tempSelectedTests = {};
        for (const testId in selectedTests) {
            if (currentTestIdsInTable.has(testId)) {
                newSubtotal += selectedTests[testId].price;
                tempSelectedTests[testId] = selectedTests[testId];
            }
        }
        selectedTests = tempSelectedTests; // Update selectedTests to only include those still in table
        subtotalInput.value = newSubtotal.toFixed(2);
        calculateTotalAndBalance();
    }

    // Check/Uncheck all for left panel (conceptual - what does this do?)
    // Assuming it refers to the parameters in the table for now.
    const checkAllLeftBilling = document.getElementById('check_all_params_billing');
    const uncheckAllLeftBilling = document.getElementById('uncheck_all_params_billing');
    if(checkAllLeftBilling) checkAllLeftBilling.addEventListener('click', () => { 
        document.querySelectorAll('#reportParametersTable tbody .param-select-checkbox').forEach(cb => cb.checked = true);
        if(checkAllTableParams) checkAllTableParams.checked = true;
    });
    if(uncheckAllLeftBilling) uncheckAllLeftBilling.addEventListener('click', () => { 
        document.querySelectorAll('#reportParametersTable tbody .param-select-checkbox').forEach(cb => cb.checked = false);
        if(checkAllTableParams) checkAllTableParams.checked = false;
    });


    // Billing Calculations
    function updateSubtotal(amountToAdd = 0) {
        let currentSubtotal = parseFloat(subtotalInput.value) || 0;
        currentSubtotal += amountToAdd;
        subtotalInput.value = currentSubtotal.toFixed(2);
        calculateTotalAndBalance();
    }

    function calculateTotalAndBalance() {
        let subtotal = parseFloat(subtotalInput.value) || 0;
        let discountAmount = parseFloat(discountAmountInput.value) || 0;
        let paidAmount = parseFloat(paidAmountInput.value) || 0;

        let totalAmount = subtotal - discountAmount;
        if (totalAmount < 0) totalAmount = 0;
        totalAmountInput.value = totalAmount.toFixed(2);

        let balanceAmount = totalAmount - paidAmount;
        balanceAmountInput.value = balanceAmount.toFixed(2);
    }

    if(discountPercentageInput) {
        discountPercentageInput.addEventListener('input', function() {
            let subtotal = parseFloat(subtotalInput.value) || 0;
            let percentage = parseFloat(this.value) || 0;
            if (percentage > 100) percentage = 100;
            if (percentage < 0) percentage = 0;
            // this.value = percentage; // Allow user to type, then validate on blur or save
            discountAmountInput.value = ((subtotal * percentage) / 100).toFixed(2);
            calculateTotalAndBalance();
        });
    }
    if(discountAmountInput) {
        discountAmountInput.addEventListener('input', function() {
            let subtotal = parseFloat(subtotalInput.value) || 0;
            let discount = parseFloat(this.value) || 0;
            // if (discount > subtotal) discount = subtotal; // Allow discount > subtotal, total will be 0
            if (discount < 0) discount = 0;
            // this.value = discount; 
            
            if (subtotal > 0 && discount > 0 && discount <= subtotal) {
                 let newPercentage = (discount / subtotal) * 100;
                 discountPercentageInput.value = newPercentage.toFixed(2);
            } else if (discount > subtotal && subtotal > 0){
                discountPercentageInput.value = '100.00'; // Cap percentage at 100 if discount exceeds subtotal
            }            
            else {
                discountPercentageInput.value = '';
            }
            calculateTotalAndBalance();
        });
    }
    if(paidAmountInput) paidAmountInput.addEventListener('input', calculateTotalAndBalance);
    
    // Initial calculation
    calculateTotalAndBalance();

    // Form Submission (AJAX)
    if(billingForm) {
        billingForm.addEventListener('submit', function(event) {
            event.preventDefault();
            showLoading(true);
            const formData = new FormData(this);
            
            // Consolidate selected tests and their parameters
            let reportItems = [];
            document.querySelectorAll('#reportParametersTable tbody tr').forEach((row, index) => {
                const isSelected = row.querySelector('input[name="param_selected[]"]')?.checked;
                if (!isSelected) return; // Skip if not selected

                reportItems.push({
                    test_id: row.dataset.testId,
                    test_parameter_id: row.dataset.testParameterId, // original test_parameter.id or empty for new
                    is_bold: row.querySelector('input[name="param_is_bold[]"]')?.checked,
                    parameter_name: row.querySelector('input[name="param_name[]"]')?.value,
                    specimen: row.querySelector('input[name="param_specimen[]"]')?.value,
                    result: row.querySelector('input[name="param_result[]"]')?.value,
                    unit: row.querySelector('input[name="param_unit[]"]')?.value,
                    ref_range: row.querySelector('input[name="param_ref_range[]"]')?.value,
                    min_value: row.querySelector('input[name="param_min_value[]"]')?.value,
                    max_value: row.querySelector('input[name="param_max_value[]"]')?.value,
                    // Add sort_order if you implement reordering in the table
                });
            });
            formData.append('report_items', JSON.stringify(reportItems));
            formData.append('selected_tests', JSON.stringify(Object.keys(selectedTests).map(id => ({id: id, price: selectedTests[id].price}))));

            // Debug: Log formData contents
            // console.log("Form Data to be submitted:");
            // for (let [key, value] of formData.entries()) {
            //     console.log(key, value);
            // }

            fetch('ajax/add-report.php', { 
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                showLoading(false);
                if(data.success) {
                    alert('Report saved successfully! S.No: ' + data.s_no);
                    sNoInput.value = data.s_no;
                    reportIdInput.value = data.report_id;
                    // Disable save button to prevent duplicate submissions until 'New' is clicked
                    document.getElementById('btn_save').disabled = true;
                    // Optionally reset parts of the form or prepare for next entry
                } else {
                    alert('Error: ' + (data.message || 'Could not save report.'));
                }
            })
            .catch(error => {
                showLoading(false);
                console.error('Error:', error);
                alert('An error occurred while saving. Please check console.');
            });
        });
    }
    
    // Button actions
    document.getElementById('btn_new')?.addEventListener('click', () => { 
        if(confirm('Are you sure you want to clear the form for a new entry? Unsaved data will be lost.')){
            billingForm.reset(); 
            if(patientSelect) patientSelect.dispatchEvent(new Event('change')); // Trigger patient data clear
            reportParametersTableBody.innerHTML = ''; // Clear parameters table
            subtotalInput.value = '0.00';
            selectedTests = {}; // Clear selected tests cache
            reportHeadingInput.value = '';
            reportMethodInput.value = '';
            sNoInput.value = ''; // Clear S.No
            reportIdInput.value = ''; // Clear Report ID
            document.getElementById('reporting_date_display').value = new Date().toISOString().slice(0,16);
            document.getElementById('date_display').value = new Date().toLocaleString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'}).replace(/,/g, '');
            calculateTotalAndBalance();
            document.getElementById('btn_save').disabled = false; // Re-enable save button
        }
    });
    document.getElementById('btn_close_esc')?.addEventListener('click', () => {
        if(confirm('Are you sure you want to close? Unsaved changes will be lost.')){
            window.location.href = 'dashboard.php'; 
        }
    });

    // Placeholder for Refresh Patient List
    document.getElementById('refresh_patient_list_btn')?.addEventListener('click', () => {
        // Implement AJAX to refresh patient list if needed, then repopulate patientSelect
        alert('Patient list refresh functionality to be implemented.');
    });

    // Placeholder for Edit Patient
    document.getElementById('edit_patient_btn')?.addEventListener('click', () => {
        const selectedPatientId = patientSelect.value;
        if(selectedPatientId){
            // Redirect to patient edit page or open modal
            alert('Edit patient functionality to be implemented for patient ID: ' + selectedPatientId);
            // window.location.href = `edit-patient.php?id=${selectedPatientId}`; // Example
        } else {
            alert('Please select a patient to edit.');
        }
    });

    // Placeholder for Add New Doctor
    document.getElementById('btn_add_new_doctor')?.addEventListener('click', () => {
        // Open modal or redirect to add doctor page
        alert('Add new doctor functionality to be implemented.');
    });

    // Placeholder for Print Bill
    document.getElementById('btn_print_bill')?.addEventListener('click', () => {
        const currentReportId = reportIdInput.value;
        if(currentReportId){
            alert('Print bill functionality for S.No: ' + sNoInput.value + ' (Report ID: ' + currentReportId + ') to be implemented.');
            // window.open(`print-bill.php?report_id=${currentReportId}`, '_blank');
        } else {
            alert('Please save the report first to print a bill.');
        }
    });

    // Placeholder for Print Report
    document.getElementById('btn_print_report')?.addEventListener('click', () => {
        const currentReportId = reportIdInput.value;
        if(currentReportId){
            alert('Print report functionality for S.No: ' + sNoInput.value + ' (Report ID: ' + currentReportId + ') to be implemented.');
            // window.open(`print-report.php?report_id=${currentReportId}`, '_blank');
        } else {
            alert('Please save the report first to print it.');
        }
    });
    
    // Set initial dates
    document.getElementById('reporting_date_display').value = new Date().toISOString().slice(0,16);
    document.getElementById('date_display').value = new Date().toLocaleString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'}).replace(/,/g, '');

});
</script>

<?php include '../inc/footer.php'; ?>