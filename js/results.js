/**
 * Results Management JavaScript
 * Modern AJAX-based functionality for test results management
 */

$(document).ready(function() {
    // Initialize the page
    initializeResultsPage();
});

/**
 * Initialize the results page
 */
function initializeResultsPage() {
    loadResultsTable();
    setupEventListeners();
    loadDropdownData();
    setupFormValidation();
    
    console.log('Results page initialized');
}

/**
 * Load and initialize the results table
 */
function loadResultsTable() {
    if ($.fn.DataTable.isDataTable('#resultsTable')) {
        $('#resultsTable').DataTable().destroy();
    }
    
    $('#resultsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: 'ajax/results_datatable.php',
            type: 'POST',
            data: function(d) {
                d.status = $('#statusFilter').val();
                d.priority = $('#priorityFilter').val();
                d.search_value = $('#searchInput').val();
            },
            error: function(xhr, error, code) {
                console.error('DataTable error:', error);
                showError('Failed to load results data');
            }
        },
        columns: [
            { data: 'id', width: '80px' },
            { data: 'patient_name', width: '200px' },
            { data: 'test_type', width: '150px' },
            { data: 'result_value', width: '120px' },
            { 
                data: 'status',
                width: '100px',
                render: function(data, type, row) {
                    return renderStatusBadge(data);
                }
            },
            { 
                data: 'created_at',
                width: '120px',
                render: function(data, type, row) {
                    return data ? moment(data).format('MMM DD, YYYY') : '';
                }
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                width: '150px',
                render: function(data, type, row) {
                    return renderActionButtons(row);
                }
            }
        ],
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true,
        autoWidth: false,
        language: {
            processing: '<i class="fas fa-spinner fa-spin"></i> Loading results...',
            emptyTable: 'No test results found',
            zeroRecords: 'No matching results found'
        },
        drawCallback: function() {
            // Re-bind action button events after table redraw
            bindActionButtons();
        }
    });
}

/**
 * Render status badge
 */
function renderStatusBadge(status) {
    const badges = {
        'pending': '<span class="badge badge-warning">Pending</span>',
        'completed': '<span class="badge badge-success">Completed</span>',
        'reviewed': '<span class="badge badge-info">Reviewed</span>',
        'cancelled': '<span class="badge badge-secondary">Cancelled</span>'
    };
    
    return badges[status] || '<span class="badge badge-secondary">Unknown</span>';
}

/**
 * Render action buttons for table rows
 */
