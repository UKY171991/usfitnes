// Equipment Management JavaScript
// AdminLTE3 Template with AJAX Operations

let equipmentTable;
let equipmentCrud;
let equipmentFormHandler;

$(document).ready(function() {
    initializeEquipmentPage();
});

function initializeEquipmentPage() {
    // Initialize CRUD operations
    equipmentCrud = new CrudOperations('api/equipment_api.php', 'Equipment');
    
    // Initialize form handler
    equipmentFormHandler = new FormHandler('#equipmentForm', 'api/equipment_api.php', {
        onSuccess: function(response) {
            $('#equipmentModal').modal('hide');
            equipmentTable.ajax.reload(null, false);
            showSuccess(response.message);
        }
    });
    
    // Initialize DataTable
    initializeEquipmentTable();
    
    // Initialize filters
    initializeFilters();
}

function initializeEquipmentTable() {
    const columns = [
        {
            data: 'equipment_code',
            name: 'equipment_code',
            title: 'Code',
            width: '100px'
        },
        {
            data: 'equipment_name',
            name: 'equipment_name',
            title: 'Name',
            render: function(data, type, row) {
                return `<strong>${data}</strong>`;
            }
        },
        {
            data: 'category',
            name: 'category',
            title: 'Category',
            render: function(data, type, row) {
                if (!data) return '-';
                const categoryClass = getCategoryClass(data);
                return `<span class="badge badge-${categoryClass}">${data}</span>`;
            }
        },
        {
            data: 'manufacturer',
            name: 'manufacturer',
            title: 'Manufacturer',
            render: function(data, type, row) {
                return data || '-';
            }
        },
        {
            data: 'location',
            name: 'location',
            title: 'Location',
            render: function(data, type, row) {
                return data || '-';
            }
        },
        {
            data: 'status',
            name: 'status',
            title: 'Status',
            render: function(data, type, row) {
                const statusClass = getStatusClass(data);
                return `<span class="badge badge-${statusClass}">${capitalizeFirst(data)}</span>`;
            }
        },
        {
            data: null,
            name: 'actions',
            title: 'Actions',
            orderable: false,
            searchable: false,
            width: '150px',
            render: function(data, type, row) {
                return `
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-info btn-action" onclick="viewEquipment(${row.id})" title="View">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="btn btn-warning btn-action" onclick="editEquipment(${row.id})" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-action" onclick="deleteEquipment(${row.id})" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
            }
        }
    ];

    equipmentTable = initializeDataTable('#equipmentTable', 'ajax/equipment_datatable.php', columns, {
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true,
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        buttons: [
            {
                extend: 'csv',
                text: '<i class="fas fa-file-csv"></i> CSV',
                className: 'btn btn-success btn-sm'
            },
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-success btn-sm'
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn btn-danger btn-sm'
            }
        ]
    });
    
    // Store reference globally
    globalDataTable = equipmentTable;
}

function initializeFilters() {
    $('#statusFilter, #categoryFilter').on('change', function() {
        applyFilters();
    });
    
    $('#manufacturerFilter').on('keyup', debounce(function() {
        applyFilters();
    }, 500));
}

function getCustomFilters() {
    return {
        status: $('#statusFilter').val(),
        category: $('#categoryFilter').val(),
        manufacturer: $('#manufacturerFilter').val()
    };
}

function applyFilters() {
    if (equipmentTable) {
        equipmentTable.ajax.reload();
    }
}

function clearFilters() {
    $('#statusFilter, #categoryFilter, #manufacturerFilter').val('').trigger('change');
    applyFilters();
}

// Modal Functions
function showAddEquipmentModal() {
    resetForm('#equipmentForm');
    $('#equipmentId').val('');
    $('#equipmentModal .modal-title').text('Add New Equipment');
    $('#equipmentModal').modal('show');
}

async function editEquipment(id) {
    try {
        const equipment = await equipmentCrud.getById(id);
        
        // Populate form
        equipmentFormHandler.populateForm(equipment);
        
        $('#equipmentModal .modal-title').text('Edit Equipment');
        $('#equipmentModal').modal('show');
    } catch (error) {
        showError('Failed to load equipment data');
    }
}

async function viewEquipment(id) {
    try {
        showLoader('Loading equipment details...');
        const equipment = await equipmentCrud.getById(id);
        
        const detailsHtml = `
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Equipment Code:</th>
                            <td>${equipment.equipment_code}</td>
                        </tr>
                        <tr>
                            <th>Name:</th>
                            <td><strong>${equipment.equipment_name}</strong></td>
                        </tr>
                        <tr>
                            <th>Category:</th>
                            <td>${equipment.category ? `<span class="badge badge-${getCategoryClass(equipment.category)}">${equipment.category}</span>` : 'N/A'}</td>
                        </tr>
                        <tr>
                            <th>Type:</th>
                            <td>${equipment.equipment_type || 'N/A'}</td>
                        </tr>
                        <tr>
                            <th>Model:</th>
                            <td>${equipment.model || 'N/A'}</td>
                        </tr>
                        <tr>
                            <th>Serial Number:</th>
                            <td>${equipment.serial_number || 'N/A'}</td>
                        </tr>
                        <tr>
                            <th>Manufacturer:</th>
                            <td>${equipment.manufacturer || 'N/A'}</td>
                        </tr>
                        <tr>
                            <th>Location:</th>
                            <td>${equipment.location || 'N/A'}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Purchase Date:</th>
                            <td>${equipment.purchase_date ? formatDate(equipment.purchase_date) : 'N/A'}</td>
                        </tr>
                        <tr>
                            <th>Warranty Expiry:</th>
                            <td>${equipment.warranty_expiry ? formatDate(equipment.warranty_expiry) : 'N/A'}</td>
                        </tr>
                        <tr>
                            <th>Cost:</th>
                            <td>${equipment.cost ? formatCurrency(equipment.cost) : 'N/A'}</td>
                        </tr>
                        <tr>
                            <th>Maintenance Schedule:</th>
                            <td>${equipment.maintenance_schedule ? capitalizeFirst(equipment.maintenance_schedule) : 'N/A'}</td>
                        </tr>
                        <tr>
                            <th>Last Maintenance:</th>
                            <td>${equipment.last_maintenance ? formatDate(equipment.last_maintenance) : 'N/A'}</td>
                        </tr>
                        <tr>
                            <th>Next Maintenance:</th>
                            <td>${equipment.next_maintenance ? formatDate(equipment.next_maintenance) : 'N/A'}</td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td><span class="badge badge-${getStatusClass(equipment.status)}">${capitalizeFirst(equipment.status)}</span></td>
                        </tr>
                        <tr>
                            <th>Added:</th>
                            <td>${formatDateTime(equipment.created_at)}</td>
                        </tr>
                    </table>
                </div>
            </div>
            ${equipment.description ? `
            <div class="row">
                <div class="col-12">
                    <hr>
                    <h6>Description:</h6>
                    <p>${equipment.description}</p>
                </div>
            </div>
            ` : ''}
        `;
        
        $('#equipmentDetails').html(detailsHtml);
        $('#viewEquipmentModal').modal('show');
        
    } catch (error) {
        showError('Failed to load equipment details');
    } finally {
        hideLoader();
    }
}

async function deleteEquipment(id) {
    try {
        await equipmentCrud.delete(id);
        equipmentTable.ajax.reload(null, false);
    } catch (error) {
        // Error handling is done in CrudOperations class
    }
}

// Export Functions
function exportEquipment() {
    const format = 'csv'; // Can be made dynamic
    const filters = getCustomFilters();
    
    AjaxUtils.exportData('api/equipment_api.php?action=export', format, filters);
}

// Utility Functions
function getCategoryClass(category) {
    const categoryClasses = {
        'Analyzer': 'primary',
        'Microscope': 'info',
        'Centrifuge': 'success',
        'Incubator': 'warning',
        'Refrigerator': 'secondary',
        'Computer': 'dark',
        'Other': 'light'
    };
    return categoryClasses[category] || 'secondary';
}

function getStatusClass(status) {
    const statusClasses = {
        'active': 'success',
        'maintenance': 'warning',
        'inactive': 'secondary'
    };
    return statusClasses[status] || 'secondary';
}

function resetEquipmentForm() {
    resetForm('#equipmentForm');
}

// Global functions for external access
window.showAddEquipmentModal = showAddEquipmentModal;
window.editEquipment = editEquipment;
window.viewEquipment = viewEquipment;
window.deleteEquipment = deleteEquipment;
window.exportEquipment = exportEquipment;
window.applyFilters = applyFilters;
window.clearFilters = clearFilters;