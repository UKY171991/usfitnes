<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';

// Check if user is master admin
if (!isLoggedIn() || $_SESSION['user_role'] !== 'master_admin') {
    header('Location: ../patient/login');
    exit;
}

$title = 'Manage Branches - US Fitness Lab';
$additionalCSS = [BASE_URL . 'assets/css/admin.css'];
$additionalJS = [
    BASE_URL . 'assets/js/admin.js',
    'https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js',
    'https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js'
];
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <i class="fas fa-building text-primary me-2"></i>
                    Manage Branches
                </h2>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#branchModal">
                        <i class="fas fa-plus me-1"></i>Add New Branch
                    </button>
                    <button type="button" class="btn btn-outline-secondary refresh-stats">
                        <i class="fas fa-sync-alt me-1"></i>Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Branch Statistics -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="stats-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stats-number" id="totalBranches">0</div>
                        <div class="stats-label">Total Branches</div>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-building"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stats-card success">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stats-number" id="activeBranches">0</div>
                        <div class="stats-label">Active Branches</div>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stats-card warning">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stats-number" id="totalAdmins">0</div>
                        <div class="stats-label">Branch Admins</div>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-users-cog"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stats-card info">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stats-number" id="totalRevenue">₹0</div>
                        <div class="stats-label">Total Revenue</div>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-rupee-sign"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Branches Table -->
    <div class="row">
        <div class="col-12">
            <div class="card admin-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2"></i>
                        Branches List
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="branchesTable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Branch Name</th>
                                    <th>Location</th>
                                    <th>Contact</th>
                                    <th>Branch Admin</th>
                                    <th>Status</th>
                                    <th>Created</th>
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
</div>

<!-- Branch Modal -->
<div class="modal fade" id="branchModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-building me-2"></i>
                    <span id="modalTitle">Add New Branch</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="branchForm" class="ajax-form">
                <div class="modal-body">
                    <input type="hidden" name="action" value="saveBranch">
                    <input type="hidden" name="branch_id" id="branch_id">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="branch_name" class="form-label">Branch Name *</label>
                            <input type="text" class="form-control" id="branch_name" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="branch_code" class="form-label">Branch Code *</label>
                            <input type="text" class="form-control" id="branch_code" name="code" required placeholder="e.g., USF001">
                        </div>
                        
                        <div class="col-12">
                            <label for="branch_address" class="form-label">Address *</label>
                            <textarea class="form-control" id="branch_address" name="address" rows="3" required></textarea>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="branch_city" class="form-label">City *</label>
                            <input type="text" class="form-control" id="branch_city" name="city" required>
                        </div>
                        <div class="col-md-4">
                            <label for="branch_state" class="form-label">State *</label>
                            <input type="text" class="form-control" id="branch_state" name="state" required>
                        </div>
                        <div class="col-md-4">
                            <label for="branch_pincode" class="form-label">Pincode *</label>
                            <input type="text" class="form-control" id="branch_pincode" name="pincode" required pattern="[0-9]{6}">
                        </div>
                        
                        <div class="col-md-6">
                            <label for="branch_phone" class="form-label">Phone *</label>
                            <input type="tel" class="form-control" id="branch_phone" name="phone" required pattern="[0-9]{10}">
                        </div>
                        <div class="col-md-6">
                            <label for="branch_email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="branch_email" name="email" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="branch_license" class="form-label">License Number</label>
                            <input type="text" class="form-control" id="branch_license" name="license_number">
                        </div>
                        <div class="col-md-6">
                            <label for="branch_gst" class="form-label">GST Number</label>
                            <input type="text" class="form-control" id="branch_gst" name="gst_number">
                        </div>
                        
                        <div class="col-12">
                            <hr>
                            <h6 class="text-primary">Branch Admin Details</h6>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="admin_name" class="form-label">Admin Name *</label>
                            <input type="text" class="form-control" id="admin_name" name="admin_name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="admin_email" class="form-label">Admin Email *</label>
                            <input type="email" class="form-control" id="admin_email" name="admin_email" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="admin_phone" class="form-label">Admin Phone *</label>
                            <input type="tel" class="form-control" id="admin_phone" name="admin_phone" required pattern="[0-9]{10}">
                        </div>
                        <div class="col-md-6">
                            <label for="admin_password" class="form-label">Admin Password *</label>
                            <input type="password" class="form-control" id="admin_password" name="admin_password" required minlength="8">
                            <div class="form-text">Minimum 8 characters</div>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="branch_status" class="form-label">Status</label>
                            <select class="form-select" id="branch_status" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Save Branch
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Branch Details Modal -->
<div class="modal fade" id="branchDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle me-2"></i>
                    Branch Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="branchDetailsContent">
                <!-- Content loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    loadBranchesTable();
    loadBranchStats();
    setupBranchManagement();
});

