<?php
require_once '../config.php';
require_once '../db_connect.php';

// Get test categories
try {
    $db = Database::getInstance();
    $categories = $db->query("SELECT category_id, category_name FROM Test_Categories WHERE branch_id = :branch_id", 
        ['branch_id' => $_SESSION['branch_id']])->fetchAll();
} catch (Exception $e) {
    $categories = [];
}
?>

<form id="addTestForm" class="needs-validation" novalidate>
    <div class="row g-3">
        <div class="col-md-6">
            <label for="category_id" class="form-label">Test Category</label>
            <select class="form-select" id="category_id" name="category_id" required>
                <option value="">Choose...</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo htmlspecialchars($category['category_id']); ?>">
                        <?php echo htmlspecialchars($category['category_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="invalid-feedback">Please select a category.</div>
        </div>
        <div class="col-md-6">
            <label for="test_name" class="form-label">Test Name</label>
            <input type="text" class="form-control" id="test_name" name="test_name" required>
            <div class="invalid-feedback">Please enter test name.</div>
        </div>
        <div class="col-md-6">
            <label for="test_code" class="form-label">Test Code</label>
            <input type="text" class="form-control" id="test_code" name="test_code" required>
            <div class="invalid-feedback">Please enter test code.</div>
        </div>
        <div class="col-md-6">
            <label for="price" class="form-label">Price</label>
            <input type="number" class="form-control" id="price" name="price" step="0.01" required>
            <div class="invalid-feedback">Please enter price.</div>
        </div>
        <div class="col-12">
            <label for="parameters" class="form-label">Parameters</label>
            <textarea class="form-control" id="parameters" name="parameters" rows="2"></textarea>
            <div class="form-text">Enter parameters separated by commas.</div>
        </div>
        <div class="col-12">
            <label for="reference_range" class="form-label">Reference Range</label>
            <textarea class="form-control" id="reference_range" name="reference_range" rows="2"></textarea>
        </div>
        <div class="col-md-6">
            <label for="normal_range" class="form-label">Normal Range</label>
            <input type="text" class="form-control" id="normal_range" name="normal_range">
        </div>
        <div class="col-md-6">
            <label for="unit" class="form-label">Unit</label>
            <input type="text" class="form-control" id="unit" name="unit">
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-primary">Save Test</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
    </div>
</form>

<script>
document.getElementById('addTestForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    if (!e.target.checkValidity()) {
        e.stopPropagation();
        e.target.classList.add('was-validated');
        return;
    }
    
    try {
        const formData = new FormData(e.target);
        const response = await fetch('api/add_test.php', {
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
        showErrorToast('An error occurred while saving the test.');
        console.error('Error:', error);
    }
});</script> 