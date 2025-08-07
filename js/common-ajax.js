/**
 * PathLab Pro - Common AJAX Functions
 * AdminLTE3 Compatible AJAX Handlers
 */

// Global AJAX settings
$.ajaxSetup({
    headers: {
        'X-Requested-With': 'XMLHttpRequest'
    },
    beforeSend: function() {
        // Show loading indicator
        if (typeof NProgress !== 'undefined') {
            NProgress.start();
        }
    },
    complete: function() {
        // Hide loading indicator
        if (typeof NProgress !== 'undefined') {
            NProgress.done();
        }
    },
    error: function(xhr, status, error) {
        console.error('AJAX Error:', error);
        showToast('error', 'Network error occurred. Please try again.');
    }
});

// Toast notification function
function showToast(type, message, title = '') {
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    };
    
    switch(type) {
        case 'success':
            toastr.success(message, title);
            break;
        case 'error':
            toastr.error(message, title);
            break;
        case 'warning':
            toastr.warning(message, title);
            break;
        case 'info':
            toastr.info(message, title);
            break;
        default:
            toastr.info(message, title);
    }
}

// Initialize DataTable with common settings
function initDataTable(selector, options = {}) {
    const defaultOptions = {
        processing: true,
        serverSide: true,
        responsive: true,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [[0, 'desc']],
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>',
            emptyTable: '<div class="text-center"><i class="fas fa-inbox fa-3x text-muted mb-3"></i><br>No data available</div>',
            zeroRecords: '<div class="text-center"><i class="fas fa-search fa-3x text-muted mb-3"></i><br>No matching records found</div>'
        },
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        drawCallback: function() {
            // Initialize tooltips after table draw
            $('[data-toggle="tooltip"]').tooltip();
        }
    };
    
    return $(selector).DataTable($.extend(true, defaultOptions, options));
}

// Handle AJAX form submission
function handleAjaxForm(formSelector, apiUrl, successCallback = null, errorCallback = null) {
    $(formSelector).on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const formData = new FormData(this);
        const isEdit = form.find('[name="id"]').val() !== '';
        
        // Disable submit button
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
        
        $.ajax({
            url: apiUrl,
            type: isEdit ? 'PUT' : 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showToast('success', response.message);
                    if (successCallback) {
                        successCallback(response);
                    }
                } else {
                    showToast('error', response.message);
                    if (errorCallback) {
                        errorCallback(response);
                    }
                }
            },
            error: function(xhr, status, error) {
                showToast('error', 'Error saving data. Please try again.');
                if (errorCallback) {
                    errorCallback({success: false, message: error});
                }
            },
            complete: function() {
                // Re-enable submit button
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
}

// Handle AJAX delete with confirmation
function handleAjaxDelete(id, apiUrl, itemName = 'item', successCallback = null) {
    Swal.fire({
        title: 'Are you sure?',
        text: `You won't be able to revert this ${itemName}!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: apiUrl,
                type: 'DELETE',
                data: { id: id },
                success: function(response) {
                    if (response.success) {
                        showToast('success', response.message);
                        if (successCallback) {
                            successCallback(response);
                        }
                    } else {
                        showToast('error', response.message);
                    }
                },
                error: function() {
                    showToast('error', `Error deleting ${itemName}. Please try again.`);
                }
            });
        }
    });
}

// Load data for edit modal
function loadDataForEdit(id, apiUrl, populateCallback) {
    $.ajax({
        url: apiUrl,
        type: 'GET',
        data: { action: 'get', id: id },
        success: function(response) {
            if (response.success) {
                if (populateCallback) {
                    populateCallback(response.data);
                }
            } else {
                showToast('error', response.message);
            }
        },
        error: function() {
            showToast('error', 'Error loading data. Please try again.');
        }
    });
}

// Reset modal form
function resetModalForm(modalId) {
    const modal = $(`#${modalId}`);
    const form = modal.find('form');
    
    // Reset form
    form[0].reset();
    
    // Clear hidden ID field
    form.find('[name="id"]').val('');
    
    // Remove validation classes
    form.find('.is-invalid').removeClass('is-invalid');
    form.find('.invalid-feedback').hide();
    
    // Reset select2 if present
    form.find('.select2').trigger('change');
}

// Populate form with data
function populateForm(formSelector, data) {
    const form = $(formSelector);
    
    Object.keys(data).forEach(key => {
        const field = form.find(`[name="${key}"]`);
        if (field.length) {
            if (field.is('select')) {
                field.val(data[key]).trigger('change');
            } else if (field.is(':checkbox')) {
                field.prop('checked', data[key] == 1 || data[key] === true);
            } else if (field.is(':radio')) {
                field.filter(`[value="${data[key]}"]`).prop('checked', true);
            } else {
                field.val(data[key]);
            }
        }
    });
}

// Format date for display
function formatDate(dateString, format = 'MMM DD, YYYY') {
    if (!dateString) return '';
    return moment(dateString).format(format);
}

// Format currency
function formatCurrency(amount, currency = '$') {
    if (!amount) return currency + '0.00';
    return currency + parseFloat(amount).toFixed(2);
}

// Debounce function for search
function debounce(func, wait, immediate) {
    let timeout;
    return function executedFunction() {
        const context = this;
        const args = arguments;
        const later = function() {
            timeout = null;
            if (!immediate) func.apply(context, args);
        };
        const callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func.apply(context, args);
    };
}

// Initialize common page elements
$(document).ready(function() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Initialize popovers
    $('[data-toggle="popover"]').popover();
    
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });
    
    // Initialize date pickers
    $('.datepicker').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true
    });
    
    // Initialize datetime pickers
    $('.datetimepicker').datetimepicker({
        format: 'YYYY-MM-DD HH:mm',
        icons: {
            time: 'far fa-clock',
            date: 'far fa-calendar',
            up: 'fas fa-arrow-up',
            down: 'fas fa-arrow-down',
            previous: 'fas fa-chevron-left',
            next: 'fas fa-chevron-right',
            today: 'far fa-calendar-check',
            clear: 'far fa-trash-alt',
            close: 'far fa-times-circle'
        }
    });
    
    // Auto-hide alerts after 5 seconds
    $('.alert:not(.alert-permanent)').delay(5000).fadeOut();
    
    // Confirm delete buttons
    $('.btn-delete').on('click', function(e) {
        e.preventDefault();
        const href = $(this).attr('href');
        const itemName = $(this).data('item-name') || 'item';
        
        Swal.fire({
            title: 'Are you sure?',
            text: `You won't be able to revert this ${itemName}!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = href;
            }
        });
    });
});

// Export functions for global use
window.showToast = showToast;
window.initDataTable = initDataTable;
window.handleAjaxForm = handleAjaxForm;
window.handleAjaxDelete = handleAjaxDelete;
window.loadDataForEdit = loadDataForEdit;
window.resetModalForm = resetModalForm;
window.populateForm = populateForm;
window.formatDate = formatDate;
window.formatCurrency = formatCurrency;
window.debounce = debounce;