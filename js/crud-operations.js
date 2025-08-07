/**
 * PathLab Pro - CRUD Operations JavaScript
 * Handles all AJAX CRUD operations for the laboratory management system
 */

// Global AJAX settings
$.ajaxSetup({
    cache: false,
    beforeSend: function(xhr, settings) {
        // Add loading indicator for all AJAX requests
        if (settings.showLoader !== false) {
            showGlobalLoader();
        }
    },
    complete: function(xhr, status) {
        // Hide loading indicator
        if (xhr.showLoader !== false) {
            hideGlobalLoader();
        }
    },
    error: function(xhr, status, error) {
        hideGlobalLoader();
        console.error('AJAX Error:', error);
        
        if (xhr.status === 401) {
            showToast('error', 'Session expired. Please login again.');
            setTimeout(() => {
                window.location.href = 'login.php';
            }, 2000);
        } else if (xhr.status === 403) {
            showToast('error', 'Access denied. You don\'t have permission for this action.');
        } else if (xhr.status >= 500) {
            showToast('error', 'Server error occurred. Please try again later.');
        } else if (xhr.status === 0) {
            showToast('error', 'Network error. Please check your connection.');
        } else {
            showToast('error', 'An unexpected error occurred.');
        }
    }
});

// Global loader functions
function showGlobalLoader() {
    if ($('#globalLoader').length === 0) {
        $('body').append(`
            <div id="globalLoader" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.3); z-index: 9999; display: flex; align-items: center; justify-content: center;">
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                    <span class="sr-only">Loading...</span>
                </div>
            </div>
        `);
    }
    $('#globalLoader').show();
}

function hideGlobalLoader() {
    $('#globalLoader').fadeOut(300);
}

// Enhanced toast notification function
function showToast(type, message, title = null, options = {}) {
    // Remove any existing toast with same message to prevent duplicates
    $('.toast').each(function() {
        if ($(this).find('.toast-body').text() === message) {
            $(this).remove();
        }
    });

    const toastId = 'toast-' + Date.now();
    const iconClass = type === 'success' ? 'fas fa-check-circle text-success' : 
                     type === 'error' ? 'fas fa-exclamation-circle text-danger' :
                     type === 'warning' ? 'fas fa-exclamation-triangle text-warning' :
                     'fas fa-info-circle text-info';
    
    const toastTitle = title || (type === 'success' ? 'Success' : 
                                type === 'error' ? 'Error' : 
                                type === 'warning' ? 'Warning' : 'Info');
    
    const toast = $(`
        <div id="${toastId}" class="toast" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 10000; min-width: 300px;">
            <div class="toast-header">
                <i class="${iconClass} mr-2"></i>
                <strong class="mr-auto">${toastTitle}</strong>
                <small class="text-muted">now</small>
                <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="toast-body">${message}</div>
        </div>
    `);
    
    $('body').append(toast);
    
    const delay = options.delay || (type === 'error' ? 5000 : 3000);
    toast.toast({ delay: delay });
    toast.toast('show');
    
    // Stack multiple toasts
    const existingToasts = $('.toast:visible').length;
    if (existingToasts > 1) {
        toast.css('top', (20 + (existingToasts - 1) * 80) + 'px');
    }
    
    toast.on('hidden.bs.toast', function() {
        $(this).remove();
        // Reposition remaining toasts
        $('.toast:visible').each(function(index) {
            $(this).css('top', (20 + index * 80) + 'px');
        });
    });
    
    return toast;
}

