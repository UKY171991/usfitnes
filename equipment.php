<?php
require_once 'config.php';
require_once 'includes/init.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pageTitle = 'Equipment Management';
$pageIcon = 'fas fa-cogs';
$breadcrumbs = ['Equipment'];

include 'includes/adminlte_template_header.php';
include 'includes/adminlte_sidebar.php';
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">
            <i class="<?php echo $pageIcon; ?> mr-2 text-warning"></i><?php echo $pageTitle; ?>
          </h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
            <?php foreach($breadcrumbs as $index => $crumb): ?>
              <li class="breadcrumb-item active"><?php echo $crumb; ?></li>
            <?php endforeach; ?>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">
          <div class="card card-warning card-outline">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-list mr-2"></i>All Equipment
              </h3>
              <div class="card-tools">
                <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#equipmentModal" onclick="openAddModal()">
                  <i class="fas fa-plus mr-1"></i>Add Equipment
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm ml-1" onclick="refreshTable()">
                  <i class="fas fa-sync-alt mr-1"></i>Refresh
                </button>
              </div>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table id="equipmentTable" class="table table-bordered table-striped table-hover">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Name</th>
                      <th>Model</th>
                      <th>Category</th>
                      <th>Serial No.</th>
                      <th>Status</th>
                      <th>Maintenance</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <!-- Data will be loaded via DataTables AJAX -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<!-- Equipment Modal -->
<div class="modal fade" id="equipmentModal" tabindex="-1" aria-labelledby="equipmentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title text-dark" id="equipmentModalLabel">
          <i class="fas fa-cogs mr-2"></i>
          <span id="modalTitle">Add New Equipment</span>
        </h5>
        <button type="button" class="close text-dark" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <form id="equipmentForm" novalidate>
        <div class="modal-body">
          <input type="hidden" id="equipmentId" name="id">
          
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="equipmentName">Equipment Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="equipmentName" name="name" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="model">Model</label>
                <input type="text" class="form-control" id="model" name="model">
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="category">Category <span class="text-danger">*</span></label>
                <select class="form-control" id="category" name="category" required>
                  <option value="">Select Category</option>
                  <option value="Laboratory">Laboratory</option>
                  <option value="Diagnostic">Diagnostic</option>
                  <option value="Imaging">Imaging</option>
                  <option value="Surgical">Surgical</option>
                  <option value="Monitoring">Monitoring</option>
                  <option value="Other">Other</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="serialNumber">Serial Number</label>
                <input type="text" class="form-control" id="serialNumber" name="serial_number">
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="manufacturer">Manufacturer</label>
                <input type="text" class="form-control" id="manufacturer" name="manufacturer">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="purchaseDate">Purchase Date</label>
                <input type="date" class="form-control" id="purchaseDate" name="purchase_date">
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="warrantyExpiry">Warranty Expiry</label>
                <input type="date" class="form-control" id="warrantyExpiry" name="warranty_expiry">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="lastMaintenance">Last Maintenance</label>
                <input type="date" class="form-control" id="lastMaintenance" name="last_maintenance">
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="nextMaintenance">Next Maintenance</label>
                <input type="date" class="form-control" id="nextMaintenance" name="next_maintenance">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="status">Status</label>
                <select class="form-control" id="status" name="status">
                  <option value="Active">Active</option>
                  <option value="Maintenance">Maintenance</option>
                  <option value="Inactive">Inactive</option>
                </select>
              </div>
            </div>
          </div>
          
          <div class="form-group">
            <label for="description">Description</label>
            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning">
            <i class="fas fa-save mr-1"></i>Save Equipment
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTable
    initDataTable();
});

function initDataTable() {
    $('#equipmentTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: 'ajax/equipment_datatable.php',
            type: 'POST',
            error: function(xhr, error, thrown) {
                console.log('DataTables Error:', error);
                showToast('error', 'Failed to load equipment data. Please check your database connection.');
            }
        },
        columns: [
            { data: 'id', width: '60px' },
            { data: 'name' },
            { data: 'model' },
            { data: 'category' },
            { data: 'serial_number' },
            { data: 'status', width: '100px' },
            { data: 'maintenance_status', width: '120px' },
            { data: 'actions', orderable: false, width: '120px' }
        ],
        order: [[0, 'desc']],
        responsive: true,
        language: {
            processing: '<div class="spinner-border spinner-border-sm text-warning" role="status"></div> Loading...'
        }
    });
}

function refreshTable() {
    $('#equipmentTable').DataTable().ajax.reload(null, false);
    showToast('success', 'Table refreshed successfully');
}

