<?php
// Set page title and active menu
$page_title = 'Equipment Management';
$active_menu = 'equipment';

// Include header and sidebar
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
  <!-- Content Header -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">Equipment Management</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
            <li class="breadcrumb-item active">Equipment</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      <!-- Stats Row -->
      <div class="row">
        <div class="col-lg-3 col-6">
          <div class="small-box bg-info">
            <div class="inner">
              <h3 id="totalEquipment">0</h3>
              <p>Total Equipment</p>
            </div>
            <div class="icon">
              <i class="fas fa-microscope"></i>
            </div>
            <a href="#" class="small-box-footer">View Details <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-success">
            <div class="inner">
              <h3 id="operationalEquipment">0</h3>
              <p>Operational</p>
            </div>
            <div class="icon">
              <i class="fas fa-check-circle"></i>
            </div>
            <a href="#" class="small-box-footer" onclick="filterByStatus('operational')">View Details <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-warning">
            <div class="inner">
              <h3 id="maintenanceEquipment">0</h3>
              <p>Under Maintenance</p>
            </div>
            <div class="icon">
              <i class="fas fa-tools"></i>
            </div>
            <a href="#" class="small-box-footer" onclick="filterByStatus('maintenance')">View Details <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-danger">
            <div class="inner">
              <h3 id="outOfServiceEquipment">0</h3>
              <p>Out of Service</p>
            </div>
            <div class="icon">
              <i class="fas fa-exclamation-triangle"></i>
            </div>
            <a href="#" class="small-box-footer" onclick="filterByStatus('out-of-service')">View Details <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
      </div>

      <!-- Main Card -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Laboratory Equipment</h3>
              <div class="card-tools">
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addEquipmentModal">
                  <i class="fas fa-plus"></i> Add Equipment
                </button>
                <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#maintenanceScheduleModal">
                  <i class="fas fa-tools"></i> Maintenance Schedule
                </button>
                <button type="button" class="btn btn-info btn-sm" onclick="exportEquipment()">
                  <i class="fas fa-download"></i> Export
                </button>
              </div>
            </div>
            <div class="card-body">
              <!-- Filters -->
              <div class="row mb-3">
                <div class="col-md-3">
                  <div class="input-group input-group-sm">
                    <input type="text" class="form-control" id="searchInput" placeholder="Search equipment...">
                    <div class="input-group-append">
                      <button class="btn btn-outline-secondary" type="button" onclick="loadEquipment()">
                        <i class="fas fa-search"></i>
                      </button>
                    </div>
                  </div>
                </div>
                <div class="col-md-2">
                  <select class="form-control form-control-sm" id="statusFilter" onchange="loadEquipment()">
                    <option value="">All Status</option>
                    <option value="operational">Operational</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="out-of-service">Out of Service</option>
                  </select>
                </div>
                <div class="col-md-2">
                  <select class="form-control form-control-sm" id="typeFilter" onchange="loadEquipment()">
                    <option value="">All Types</option>
                    <option value="analyzer">Analyzer</option>
                    <option value="microscope">Microscope</option>
                    <option value="centrifuge">Centrifuge</option>
                    <option value="incubator">Incubator</option>
                    <option value="refrigerator">Refrigerator</option>
                    <option value="other">Other</option>
                  </select>
                </div>
                <div class="col-md-2">
                  <input type="date" class="form-control form-control-sm" id="warrantyFilter" onchange="loadEquipment()" placeholder="Warranty expiry">
                </div>
                <div class="col-md-3">
                  <button class="btn btn-secondary btn-sm" onclick="clearFilters()">
                    <i class="fas fa-times"></i> Clear Filters
                  </button>
                  <button class="btn btn-success btn-sm ml-1" onclick="refreshEquipment()">
                    <i class="fas fa-sync"></i> Refresh
                  </button>
                </div>
              </div>

              <!-- Equipment Table -->
              <div class="table-responsive">
                <table id="equipmentTable" class="table table-bordered table-striped table-hover">
                  <thead>
                    <tr>
                      <th width="15%">Equipment Name</th>
                      <th width="10%">Type</th>
                      <th width="12%">Model</th>
                      <th width="12%">Serial Number</th>
                      <th width="10%">Status</th>
                      <th width="10%">Purchase Date</th>
                      <th width="10%">Warranty</th>
                      <th width="10%">Last Maintenance</th>
                      <th width="11%">Actions</th>
                    </tr>
                  </thead>
                  <tbody id="equipmentTableBody">
                    <!-- Dynamic content will be loaded here -->
                  </tbody>
                </table>
              </div>

              <!-- Loading indicator -->
              <div id="loadingIndicator" class="text-center p-3" style="display: none;">
                <i class="fas fa-spinner fa-spin fa-2x"></i>
                <p class="mt-2">Loading equipment...</p>
              </div>

              <!-- Pagination -->
              <div class="row mt-3">
                <div class="col-sm-12 col-md-5">
                  <div id="equipmentInfo" class="dataTables_info"></div>
                </div>
                <div class="col-sm-12 col-md-7">
                  <nav>
                    <ul class="pagination pagination-sm float-right" id="equipmentPagination">
                      <!-- Pagination will be loaded here -->
                    </ul>
                  </nav>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<!-- Add Equipment Modal -->