// Enhanced AJAX form handler
function handleAjaxForm(formSelector, options = {}) {
    $(document).on('submit', formSelector, function(e) {
        e.preventDefault();
        
        const form = $(this);
        const formData = new FormData(this);
        
        // Validate form
        if (!this.checkValidity()) {
            e.stopPropagation();
            form.addClass('was-validated');
            showToast('warning', 'Please fill in all required fields correctly.');
            return;
        }
        
        // Get form configuration
        const config = {
            url: form.attr('action') || options.url,
            method: form.attr('method') || options.method || 'POST',
            successMessage: options.successMessage || 'Operation completed successfully',
            errorMessage: options.errorMessage || 'An error occurred',
            redirectUrl: options.redirectUrl,
            refreshTable: options.refreshTable || false,
            closeModal: options.closeModal || false,
            resetForm: options.resetForm || false,
            ...options
        };
        
        // Disable submit button
        const submitBtn = form.find('button[type="submit"]');
        const originalBtnText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Saving...');
        
        $.ajax({
            url: config.url,
            type: config.method,
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showToast('success', response.message || config.successMessage);
                    
                    // Handle post-success actions
                    if (config.closeModal) {
                        const modalId = form.closest('.modal').attr('id');
                        if (modalId) {
                            $('#' + modalId).modal('hide');
                        }
                    }
                    
                    if (config.resetForm) {
                        form[0].reset();
                        form.removeClass('was-validated');
                    }
                    
                    if (config.refreshTable) {
                        if (typeof window[config.refreshTable] === 'function') {
                            window[config.refreshTable]();
                        } else if (typeof refreshTable === 'function') {
                            refreshTable();
                        }
                    }
                    
                    if (config.redirectUrl) {
                        setTimeout(() => {
                            window.location.href = config.redirectUrl;
                        }, 1500);
                    }
                    
                    // Trigger custom callback
                    if (typeof config.onSuccess === 'function') {
                        config.onSuccess(response, form);
                    }
                } else {
                    showToast('error', response.message || config.errorMessage);
                    
                    if (typeof config.onError === 'function') {
                        config.onError(response, form);
                    }
                }
            },
            error: function(xhr, status, error) {
                let errorMessage = config.errorMessage;
                
                try {
                    const response = JSON.parse(xhr.responseText);
                    errorMessage = response.message || errorMessage;
                } catch (e) {
                    // Use default error message
                }
                
                showToast('error', errorMessage);
                
                if (typeof config.onError === 'function') {
                    config.onError({ success: false, message: errorMessage }, form);
                }
            },
            complete: function() {
                // Re-enable submit button
                submitBtn.prop('disabled', false).html(originalBtnText);
            }
        });
    });
}

// Enhanced AJAX delete handler
function handleAjaxDelete(selector, options = {}) {
    $(document).on('click', selector, function(e) {
        e.preventDefault();
        
        const element = $(this);
        const config = {
            url: element.data('url') || options.url,
            confirmTitle: options.confirmTitle || 'Are you sure?',
            confirmText: options.confirmText || 'This action cannot be undone.',
            confirmButtonText: options.confirmButtonText || 'Yes, delete it!',
            successMessage: options.successMessage || 'Record deleted successfully',
            errorMessage: options.errorMessage || 'Failed to delete record',
            refreshTable: options.refreshTable || false,
            ...options
        };
        
        // Show confirmation dialog
        Swal.fire({
            title: config.confirmTitle,
            text: config.confirmText,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: config.confirmButtonText,
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                const deleteData = element.data();
                
                $.ajax({
                    url: config.url,
                    type: 'POST',
                    data: {
                        action: 'delete',
                        id: deleteData.id,
                        ...config.data
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message || config.successMessage);
                            
                            if (config.refreshTable) {
                                if (typeof window[config.refreshTable] === 'function') {
                                    window[config.refreshTable]();
                                } else if (typeof refreshTable === 'function') {
                                    refreshTable();
                                }
                            }
                            
                            if (typeof config.onSuccess === 'function') {
                                config.onSuccess(response, element);
                            }
                        } else {
                            showToast('error', response.message || config.errorMessage);
                        }
                    },
                    error: function(xhr, status, error) {
                        showToast('error', config.errorMessage);
                        
                        if (typeof config.onError === 'function') {
                            config.onError({ success: false, message: config.errorMessage }, element);
                        }
                    }
                });
            }
        });
    });
}

