<?php
require_once '../config.php';
require_once '../db_connect.php';

// Get test request details
$request_id = $_GET['request_id'] ?? 0;

try {
    $db = Database::getInstance();
    $branch_id = $_SESSION['branch_id'];

    $request = $db->query(
        "SELECT tr.request_id, tr.test_id, tr.patient_id, tr.priority,
                p.first_name, p.last_name,
                t.test_name, t.test_code, t.parameters, t.reference_range, t.normal_range, t.unit
         FROM Test_Requests tr
         JOIN Patients p ON tr.patient_id = p.patient_id
         JOIN Tests_Catalog t ON tr.test_id = t.test_id
         WHERE tr.request_id = :request_id AND tr.branch_id = :branch_id",
        ['request_id' => $request_id, 'branch_id' => $branch_id]
    )->fetch();

    if (!$request) {
        throw new Exception('Test request not found');
    }

    // Parse parameters if they exist
    $parameters = $request['parameters'] ? explode(',', $request['parameters']) : [];
} catch (Exception $e) {
    $request = null;
    $parameters = [];
}
?>

<form id="addTestResultForm" class="needs-validation" novalidate>
    <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($request_id); ?>">
    
    <?php if ($request): ?>
    <div class="row g-3">
        <div class="col-12">
            <div class="alert alert-info">
                <h6 class="mb-1">Test Request Details</h6>
                <p class="mb-1">
                    <strong>Patient:</strong> <?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?><br>
                    <strong>Test:</strong> <?php echo htmlspecialchars($request['test_name'] . ' (' . $request['test_code'] . ')'); ?><br>
                    <strong>Priority:</strong> <?php echo htmlspecialchars($request['priority']); ?>
                </p>
            </div>
        </div>

        <?php if (!empty($parameters)): ?>
            <?php foreach ($parameters as $index => $parameter): ?>
                <div class="col-md-6">
                    <label for="sub_test_<?php echo $index; ?>" class="form-label"><?php echo htmlspecialchars(trim($parameter)); ?></label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="sub_test_<?php echo $index; ?>" 
                               name="sub_tests[<?php echo $index; ?>][value]" required>
                        <input type="text" class="form-control" placeholder="Unit" 
                               name="sub_tests[<?php echo $index; ?>][unit]">
                        <input type="hidden" name="sub_tests[<?php echo $index; ?>][name]" 
                               value="<?php echo htmlspecialchars(trim($parameter)); ?>">
                    </div>
                    <div class="form-text">Normal Range: <?php echo htmlspecialchars($request['normal_range'] ?? ''); ?></div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <label for="result_value" class="form-label">Result Value</label>
                <input type="text" class="form-control" id="result_value" name="result_value" required>
                <?php if ($request['normal_range']): ?>
                    <div class="form-text">Normal Range: <?php echo htmlspecialchars($request['normal_range']); ?></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="col-12">
            <label for="comments" class="form-label">Comments</label>
            <textarea class="form-control" id="comments" name="comments" rows="3"></textarea>
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-primary">Save Result</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
    </div>
    <?php else: ?>
    <div class="alert alert-danger">
        Test request not found or you don't have permission to view it.
    </div>
    <?php endif; ?>
</form>

<script>
document.getElementById('addTestResultForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    if (!e.target.checkValidity()) {
        e.stopPropagation();
        e.target.classList.add('was-validated');
        return;
    }
    
    try {
        const formData = new FormData(e.target);
        const response = await fetch('api/add_test_result.php', {
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
        showErrorToast('An error occurred while saving the test result.');
        console.error('Error:', error);
    }
});</script> 