<div class="modal fade" id="addEquipmentModal" tabindex="-1" role="dialog" aria-labelledby="addEquipmentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h4 class="modal-title" id="addEquipmentModalLabel">
          <i class="fas fa-plus-circle"></i> Add Laboratory Equipment
        </h4>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="addEquipmentForm">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="add_equipment_name">Equipment Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="add_equipment_name" name="equipment_name" required>
                <small class="form-text text-muted">Enter the equipment name or brand</small>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="add_equipment_type">Equipment Type <span class="text-danger">*</span></label>
                <select class="form-control" id="add_equipment_type" name="equipment_type" required>
                  <option value="">Select Type</option>
                  <option value="analyzer">Analyzer</option>
                  <option value="microscope">Microscope</option>
                  <option value="centrifuge">Centrifuge</option>
                  <option value="incubator">Incubator</option>
                  <option value="refrigerator">Refrigerator</option>
                  <option value="autoclave">Autoclave</option>
                  <option value="other">Other</option>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="add_model">Model Number</label>
                <input type="text" class="form-control" id="add_model" name="model">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="add_serial_number">Serial Number <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="add_serial_number" name="serial_number" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="add_manufacturer">Manufacturer</label>
                <input type="text" class="form-control" id="add_manufacturer" name="manufacturer">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="add_location">Location</label>
                <input type="text" class="form-control" id="add_location" name="location" placeholder="Lab room/section">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="add_purchase_date">Purchase Date</label>
                <input type="date" class="form-control" id="add_purchase_date" name="purchase_date">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="add_warranty_expiry">Warranty Expiry</label>
                <input type="date" class="form-control" id="add_warranty_expiry" name="warranty_expiry">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="add_status">Status <span class="text-danger">*</span></label>
                <select class="form-control" id="add_status" name="status" required>
                  <option value="operational">Operational</option>
                  <option value="maintenance">Under Maintenance</option>
                  <option value="out-of-service">Out of Service</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="add_cost">Purchase Cost</label>
                <input type="number" class="form-control" id="add_cost" name="cost" step="0.01" placeholder="0.00">
              </div>
            </div>
          </div>
          <div class="form-group">
            <label for="add_notes">Notes</label>
            <textarea class="form-control" id="add_notes" name="notes" rows="3" placeholder="Additional notes about the equipment..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times"></i> Cancel
          </button>
          <button type="submit" class="btn btn-primary" id="addEquipmentBtn">
            <i class="fas fa-save"></i> Add Equipment
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Equipment Modal -->
<div class="modal fade" id="editEquipmentModal" tabindex="-1" role="dialog" aria-labelledby="editEquipmentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h4 class="modal-title" id="editEquipmentModalLabel">
          <i class="fas fa-edit"></i> Edit Equipment
        </h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="editEquipmentForm">
        <input type="hidden" id="edit_equipment_id" name="id">
        <div class="modal-body">
          <!-- Same form fields as add modal but with edit_ prefixes -->
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_equipment_name">Equipment Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="edit_equipment_name" name="equipment_name" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_equipment_type">Equipment Type <span class="text-danger">*</span></label>
                <select class="form-control" id="edit_equipment_type" name="equipment_type" required>
                  <option value="">Select Type</option>
                  <option value="analyzer">Analyzer</option>
                  <option value="microscope">Microscope</option>
                  <option value="centrifuge">Centrifuge</option>
                  <option value="incubator">Incubator</option>
                  <option value="refrigerator">Refrigerator</option>
                  <option value="autoclave">Autoclave</option>
                  <option value="other">Other</option>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_model">Model Number</label>
                <input type="text" class="form-control" id="edit_model" name="model">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_serial_number">Serial Number <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="edit_serial_number" name="serial_number" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_manufacturer">Manufacturer</label>
                <input type="text" class="form-control" id="edit_manufacturer" name="manufacturer">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_location">Location</label>
                <input type="text" class="form-control" id="edit_location" name="location">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_purchase_date">Purchase Date</label>
                <input type="date" class="form-control" id="edit_purchase_date" name="purchase_date">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_warranty_expiry">Warranty Expiry</label>
                <input type="date" class="form-control" id="edit_warranty_expiry" name="warranty_expiry">
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_status">Status <span class="text-danger">*</span></label>
                <select class="form-control" id="edit_status" name="status" required>
                  <option value="operational">Operational</option>
                  <option value="maintenance">Under Maintenance</option>
                  <option value="out-of-service">Out of Service</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="edit_cost">Purchase Cost</label>
                <input type="number" class="form-control" id="edit_cost" name="cost" step="0.01">
              </div>
            </div>
          </div>
          <div class="form-group">
            <label for="edit_notes">Notes</label>
            <textarea class="form-control" id="edit_notes" name="notes" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times"></i> Cancel
          </button>
          <button type="submit" class="btn btn-warning" id="editEquipmentBtn">
            <i class="fas fa-save"></i> Update Equipment
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Maintenance Schedule Modal -->
<div class="modal fade" id="maintenanceModal" tabindex="-1" role="dialog" aria-labelledby="maintenanceModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-info">
        <h4 class="modal-title" id="maintenanceModalLabel">
          <i class="fas fa-tools"></i> Schedule Maintenance
        </h4>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="maintenanceForm">
        <input type="hidden" id="maintenance_equipment_id" name="equipment_id">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="maintenance_type">Maintenance Type <span class="text-danger">*</span></label>
                <select class="form-control" id="maintenance_type" name="maintenance_type" required>
                  <option value="">Select Type</option>
                  <option value="routine">Routine Maintenance</option>
                  <option value="preventive">Preventive Maintenance</option>
                  <option value="corrective">Corrective Maintenance</option>
                  <option value="emergency">Emergency Repair</option>
                  <option value="calibration">Calibration</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="maintenance_date">Scheduled Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="maintenance_date" name="maintenance_date" required>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="technician_name">Technician</label>
                <input type="text" class="form-control" id="technician_name" name="technician_name" placeholder="Technician name">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="estimated_duration">Estimated Duration (hours)</label>
                <input type="number" class="form-control" id="estimated_duration" name="estimated_duration" step="0.5" min="0.5">
              </div>
            </div>
          </div>
          <div class="form-group">
            <label for="maintenance_description">Description <span class="text-danger">*</span></label>
            <textarea class="form-control" id="maintenance_description" name="description" rows="3" required placeholder="Describe the maintenance work to be performed..."></textarea>
          </div>
          <div class="form-group">
            <label for="maintenance_cost">Estimated Cost</label>
            <input type="number" class="form-control" id="maintenance_cost" name="cost" step="0.01" placeholder="0.00">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times"></i> Cancel
          </button>
          <button type="submit" class="btn btn-info" id="scheduleMaintenanceBtn">
            <i class="fas fa-calendar-plus"></i> Schedule Maintenance
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Maintenance Schedule List Modal -->
<div class="modal fade" id="maintenanceScheduleModal" tabindex="-1" role="dialog" aria-labelledby="maintenanceScheduleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h4 class="modal-title" id="maintenanceScheduleModalLabel">
          <i class="fas fa-calendar-alt"></i> Maintenance Schedule
        </h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="table-responsive">
          <table id="maintenanceScheduleTable" class="table table-bordered table-striped">
            <thead>
              <tr>
                <th>Equipment</th>
                <th>Type</th>
                <th>Scheduled Date</th>
                <th>Technician</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="maintenanceScheduleTableBody">
              <!-- Dynamic content will be loaded here -->
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
          <i class="fas fa-times"></i> Close
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteEquipmentModal" tabindex="-1" role="dialog" aria-labelledby="deleteEquipmentModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h4 class="modal-title text-white" id="deleteEquipmentModalLabel">
          <i class="fas fa-trash"></i> Confirm Delete
        </h4>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete this equipment?</p>
        <div class="alert alert-warning">
          <i class="fas fa-exclamation-triangle"></i>
          <strong>Warning:</strong> This action cannot be undone and will also delete all maintenance records.
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
          <i class="fas fa-times"></i> Cancel
        </button>
        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
          <i class="fas fa-trash"></i> Delete Equipment
        </button>
      </div>
    </div>
  </div>
