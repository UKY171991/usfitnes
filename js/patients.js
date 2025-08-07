/**
 * PathLab Pro - Patients Management JavaScript
 * External JS file for patients.php
 */

$(document).ready(function() {
    // Initialize DataTable
    window.patientsTable = initDataTable('#patientsTable', {
        ajax: {
            url: 'ajax/patients_datatable.php',
            type: 'POST'
        },
        columns: [
            { data: 'patient_id', width: '100px' },
            { data: 'full_name' },
            { data: 'phone', width: '120px' },
            { data: 'email' },
            { data: 'blood_group', width: '100px' },
            { data: 'status', width: '80px' },
            { data: 'actions', orderable: false, searchable: false, width: '120px' }
        ]
    });
    
    // Handle form submission
    handleAjaxForm('#patientForm', 'api/patients_api.php', function(response) {
        $('#patientModal').modal('hide');
        window.patientsTable.ajax.reload();
    });
});

function openAddModal() {
    resetModalForm('patientModal');
    $('#patientModalLabel').html('<i class="fas fa-user-injured mr-2"></i>Add Patient');
    $('#patientModal').modal('show');
    $('#firstName').focus();
}

function editPatient(id) {
    $('#patientModalLabel').html('<i class="fas fa-user-injured mr-2"></i>Edit Patient');
    
    loadDataForEdit(id, 'api/patients_api.php', function(data) {
        populateForm('#patientForm', data);
        $('#patientModal').modal('show');
    });
}

function deletePatient(id) {
    handleAjaxDelete(id, 'api/patients_api.php', 'patient', function() {
        window.patientsTable.ajax.reload();
    });
}

function viewPatient(id) {
    // View functionality can be implemented later
    showToast('info', 'View patient functionality coming soon');
}

function refreshTable() {
    window.patientsTable.ajax.reload();
    showToast('info', 'Table refreshed');
}

// Export functions for global access
window.openAddModal = openAddModal;
window.editPatient = editPatient;
window.deletePatient = deletePatient;
window.viewPatient = viewPatient;
window.refreshTable = refreshTable;