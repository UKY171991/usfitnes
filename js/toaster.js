// Enhanced Toastr configuration and functions for AdminLTE3
toastr.options = {
    "closeButton": true,
    "debug": false,
    "newestOnTop": true,
    "progressBar": true,
    "positionClass": "toast-top-right",
    "preventDuplicates": true,
    "onclick": null,
    "showDuration": "300",
    "hideDuration": "1000",
    "timeOut": "5000",
    "extendedTimeOut": "1000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut",
    "tapToDismiss": true,
    "escapeHtml": false
};

// Show toast notification with enhanced features
function showToast(type, message, title = '', options = {}) {
    // Clear previous toasts if specified
    if (options.clearPrevious) {
        toastr.clear();
    }
    
    // Merge custom options
    const customOptions = Object.assign({}, toastr.options, options);
    
    // Set custom timeout based on type
    if (!options.timeOut) {
        switch(type) {
            case 'success':
                customOptions.timeOut = 3000;
                break;
            case 'error':
                customOptions.timeOut = 8000;
                break;
            case 'warning':
                customOptions.timeOut = 6000;
                break;
            case 'info':
                customOptions.timeOut = 4000;
                break;
        }
    }
    
    // Apply custom options temporarily
    const originalOptions = toastr.options;
    toastr.options = customOptions;
    
    // Show toast based on type
    let toast;
    switch(type) {
        case 'success':
            toast = toastr.success(message, title || 'Success');
            break;
        case 'error':
            toast = toastr.error(message, title || 'Error');
            break;
        case 'warning':
            toast = toastr.warning(message, title || 'Warning');
            break;
        case 'info':
            toast = toastr.info(message, title || 'Information');
            break;
        default:
            toast = toastr.info(message, title || 'Notification');
    }
    
    // Restore original options
    toastr.options = originalOptions;
    
    return toast;
}

// Enhanced toast functions with specific styling
function showSuccess(message, title = 'Success', options = {}) {
    return showToast('success', message, title, Object.assign({
        iconClass: 'toast-success',
        timeOut: 3000
    }, options));
}

function showError(message, title = 'Error', options = {}) {
    return showToast('error', message, title, Object.assign({
        iconClass: 'toast-error',
        timeOut: 8000,
        closeButton: true
    }, options));
}

function showWarning(message, title = 'Warning', options = {}) {
    return showToast('warning', message, title, Object.assign({
        iconClass: 'toast-warning',
        timeOut: 6000
    }, options));
}

function showInfo(message, title = 'Information', options = {}) {
    return showToast('info', message, title, Object.assign({
        iconClass: 'toast-info',
        timeOut: 4000
    }, options));
}

// Persistent toast (doesn't auto-hide)
function showPersistentToast(type, message, title = '') {
    return showToast(type, message, title, {
        timeOut: 0,
        extendedTimeOut: 0,
        closeButton: true,
        tapToDismiss: false
    });
}

// Quick action toasts
function showSaveSuccess(entityName = 'Record') {
    return showSuccess(`${entityName} saved successfully!`, 'Saved');
}

function showDeleteSuccess(entityName = 'Record') {
    return showSuccess(`${entityName} deleted successfully!`, 'Deleted');
}

function showUpdateSuccess(entityName = 'Record') {
    return showSuccess(`${entityName} updated successfully!`, 'Updated');
}

function showValidationError(message = 'Please check the form for errors') {
    return showError(message, 'Validation Error');
}

function showNetworkError(message = 'Network error occurred. Please try again.') {
    return showError(message, 'Network Error');
}

function showPermissionError(message = 'You do not have permission to perform this action') {
    return showError(message, 'Permission Denied');
}

// Loading toast with custom styling
function showLoadingToast(message = 'Processing...', title = 'Please Wait') {
    return showToast('info', `<i class="fas fa-spinner fa-spin mr-2"></i>${message}`, title, {
        timeOut: 0,
        extendedTimeOut: 0,
        closeButton: false,
        tapToDismiss: false,
        escapeHtml: false
    });
}

// Clear all toasts
function clearToasts() {
    toastr.clear();
}

// Clear specific toast
function clearToast(toast) {
    if (toast) {
        toastr.clear(toast);
    }
}