function openAddModal() {
    $('#equipmentModalLabel #modalTitle').text('Add New Equipment');
    $('#equipmentForm')[0].reset();
    $('#equipmentId').val('');
    $('#equipmentForm').removeClass('was-validated');
    $('#status').val('Active');
}

function editEquipment(id) {
    $('#equipmentModalLabel #modalTitle').text('Edit Equipment');
    
    $.ajax({
        url: 'api/equipment_api.php',
        type: 'GET',
        data: { action: 'get', id: id },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const equipment = response.data;
                $('#equipmentId').val(equipment.id);
                $('#equipmentName').val(equipment.name);
                $('#model').val(equipment.model);
                $('#category').val(equipment.category);
                $('#serialNumber').val(equipment.serial_number);
                $('#manufacturer').val(equipment.manufacturer);
                $('#purchaseDate').val(equipment.purchase_date);
                $('#warrantyExpiry').val(equipment.warranty_expiry);
                $('#lastMaintenance').val(equipment.last_maintenance);
                $('#nextMaintenance').val(equipment.next_maintenance);
                $('#status').val(equipment.status);
                $('#description').val(equipment.description);
                $('#equipmentModal').modal('show');
            } else {
                showToast('error', response.message);
            }
        },
        error: function() {
            showToast('error', 'Failed to load equipment data');
        }
    });
}

function deleteEquipment(id) {
    if (confirm('Are you sure you want to delete this equipment?')) {
        $.ajax({
            url: 'api/equipment_api.php',
            type: 'POST',
            data: { 
                action: 'delete', 
                id: id 
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showToast('success', 'Equipment deleted successfully');
                    refreshTable();
                } else {
                    showToast('error', response.message);
                }
            },
            error: function() {
                showToast('error', 'Failed to delete equipment');
            }
        });
    }
}

// Form submission
$('#equipmentForm').on('submit', function(e) {
    e.preventDefault();
    
    if (!this.checkValidity()) {
        e.stopPropagation();
        $(this).addClass('was-validated');
        return;
    }
    
    const formData = new FormData(this);
    const isEdit = $('#equipmentId').val() !== '';
    formData.append('action', isEdit ? 'update' : 'create');
    
    $.ajax({
        url: 'api/equipment_api.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showToast('success', isEdit ? 'Equipment updated successfully' : 'Equipment created successfully');
                $('#equipmentModal').modal('hide');
                refreshTable();
            } else {
                showToast('error', response.message);
            }
        },
        error: function() {
            showToast('error', 'Failed to save equipment');
        }
    });
});

function showToast(type, message) {
    const toast = $(`
        <div class="toast toast-${type}" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
            <div class="toast-header">
                <i class="fas fa-${type === 'success' ? 'check-circle text-success' : 'exclamation-circle text-danger'} mr-2"></i>
                <strong class="mr-auto">${type === 'success' ? 'Success' : 'Error'}</strong>
                <button type="button" class="ml-2 mb-1 close" data-dismiss="toast">
                    <span>&times;</span>
                </button>
            </div>
            <div class="toast-body">${message}</div>
        </div>
    `);
    
    $('body').append(toast);
    toast.toast({ delay: 3000 });
    toast.toast('show');
    
    toast.on('hidden.bs.toast', function() {
        $(this).remove();
    });
}
</script>