function loadBranchesTable() {
    if ($.fn.DataTable.isDataTable('#branchesTable')) {
        $('#branchesTable').DataTable().destroy();
    }
    
    $('#branchesTable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: AJAX_URL,
            type: 'POST',
            data: {action: 'getBranches'},
            dataSrc: function(json) {
                return json.success ? json.data : [];
            }
        },
        columns: [
            {data: 'id'},
            {data: 'name'},
            {
                data: null,
                render: function(data, type, row) {
                    return row.city + ', ' + row.state;
                }
            },
            {
                data: null,
                render: function(data, type, row) {
                    return row.phone + '<br><small class="text-muted">' + row.email + '</small>';
                }
            },
            {
                data: 'admin_name',
                render: function(data) {
                    return data || '<span class="text-muted">No Admin</span>';
                }
            },
            {
                data: 'status',
                render: function(data) {
                    var badgeClass = data === 'active' ? 'bg-success' : 'bg-secondary';
                    return '<span class="badge ' + badgeClass + '">' + data.toUpperCase() + '</span>';
                }
            },
            {
                data: 'created_at',
                render: function(data) {
                    return new Date(data).toLocaleDateString();
                }
            },
            {
                data: null,
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-info view-branch" 
                                    data-branch-id="${row.id}" 
                                    title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-outline-primary edit-branch" 
                                    data-branch-id="${row.id}" 
                                    title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-outline-warning toggle-status" 
                                    data-branch-id="${row.id}" 
                                    data-status="${row.status}" 
                                    title="Toggle Status">
                                <i class="fas fa-power-off"></i>
                            </button>
                            <button class="btn btn-outline-danger delete-branch" 
                                    data-branch-id="${row.id}" 
                                    data-branch-name="${row.name}" 
                                    title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true
    });
}

function loadBranchStats() {
    $.ajax({
        url: AJAX_URL,
        type: 'POST',
        data: {action: 'getBranchStats'},
        success: function(response) {
            if (response.success) {
                $('#totalBranches').text(response.data.total_branches || 0);
                $('#activeBranches').text(response.data.active_branches || 0);
                $('#totalAdmins').text(response.data.total_admins || 0);
                $('#totalRevenue').text(formatCurrency(response.data.total_revenue || 0));
            }
        }
    });
}

function setupBranchManagement() {
    // Handle branch form submission
    $('#branchForm').on('submit', function(e) {
        e.preventDefault();
        saveBranch();
    });
    
    // Handle edit branch
    $(document).on('click', '.edit-branch', function() {
        var branchId = $(this).data('branch-id');
        editBranch(branchId);
    });
    
    // Handle view branch
    $(document).on('click', '.view-branch', function() {
        var branchId = $(this).data('branch-id');
        viewBranch(branchId);
    });
    
    // Handle toggle status
    $(document).on('click', '.toggle-status', function() {
        var branchId = $(this).data('branch-id');
        var currentStatus = $(this).data('status');
        toggleBranchStatus(branchId, currentStatus);
    });
    
    // Handle delete branch
    $(document).on('click', '.delete-branch', function() {
        var branchId = $(this).data('branch-id');
        var branchName = $(this).data('branch-name');
        deleteBranch(branchId, branchName);
    });
    
    // Reset form when modal is hidden
    $('#branchModal').on('hidden.bs.modal', function() {
        resetBranchForm();
    });
}

function saveBranch() {
    var form = $('#branchForm');
    var formData = new FormData(form[0]);
    
    $.ajax({
        url: AJAX_URL,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                showToast('Branch saved successfully', 'success');
                $('#branchModal').modal('hide');
                loadBranchesTable();
                loadBranchStats();
            } else {
                showToast(response.message || 'Failed to save branch', 'error');
            }
        }
    });
}

function editBranch(branchId) {
    $.ajax({
        url: AJAX_URL,
        type: 'POST',
        data: {action: 'getBranch', branch_id: branchId},
        success: function(response) {
            if (response.success) {
                populateBranchForm(response.data);
                $('#modalTitle').text('Edit Branch');
                $('#branchModal').modal('show');
            }
        }
    });
}