</div>

<!-- JavaScript -->
<script>
$(document).ready(function() {
    // Initialize page
    loadStats();
    loadEquipment();
    
    // Search on Enter key
    $('#searchInput').on('keypress', function(e) {
        if (e.which === 13) {
            loadEquipment();
        }
    });
    
    // Auto-refresh every 30 seconds
    setInterval(function() {
        if (!$('.modal').hasClass('show')) {
            loadStats();
        }
    }, 30000);
});

// Global variables
let currentPage = 1;
let equipmentPerPage = 10;
let currentFilters = {
    search: '',
    status: '',
    type: '',
    warranty: ''
};

// Load statistics
function loadStats() {
    $.ajax({
        url: 'api/equipment_api.php',
        method: 'GET',
        data: { action: 'stats' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const stats = response.data;
                $('#totalEquipment').text(stats.total || 0);
                $('#operationalEquipment').text(stats.operational || 0);
                $('#maintenanceEquipment').text(stats.maintenance || 0);
                $('#outOfServiceEquipment').text(stats.out_of_service || 0);
            }
        },
        error: function() {
            console.log('Error loading statistics');
        }
    });
}

// Load equipment with filters and pagination
function loadEquipment(page = 1) {
    currentPage = page;
    
    // Get current filters
    currentFilters.search = $('#searchInput').val().trim();
    currentFilters.status = $('#statusFilter').val();
    currentFilters.type = $('#typeFilter').val();
    currentFilters.warranty = $('#warrantyFilter').val();
    
    // Show loading indicator
    $('#loadingIndicator').show();
    $('#equipmentTableBody').hide();
    
    $.ajax({
        url: 'api/equipment_api.php',
        method: 'GET',
        data: {
            action: 'read',
            page: currentPage,
            limit: equipmentPerPage,
            search: currentFilters.search,
            status: currentFilters.status,
            type: currentFilters.type,
            warranty: currentFilters.warranty
        },
        dataType: 'json',
        success: function(response) {
            $('#loadingIndicator').hide();
            $('#equipmentTableBody').show();
            
            if (response.success) {
                displayEquipment(response.data);
                displayPagination(response.pagination);
                updateEquipmentInfo(response.pagination);
            } else {
                $('#equipmentTableBody').html(`
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="fas fa-info-circle fa-2x mb-2"></i><br>
                            ${response.message || 'No equipment found'}
                        </td>
                    </tr>
                `);
                $('#equipmentPagination').empty();
                $('#equipmentInfo').text('Showing 0 to 0 of 0 entries');
            }
        },
        error: function() {
            $('#loadingIndicator').hide();
            $('#equipmentTableBody').show();
            
            // Show sample data for demonstration
            displaySampleEquipment();
            showAlert('info', 'Using sample data for demonstration');
        }
    });
}

