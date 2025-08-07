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
                <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#equipmentModal" onclick="openEquipmentModal()">
                  <i class="fas fa-plus mr-1"></i>Add Equipment
                </button>
              </div>
            </div>
            <div class="card-body">
              <table id="equipmentTable" class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>Equipment Code</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Last Maintenance</th>
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
  </section>
</div>

<!-- Equipment Modal -->
<div class="modal fade" id="equipmentModal" tabindex="-1" role="dialog" aria-labelledby="equipmentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="equipmentModalLabel">Add Equipment</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="equipmentForm">
        <div class="modal-body">
          <input type="hidden" id="equipmentId" name="id">
          
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="equipmentName">Equipment Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="equipmentName" name="equipment_name" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="equipmentType">Type</label>
                <input type="text" class="form-control" id="equipmentType" name="equipment_type">
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="model">Model</label>
                <input type="text" class="form-control" id="model" name="model">
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
                <label for="location">Location</label>
                <input type="text" class="form-control" id="location" name="location">
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="purchaseDate">Purchase Date</label>
                <input type="date" class="form-control" id="purchaseDate" name="purchase_date">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="warrantyExpiry">Warranty Expiry</label>
                <input type="date" class="form-control" id="warrantyExpiry" name="warranty_expiry">
              </div>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="cost">Cost</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">$</span>
                  </div>
                  <input type="number" class="form-control" id="cost" name="cost" step="0.01">
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="maintenanceSchedule">Maintenance Schedule</label>
                <select class="form-control" id="maintenanceSchedule" name="maintenance_schedule">
                  <option value="weekly">Weekly</option>
                  <option value="monthly" selected>Monthly</option>
                  <option value="quarterly">Quarterly</option>
                  <option value="yearly">Yearly</option>
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
          <button type="submit" class="btn btn-warning">Save Equipment</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#equipmentTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: 'ajax/equipment_datatable.php',
            type: 'POST'
        },
        columns: [
            { data: 'equipment_code' },
            { data: 'equipment_name' },
            { data: 'equipment_type' },
            { data: 'location' },
            { data: 'status' },
            { data: 'last_maintenance' },
            { data: 'actions', orderable: false, searchable: false }
        ],
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true
    });
    
    // Handle form submission
    $('#equipmentForm').on('submit', function(e) {
        e.preventDefault();
        saveEquipment();
    });
});

function openEquipmentModal(id = null) {
    if (id) {
        // Edit mode
        $('#equipmentModalLabel').text('Edit Equipment');
        loadEquipmentData(id);
    } else {
        // Add mode
        $('#equipmentModalLabel').text('Add Equipment');
        $('#equipmentForm')[0].reset();
        $('#equipmentId').val('');
    }
}

function loadEquipmentData(id) {
    $.ajax({
        url: 'api/equipment_api.php',
        type: 'GET',
        data: { action: 'get', id: id },
        success: function(response) {
            if (response.success) {
                const equipment = response.data;
                $('#equipmentId').val(equipment.id);
                $('#equipmentName').val(equipment.equipment_name);
                $('#equipmentType').val(equipment.equipment_type);
                $('#model').val(equipment.model);
                $('#serialNumber').val(equipment.serial_number);
                $('#manufacturer').val(equipment.manufacturer);
                $('#location').val(equipment.location);
                $('#purchaseDate').val(equipment.purchase_date);
                $('#warrantyExpiry').val(equipment.warranty_expiry);
                $('#cost').val(equipment.cost);
                $('#maintenanceSchedule').val(equipment.maintenance_schedule);
                $('#description').val(equipment.description);
            } else {
                toastr.error(response.message);
            }
        },
        error: function() {
            toastr.error('Error loading equipment data');
        }
    });
}

function saveEquipment() {
    const formData = new FormData($('#equipmentForm')[0]);
    const isEdit = $('#equipmentId').val() !== '';
    
    $.ajax({
        url: 'api/equipment_api.php',
        type: isEdit ? 'PUT' : 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                toastr.success(response.message);
                $('#equipmentModal').modal('hide');
                $('#equipmentTable').DataTable().ajax.reload();
            } else {
                toastr.error(response.message);
            }
        },
        error: function() {
            toastr.error('Error saving equipment');
        }
    });
}

function deleteEquipment(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'api/equipment_api.php',
                type: 'DELETE',
                data: { id: id },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('#equipmentTable').DataTable().ajax.reload();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    toastr.error('Error deleting equipment');
                }
            });
        }
    });
}
</script>

<?php include 'includes/adminlte_template_footer.php'; ?>