<?php
session_start();
require_once 'includes/config.php';

// Check if user is logged in or demo mode
if (!isset($_SESSION['user_id'])) {
    // Allow demo mode
    if (isset($_GET['demo'])) {
        $_SESSION['user_id'] = 999;
        $_SESSION['username'] = 'demo';
        $_SESSION['full_name'] = 'Demo User';
    } else {
        header('Location: login.php');
        exit();
    }
}

$page_title = 'Patients Management';
$breadcrumbs = [
    ['title' => 'Home', 'url' => 'dashboard.php'],
    ['title' => 'Patients']
];
$additional_css = ['css/patients.css'];
$additional_js = ['js/patients.js'];

ob_start();
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="card-header-actions">
                    <div>
                        <h3 class="card-title">
                            <i class="fas fa-users mr-2"></i>
                            Patients Management
                        </h3>
                    </div>
                    <div>
                        <button type="button" class="btn btn-primary btn-sm" onclick="showAddPatientModal()">
                            <i class="fas fa-plus mr-1"></i> Add Patient
                        </button>
                        <button type="button" class="btn btn-success btn-sm" onclick="exportPatients()">
                            <i class="fas fa-download mr-1"></i> Export
                        </button>
                        <button type="button" class="btn btn-info btn-sm" onclick="refreshTable()">
                            <i class="fas fa-sync mr-1"></i> Refresh
                        </button>
                    </div>
                </div>
                
                <!-- Filters -->
                <div class="card-header-filters">
                    <div class="row">
                        <div class="col-md-3 col-sm-6 mb-2">
                            <select class="form-control select2" id="statusFilter" onchange="filterTable()">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-2">
                            <select class="form-control select2" id="bloodGroupFilter" onchange="filterTable()">
                                <option value="">All Blood Groups</option>
                                <option value="A+">A+</option>
                                <option value="A-">A-</option>
                                <option value="B+">B+</option>
                                <option value="B-">B-</option>
                                <option value="AB+">AB+</option>
                                <option value="AB-">AB-</option>
                                <option value="O+">O+</option>
                                <option value="O-">O-</option>
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-2">
                            <input type="date" class="form-control" id="dateFromFilter" onchange="filterTable()" placeholder="From Date">
                        </div>
                        <div class="col-md-3 col-sm-6 mb-2">
                            <input type="date" class="form-control" id="dateToFilter" onchange="filterTable()" placeholder="To Date">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <div class="table-responsive">
                    <table id="patientsTable" class="table table-bordered table-striped table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Blood Group</th>
                                <th>Age</th>
                                <th>Gender</th>
                                <th>Status</th>
                                <th>Created Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Patient Modal -->
<div class="modal fade" id="patientModal" tabindex="-1" role="dialog" aria-labelledby="patientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="patientModalLabel">Add Patient</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="patientForm" data-ajax="true" data-api-url="patients_api.php">
                <div class="modal-body">
                    <input type="hidden" id="patient_id" name="patient_id">
                    <input type="hidden" name="action" value="save">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="first_name">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="last_name">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone">Phone <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="age">Age <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="age" name="age" min="1" max="120" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="gender">Gender <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="gender" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="blood_group">Blood Group</label>
                                <select class="form-control select2" id="blood_group" name="blood_group">
                                    <option value="">Select Blood Group</option>
                                    <option value="A+">A+</option>
                                    <option value="A-">A-</option>
                                    <option value="B+">B+</option>
                                    <option value="B-">B-</option>
                                    <option value="AB+">AB+</option>
                                    <option value="AB-">AB-</option>
                                    <option value="O+">O+</option>
                                    <option value="O-">O-</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="address">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="3" data-auto-resize></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status">Status <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="status" name="status" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Save Patient
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Patient Modal -->
<div class="modal fade" id="viewPatientModal" tabindex="-1" role="dialog" aria-labelledby="viewPatientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewPatientModalLabel">Patient Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="patientDetailsContent">
                <!-- Patient details loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Close
                </button>
                <button type="button" class="btn btn-primary" onclick="printPatientDetails()">
                    <i class="fas fa-print mr-1"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once 'includes/layout.php';
?>
