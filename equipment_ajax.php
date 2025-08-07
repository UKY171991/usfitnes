<?php
require_once 'config.php';
require_once 'includes/adminlte_template.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

renderTemplate('equipment_ajax', 'Equipment Management', [
    'page_title' => 'Equipment Management',
    'breadcrumbs' => [
        ['name' => 'Home', 'url' => 'dashboard.php'],
        ['name' => 'Equipment', 'url' => '']
    ]
]);

function getContent() {
    ob_start();
?>

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
        <!-- Equipment Table Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-microscope mr-2"></i>
                    All Equipment
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" id="addEquipmentBtn">
                        <i class="fas fa-plus mr-1"></i>
                        Add Equipment
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="equipmentTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Model</th>
                                <th>Serial Number</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- DataTables will populate this -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Add/Edit Equipment Modal -->
<div class="modal fade" id="equipmentModal" tabindex="-1" role="dialog" aria-labelledby="equipmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="equipmentModalLabel">Add Equipment</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="equipmentForm">
                <div class="modal-body">
                    <input type="hidden" id="equipmentId" name="id">
                    <input type="hidden" name="action" id="formAction" value="add">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="equipmentName">Equipment Name *</label>
                                <input type="text" class="form-control" id="equipmentName" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="equipmentType">Type *</label>
                                <select class="form-control" id="equipmentType" name="type" required>
                                    <option value="">Select Type</option>
                                    <option value="Analyzer">Analyzer</option>
                                    <option value="Microscope">Microscope</option>
                                    <option value="Centrifuge">Centrifuge</option>
                                    <option value="Spectrophotometer">Spectrophotometer</option>
                                    <option value="Incubator">Incubator</option>
                                    <option value="pH Meter">pH Meter</option>
                                    <option value="Balance">Balance</option>
                                    <option value="Pipette">Pipette</option>
                                    <option value="Water Bath">Water Bath</option>
                                    <option value="Other">Other</option>
                                </select>
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
                                <label for="purchaseDate">Purchase Date</label>
                                <input type="date" class="form-control" id="purchaseDate" name="purchase_date">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="saveEquipmentBtn">
                        <i class="fas fa-save mr-1"></i>
                        Save Equipment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTable
    let equipmentTable = $('#equipmentTable').DataTable({
        ajax: {
            url: 'api/equipment_api.php',
            type: 'GET',
            data: { action: 'list' },
            dataSrc: function(json) {
                if (!json.success) {
                    toastr.error(json.message || 'Failed to load equipment');
                    return [];
                }
                return json.data || [];
            }
        },
        columns: [
            { 
                data: 'equipment_id',
                defaultContent: 'N/A'
            },
            { 
                data: 'name',
                defaultContent: 'N/A'
            },
            { 
                data: 'type',
                defaultContent: 'N/A'
            },
            { 
                data: 'model',
                defaultContent: 'N/A'
            },
            { 
                data: 'serial_number',
                defaultContent: 'N/A'
            },
            { 
                data: 'status',
                render: function(data, type, row) {
                    let badgeClass = 'secondary';
                    if (data === 'active') badgeClass = 'success';
                    else if (data === 'maintenance') badgeClass = 'warning';
                    else if (data === 'repair') badgeClass = 'danger';
                    
                    return `<span class="badge badge-${badgeClass}">${data || 'N/A'}</span>`;
                }
            },
            {
                data: null,
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-info btn-sm edit-equipment" data-id="${row.id}" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-sm delete-equipment" data-id="${row.id}" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        responsive: true,
        order: [[0, 'desc']],
        pageLength: 25,
        language: {
            emptyTable: "No equipment found",
            loadingRecords: "Loading equipment...",
            processing: "Processing..."
        }
    });

    // Add Equipment Button
    $('#addEquipmentBtn').click(function() {
        resetForm();
        $('#equipmentModalLabel').text('Add Equipment');
        $('#formAction').val('add');
        $('#equipmentModal').modal('show');
    });

    // Edit Equipment
    $(document).on('click', '.edit-equipment', function() {
        const equipmentId = $(this).data('id');
        editEquipment(equipmentId);
    });

    // Delete Equipment
    $(document).on('click', '.delete-equipment', function() {
        const equipmentId = $(this).data('id');
        deleteEquipment(equipmentId);
    });

    // Form Submission
    $('#equipmentForm').submit(function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        // Show loading state
        $('#saveEquipmentBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Saving...');
        
        $.ajax({
            url: 'api/equipment_api.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#equipmentModal').modal('hide');
                    equipmentTable.ajax.reload(null, false);
                } else {
                    toastr.error(response.message || 'Operation failed');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                toastr.error('Network error occurred. Please try again.');
            },
            complete: function() {
                $('#saveEquipmentBtn').prop('disabled', false).html('<i class="fas fa-save mr-1"></i>Save Equipment');
            }
        });
    });

    function editEquipment(id) {
        $.ajax({
            url: 'api/equipment_api.php',
            type: 'GET',
            data: { action: 'get', id: id },
            success: function(response) {
                if (response.success && response.data) {
                    const equipment = response.data;
                    
                    // Populate form
                    $('#equipmentId').val(equipment.id);
                    $('#equipmentName').val(equipment.name);
                    $('#equipmentType').val(equipment.type);
                    $('#model').val(equipment.model);
                    $('#serialNumber').val(equipment.serial_number);
                    $('#manufacturer').val(equipment.manufacturer);
                    $('#purchaseDate').val(equipment.purchase_date);
                    
                    $('#equipmentModalLabel').text('Edit Equipment');
                    $('#formAction').val('update');
                    $('#equipmentModal').modal('show');
                } else {
                    toastr.error(response.message || 'Failed to load equipment data');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                toastr.error('Failed to load equipment data');
            }
        });
    }

    function deleteEquipment(id) {
        Swal.fire({
            title: 'Delete Equipment',
            text: 'Are you sure you want to delete this equipment? This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, Delete',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'api/equipment_api.php',
                    type: 'POST',
                    data: { 
                        action: 'delete', 
                        id: id 
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            equipmentTable.ajax.reload(null, false);
                        } else {
                            toastr.error(response.message || 'Delete failed');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', xhr.responseText);
                        toastr.error('Failed to delete equipment');
                    }
                });
            }
        });
    }

    function resetForm() {
        $('#equipmentForm')[0].reset();
        $('#equipmentId').val('');
        $('#formAction').val('add');
    }

    // Auto-refresh table every 30 seconds
    setInterval(function() {
        if ($('#equipmentModal').is(':visible') === false) {
            equipmentTable.ajax.reload(null, false);
        }
    }, 30000);
});
</script>

<?php
    return ob_get_clean();
}
?>