<?php include 'includes/adminlte_template_footer.php'; ?>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      
      <?php if ($action === 'list'): ?>
      <!-- List View -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-list mr-1"></i>
                All Equipment
              </h3>
              <div class="card-tools">
                <a href="?action=add" class="btn btn-primary btn-sm">
                  <i class="fas fa-plus mr-1"></i>Add New Equipment
                </a>
              </div>
            </div>
            <div class="card-body">
              <?php if (empty($equipment)): ?>
                <div class="text-center p-4">
                  <i class="fas fa-cogs fa-3x text-muted mb-3"></i>
                  <h5 class="text-muted">No Equipment Found</h5>
                  <p class="text-muted">Start by adding laboratory equipment to the system.</p>
                  <a href="?action=add" class="btn btn-primary">
                    <i class="fas fa-plus mr-2"></i>Add First Equipment
                  </a>
                </div>
              <?php else: ?>
                <table id="equipmentTable" class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Equipment Name</th>
                      <th>Model/Brand</th>
                      <th>Category</th>
                      <th>Status</th>
                      <th>Last Maintenance</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($equipment as $item): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($item['id'] ?? ''); ?></td>
                      <td>
                        <div>
                          <strong><?php echo htmlspecialchars($item['name'] ?? ''); ?></strong>
                          <br>
                          <small class="text-muted"><?php echo htmlspecialchars($item['serial_number'] ?? ''); ?></small>
                        </div>
                      </td>
                      <td>
                        <div>
                          <strong><?php echo htmlspecialchars($item['model'] ?? ''); ?></strong>
                          <br>
                          <small class="text-muted"><?php echo htmlspecialchars($item['manufacturer'] ?? ''); ?></small>
                        </div>
                      </td>
                      <td><?php echo htmlspecialchars($item['category'] ?? ''); ?></td>
                      <td>
                        <?php
                        $status = $item['status'] ?? 'active';
                        $badge_classes = [
                            'active' => 'badge-success',
                            'maintenance' => 'badge-warning',
                            'out_of_order' => 'badge-danger',
                            'retired' => 'badge-secondary'
                        ];
                        $badge_class = $badge_classes[$status] ?? 'badge-secondary';
                        echo "<span class=\"badge {$badge_class}\">" . ucfirst(str_replace('_', ' ', $status)) . "</span>";
                        ?>
                      </td>
                      <td><?php echo $item['last_maintenance'] ? date('M d, Y', strtotime($item['last_maintenance'])) : 'Never'; ?></td>
                      <td>
                        <div class="btn-group">
                          <a href="?action=view&id=<?php echo $item['id']; ?>" class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i>
                          </a>
                          <a href="?action=edit&id=<?php echo $item['id']; ?>" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i>
                          </a>
                          <button class="btn btn-secondary btn-sm" onclick="scheduleMaintenance(<?php echo $item['id']; ?>)">
                            <i class="fas fa-wrench"></i>
                          </button>
                          <button class="btn btn-danger btn-sm" onclick="deleteEquipment(<?php echo $item['id']; ?>)">
                            <i class="fas fa-trash"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      
      <?php elseif ($action === 'add' || $action === 'edit'): ?>
      <!-- Add/Edit Form -->
      <div class="row">
        <div class="col-md-10 offset-md-1">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-<?php echo $action === 'add' ? 'plus' : 'edit'; ?> mr-1"></i>
                <?php echo $action === 'add' ? 'Add New' : 'Edit'; ?> Equipment
              </h3>
              <div class="card-tools">
                <a href="?" class="btn btn-secondary btn-sm">
                  <i class="fas fa-arrow-left mr-1"></i>Back to List
                </a>
              </div>
            </div>
            <form id="equipmentForm" method="POST">
              <div class="card-body">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="name">Equipment Name <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="category">Category <span class="text-danger">*</span></label>
                      <select class="form-control select2" id="category" name="category" required>
                        <option value="">Select Category</option>
                        <option value="Analyzer">Analyzer</option>
                        <option value="Microscope">Microscope</option>
                        <option value="Centrifuge">Centrifuge</option>
                        <option value="Incubator">Incubator</option>
                        <option value="Refrigerator">Refrigerator</option>
                        <option value="Scale/Balance">Scale/Balance</option>
                        <option value="Sterilizer">Sterilizer</option>
                        <option value="Computer/IT">Computer/IT</option>
                        <option value="Other">Other</option>
                      </select>
                    </div>
                  </div>
                </div>
                
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="manufacturer">Manufacturer</label>
                      <input type="text" class="form-control" id="manufacturer" name="manufacturer">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="model">Model</label>
                      <input type="text" class="form-control" id="model" name="model">
                    </div>
                  </div>
                </div>
                
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="serial_number">Serial Number</label>
                      <input type="text" class="form-control" id="serial_number" name="serial_number">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="purchase_date">Purchase Date</label>
                      <input type="date" class="form-control" id="purchase_date" name="purchase_date">
                    </div>
                  </div>
                </div>
                
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="purchase_cost">Purchase Cost</label>
                      <div class="input-group">
                        <div class="input-group-prepend">
                          <span class="input-group-text">$</span>
                        </div>
                        <input type="number" class="form-control" id="purchase_cost" name="purchase_cost" step="0.01">
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="warranty_expiry">Warranty Expiry</label>
                      <input type="date" class="form-control" id="warranty_expiry" name="warranty_expiry">
                    </div>
                  </div>
                </div>
                
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="location">Location</label>
                      <input type="text" class="form-control" id="location" name="location" 
                             placeholder="Lab room, department, etc.">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="status">Status</label>
                      <select class="form-control" id="status" name="status">
                        <option value="active">Active</option>
                        <option value="maintenance">Under Maintenance</option>
                        <option value="out_of_order">Out of Order</option>
                        <option value="retired">Retired</option>
                      </select>
                    </div>
                  </div>
                </div>
                
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="last_maintenance">Last Maintenance Date</label>
                      <input type="date" class="form-control" id="last_maintenance" name="last_maintenance">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="next_maintenance">Next Maintenance Date</label>
                      <input type="date" class="form-control" id="next_maintenance" name="next_maintenance">
                    </div>
                  </div>
                </div>
                
                <div class="form-group">
                  <label for="supplier_contact">Supplier Contact Information</label>
                  <textarea class="form-control" id="supplier_contact" name="supplier_contact" rows="3" 
                           placeholder="Supplier name, phone, email, address..."></textarea>
                </div>
                
                <div class="form-group">
                  <label for="specifications">Technical Specifications</label>
                  <textarea class="form-control" id="specifications" name="specifications" rows="4" 
                           placeholder="Technical details, capacity, power requirements, etc..."></textarea>
                </div>
                
                <div class="form-group">
                  <label for="notes">Notes</label>
                  <textarea class="form-control" id="notes" name="notes" rows="3" 
                           placeholder="Additional notes about the equipment..."></textarea>
                </div>
              </div>
              
              <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-save mr-1"></i>
                  <?php echo $action === 'add' ? 'Add' : 'Update'; ?> Equipment
                </button>
                <a href="?" class="btn btn-secondary">
                  <i class="fas fa-times mr-1"></i>Cancel
                </a>
              </div>
            </form>
          </div>
        </div>
      </div>
      
      <?php elseif ($action === 'view'): ?>
      <!-- View Equipment Details -->
      <div class="row">
        <div class="col-md-10 offset-md-1">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-cogs mr-1"></i>
                Equipment Details - #<?php echo $equipment_id; ?>
              </h3>
              <div class="card-tools">
                <a href="?" class="btn btn-secondary btn-sm">
                  <i class="fas fa-arrow-left mr-1"></i>Back to List
                </a>
                <a href="?action=edit&id=<?php echo $equipment_id; ?>" class="btn btn-warning btn-sm">
                  <i class="fas fa-edit mr-1"></i>Edit
                </a>
                <button class="btn btn-info btn-sm" onclick="scheduleMaintenance(<?php echo $equipment_id; ?>)">
                  <i class="fas fa-wrench mr-1"></i>Schedule Maintenance
                </button>
              </div>
            </div>
            <div class="card-body">
              <!-- Equipment details would be loaded here -->
              <div class="alert alert-info">
                <i class="fas fa-info-circle mr-2"></i>
                Equipment details will be displayed here when integrated with the database.
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>
      
    </div><!-- /.container-fluid -->
  </section>
  <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<script>
