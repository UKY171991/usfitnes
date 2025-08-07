<?php
$page_title = 'Equipment Management';
$breadcrumbs = [
    ['title' => 'Home', 'url' => 'dashboard.php'],
    ['title' => 'Equipment']
];
$additional_css = ['css/equipment.css'];
$additional_js = ['js/equipment.js'];

ob_start();
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-tools mr-2"></i>
                    Equipment Management
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" onclick="showAddEquipmentModal()">
                        <i class="fas fa-plus mr-1"></i>
                        Add Equipment
                    </button>
                    <button type="button" class="btn btn-success btn-sm" onclick="exportEquipment()">
                        <i class="fas fa-download mr-1"></i>
                        Export
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Filters -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <select class="form-control" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control" id="categoryFilter">
                            <option value="">All Categories</option>
                            <option value="Analyzer">Analyzer</option>
                            <option value="Microscope">Microscope</option>
                            <option value="Centrifuge">Centrifuge</option>
                            <option value="Incubator">Incubator</option>
                            <option value="Refrigerator">Refrigerator</option>
                            <option value="Computer">Computer</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" id="manufacturerFilter" placeholder="Manufacturer">
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-info" onclick="applyFilters()">
                            <i class="fas fa-filter mr-1"></i>
                            Apply Filters
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="clearFilters()">
                            <i class="fas fa-times mr-1"></i>
                            Clear
                        </button>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="table-responsive">
                    <table id="equipmentTable" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Manufacturer</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Equipment Modal -->
<div class="modal fade" id="equipmentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Add Equipment</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="equipmentForm">
                <input type="hidden" name="id" id="equipmentId">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Equipment Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="equipment_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Equipment Code</label>
                                <input type="text" class="form-control" name="equipment_code" placeholder="Auto-generated if empty">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Category</label>
                                <select class="form-control select2" name="category">
                                    <option value="">Select Category</option>
                                    <option value="Analyzer">Analyzer</option>
                                    <option value="Microscope">Microscope</option>
                                    <option value="Centrifuge">Centrifuge</option>
                                    <option value="Incubator">Incubator</option>
                                    <option value="Refrigerator">Refrigerator</option>
                                    <option value="Computer">Computer</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Equipment Type</label>
                                <input type="text" class="form-control" name="equipment_type">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Model</label>
                                <input type="text" class="form-control" name="model">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Serial Number</label>
                                <input type="text" class="form-control" name="serial_number">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Manufacturer</label>
                                <input type="text" class="form-control" name="manufacturer">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Location</label>
                                <input type="text" class="form-control" name="location">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Purchase Date</label>
                                <input type="date" class="form-control" name="purchase_date">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Warranty Expiry</label>
                                <input type="date" class="form-control" name="warranty_expiry">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Cost</label>
                                <input type="number" class="form-control" name="cost" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Maintenance Schedule</label>
                                <select class="form-control" name="maintenance_schedule">
                                    <option value="">Select Schedule</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="quarterly">Quarterly</option>
                                    <option value="yearly">Yearly</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Description</label>
                                <textarea class="form-control" name="description" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Equipment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Equipment Modal -->
<div class="modal fade" id="viewEquipmentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Equipment Details</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="equipmentDetails">
                <!-- Equipment details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once 'includes/adminlte3_template.php';
?>