/**
 * PathLab Pro - Test Orders Management JavaScript
 * External JS file for test-orders.php
 */

$(document).ready(function() {
    // Initialize DataTable
    window.testOrdersTable = initDataTable('#testOrdersTable', {
        ajax: {
            url: 'ajax/test_orders_datatable.php',
            type: 'POST'
        },
        columns: [
            { data: 'order_number', width: '120px' },
            { data: 'patient_name' },
            { data: 'doctor_name' },
            { data: 'test_count', width: '80px' },
            { data: 'status', width: '100px' },
            { data: 'priority', width: '80px' },
            { data: 'order_date', width: '120px' },
            { data: 'actions', orderable: false, searchable: false, width: '120px' }
        ]
    });
    
    // Handle form submission with custom logic for test orders
    $('#testOrderForm').off('submit').on('submit', function(e) {
        e.preventDefault();
        
        const selectedTests = [];
        $('.test-checkbox:checked').each(function() {
            selectedTests.push($(this).val());
        });
        
        if (selectedTests.length === 0) {
            showToast('error', 'Please select at least one test');
            return;
        }
        
        const formData = new FormData(this);
        formData.append('tests', JSON.stringify(selectedTests));
        
        const isEdit = $('#testOrderId').val() !== '';
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
        
        $.ajax({
            url: 'api/test_orders_api.php',
            type: isEdit ? 'PUT' : 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showToast('success', response.message);
                    $('#testOrderModal').modal('hide');
                    window.testOrdersTable.ajax.reload();
                } else {
                    showToast('error', response.message);
                }
            },
            error: function() {
                showToast('error', 'Error saving test order');
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
    
    // Load data when modal opens
    $('#testOrderModal').on('show.bs.modal', function() {
        loadPatients();
        loadDoctors();
        loadTests();
    });
    
    // Calculate total when discount changes
    $('#discount').on('input', function() {
        calculateTotal();
    });
});

function openAddModal() {
    resetModalForm('testOrderModal');
    $('#testOrderModalLabel').html('<i class="fas fa-vials mr-2"></i>Create Test Order');
    $('#orderDate').val(new Date().toISOString().slice(0, 16));
    $('#testOrderModal').modal('show');
}

function editTestOrder(id) {
    $('#testOrderModalLabel').html('<i class="fas fa-vials mr-2"></i>Edit Test Order');
    
    loadDataForEdit(id, 'api/test_orders_api.php', function(data) {
        populateForm('#testOrderForm', data);
        $('#testOrderModal').modal('show');
    });
}

function deleteTestOrder(id) {
    handleAjaxDelete(id, 'api/test_orders_api.php', 'test order', function() {
        window.testOrdersTable.ajax.reload();
    });
}

function viewTestOrder(id) {
    // View functionality can be implemented later
    showToast('info', 'View test order functionality coming soon');
}

function refreshTable() {
    window.testOrdersTable.ajax.reload();
    showToast('info', 'Table refreshed');
}

function loadPatients() {
    $.ajax({
        url: 'api/patients_api.php',
        type: 'GET',
        data: { limit: 1000, status: 'active' },
        success: function(response) {
            if (response.success) {
                const select = $('#patientSelect');
                select.empty().append('<option value="">Select Patient</option>');
                response.data.patients.forEach(function(patient) {
                    select.append(`<option value="${patient.id}">${patient.full_name} (${patient.patient_id})</option>`);
                });
            }
        },
        error: function() {
            showToast('error', 'Failed to load patients');
        }
    });
}

function loadDoctors() {
    $.ajax({
        url: 'api/doctors_api.php',
        type: 'GET',
        data: { limit: 1000, status: 'active' },
        success: function(response) {
            if (response.success) {
                const select = $('#doctorSelect');
                select.empty().append('<option value="">Select Doctor</option>');
                response.data.doctors.forEach(function(doctor) {
                    select.append(`<option value="${doctor.id}">${doctor.name} - ${doctor.specialization}</option>`);
                });
            }
        },
        error: function() {
            showToast('error', 'Failed to load doctors');
        }
    });
}

function loadTests() {
    $.ajax({
        url: 'api/tests_api.php',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const container = $('#testsContainer');
                container.empty();
                
                if (response.data.tests.length === 0) {
                    container.html('<div class="text-center text-muted">No tests available</div>');
                    return;
                }
                
                response.data.tests.forEach(function(test) {
                    const testHtml = `
                        <div class="form-check mb-2">
                            <input class="form-check-input test-checkbox" type="checkbox" value="${test.id}" 
                                   id="test_${test.id}" data-price="${test.price}" onchange="calculateTotal()">
                            <label class="form-check-label" for="test_${test.id}">
                                <strong>${test.name}</strong> - $${test.price}
                                ${test.description ? '<br><small class="text-muted">' + test.description + '</small>' : ''}
                            </label>
                        </div>
                    `;
                    container.append(testHtml);
                });
            }
        },
        error: function() {
            $('#testsContainer').html('<div class="text-center text-danger">Failed to load tests</div>');
            showToast('error', 'Failed to load tests');
        }
    });
}

function calculateTotal() {
    let total = 0;
    $('.test-checkbox:checked').each(function() {
        total += parseFloat($(this).data('price')) || 0;
    });
    
    const discount = parseFloat($('#discount').val()) || 0;
    const finalTotal = Math.max(0, total - discount);
    
    $('#totalAmount').val(total.toFixed(2));
    $('#finalAmount').text(finalTotal.toFixed(2));
}

// Export functions for global access
window.openAddModal = openAddModal;
window.editTestOrder = editTestOrder;
window.deleteTestOrder = deleteTestOrder;
window.viewTestOrder = viewTestOrder;
window.refreshTable = refreshTable;
window.loadPatients = loadPatients;
window.loadDoctors = loadDoctors;
window.loadTests = loadTests;
window.calculateTotal = calculateTotal;