function populateBranchForm(branchData) {
    $('#branch_id').val(branchData.id);
    $('#branch_name').val(branchData.name);
    $('#branch_code').val(branchData.code);
    $('#branch_address').val(branchData.address);
    $('#branch_city').val(branchData.city);
    $('#branch_state').val(branchData.state);
    $('#branch_pincode').val(branchData.pincode);
    $('#branch_phone').val(branchData.phone);
    $('#branch_email').val(branchData.email);
    $('#branch_license').val(branchData.license_number);
    $('#branch_gst').val(branchData.gst_number);
    $('#branch_status').val(branchData.status);
    
    if (branchData.admin) {
        $('#admin_name').val(branchData.admin.name);
        $('#admin_email').val(branchData.admin.email);
        $('#admin_phone').val(branchData.admin.phone);
        $('#admin_password').prop('required', false);
    }
}

function viewBranch(branchId) {
    $.ajax({
        url: AJAX_URL,
        type: 'POST',
        data: {action: 'getBranchDetails', branch_id: branchId},
        success: function(response) {
            if (response.success) {
                displayBranchDetails(response.data);
                $('#branchDetailsModal').modal('show');
            }
        }
    });
}

function displayBranchDetails(branch) {
    var html = `
        <div class="row g-3">
            <div class="col-md-6">
                <h6>Branch Information</h6>
                <table class="table table-sm">
                    <tr><td><strong>Name:</strong></td><td>${branch.name}</td></tr>
                    <tr><td><strong>Code:</strong></td><td>${branch.code}</td></tr>
                    <tr><td><strong>Address:</strong></td><td>${branch.address}</td></tr>
                    <tr><td><strong>City:</strong></td><td>${branch.city}</td></tr>
                    <tr><td><strong>State:</strong></td><td>${branch.state}</td></tr>
                    <tr><td><strong>Pincode:</strong></td><td>${branch.pincode}</td></tr>
                    <tr><td><strong>Phone:</strong></td><td>${branch.phone}</td></tr>
                    <tr><td><strong>Email:</strong></td><td>${branch.email}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6>Branch Admin</h6>
                <table class="table table-sm">
                    <tr><td><strong>Name:</strong></td><td>${branch.admin?.name || 'N/A'}</td></tr>
                    <tr><td><strong>Email:</strong></td><td>${branch.admin?.email || 'N/A'}</td></tr>
                    <tr><td><strong>Phone:</strong></td><td>${branch.admin?.phone || 'N/A'}</td></tr>
                </table>
                
                <h6 class="mt-3">Statistics</h6>
                <table class="table table-sm">
                    <tr><td><strong>Total Patients:</strong></td><td>${branch.stats?.total_patients || 0}</td></tr>
                    <tr><td><strong>Total Tests:</strong></td><td>${branch.stats?.total_tests || 0}</td></tr>
                    <tr><td><strong>Pending Reports:</strong></td><td>${branch.stats?.pending_reports || 0}</td></tr>
                    <tr><td><strong>Revenue:</strong></td><td>${formatCurrency(branch.stats?.revenue || 0)}</td></tr>
                </table>
            </div>
        </div>
    `;
    
    $('#branchDetailsContent').html(html);
}

function toggleBranchStatus(branchId, currentStatus) {
    var newStatus = currentStatus === 'active' ? 'inactive' : 'active';
    var action = newStatus === 'active' ? 'activate' : 'deactivate';
    
    if (confirm('Are you sure you want to ' + action + ' this branch?')) {
        $.ajax({
            url: AJAX_URL,
            type: 'POST',
            data: {
                action: 'toggleBranchStatus',
                branch_id: branchId,
                status: newStatus
            },
            success: function(response) {
                if (response.success) {
                    showToast('Branch status updated successfully', 'success');
                    loadBranchesTable();
                    loadBranchStats();
                } else {
                    showToast(response.message || 'Failed to update status', 'error');
                }
            }
        });
    }
}

function deleteBranch(branchId, branchName) {
    if (confirm('Are you sure you want to delete "' + branchName + '"? This action cannot be undone and will delete all associated data.')) {
        $.ajax({
            url: AJAX_URL,
            type: 'POST',
            data: {action: 'deleteBranch', branch_id: branchId},
            success: function(response) {
                if (response.success) {
                    showToast('Branch deleted successfully', 'success');
                    loadBranchesTable();
                    loadBranchStats();
                } else {
                    showToast(response.message || 'Failed to delete branch', 'error');
                }
            }
        });
    }
}

function resetBranchForm() {
    $('#branchForm')[0].reset();
    $('#branch_id').val('');
    $('#modalTitle').text('Add New Branch');
    $('#admin_password').prop('required', true);
}

// Refresh stats every 5 minutes
setInterval(loadBranchStats, 300000);
</script>
