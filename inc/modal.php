<?php
// Common modal component for insert operations
?>
<!-- Generic Modal -->
<div class="modal fade" id="genericModal" tabindex="-1" aria-labelledby="genericModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="genericModalLabel">Add New Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Dynamic form content will be loaded here -->
                <div id="modalContent"></div>
            </div>
        </div>
    </div>
</div>

<!-- Success Toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div id="successToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                Record saved successfully!
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<!-- Error Toast -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div id="errorToast" class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="errorToastMessage">
                An error occurred while saving the record.
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script>
// Common JavaScript functions for modal operations
function showModal(title, content) {
    document.getElementById('genericModalLabel').textContent = title;
    document.getElementById('modalContent').innerHTML = content;
    new bootstrap.Modal(document.getElementById('genericModal')).show();
}

function showSuccessToast() {
    const toast = new bootstrap.Toast(document.getElementById('successToast'));
    toast.show();
}

function showErrorToast(message) {
    document.getElementById('errorToastMessage').textContent = message || 'An error occurred while saving the record.';
    const toast = new bootstrap.Toast(document.getElementById('errorToast'));
    toast.show();
}

function handleFormSubmit(formId, endpoint) {
    const form = document.getElementById(formId);
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        try {
            const formData = new FormData(form);
            const response = await fetch(endpoint, {
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
            showErrorToast('An error occurred while saving the record.');
            console.error('Error:', error);
        }
    });
}
</script> 