<?php
require_once 'includes/adminlte_template.php';

// Patients page content function
function patientsContent() {
    global $pdo;
    
    // Get patients data
    try {
        $stmt = $pdo->query("SELECT * FROM patients WHERE status = 'active' ORDER BY created_at DESC LIMIT 100");
        $patients = $stmt->fetchAll();
    } catch (Exception $e) {
        $patients = [];
    }
    ?>
    
    <div class="row">
        <div class="col-12">
            <?php
            echo createCard(
                '<i class="fas fa-user-injured mr-2"></i>Patients Management',
                '<div class="row mb-3">
                    <div class="col-md-6">
                        <button class="btn btn-primary" data-toggle="modal" data-target="#addPatientModal">
                            <i class="fas fa-plus mr-2"></i>Add New Patient
                        </button>
                        <button class="btn btn-success ml-2" onclick="exportPatients()">
                            <i class="fas fa-download mr-2"></i>Export
                        </button>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Search patients..." id="patient-search">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="patients-table">
                        <thead>
                            <tr>
                                <th>Patient ID</th>
                                <th>Name</th>
                                <th>Gender</th>
                                <th>Age</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Blood Group</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($patients as $patient): 
                                $age = 'N/A';
                                if (!empty($patient['date_of_birth'])) {
                                    try {
                                        $birthDate = new DateTime($patient['date_of_birth']);
                                        $today = new DateTime('today');
                                        $age = $birthDate->diff($today)->y;
                                    } catch (Exception $e) {
                                        $age = 'N/A';
                                    }
                                }
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($patient['patient_id'] ?? ''); ?></strong></td>
                                <td>
                                    <div class="user-block">
                                        <div class="username">
                                            <?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?>
                                        </div>
                                        <div class="description text-muted">
                                            <?php echo ucfirst(htmlspecialchars($patient['gender'] ?? 'Unknown')); ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($patient['gender']): ?>
                                        <span class="badge badge-<?php echo $patient['gender'] === 'male' ? 'primary' : ($patient['gender'] === 'female' ? 'danger' : 'secondary'); ?>">
                                            <i class="fas fa-<?php echo $patient['gender'] === 'male' ? 'mars' : ($patient['gender'] === 'female' ? 'venus' : 'genderless'); ?>"></i>
                                            <?php echo ucfirst(htmlspecialchars($patient['gender'])); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">Unknown</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $age; ?></td>
                                <td>
                                    <?php if ($patient['phone']): ?>
                                        <a href="tel:<?php echo htmlspecialchars($patient['phone']); ?>">
                                            <i class="fas fa-phone mr-1"></i><?php echo htmlspecialchars($patient['phone']); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Not provided</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($patient['email']): ?>
                                        <a href="mailto:<?php echo htmlspecialchars($patient['email']); ?>">
                                            <i class="fas fa-envelope mr-1"></i><?php echo htmlspecialchars($patient['email']); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Not provided</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($patient['blood_group']): ?>
                                        <span class="badge badge-info">
                                            <i class="fas fa-tint mr-1"></i><?php echo htmlspecialchars($patient['blood_group']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">Unknown</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar mr-1"></i>
                                        <?php echo date('M j, Y', strtotime($patient['created_at'])); ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-info" onclick="viewPatient(<?php echo $patient['id']; ?>)" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-warning" onclick="editPatient(<?php echo $patient['id']; ?>)" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-success" onclick="createTestOrder(<?php echo $patient['id']; ?>)" title="New Test">
                                            <i class="fas fa-flask"></i>
                                        </button>
                                        <button class="btn btn-danger" onclick="deletePatient(<?php echo $patient['id']; ?>)" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>'
            );
            ?>
        </div>
    </div>
    
    <script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#patients-table').DataTable({
            responsive: true,
            dom: 'Bfrtip',
            buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
            pageLength: 25,
            order: [[7, 'desc']] // Sort by registered date
        });
    });
    
    // Patient action functions
    function viewPatient(id) {
        window.open('patient_details.php?id=' + id, '_blank');
    }
    
    function editPatient(id) {
        showInfo('Loading patient data...');
        // Implementation would go here
    }
    
    function createTestOrder(id) {
        window.location.href = 'test-orders.php?patient_id=' + id + '&action=create';
    }
    
    function deletePatient(id) {
        confirmAction(
            'Are you sure you want to delete this patient?',
            function() {
                $.ajax({
                    url: 'api/patients_api.php',
                    type: 'POST',
                    data: { action: 'delete', id: id },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showSuccess('Patient deleted successfully');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            showError(response.message || 'Failed to delete patient');
                        }
                    },
                    error: function() {
                        showError('Network error. Please try again.');
                    }
                });
            },
            'Delete Patient'
        );
    }
    
    function exportPatients() {
        showInfo('Preparing export...');
        window.open('exports/patients_export.php', '_blank');
    }
    </script>
    
    <?php
}

// Render the patients page
renderTemplate('patients', 'patientsContent');
?>
