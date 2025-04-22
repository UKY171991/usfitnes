<?php
require_once '../inc/config.php';
require_once '../inc/db.php';
require_once '../auth/session-check.php';

checkBranchAdminAccess();

$branch_id = $_SESSION['branch_id'];
// $user_id = $_SESSION['user_id']; // Removed user ID as it's handled in AJAX

// REMOVED all PHP POST handling logic (Add, Edit, Delete)
// It's now handled by ajax/add-patient.php, ajax/update-patient.php, ajax/delete-patient.php

// --- Fetch Initial Data (Still needed for initial page load) --- //

// Get all patients for this branch initially
$patients_stmt = $conn->prepare("
    SELECT id, name, age, LOWER(TRIM(gender)) as gender, phone, email, address 
    FROM patients 
    WHERE branch_id = ? 
    ORDER BY name
");
$patients_stmt->execute([$branch_id]);
$patients_list = $patients_stmt->fetchAll(PDO::FETCH_ASSOC);

include '../inc/branch-header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Patients</h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPatientModal">
        <i class="fas fa-user-plus"></i> Add New Patient
    </button>
</div>

<!-- Placeholder for AJAX messages -->
<div id="message-container" class="mb-3"></div> 

<!-- Patients Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Patient Details</th>
                        <th>Contact</th>
                        <th>Address</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="patients-table-body"> 
                    <!-- Table rows will be populated by PHP initially and by JavaScript after AJAX calls -->
                    <?php if (empty($patients_list)): ?>
                        <tr id="no-patients-row">
                            <td colspan="5" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-users fa-2x mb-2"></i>
                                    <p>No patients found for this branch.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($patients_list as $patient): ?>
                            <?php 
                                // We still need this cleanup for the initial PHP load
                                $patient_data_for_js = $patient; 
                            ?>
                            <tr>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?php echo $patient['id']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($patient['name']); ?></div>
                                    <small class="text-muted">
                                        Age: <?php echo htmlspecialchars($patient['age'] ?: '-'); ?> |
                                        Gender: <?php echo ucfirst(htmlspecialchars($patient['gender'] ?: '-')); ?>
                                    </small>
                                </td>
                                <td>
                                    <div><?php echo htmlspecialchars($patient['phone'] ?: '-'); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($patient['email'] ?: 'No email'); ?></small>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 250px;" title="<?php echo htmlspecialchars($patient['address']); ?>">
                                        <?php echo htmlspecialchars($patient['address'] ?: '-'); ?>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-info view-patient" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#viewPatientModal"
                                                data-patient='<?php echo htmlspecialchars(json_encode($patient_data_for_js)); ?>'
                                                title="View Patient">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-primary edit-patient" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editPatientModal"
                                                data-patient='<?php echo htmlspecialchars(json_encode($patient_data_for_js)); ?>'
                                                title="Edit Patient">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <!-- Delete button changed to trigger JS -->
                                        <button type="button" class="btn btn-sm btn-danger delete-patient" 
                                                data-patient-id="<?php echo $patient['id']; ?>" 
                                                data-patient-name="<?php echo htmlspecialchars($patient['name']); ?>"
                                                title="Delete Patient">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <!-- <a href="new-report.php?patient_id=<?php echo $patient['id']; ?>" 
                                           class="btn btn-sm btn-success" title="New Report">
                                            <i class="fas fa-file-medical"></i>
                                        </a> -->
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- View Patient Modal -->
<div class="modal fade" id="viewPatientModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Patient Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                 <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title">Personal Information</h6>
                                <dl class="row mb-0">
                                    <dt class="col-sm-4">Name</dt>
                                    <dd class="col-sm-8" id="view-patient-name">-</dd>
                                    <dt class="col-sm-4">Age</dt>
                                    <dd class="col-sm-8" id="view-patient-age">-</dd>
                                    <dt class="col-sm-4">Gender</dt>
                                    <dd class="col-sm-8" id="view-patient-gender">-</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title">Contact Information</h6>
                                <dl class="row mb-0">
                                    <dt class="col-sm-4">Phone</dt>
                                    <dd class="col-sm-8" id="view-patient-phone">-</dd>
                                    <dt class="col-sm-4">Email</dt>
                                    <dd class="col-sm-8" id="view-patient-email">-</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                         <div class="card h-100">
                            <div class="card-body">
                                <h6 class="card-title">Address</h6>
                                <p id="view-patient-address">-</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Patient Modal -->
<div class="modal fade" id="editPatientModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Patient</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
             <!-- Changed form ID -->
             <form id="editPatientForm" class="needs-validation" novalidate>
                <!-- Removed action="" and method="POST" -->
                <!-- Removed hidden input for edit_patient -->
                <input type="hidden" name="patient_id" id="edit_patient_id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="edit_name" class="form-label">Name *</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                            <div class="invalid-feedback">Please enter patient name.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_age" class="form-label">Age *</label>
                            <input type="number" class="form-control" id="edit_age" name="age" required min="0">
                             <div class="invalid-feedback">Please enter a valid age.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_gender" class="form-label">Gender *</label>
                            <select class="form-select" id="edit_gender" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                            <div class="invalid-feedback">Please select a gender.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="edit_phone" name="phone">
                        </div>
                        <div class="col-md-6">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email">
                            <div class="invalid-feedback">Please enter a valid email address.</div>
                        </div>
                        <div class="col-12">
                            <label for="edit_address" class="form-label">Address</label>
                            <textarea class="form-control" id="edit_address" name="address" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Patient Modal -->
<div class="modal fade" id="addPatientModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Patient</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <!-- Changed form ID -->
            <form id="addPatientForm" class="needs-validation" novalidate>
                 <!-- Removed action="" and method="POST" -->
                 <!-- Removed hidden input for add_patient -->
                 <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="add_name" class="form-label">Name *</label>
                            <input type="text" class="form-control" id="add_name" name="name" required>
                            <div class="invalid-feedback">Please enter patient name.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="add_age" class="form-label">Age *</label>
                            <input type="number" class="form-control" id="add_age" name="age" required min="0">
                            <div class="invalid-feedback">Please enter a valid age.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="add_gender" class="form-label">Gender *</label>
                            <select class="form-select" id="add_gender" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                            <div class="invalid-feedback">Please select a gender.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="add_phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="add_phone" name="phone">
                        </div>
                        <div class="col-md-6">
                            <label for="add_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="add_email" name="email">
                            <div class="invalid-feedback">Please enter a valid email address.</div>
                        </div>
                        <div class="col-12">
                            <label for="add_address" class="form-label">Address</label>
                            <textarea class="form-control" id="add_address" name="address" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Patient</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Modal References --- 
    const addPatientModalEl = document.getElementById('addPatientModal');
    const editPatientModalEl = document.getElementById('editPatientModal');
    const viewPatientModalEl = document.getElementById('viewPatientModal');
    const addPatientModal = bootstrap.Modal.getInstance(addPatientModalEl) || new bootstrap.Modal(addPatientModalEl);
    const editPatientModal = bootstrap.Modal.getInstance(editPatientModalEl) || new bootstrap.Modal(editPatientModalEl);
    const viewPatientModal = bootstrap.Modal.getInstance(viewPatientModalEl) || new bootstrap.Modal(viewPatientModalEl);

    // --- Form References ---
    const addPatientForm = document.getElementById('addPatientForm');
    const editPatientForm = document.getElementById('editPatientForm');

    // --- Message Container ---
    const messageContainer = document.getElementById('message-container');

    // --- Table Body ---
    const patientsTableBody = document.getElementById('patients-table-body');

    // --- Helper Functions ---
    function showMessage(message, type = 'success') {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        messageContainer.innerHTML = `<div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                                        ${message}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                      </div>`;
    }

    function escapeHtml(unsafe) {
        if (unsafe === null || typeof unsafe === 'undefined') return '-';
        return String(unsafe)
             .replace(/&/g, "&amp;")
             .replace(/</g, "&lt;")
             .replace(/>/g, "&gt;")
             .replace(/"/g, "&quot;")
             .replace(/'/g, "&#039;");
    }

    function refreshPatientTable() {
        fetch('ajax/get-patients.php') // Assumes get-patients.php is in the ajax folder
            .then(response => response.json())
            .then(data => {
                if (data.success && data.patients) {
                    patientsTableBody.innerHTML = ''; // Clear existing rows
                    if (data.patients.length === 0) {
                        patientsTableBody.innerHTML = `<tr id="no-patients-row">
                            <td colspan="5" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-users fa-2x mb-2"></i>
                                    <p>No patients found for this branch.</p>
                                </div>
                            </td>
                        </tr>`;
                    } else {
                        data.patients.forEach(patient => {
                            const patientDataJson = escapeHtml(JSON.stringify(patient));
                            const genderDisplay = patient.gender ? patient.gender.charAt(0).toUpperCase() + patient.gender.slice(1) : '-';
                            const addressDisplay = escapeHtml(patient.address);
                            const addressTitle = addressDisplay !== '-' ? `title="${addressDisplay}"` : '';
                            const truncatedAddress = addressDisplay.length > 50 ? addressDisplay.substring(0, 47) + '...' : addressDisplay;

                            const row = `
                                <tr>
                                    <td><span class="badge bg-secondary">${patient.id}</span></td>
                                    <td>
                                        <div class="fw-bold">${escapeHtml(patient.name)}</div>
                                        <small class="text-muted">
                                            Age: ${escapeHtml(patient.age)} | Gender: ${genderDisplay}
                                        </small>
                                    </td>
                                    <td>
                                        <div>${escapeHtml(patient.phone)}</div>
                                        <small class="text-muted">${escapeHtml(patient.email) || 'No email'}</small>
                                    </td>
                                    <td>
                                        <div class="text-truncate" style="max-width: 250px;" ${addressTitle}>
                                            ${truncatedAddress}
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-info view-patient" 
                                                    data-bs-toggle="modal" data-bs-target="#viewPatientModal"
                                                    data-patient='${patientDataJson}' title="View Patient">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-primary edit-patient" 
                                                    data-bs-toggle="modal" data-bs-target="#editPatientModal"
                                                    data-patient='${patientDataJson}' title="Edit Patient">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                             <button type="button" class="btn btn-sm btn-danger delete-patient" 
                                                    data-patient-id="${patient.id}" 
                                                    data-patient-name="${escapeHtml(patient.name)}"
                                                    title="Delete Patient">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <a href="new-report.php?patient_id=${patient.id}" 
                                               class="btn btn-sm btn-success" title="New Report">
                                                <i class="fas fa-file-medical"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>`;
                            patientsTableBody.innerHTML += row;
                        });
                         // Re-attach event listeners after refreshing table content
                        attachActionListeners(); 
                    }
                } else {
                    showMessage(data.message || 'Failed to fetch patients.', 'danger');
                }
            })
            .catch(error => {
                console.error('Error fetching patients:', error);
                showMessage('An error occurred while refreshing the patient list.', 'danger');
            });
    }

    // --- Form Validation (Bootstrap's standard validation) ---
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // --- Event Listeners ---

    // Reset Add form when modal is shown
    addPatientModalEl.addEventListener('show.bs.modal', function () {
        addPatientForm.reset();
        addPatientForm.classList.remove('was-validated');
    });

    // Handle Add Patient Form Submission
    addPatientForm.addEventListener('submit', function(event) {
        event.preventDefault();
        event.stopPropagation();

        if (!addPatientForm.checkValidity()) {
             addPatientForm.classList.add('was-validated');
            return;
        }

        const formData = new FormData(addPatientForm);
        const submitButton = addPatientForm.querySelector('button[type="submit"]');
        submitButton.disabled = true; // Disable button during request

        fetch('ajax/add-patient.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            showMessage(data.message, data.success ? 'success' : 'danger');
            if (data.success) {
                addPatientModal.hide();
                refreshPatientTable(); // Refresh table on success
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('An unexpected error occurred.', 'danger');
        })
        .finally(() => {
            submitButton.disabled = false; // Re-enable button
        });
    });

    // Handle Edit Patient Form Submission
    editPatientForm.addEventListener('submit', function(event) {
        event.preventDefault();
        event.stopPropagation();

        if (!editPatientForm.checkValidity()) {
            editPatientForm.classList.add('was-validated');
            return;
        }

        const formData = new FormData(editPatientForm);
        const submitButton = editPatientForm.querySelector('button[type="submit"]');
        submitButton.disabled = true;

        fetch('ajax/update-patient.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            showMessage(data.message, data.success ? 'success' : 'danger');
            if (data.success) {
                editPatientModal.hide();
                refreshPatientTable(); // Refresh table on success
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('An unexpected error occurred.', 'danger');
        })
         .finally(() => {
            submitButton.disabled = false;
        });
    });
    
    // --- Function to Attach Listeners to Action Buttons (needed after table refresh) ---
    function attachActionListeners() {
        // Handle View Button Clicks (Attach to table body for delegation)
        patientsTableBody.querySelectorAll('.view-patient').forEach(button => {
             // Clone and replace to remove old listeners if any
             const newButton = button.cloneNode(true);
             button.parentNode.replaceChild(newButton, button);

            newButton.addEventListener('click', function() {
                const patientData = JSON.parse(this.dataset.patient);
                document.getElementById('view-patient-name').textContent = patientData.name || '-';
                document.getElementById('view-patient-age').textContent = patientData.age || '-';
                document.getElementById('view-patient-gender').textContent = patientData.gender ? patientData.gender.charAt(0).toUpperCase() + patientData.gender.slice(1) : '-';
                document.getElementById('view-patient-phone').textContent = patientData.phone || '-';
                document.getElementById('view-patient-email').textContent = patientData.email || '-';
                document.getElementById('view-patient-address').textContent = patientData.address || '-';
                // No need to manually show modal, data-bs-toggle does it
            });
        });

        // Handle Edit Button Clicks (Attach to table body for delegation)
        patientsTableBody.querySelectorAll('.edit-patient').forEach(button => {
            // Clone and replace to remove old listeners
            const newButton = button.cloneNode(true);
            button.parentNode.replaceChild(newButton, button);

            newButton.addEventListener('click', function() {
                const patientData = JSON.parse(this.dataset.patient);
                document.getElementById('edit_patient_id').value = patientData.id;
                document.getElementById('edit_name').value = patientData.name || '';
                document.getElementById('edit_age').value = patientData.age || '';
                document.getElementById('edit_gender').value = patientData.gender || ''; // Should be lowercase now
                document.getElementById('edit_phone').value = patientData.phone || '';
                document.getElementById('edit_email').value = patientData.email || '';
                document.getElementById('edit_address').value = patientData.address || '';
                
                // Reset validation state
                 const form = editPatientModalEl.querySelector('form');
                 form.classList.remove('was-validated');
                // No need to manually show modal, data-bs-toggle does it
            });
        });

        // Handle Delete Button Clicks (Attach to table body for delegation)
        patientsTableBody.querySelectorAll('.delete-patient').forEach(button => {
            // Clone and replace to remove old listeners
            const newButton = button.cloneNode(true);
            button.parentNode.replaceChild(newButton, button);

            newButton.addEventListener('click', function() {
                const patientId = this.dataset.patientId;
                const patientName = this.dataset.patientName;
                
                if (confirm(`Are you sure you want to delete patient: ${patientName} (ID: ${patientId})?`)) {
                     // Disable button during request
                    this.disabled = true; 
                    
                    const formData = new FormData();
                    formData.append('patient_id', patientId);

                    fetch('ajax/delete-patient.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        showMessage(data.message, data.success ? 'success' : 'danger');
                        if (data.success) {
                            refreshPatientTable(); // Refresh table on success
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showMessage('An unexpected error occurred during deletion.', 'danger');
                    })
                    .finally(() => {
                       // Don't re-enable here, row will be removed on success
                       // If it fails, maybe re-enable? Or let user retry?
                       // For now, leave it disabled on failure too, message shown. 
                    });
                }
            });
        });
    }

    // --- Initial Setup ---
    attachActionListeners(); // Attach listeners on initial page load

});
</script>

<?php include '../inc/footer.php'; ?> 