// Display sample equipment data
function displaySampleEquipment() {
    const sampleData = [
        {
            id: 1,
            equipment_name: 'Hematology Analyzer',
            equipment_type: 'analyzer',
            model: 'HAX-3000',
            serial_number: 'HAX3000-001',
            status: 'operational',
            purchase_date: '2023-01-15',
            warranty_expiry: '2026-01-15',
            last_maintenance: '2024-05-01'
        },
        {
            id: 2,
            equipment_name: 'Microscope',
            equipment_type: 'microscope',
            model: 'MIC-2500',
            serial_number: 'MIC2500-002',
            status: 'operational',
            purchase_date: '2023-03-10',
            warranty_expiry: '2026-03-10',
            last_maintenance: '2024-04-15'
        },
        {
            id: 3,
            equipment_name: 'Centrifuge',
            equipment_type: 'centrifuge',
            model: 'CEN-1200',
            serial_number: 'CEN1200-003',
            status: 'maintenance',
            purchase_date: '2022-11-05',
            warranty_expiry: '2025-11-05',
            last_maintenance: '2024-06-01'
        }
    ];
    
    displayEquipment(sampleData);
    
    // Update pagination for sample data
    const samplePagination = {
        page: 1,
        pages: 1,
        limit: 10,
        total: 3
    };
    updateEquipmentInfo(samplePagination);
}

