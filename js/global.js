/**
 * Global JavaScript functions for USFitness Lab
 * Handles common AJAX operations, modals, and utilities
 */

// Global variables
let currentDataTable = null;

/**
 * Initialize when document is ready
 */
$(document).ready(function() {
    // Set up global AJAX settings
    $.ajaxSetup({
        error: function(xhr, status, error) {
            let errorMessage = 'An error occurred';
            
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            } else if (xhr.statusText) {
                errorMessage = xhr.statusText;
            }
            
            showToast('error', errorMessage);
        }
    });
    
    // Set up toastr defaults
    if (typeof toastr !== 'undefined') {
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
    }
});

/**
 * Show toast notification
 */
function showToast(type, message, title = '') {
    if (typeof toastr !== 'undefined') {
        switch (type) {
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
    } else {
        // Fallback to alert if toastr is not available
        alert((title ? title + ': ' : '') + message);
    }
}

/**
 * Initialize DataTable with common settings
 */
function initializeDataTables(selector, ajaxUrl, columns, options = {}) {
    const defaultOptions = {
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": ajaxUrl,
            "type": "POST"
        },
        "columns": columns,
        "pageLength": 25,
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        "order": [[0, "desc"]],
        "responsive": true,
        "autoWidth": false,
        "language": {
            "processing": '<div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>',
            "emptyTable": "No data available",
            "zeroRecords": "No matching records found"
        }
    };
    
    const finalOptions = $.extend(true, {}, defaultOptions, options);
    
    if (currentDataTable) {
        currentDataTable.destroy();
    }
    
    currentDataTable = $(selector).DataTable(finalOptions);
    return currentDataTable;
}

/**
 * Submit form via AJAX
 */
function submitForm(formId, successCallback, options = {}) {
    const form = $(formId);
    const submitBtn = form.find('button[type="submit"]');
    const originalBtnText = submitBtn.html();
    
    // Disable submit button and show loading
    submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
    
    $.ajax({
        url: form.attr('action') || options.url,
        type: form.attr('method') || 'POST',
        data: new FormData(form[0]),
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                showToast('success', response.message || 'Operation completed successfully');
                
                if (typeof successCallback === 'function') {
                    successCallback(response);
                }
                
                // Reset form if specified
                if (options.resetForm !== false) {
                    form[0].reset();
                }
                
                // Close modal if specified
                if (options.closeModal !== false) {
                    form.closest('.modal').modal('hide');
                }
                
                // Refresh DataTable if exists
                if (currentDataTable && options.refreshTable !== false) {
                    currentDataTable.ajax.reload(null, false);
                }
                
            } else {
                showToast('error', response.message || 'An error occurred');
            }
        },
        error: function(xhr, status, error) {
            let errorMessage = 'An error occurred while processing the request';
            
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            
            showToast('error', errorMessage);
        },
        complete: function() {
            // Re-enable submit button
            submitBtn.prop('disabled', false).html(originalBtnText);
        }
    });
}

/**
 * Load content into modal
 */
function loadModal(url, modalId = '#globalModal', data = {}) {
    const modal = $(modalId);
    
    // Show loading in modal
    modal.find('.modal-content').html(`
        <div class="modal-header">
            <h4 class="modal-title">Loading...</h4>
        </div>
        <div class="modal-body text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
    `);
    
    modal.modal('show');
    
    $.post(url, data)
        .done(function(response) {
            if (typeof response === 'string') {
                modal.find('.modal-content').html(response);
            } else if (response.success && response.html) {
                modal.find('.modal-content').html(response.html);
            } else {
                showToast('error', response.message || 'Failed to load content');
                modal.modal('hide');
            }
        })
        .fail(function() {
            showToast('error', 'Failed to load modal content');
            modal.modal('hide');
        });
}

/**
 * Confirm and delete record
 */
function confirmDelete(url, message = 'Are you sure you want to delete this record?', callback = null) {
    if (confirm(message)) {
        $.post(url, { _method: 'DELETE' })
            .done(function(response) {
                if (response.success) {
                    showToast('success', response.message || 'Record deleted successfully');
                    
                    if (typeof callback === 'function') {
                        callback(response);
                    }
                    
                    // Refresh DataTable if exists
                    if (currentDataTable) {
                        currentDataTable.ajax.reload(null, false);
                    }
                } else {
                    showToast('error', response.message || 'Failed to delete record');
                }
            })
            .fail(function() {
                showToast('error', 'Failed to delete record');
            });
    }
}

/**
 * Format date for display
 */
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Capitalize first letter
 */
function capitalize(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}

/**
 * Show loading overlay
 */
function showLoading(target = 'body') {
    const loadingHtml = `
        <div class="loading-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; 
             background: rgba(255,255,255,0.8); display: flex; align-items: center; justify-content: center; z-index: 9999;">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
    `;
    
    $(target).css('position', 'relative').append(loadingHtml);
}

/**
 * Hide loading overlay
 */
function hideLoading(target = 'body') {
    $(target).find('.loading-overlay').remove();
}

/**
 * Validate form fields
 */
function validateForm(formId) {
    const form = $(formId);
    let isValid = true;
    
    // Clear previous validation states
    form.find('.is-invalid').removeClass('is-invalid');
    form.find('.invalid-feedback').remove();
    
    // Check required fields
    form.find('[required]').each(function() {
        const field = $(this);
        if (!field.val().trim()) {
            field.addClass('is-invalid');
            field.after('<div class="invalid-feedback">This field is required.</div>');
            isValid = false;
        }
    });
    
    // Check email fields
    form.find('input[type="email"]').each(function() {
        const field = $(this);
        const email = field.val().trim();
        if (email && !isValidEmail(email)) {
            field.addClass('is-invalid');
            field.after('<div class="invalid-feedback">Please enter a valid email address.</div>');
            isValid = false;
        }
    });
    
    return isValid;
}

/**
 * Check if email is valid
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Get URL parameters
 */
function getUrlParameter(name) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(name);
}

/**
 * Refresh current page
 */
function refreshPage() {
    location.reload();
}

/**
 * Scroll to element
 */
function scrollToElement(selector) {
    const element = $(selector);
    if (element.length) {
        $('html, body').animate({
            scrollTop: element.offset().top - 100
        }, 500);
    }
}
