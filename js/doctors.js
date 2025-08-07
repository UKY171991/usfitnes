/**
 * PathLab Pro - Doctors Management JavaScript
 * External JS file for doctors.php
 */

$(document).ready(function() {
    // Initialize DataTable
    window.doctorsTable = initDataTable('#doctorsTable', {
        ajax: {
            url: 'ajax/doctors_datatable.php',
            type: 'POST'
        },
        columns: [
            { data: 'doctor_id', width: '100px' },
            { data: 'name' },
            { data: 'specialization' },
            { data: 'phone', width: '120px' },
            { data: 'email' },
            { data: 'status', width: '80px' },
            { data: 'actions', orderable: false, searchable: false, width: '120px' }
        ]
    });
    
    // Handle form submission
    handleAjaxForm('#doctorForm', 'api/doctors_api.php', function(response) {
        $('#doctorModal').modal('hide');
        window.doctorsTable.ajax.reload();
    });
});

function openAddModal() {
    resetModalForm('doctorModal');
    $('#doctorModalLabel').html('<i class="fas fa-user-md mr-2"></i>Add Doctor');
    $('#doctorModal').modal('show');
    $('#doctorName').focus();
}

function editDoctor(id) {
    $('#doctorModalLabel').html('<i class="fas fa-user-md mr-2"></i>Edit Doctor');
    
    loadDataForEdit(id, 'api/doctors_api.php', function(data) {
        populateForm('#doctorForm', data);
        $('#doctorModal').modal('show');
    });
}

function deleteDoctor(id) {
    handleAjaxDelete(id, 'api/doctors_api.php', 'doctor', function() {
        window.doctorsTable.ajax.reload();
    });
}

function viewDoctor(id) {
    // View functionality can be implemented later
    showToast('info', 'View doctor functionality coming soon');
}

function refreshTable() {
    window.doctorsTable.ajax.reload();
    showToast('info', 'Table refreshed');
}

// Export functions for global access
window.openAddModal = openAddModal;
window.editDoctor = editDoctor;
window.deleteDoctor = deleteDoctor;
window.viewDoctor = viewDoctor;
window.refreshTable = refreshTable;