// Display equipment in table
function displayEquipment(equipment) {
    let html = '';
    
    if (equipment.length === 0) {
        html = `
            <tr>
                <td colspan="9" class="text-center text-muted py-4">
                    <i class="fas fa-search fa-2x mb-2"></i><br>
                    No equipment found matching your criteria
                </td>
            </tr>
        `;
    } else {
        equipment.forEach(function(item) {
            // Status badge
            let statusBadge = '';
            switch(item.status) {
                case 'operational':
                    statusBadge = '<span class="badge badge-success">Operational</span>';
                    break;
                case 'maintenance':
                    statusBadge = '<span class="badge badge-warning">Maintenance</span>';
                    break;
                case 'out-of-service':
                    statusBadge = '<span class="badge badge-danger">Out of Service</span>';
                    break;
                default:
                    statusBadge = '<span class="badge badge-secondary">' + item.status + '</span>';
            }
            
            // Check warranty status
            let warrantyStatus = '';
            if (item.warranty_expiry) {
                const warrantyDate = new Date(item.warranty_expiry);
                const today = new Date();
                const daysToExpiry = Math.ceil((warrantyDate - today) / (1000 * 60 * 60 * 24));
                
                if (daysToExpiry < 0) {
                    warrantyStatus = '<span class="text-danger">Expired</span>';
                } else if (daysToExpiry < 30) {
                    warrantyStatus = '<span class="text-warning">Expiring Soon</span>';
                } else {
                    warrantyStatus = '<span class="text-success">Valid</span>';
                }
            } else {
                warrantyStatus = '<span class="text-muted">N/A</span>';
            }
            
            html += `
                <tr ${item.status === 'out-of-service' ? 'class="table-danger"' : item.status === 'maintenance' ? 'class="table-warning"' : ''}>
                    <td>
                        <strong>${item.equipment_name || 'N/A'}</strong>
                        ${item.manufacturer ? '<br><small class="text-muted">' + item.manufacturer + '</small>' : ''}
                    </td>
                    <td>
                        <span class="badge badge-info">${item.equipment_type || 'N/A'}</span>
                    </td>
                    <td>${item.model || 'N/A'}</td>
                    <td>
                        <code>${item.serial_number || 'N/A'}</code>
                    </td>
                    <td>${statusBadge}</td>
                    <td>
                        <small>${formatDate(item.purchase_date)}</small>
                    </td>
                    <td>
                        ${warrantyStatus}<br>
                        <small>${formatDate(item.warranty_expiry)}</small>
                    </td>
                    <td>
                        <small>${formatDate(item.last_maintenance)}</small>
                    </td>
                    <td>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-info btn-sm" onclick="viewEquipment(${item.id})" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-warning btn-sm" onclick="editEquipment(${item.id})" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-success btn-sm" onclick="scheduleMaintenance(${item.id})" title="Maintenance">
                                <i class="fas fa-tools"></i>
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteEquipment(${item.id})" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
    }
    
    $('#equipmentTableBody').html(html);
}

// Display pagination
function displayPagination(pagination) {
    let html = '';
    
    if (pagination.pages > 1) {
        // Previous button
        if (pagination.page > 1) {
            html += `<li class="page-item">
                <a class="page-link" href="#" onclick="loadEquipment(${pagination.page - 1})">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>`;
        }
        
        // Page numbers
        const startPage = Math.max(1, pagination.page - 2);
        const endPage = Math.min(pagination.pages, pagination.page + 2);
        
        if (startPage > 1) {
            html += `<li class="page-item">
                <a class="page-link" href="#" onclick="loadEquipment(1)">1</a>
            </li>`;
            if (startPage > 2) {
                html += `<li class="page-item disabled">
                    <span class="page-link">...</span>
                </li>`;
            }
        }
        
        for (let i = startPage; i <= endPage; i++) {
            const active = i === pagination.page ? 'active' : '';
            html += `<li class="page-item ${active}">
                <a class="page-link" href="#" onclick="loadEquipment(${i})">${i}</a>
            </li>`;
        }
        
        if (endPage < pagination.pages) {
            if (endPage < pagination.pages - 1) {
                html += `<li class="page-item disabled">
                    <span class="page-link">...</span>
                </li>`;
            }
            html += `<li class="page-item">
                <a class="page-link" href="#" onclick="loadEquipment(${pagination.pages})">${pagination.pages}</a>
            </li>`;
        }
        
        // Next button
        if (pagination.page < pagination.pages) {
            html += `<li class="page-item">
                <a class="page-link" href="#" onclick="loadEquipment(${pagination.page + 1})">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>`;
        }
    }
    
    $('#equipmentPagination').html(html);
}

// Update equipment info
function updateEquipmentInfo(pagination) {
    const start = (pagination.page - 1) * pagination.limit + 1;
    const end = Math.min(pagination.page * pagination.limit, pagination.total);
    $('#equipmentInfo').text(`Showing ${start} to ${end} of ${pagination.total} entries`);
}

// Filter functions
function filterByStatus(status) {
    $('#statusFilter').val(status);
    loadEquipment(1);
}

function clearFilters() {
    $('#searchInput').val('');
    $('#statusFilter').val('');
    $('#typeFilter').val('');
    $('#warrantyFilter').val('');
    loadEquipment(1);
}

function refreshEquipment() {
    loadStats();
    loadEquipment(currentPage);
    showAlert('success', 'Equipment list refreshed successfully');
}

// Add equipment form submission
$('#addEquipmentForm').submit(function(e) {
    e.preventDefault();
    
    const submitBtn = $('#addEquipmentBtn');
    const originalText = submitBtn.html();
    submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Adding...').prop('disabled', true);
    
    $.ajax({
        url: 'api/equipment_api.php',
        method: 'POST',
        data: $(this).serialize() + '&action=create',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#addEquipmentModal').modal('hide');
                $('#addEquipmentForm')[0].reset();
                loadEquipment(currentPage);
                loadStats();
                showAlert('success', 'Equipment added successfully!');
            } else {
                showAlert('Error adding equipment: ' + response.message, 'danger');
            }
        },
        error: function() {
            showAlert('danger', 'Error adding equipment. Please try again.');
        },
        complete: function() {
            submitBtn.html(originalText).prop('disabled', false);
        }
    });
});

// Edit equipment
function editEquipment(id) {
    $.ajax({
        url: 'api/equipment_api.php',
        method: 'GET',
        data: { action: 'read', id: id },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data.length > 0) {
                const equipment = response.data[0];
                $('#edit_equipment_id').val(equipment.id);
                $('#edit_equipment_name').val(equipment.equipment_name);
                $('#edit_equipment_type').val(equipment.equipment_type);
                $('#edit_model').val(equipment.model);
                $('#edit_serial_number').val(equipment.serial_number);
                $('#edit_manufacturer').val(equipment.manufacturer);
                $('#edit_location').val(equipment.location);
                $('#edit_purchase_date').val(equipment.purchase_date);
                $('#edit_warranty_expiry').val(equipment.warranty_expiry);
                $('#edit_status').val(equipment.status);
                $('#edit_cost').val(equipment.cost);
                $('#edit_notes').val(equipment.notes);
                $('#editEquipmentModal').modal('show');
            } else {
                showAlert('danger', 'Equipment not found');
            }
        },
        error: function() {
            showAlert('danger', 'Error loading equipment details');
        }
    });
}

// Edit equipment form submission
$('#editEquipmentForm').submit(function(e) {
    e.preventDefault();
    
    const submitBtn = $('#editEquipmentBtn');
    const originalText = submitBtn.html();
    submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Updating...').prop('disabled', true);
    
    $.ajax({
        url: 'api/equipment_api.php',
        method: 'POST',
        data: $(this).serialize() + '&action=update',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#editEquipmentModal').modal('hide');
                loadEquipment(currentPage);
                loadStats();
                showAlert('success', 'Equipment updated successfully!');
            } else {
                showAlert('Error updating equipment: ' + response.message, 'danger');
            }
        },
        error: function() {
            showAlert('danger', 'Error updating equipment. Please try again.');
        },
        complete: function() {
            submitBtn.html(originalText).prop('disabled', false);
        }
    });
});

// Schedule maintenance
function scheduleMaintenance(equipmentId) {
    $('#maintenance_equipment_id').val(equipmentId);
    $('#maintenance_date').val(new Date().toISOString().split('T')[0]);
    $('#maintenanceModal').modal('show');
}

// Maintenance form submission
$('#maintenanceForm').submit(function(e) {
    e.preventDefault();
    
    const submitBtn = $('#scheduleMaintenanceBtn');
    const originalText = submitBtn.html();
    submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Scheduling...').prop('disabled', true);
    
    $.ajax({
        url: 'api/equipment_api.php',
        method: 'POST',
        data: $(this).serialize() + '&action=schedule_maintenance',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#maintenanceModal').modal('hide');
                $('#maintenanceForm')[0].reset();
                loadEquipment(currentPage);
                showAlert('success', 'Maintenance scheduled successfully!');
            } else {
                showAlert('Error scheduling maintenance: ' + response.message, 'danger');
            }
        },
        error: function() {
            showAlert('danger', 'Error scheduling maintenance. Please try again.');
        },
        complete: function() {
            submitBtn.html(originalText).prop('disabled', false);
        }
    });
});

// Delete confirmation
let equipmentToDelete = null;

function confirmDeleteEquipment(id) {
    equipmentToDelete = id;
    $('#deleteEquipmentModal').modal('show');
}

$('#confirmDeleteBtn').click(function() {
    if (equipmentToDelete) {
        deleteEquipment(equipmentToDelete);
        equipmentToDelete = null;
    }
});

// Delete equipment
function deleteEquipment(id) {
    const deleteBtn = $('#confirmDeleteBtn');
    const originalText = deleteBtn.html();
    deleteBtn.html('<i class="fas fa-spinner fa-spin"></i> Deleting...').prop('disabled', true);
    
    $.ajax({
        url: 'api/equipment_api.php',
        method: 'POST',
        data: { action: 'delete', id: id },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#deleteEquipmentModal').modal('hide');
                loadEquipment(currentPage);
                loadStats();
                showAlert('success', 'Equipment deleted successfully!');
            } else {
                showAlert('Error deleting equipment: ' + response.message, 'danger');
            }
        },
        error: function() {
            showAlert('danger', 'Error deleting equipment. Please try again.');
        },
        complete: function() {
            deleteBtn.html(originalText).prop('disabled', false);
        }
    });
}

// View equipment details
function viewEquipment(id) {
    // This could open a detailed view modal
    showAlert('info', 'Equipment details view will be implemented soon.');
}

// Export equipment
function exportEquipment() {
    showAlert('info', 'Export feature will be available soon.');
}

// Utility functions
function formatDate(dateStr) {
    if (!dateStr) return 'N/A';
    const date = new Date(dateStr);
    return date.toLocaleDateString();
}

function showAlert(type, message) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-triangle' : 'info-circle'}"></i>
            ${message}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    `;
    
    // Remove existing alerts
    $('.alert').remove();
    
    // Add new alert
    $('.content-wrapper .content').prepend(alertHtml);
    
    // Auto dismiss after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
}
</script>

<?php
// Include footer
include 'includes/footer.php';
?>
