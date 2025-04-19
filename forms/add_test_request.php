<?php
require_once '../config.php';
require_once '../db_connect.php';

// Get patients and tests
try {
    $db = Database::getInstance();
    $branch_id = $_SESSION['branch_id'];

    $patients = $db->query(
        "SELECT patient_id, CONCAT(first_name, ' ', last_name) as patient_name 
         FROM Patients 
         WHERE branch_id = :branch_id 
         ORDER BY first_name, last_name",
        ['branch_id' => $branch_id]
    )->fetchAll();

    $tests = $db->query(
        "SELECT t.test_id, t.test_name, t.test_code, t.price, c.category_name 
         FROM Tests_Catalog t
         JOIN Test_Categories c ON t.category_id = c.category_id
         WHERE t.branch_id = :branch_id
         ORDER BY c.category_name, t.test_name",
        ['branch_id' => $branch_id]
    )->fetchAll();
} catch (Exception $e) {
    $patients = [];
    $tests = [];
}
?>

<form id="addTestRequestForm" class="needs-validation" novalidate>
    <div class="row g-3">
        <div class="col-md-6">
            <label for="patient_id" class="form-label">Patient</label>
            <select class="form-select" id="patient_id" name="patient_id" required>
                <option value="">Choose...</option>
                <?php foreach ($patients as $patient): ?>
                    <option value="<?php echo htmlspecialchars($patient['patient_id']); ?>">
                        <?php echo htmlspecialchars($patient['patient_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="invalid-feedback">Please select a patient.</div>
        </div>
        <div class="col-md-6">
            <label for="test_id" class="form-label">Test</label>
            <select class="form-select" id="test_id" name="test_id" required>
                <option value="">Choose...</option>
                <?php 
                $current_category = '';
                foreach ($tests as $test):
                    if ($current_category != $test['category_name']):
                        if ($current_category != '') echo '</optgroup>';
                        $current_category = $test['category_name'];
                        echo '<optgroup label="' . htmlspecialchars($current_category) . '">';
                    endif;
                ?>
                    <option value="<?php echo htmlspecialchars($test['test_id']); ?>" 
                            data-price="<?php echo htmlspecialchars($test['price']); ?>">
                        <?php echo htmlspecialchars($test['test_name'] . ' (' . $test['test_code'] . ')'); ?>
                    </option>
                <?php 
                endforeach;
                if ($current_category != '') echo '</optgroup>';
                ?>
            </select>
            <div class="invalid-feedback">Please select a test.</div>
        </div>
        <div class="col-md-6">
            <label for="ordered_by" class="form-label">Ordered By</label>
            <input type="text" class="form-control" id="ordered_by" name="ordered_by" required>
            <div class="invalid-feedback">Please enter who ordered the test.</div>
        </div>
        <div class="col-md-6">
            <label for="priority" class="form-label">Priority</label>
            <select class="form-select" id="priority" name="priority">
                <option value="Normal">Normal</option>
                <option value="Urgent">Urgent</option>
            </select>
        </div>
        <div class="col-12">
            <div class="alert alert-info">
                Test Price: <span id="testPrice">₹0.00</span>
            </div>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-primary">Save Test Request</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
    </div>
</form>

<script>
// Update test price when test is selected
document.getElementById('test_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const price = selectedOption.dataset.price || 0;
    document.getElementById('testPrice').textContent = `₹${parseFloat(price).toFixed(2)}`;
});

document.getElementById('addTestRequestForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    if (!e.target.checkValidity()) {
        e.stopPropagation();
        e.target.classList.add('was-validated');
        return;
    }
    
    try {
        const formData = new FormData(e.target);
        const response = await fetch('api/add_test_request.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showSuccessToast();
            bootstrap.Modal.getInstance(document.getElementById('genericModal')).hide();
            if (typeof refreshTable === 'function') {
                refreshTable();
            }
        } else {
            showErrorToast(result.message);
        }
    } catch (error) {
        showErrorToast('An error occurred while saving the test request.');
        console.error('Error:', error);
    }
});</script> 