// Toast with custom action button
function showActionToast(type, message, title, actionText, actionCallback) {
    const toast = showToast(type, `
        ${message}
        <br><br>
        <button type="button" class="btn btn-sm btn-light toast-action-btn" onclick="(${actionCallback})(); toastr.clear(this.closest('.toast'));">
            ${actionText}
        </button>
    `, title, {
        timeOut: 0,
        extendedTimeOut: 0,
        closeButton: true,
        tapToDismiss: false,
        escapeHtml: false
    });
    
    return toast;
}

// Confirmation toast
function showConfirmToast(message, title, confirmCallback, cancelCallback = null) {
    const toast = showToast('warning', `
        ${message}
        <br><br>
        <button type="button" class="btn btn-sm btn-success mr-2" onclick="(${confirmCallback})(); toastr.clear(this.closest('.toast'));">
            <i class="fas fa-check mr-1"></i>Confirm
        </button>
        <button type="button" class="btn btn-sm btn-secondary" onclick="${cancelCallback ? `(${cancelCallback})();` : ''} toastr.clear(this.closest('.toast'));">
            <i class="fas fa-times mr-1"></i>Cancel
        </button>
    `, title, {
        timeOut: 0,
        extendedTimeOut: 0,
        closeButton: true,
        tapToDismiss: false,
        escapeHtml: false
    });
    
    return toast;
}

// Progress toast
function showProgressToast(message, title = 'Progress') {
    const toast = showToast('info', `
        ${message}
        <div class="progress mt-2" style="height: 6px;">
            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
        </div>
    `, title, {
        timeOut: 0,
        extendedTimeOut: 0,
        closeButton: false,
        tapToDismiss: false,
        escapeHtml: false
    });
    
    return toast;
}

// Update progress toast
function updateProgressToast(toast, percentage, message = null) {
    if (toast && toast.length) {
        const progressBar = toast.find('.progress-bar');
        if (progressBar.length) {
            progressBar.css('width', percentage + '%');
            progressBar.attr('aria-valuenow', percentage);
        }
        
        if (message) {
            const messageElement = toast.find('.toast-message');
            const progressHtml = messageElement.find('.progress').prop('outerHTML');
            messageElement.html(message + progressHtml);
        }
        
        // Auto-close when complete
        if (percentage >= 100) {
            setTimeout(() => {
                clearToast(toast);
            }, 2000);
        }
    }
}

// Batch operation toast
function showBatchOperationToast(operation, total, completed = 0) {
    const percentage = total > 0 ? Math.round((completed / total) * 100) : 0;
    const message = `${operation}: ${completed}/${total} completed`;
    
    return showProgressToast(message, 'Batch Operation');
}

// Custom styled toasts for specific actions
function showFormSubmissionToast() {
    return showLoadingToast('Submitting form...', 'Processing');
}

function showDataLoadingToast() {
    return showLoadingToast('Loading data...', 'Please Wait');
}

function showFileUploadToast() {
    return showLoadingToast('Uploading file...', 'Upload in Progress');
}

// Initialize toast container styling
$(document).ready(function() {
    // Add custom CSS for toast enhancements
    if (!$('#toast-custom-styles').length) {
        $('head').append(`
            <style id="toast-custom-styles">
                .toast-action-btn {
                    margin-top: 8px;
                    border: 1px solid rgba(255,255,255,0.3);
                }
                .toast-action-btn:hover {
                    background-color: rgba(255,255,255,0.2) !important;
                }
                .toast .progress {
                    background-color: rgba(255,255,255,0.2);
                }
                .toast .progress-bar {
                    background-color: rgba(255,255,255,0.8);
                }
                #toast-container > .toast {
                    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                }
                #toast-container > .toast:before {
                    position: absolute;
                    left: 0;
                    top: 0;
                    width: 4px;
                    height: 100%;
                    content: '';
                    background: linear-gradient(to bottom, rgba(255,255,255,0.8), rgba(255,255,255,0.4));
                }
                .global-loader {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.5);
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    z-index: 9999;
                }
                .loader-content {
                    background: white;
                    padding: 30px;
                    border-radius: 10px;
                    text-align: center;
                    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
                }
            </style>
        `);
    }
});