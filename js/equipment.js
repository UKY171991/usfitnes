/**
 * PathLab Pro - Equipment Management JavaScript
 * External JS file for equipment.php
 */

$(document).ready(function() {
    // Initialize DataTable
    window.equipmentTable = initDataTable('#equipmentTable', {
        ajax: {
            url: 'ajax/equipment_datatable.php',
            type: 'POST'
        },
        columns: [
            { data: 'equipment_code', width: '120px' },
            { data: 'equipment_name' },
            { data: 'equipment_type', width: '120px' },
            { data: 'location', width: '120px' },
            { data: 'status', width: '80px' },
            { data: 'last_maintenance', width: '120px' },
            { data: 'actions', orderable: false, searchable: false, width: '120px' }
        ]
    });
    
    // Handle form submission
    handleAjaxForm('#equipmentForm', 'api/equipment_api.php', function(response) {
        $('#equipmentModal').modal('hide');
        window.equipmentTable.ajax.reload();
    });
});

function openAddModal() {
    resetModalForm('equipmentModal');
    $('#equipmentModalLabel').html('<i class="fas fa-cogs mr-2"></i>Add Equipment');
    $('#equipmentModal').modal('show');
    $('#equipmentName').focus();
}

function editEquipment(id) {
    $('#equipmentModalLabel').html('<i class="fas fa-cogs mr-2"></i>Edit Equipment');
    
    loadDataForEdit(id, 'api/equipment_api.php', function(data) {
        populateForm('#equipmentForm', data);
        $('#equipmentModal').modal('show');
    });
}

function deleteEquipment(id) {
    handleAjaxDelete(id, 'api/equipment_api.php', 'equipment', function() {
        window.equipmentTable.ajax.reload();
    });
}

function viewEquipment(id) {
    // View functionality can be implemented later
    showToast('info', 'View equipment functionality coming soon');
}

function refreshTable() {
    window.equipmentTable.ajax.reload();
    showToast('info', 'Table refreshed');
}

// Export functions for global access
window.openAddModal = openAddModal;
window.editEquipment = editEquipment;
window.deleteEquipment = deleteEquipment;
window.viewEquipment = viewEquipment;
window.refreshTable = refreshTable;