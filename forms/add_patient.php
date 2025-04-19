<form id="addPatientForm" class="needs-validation" novalidate>
    <div class="row g-3">
        <div class="col-md-6">
            <label for="first_name" class="form-label">First Name</label>
            <input type="text" class="form-control" id="first_name" name="first_name" required>
            <div class="invalid-feedback">Please enter first name.</div>
        </div>
        <div class="col-md-6">
            <label for="last_name" class="form-label">Last Name</label>
            <input type="text" class="form-control" id="last_name" name="last_name" required>
            <div class="invalid-feedback">Please enter last name.</div>
        </div>
        <div class="col-md-6">
            <label for="date_of_birth" class="form-label">Date of Birth</label>
            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" required>
            <div class="invalid-feedback">Please select date of birth.</div>
        </div>
        <div class="col-md-6">
            <label for="gender" class="form-label">Gender</label>
            <select class="form-select" id="gender" name="gender" required>
                <option value="">Choose...</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>
            <div class="invalid-feedback">Please select gender.</div>
        </div>
        <div class="col-md-6">
            <label for="phone" class="form-label">Phone</label>
            <input type="tel" class="form-control" id="phone" name="phone" required>
            <div class="invalid-feedback">Please enter phone number.</div>
        </div>
        <div class="col-md-6">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email">
            <div class="invalid-feedback">Please enter a valid email address.</div>
        </div>
        <div class="col-12">
            <label for="address" class="form-label">Address</label>
            <textarea class="form-control" id="address" name="address" rows="3"></textarea>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-primary">Save Patient</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
    </div>
</form>

<script>
document.getElementById('addPatientForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    if (!e.target.checkValidity()) {
        e.stopPropagation();
        e.target.classList.add('was-validated');
        return;
    }
    
    try {
        const formData = new FormData(e.target);
        const response = await fetch('api/add_patient.php', {
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
        showErrorToast('An error occurred while saving the patient.');
        console.error('Error:', error);
    }
});</script> 