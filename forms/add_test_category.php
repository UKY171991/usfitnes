<form id="addTestCategoryForm" class="needs-validation" novalidate>
    <div class="row g-3">
        <div class="col-12">
            <label for="category_name" class="form-label">Category Name</label>
            <input type="text" class="form-control" id="category_name" name="category_name" required>
            <div class="invalid-feedback">Please enter category name.</div>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-primary">Save Category</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
    </div>
</form>

<script>
document.getElementById('addTestCategoryForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    if (!e.target.checkValidity()) {
        e.stopPropagation();
        e.target.classList.add('was-validated');
        return;
    }
    
    try {
        const formData = new FormData(e.target);
        const response = await fetch('api/add_test_category.php', {
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
        showErrorToast('An error occurred while saving the category.');
        console.error('Error:', error);
    }
});</script> 