$(document).ready(function() {
    // Initialize DataTable for equipment list
    if ($('#equipmentTable').length) {
        $('#equipmentTable').DataTable({
            responsive: true,
            autoWidth: false,
            pageLength: 25,
            order: [[0, 'desc']], // Order by ID descending
            columnDefs: [
                { 
                    targets: -1, // Last column (Actions)
                    orderable: false,
                    searchable: false
                }
            ]
        });
    }
    
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });
    
    // Form validation
    $('#equipmentForm').on('submit', function(e) {
        e.preventDefault();
        
        // Basic validation
        let isValid = true;
        $(this).find('[required]').each(function() {
            if (!$(this).val().trim()) {
                isValid = false;
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        if (isValid) {
            // Submit form (you would typically send this to a handler)
            PathLabPro.notifications.success('Equipment saved successfully!');
            // window.location.href = '?';
        } else {
            PathLabPro.notifications.error('Please fill in all required fields.');
        }
    });
});

function scheduleMaintenance(id) {
    PathLabPro.modal.prompt({
        title: 'Schedule Maintenance',
        text: 'Enter the maintenance date:',
        inputType: 'date'
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            // Schedule maintenance logic here
            PathLabPro.notifications.success('Maintenance scheduled for ' + result.value);
        }
    });
}

function deleteEquipment(id) {
    PathLabPro.modal.confirm({
        title: 'Delete Equipment',
        text: 'Are you sure you want to delete this equipment? This action cannot be undone.',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Delete equipment logic here
            PathLabPro.notifications.success('Equipment deleted successfully!');
            // Reload page or remove row
        }
    });
}
</script>

<?php include 'includes/adminlte_template_footer.php'; ?>