function renderActionButtons(row) {
    const criticalClass = row.is_critical ? 'critical-result' : '';
    const priorityClass = `priority-${row.priority}`;
    
    return `
        <div class="btn-group ${criticalClass}" role="group">
            <button type="button" class="btn btn-info btn-sm view-result" data-id="${row.id}" title="View Details">
                <i class="fas fa-eye"></i>
            </button>
            <button type="button" class="btn btn-warning btn-sm edit-result" data-id="${row.id}" title="Edit Result">
                <i class="fas fa-edit"></i>
            </button>
            <button type="button" class="btn btn-success btn-sm download-result" data-id="${row.id}" title="Download Report">
                <i class="fas fa-download"></i>
            </button>
            <button type="button" class="btn btn-danger btn-sm delete-result" data-id="${row.id}" title="Delete Result">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;
}

/**
 * Bind action button events
 */
function bindActionButtons() {
    // View result
    $('.view-result').off('click').on('click', function() {
        const id = $(this).data('id');
        viewResult(id);
    });
    
    // Edit result
    $('.edit-result').off('click').on('click', function() {
        const id = $(this).data('id');
        editResult(id);
    });
    
    // Download result
    $('.download-result').off('click').on('click', function() {
        const id = $(this).data('id');
        downloadResult(id);
    });
    
    // Delete result
    $('.delete-result').off('click').on('click', function() {
        const id = $(this).data('id');
        deleteResult(id);
    });
}

/**
 * Setup event listeners
 */
function setupEventListeners() {
    // Filter changes
    $('#statusFilter, #priorityFilter').on('change', function() {
        refreshTable();
    });
    
    // Search functionality
    $('#searchInput').on('keyup', debounce(function() {
        refreshTable();
    }, 500));
    
    // Form submission
    $('#resultForm').on('submit', function(e) {
        e.preventDefault();
        saveResult();
    });
    
    // Modal events
    $('#resultModal').on('hidden.bs.modal', function() {
        resetForm();
    });
    
    // Patient selection change
    $('#patientId').on('change', function() {
        loadTestOrders($(this).val());
    });
}

/**
 * Load dropdown data
 */
function loadDropdownData() {
    // Load patients
    $.ajax({
        url: 'api/patients_api.php',
        method: 'GET',
        data: { action: 'list' },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                populateSelect('#patientId', response.data, 'id', 'name');
            }
        },
        error: function() {
            console.error('Failed to load patients');
        }
    });
}

/**
 * Load test orders for selected patient
 */
function loadTestOrders(patientId) {
    if (!patientId) {
        $('#orderId').html('<option value="">Select Order</option>');
        return;
    }
    
    $.ajax({
        url: 'api/test_orders_api.php',
        method: 'GET',
        data: { 
            action: 'list',
            patient_id: patientId 
        },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                populateSelect('#orderId', response.data, 'id', 'order_number');
            }
        },
        error: function() {
            console.error('Failed to load test orders');
            showError('Failed to load test orders');
        }
    });
}

/**
 * Populate select dropdown
 */
function populateSelect(selector, data, valueField, textField) {
    const select = $(selector);
    const defaultOption = select.find('option:first').clone();
    
    select.empty().append(defaultOption);
    
    data.forEach(function(item) {
        const option = $('<option></option>')
            .val(item[valueField])
            .text(item[textField]);
        select.append(option);
    });
}

/**
 * Show add result modal
 */
function showAddResultModal() {
    $('#resultModalTitle').text('Add Test Result');
    $('#resultId').val('');
    resetForm();
    $('#resultModal').modal('show');
}

/**
 * View result details
 */
function viewResult(id) {
    $.ajax({
        url: 'api/results_api.php',
        method: 'GET',
        data: { 
            action: 'get',
            id: id 
        },
        dataType: 'json',
        beforeSend: function() {
            $('#viewResultContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
        },
        success: function(response) {
            if (response.success && response.data) {
                displayResultDetails(response.data);
                $('#viewResultModal').modal('show');
            } else {
                showError(response.message || 'Failed to load result details');
            }
        },
        error: function() {
            showError('Failed to load result details');
        }
    });
}

/**
 * Display result details in view modal
 */
function displayResultDetails(result) {
    const criticalBadge = result.is_critical ? '<span class="badge badge-danger ml-2">Critical</span>' : '';
    const statusBadge = renderStatusBadge(result.status);
    
    const html = `
        <div class="row">
            <div class="col-md-6">
                <h6><strong>Patient Information</strong></h6>
                <p><strong>Name:</strong> ${result.patient_name || 'N/A'}</p>
                <p><strong>Order ID:</strong> #${result.order_id || 'N/A'}</p>
                <p><strong>Test Type:</strong> ${result.test_type || 'N/A'}</p>
            </div>
            <div class="col-md-6">
                <h6><strong>Result Information</strong></h6>
                <p><strong>Result Value:</strong> ${result.result_value || 'N/A'} ${result.unit || ''}</p>
                <p><strong>Reference Range:</strong> ${result.reference_range || 'N/A'}</p>
                <p><strong>Status:</strong> ${statusBadge} ${criticalBadge}</p>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <h6><strong>Additional Details</strong></h6>
                <p><strong>Priority:</strong> <span class="priority-${result.priority}">${result.priority || 'normal'}</span></p>
                <p><strong>Date Created:</strong> ${result.created_at ? moment(result.created_at).format('MMMM DD, YYYY HH:mm') : 'N/A'}</p>
                ${result.comments ? `<p><strong>Comments:</strong><br>${result.comments}</p>` : ''}
            </div>
        </div>
    `;
    
    $('#viewResultContent').html(html);
}

/**
 * Edit result
 */
function editResult(id) {
    $.ajax({
        url: 'api/results_api.php',
        method: 'GET',
        data: { 
            action: 'get',
            id: id 
        },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                populateForm(response.data);
                $('#resultModalTitle').text('Edit Test Result');
                $('#resultModal').modal('show');
            } else {
                showError(response.message || 'Failed to load result data');
            }
        },
        error: function() {
            showError('Failed to load result data');
        }
    });
}

/**
 * Populate form with result data
 */
function populateForm(data) {
    $('#resultId').val(data.id);
    $('#patientId').val(data.patient_id);
    $('#orderId').val(data.order_id);
    $('#testType').val(data.test_type);
    $('#resultValue').val(data.result_value);
    $('#referenceRange').val(data.reference_range);
    $('#unit').val(data.unit);
    $('#status').val(data.status);
    $('#priority').val(data.priority);
    $('#comments').val(data.comments);
    $('#isCritical').prop('checked', data.is_critical == 1);
    
    // Load test orders for the selected patient
    if (data.patient_id) {
        loadTestOrders(data.patient_id);
    }
}

/**
 * Save result (add or edit)
 */
function saveResult() {
    const formData = new FormData($('#resultForm')[0]);
    const isEdit = $('#resultId').val() !== '';
    
    $.ajax({
        url: 'api/results_api.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        beforeSend: function() {
            $('#resultForm button[type="submit"]').prop('disabled', true).addClass('loading');
        },
        success: function(response) {
            if (response.success) {
                showSuccess(response.message || (isEdit ? 'Result updated successfully' : 'Result added successfully'));
                $('#resultModal').modal('hide');
                refreshTable();
            } else {
                showError(response.message || 'Failed to save result');
            }
        },
        error: function() {
            showError('Failed to save result');
        },
        complete: function() {
            $('#resultForm button[type="submit"]').prop('disabled', false).removeClass('loading');
        }
    });
}

/**
 * Delete result
 */
function deleteResult(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'api/results_api.php',
                method: 'DELETE',
                data: { id: id },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showSuccess('Result deleted successfully');
                        refreshTable();
                    } else {
                        showError(response.message || 'Failed to delete result');
                    }
                },
                error: function() {
                    showError('Failed to delete result');
                }
            });
        }
    });
}

/**
 * Download result report
 */
function downloadResult(id) {
    showInfo('Generating report...');
    
    // Create a temporary form to download the file
    const form = $('<form>', {
        method: 'POST',
        action: 'api/results_api.php',
        target: '_blank'
    });
    
    form.append($('<input>', {
        type: 'hidden',
        name: 'action',
        value: 'download'
    }));
    
    form.append($('<input>', {
        type: 'hidden',
        name: 'id',
        value: id
    }));
    
    $('body').append(form);
    form.submit();
    form.remove();
}

/**
 * Export results
 */
function exportResults() {
    const filters = {
        status: $('#statusFilter').val(),
        priority: $('#priorityFilter').val(),
        search: $('#searchInput').val()
    };
    
    Swal.fire({
        title: 'Export Results',
        text: 'Choose export format:',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Excel',
        cancelButtonText: 'PDF',
        showDenyButton: true,
        denyButtonText: 'CSV'
    }).then((result) => {
        let format = '';
        if (result.isConfirmed) format = 'excel';
        else if (result.isDenied) format = 'csv';
        else if (result.dismiss === Swal.DismissReason.cancel) return;
        else format = 'pdf';
        
        if (format) {
            exportResultsData(format, filters);
        }
    });
}

/**
 * Export results data
 */
function exportResultsData(format, filters) {
    const form = $('<form>', {
        method: 'POST',
        action: 'api/results_api.php',
        target: '_blank'
    });
    
    form.append($('<input>', {
        type: 'hidden',
        name: 'action',
        value: 'export'
    }));
    
    form.append($('<input>', {
        type: 'hidden',
        name: 'format',
        value: format
    }));
    
    Object.keys(filters).forEach(key => {
        if (filters[key]) {
            form.append($('<input>', {
                type: 'hidden',
                name: key,
                value: filters[key]
            }));
        }
    });
    
    $('body').append(form);
    form.submit();
    form.remove();
    
    showInfo(`Exporting results as ${format.toUpperCase()}...`);
}

/**
 * Search results
 */
function searchResults() {
    refreshTable();
}

/**
 * Refresh table data
 */
function refreshTable() {
    if ($.fn.DataTable.isDataTable('#resultsTable')) {
        $('#resultsTable').DataTable().ajax.reload();
    }
}

/**
 * Reset form
 */
function resetForm() {
    $('#resultForm')[0].reset();
    $('#resultId').val('');
    $('#resultForm .is-invalid').removeClass('is-invalid');
    $('#orderId').html('<option value="">Select Order</option>');
}

/**
 * Setup form validation
 */
function setupFormValidation() {
    $('#resultForm').validate({
        rules: {
            patient_id: 'required',
            order_id: 'required',
            test_type: 'required',
            result_value: 'required'
        },
        messages: {
            patient_id: 'Please select a patient',
            order_id: 'Please select a test order',
            test_type: 'Please enter the test type',
            result_value: 'Please enter the result value'
        },
        errorClass: 'is-invalid',
        validClass: 'is-valid',
        errorElement: 'div',
        errorPlacement: function(error, element) {
            error.addClass('invalid-feedback');
            element.closest('.form-group').append(error);
        },
        highlight: function(element) {
            $(element).addClass('is-invalid').removeClass('is-valid');
        },
        unhighlight: function(element) {
            $(element).addClass('is-valid').removeClass('is-invalid');
        }
    });
}

/**
 * Utility functions
 */

// Debounce function for search
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Show success message
function showSuccess(message) {
    toastr.success(message);
}

// Show error message
function showError(message) {
    toastr.error(message);
}

// Show info message
function showInfo(message) {
    toastr.info(message);
}

// Show warning message
function showWarning(message) {
    toastr.warning(message);
}

// Global functions for external access
window.showAddResultModal = showAddResultModal;
window.exportResults = exportResults;
window.searchResults = searchResults;