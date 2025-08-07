<?php
session_start();
require_once 'includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$page_title = 'Doctors Management';
$breadcrumbs = [
    ['title' => 'Home', 'url' => 'dashboard.php'],
    ['title' => 'Doctors']
];
$additional_css = ['css/doctors.css'];
$additional_js = ['js/doctors.js'];

ob_start();
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="card-header-actions">
                    <div>
                        <h3 class="card-title">
                            <i class="fas fa-user-md mr-2"></i>
                            Doctors Management
                        </h3>
                    </div>
                    <div>
                        <button type="button" class="btn btn-primary btn-sm" onclick="showAddDoctorModal()">
                            <i class="fas fa-plus mr-1"></i> Add Doctor
                        </button>
                        <button type="button" class="btn btn-success btn-sm" onclick="exportDoctors()">
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
                            <input type="text" class="form-control" id="specializationFilter" onchange="filterTable()" placeholder="Filter by specialization">
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
                    <table id="doctorsTable" class="table table-bordered table-striped table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Specialization</th>
                                <th>License No.</th>
                                <th>Hospital</th>
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

<!-- Add/Edit Doctor Modal -->
<div class="modal fade" id="doctorModal" tabindex="-1" role="dialog" aria-labelledby="doctorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="doctorModalLabel">Add Doctor</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="doctorForm" data-ajax="true" data-api-url="doctors_api.php">
                <div class="modal-body">
                    <input type="hidden" id="doctor_id" name="doctor_id">
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
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="specialization">Specialization <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="specialization" name="specialization" required>
                                    <option value="">Select Specialization</option>
                                    <option value="Pathologist">Pathologist</option>
                                    <option value="Radiologist">Radiologist</option>
                                    <option value="Cardiologist">Cardiologist</option>
                                    <option value="Neurologist">Neurologist</option>
                                    <option value="Orthopedic">Orthopedic</option>
                                    <option value="Pediatrician">Pediatrician</option>
                                    <option value="Gynecologist">Gynecologist</option>
                                    <option value="General Physician">General Physician</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="license_number">License Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="license_number" name="license_number" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="hospital_affiliation">Hospital Affiliation</label>
                                <input type="text" class="form-control" id="hospital_affiliation" name="hospital_affiliation" placeholder="Current hospital or clinic">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="notes">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3" data-auto-resize placeholder="Additional notes or comments"></textarea>
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
                        <i class="fas fa-save mr-1"></i> Save Doctor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Doctor Modal -->
<div class="modal fade" id="viewDoctorModal" tabindex="-1" role="dialog" aria-labelledby="viewDoctorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewDoctorModalLabel">Doctor Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="doctorDetailsContent">
                <!-- Doctor details loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Close
                </button>
                <button type="button" class="btn btn-primary" onclick="printDoctorDetails()">
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