// DataTable initialization helper
function initializeDataTable(tableSelector, options = {}) {
    const defaultOptions = {
        processing: true,
        serverSide: true,
        responsive: true,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        language: {
            processing: '<div class="d-flex align-items-center"><div class="spinner-border spinner-border-sm text-primary mr-2" role="status"></div>Loading...</div>',
            search: 'Search:',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ entries',
            infoEmpty: 'No entries available',
            infoFiltered: '(filtered from _MAX_ total entries)',
            paginate: {
                first: 'First',
                last: 'Last',
                next: 'Next',
                previous: 'Previous'
            },
            emptyTable: 'No data available in table'
        },
        order: [[0, 'desc']],
        ajax: {
            error: function(xhr, error, thrown) {
                console.log('DataTables AJAX Error:', error);
                showToast('error', 'Failed to load data. Please check your database connection.');
            }
        }
    };
    
    const config = $.extend(true, {}, defaultOptions, options);
    
    return $(tableSelector).DataTable(config);
}

// Modal helper functions
function openModal(modalId, title = null) {
    const modal = $('#' + modalId);
    if (modal.length) {
        if (title) {
            modal.find('.modal-title span, .modal-title #modalTitle').text(title);
        }
        modal.modal('show');
    }
}

function closeModal(modalId) {
    $('#' + modalId).modal('hide');
}

function resetModalForm(modalId) {
    const modal = $('#' + modalId);
    const form = modal.find('form');
    if (form.length) {
        form[0].reset();
        form.removeClass('was-validated');
        // Clear any hidden ID fields
        form.find('input[type="hidden"]').val('');
    }
}

// Form validation helper
function validateForm(formSelector) {
    const form = $(formSelector)[0];
    if (form.checkValidity()) {
        $(form).removeClass('was-validated');
        return true;
    } else {
        $(form).addClass('was-validated');
        return false;
    }
}

// Load dropdown options via AJAX
function loadDropdownOptions(selectSelector, apiUrl, options = {}) {
    const config = {
        valueField: options.valueField || 'id',
        textField: options.textField || 'name',
        placeholder: options.placeholder || 'Select an option',
        ...options
    };
    
    $.ajax({
        url: apiUrl,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                const select = $(selectSelector);
                select.empty().append(`<option value="">${config.placeholder}</option>`);
                
                response.data.forEach(function(item) {
                    const value = item[config.valueField];
                    const text = item[config.textField];
                    select.append(`<option value="${value}">${text}</option>`);
                });
                
                // Refresh Select2 if it's initialized
                if (select.hasClass('select2-hidden-accessible')) {
                    select.trigger('change');
                }
            }
        },
        error: function() {
            console.log('Failed to load dropdown options for', selectSelector);
        }
    });
}

// Auto-refresh functionality
function setupAutoRefresh(refreshFunction, intervalMinutes = 5) {
    if (typeof refreshFunction === 'function') {
        setInterval(refreshFunction, intervalMinutes * 60 * 1000);
    }
}

// Initialize common functionality when document is ready
$(document).ready(function() {
    // Initialize tooltips and popovers
    $('[data-toggle="tooltip"]').tooltip();
    $('[data-toggle="popover"]').popover();
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert:not(.alert-permanent)').fadeOut();
    }, 5000);
    
    // Make tables responsive
    $('.table-responsive table').addClass('table-sm');
    
    // Add loading states to buttons with data-loading attribute
    $(document).on('click', '[data-loading]', function() {
        const btn = $(this);
        const loadingText = btn.data('loading') || 'Loading...';
        const originalText = btn.html();
        
        btn.data('original-text', originalText);
        btn.html('<i class="fas fa-spinner fa-spin mr-1"></i>' + loadingText);
        btn.prop('disabled', true);
        
        // Auto-restore after 10 seconds as fallback
        setTimeout(() => {
            if (btn.data('original-text')) {
                btn.html(btn.data('original-text'));
                btn.prop('disabled', false);
                btn.removeData('original-text');
            }
        }, 10000);
    });
    
    // Global form validation styling
    $(document).on('input change', '.form-control', function() {
        if ($(this).closest('form').hasClass('was-validated')) {
            // Re-validate individual field
            if (this.checkValidity()) {
                $(this).removeClass('is-invalid').addClass('is-valid');
            } else {
                $(this).removeClass('is-valid').addClass('is-invalid');
            }
        }
    });
});
