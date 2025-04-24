<?php
require_once '../inc/config.php';
require_once '../inc/db.php';
require_once '../auth/branch-admin-check.php';
include '../inc/branch-header.php';

$branch_id = $_SESSION['branch_id'];

// Fetch patients
$patient_stmt = $conn->prepare("SELECT id, name, phone FROM patients WHERE branch_id = ? ORDER BY name");
$patient_stmt->execute([$branch_id]);
$patients = $patient_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch tests
$test_stmt = $conn->prepare(
    "SELECT t.id, t.test_name, t.price, c.category_name 
     FROM tests t 
     JOIN test_categories c ON t.category_id = c.id
     JOIN branch_tests bt ON t.id = bt.test_id
     WHERE bt.branch_id = ? 
     ORDER BY c.category_name, t.test_name"
);
$test_stmt->execute([$branch_id]);
$tests = $test_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <h1 class="h3 mb-4">Create New Report</h1>

    <form id="addReportForm" method="POST" action="ajax/add-report.php">
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Select Patient</label>
                <select class="form-select" name="patient_id" required>
                    <option value="">Select Patient</option>
                    <?php foreach ($patients as $patient): ?>
                        <option value="<?= $patient['id'] ?>">
                            <?= htmlspecialchars($patient['name']) ?> (<?= $patient['phone'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Select Test</label>
                <select class="form-select" name="test_id" id="testSelect">
                    <option value="">Select Test</option>
                    <?php
                    $current_category = '';
                    foreach ($tests as $test):
                        if ($current_category != $test['category_name']):
                            if ($current_category != '') echo "</optgroup>";
                            echo "<optgroup label='" . htmlspecialchars($test['category_name']) . "'>";
                            $current_category = $test['category_name'];
                        endif;
                        ?>
                        <option value="<?= $test['id'] ?>" data-price="<?= $test['price'] ?>">
                            <?= htmlspecialchars($test['test_name']) ?> (₹<?= number_format($test['price'], 2) ?>)
                        </option>
                    <?php endforeach; ?>
                    <?php if ($current_category != '') echo "</optgroup>"; ?>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Selected Tests</label>
            <div id="selectedTestsContainer"></div>
            <button type="button" class="btn btn-outline-primary mt-2" id="addTestButton">Add Test</button>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Total Price</label>
                <input type="number" class="form-control" name="price" id="testPrice" readonly>
            </div>
            <div class="col-md-6">
                <label class="form-label">Payment Amount</label>
                <input type="number" class="form-control" name="paid_amount" required min="0" step="0.01">
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Payment Method</label>
                <select class="form-select" name="payment_method" required>
                    <option value="">Select Method</option>
                    <option value="cash">Cash</option>
                    <option value="card">Card</option>
                    <option value="upi">UPI</option>
                    <option value="netbanking">Net Banking</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Transaction ID</label>
                <input type="text" class="form-control" name="transaction_id">
                <small class="text-muted">Required for Card/UPI/Net Banking payments</small>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Due Amount</label>
                <input type="number" class="form-control" id="dueAmount" readonly>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Notes</label>
            <textarea class="form-control" name="notes" rows="2"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Create Report</button>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const testSelect = document.getElementById('testSelect');
        const testPriceInput = document.getElementById('testPrice');
        const paymentAmountInput = document.querySelector('input[name="paid_amount"]');
        const dueAmountInput = document.getElementById('dueAmount');
        const selectedTestsContainer = document.getElementById('selectedTestsContainer');
        const addTestButton = document.getElementById('addTestButton');

        // Function to add a selected test to the list
        function addSelectedTest(testId, testName, testPrice) {
            const row = document.createElement('div');
            row.className = 'row mb-2';
            row.dataset.testId = testId;

            row.innerHTML = `
                <div class="col-md-6">
                    <input type="text" class="form-control" value="${testName}" readonly>
                </div>
                <div class="col-md-4">
                    <input type="number" class="form-control test-price" value="${testPrice}" readonly>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger remove-test">&times;</button>
                </div>
            `;

            selectedTestsContainer.appendChild(row);

            // Add event listener to remove button
            row.querySelector('.remove-test').addEventListener('click', function () {
                row.remove();
                updateTotalPrice();
            });

            updateTotalPrice();
        }

        // Update total price based on selected tests
        function updateTotalPrice() {
            let totalPrice = 0;
            document.querySelectorAll('.test-price').forEach(input => {
                totalPrice += parseFloat(input.value) || 0;
            });
            testPriceInput.value = totalPrice.toFixed(2);
            updateDueAmount();
        }

        // Update due amount dynamically
        function updateDueAmount() {
            const testPrice = parseFloat(testPriceInput.value) || 0;
            const paymentAmount = parseFloat(paymentAmountInput.value) || 0;
            const dueAmount = testPrice - paymentAmount;
            dueAmountInput.value = dueAmount > 0 ? dueAmount.toFixed(2) : '0.00';
        }

        // Add test to the list when the button is clicked
        addTestButton.addEventListener('click', function () {
            const selectedOption = testSelect.options[testSelect.selectedIndex];
            const testId = selectedOption.value;
            const testName = selectedOption.text;
            const testPrice = selectedOption.getAttribute('data-price');

            if (testId && testPrice) {
                addSelectedTest(testId, testName, testPrice);
            }
        });

        paymentAmountInput.addEventListener('input', updateDueAmount);
    });
</script>

<?php include '../inc/footer.php'; ?>