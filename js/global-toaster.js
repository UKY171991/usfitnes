/**
 * Global Toaster Notification System
 * Provides consistent notifications across all pages
 */

// Configure Toastr globally
$(document).ready(function() {
    // Configure toastr options
    toastr.options = {
        closeButton: true,
        debug: false,
        newestOnTop: true,
        progressBar: true,
        positionClass: "toast-top-right",
        preventDuplicates: true,
        onclick: null,
        showDuration: "300",
        hideDuration: "1000",
        timeOut: "5000",
        extendedTimeOut: "1000",
        showEasing: "swing",
        hideEasing: "linear",
        showMethod: "fadeIn",
        hideMethod: "fadeOut",
        tapToDismiss: true,
        escapeHtml: true
    };
    
    console.log('Global toaster system initialized');
});

/**
 * Global Toaster Functions
 */
window.PathLabToaster = {
    
    /**
     * Show success message
     */
    success: function(message, title = '', options = {}) {
        const defaultOptions = {
            timeOut: 4000,
            iconClass: 'toast-success',
            positionClass: 'toast-top-right'
        };
        
        const finalOptions = Object.assign(defaultOptions, options);
        
        return toastr.success(message, title, finalOptions);
    },
    
    /**
     * Show error message
     */
    error: function(message, title = '', options = {}) {
        const defaultOptions = {
            timeOut: 8000,
            iconClass: 'toast-error',
            positionClass: 'toast-top-right'
        };
        
        const finalOptions = Object.assign(defaultOptions, options);
        
        return toastr.error(message, title, finalOptions);
    },
    
    /**
     * Show warning message
     */
    warning: function(message, title = '', options = {}) {
        const defaultOptions = {
            timeOut: 6000,
            iconClass: 'toast-warning',
            positionClass: 'toast-top-right'
        };
        
        const finalOptions = Object.assign(defaultOptions, options);
        
        return toastr.warning(message, title, finalOptions);
    },
    
    /**
     * Show info message
     */
    info: function(message, title = '', options = {}) {
        const defaultOptions = {
            timeOut: 5000,
            iconClass: 'toast-info',
            positionClass: 'toast-top-right'
        };
        
        const finalOptions = Object.assign(defaultOptions, options);
        
        return toastr.info(message, title, finalOptions);
    },
    
    /**
     * Show persistent message (doesn't auto-hide)
     */
    persistent: function(message, title = '', type = 'info') {
        const options = {
            timeOut: 0,
            extendedTimeOut: 0,
            tapToDismiss: false,
            closeButton: true
        };
        
        switch(type.toLowerCase()) {
            case 'success':
                return this.success(message, title, options);
            case 'error':
                return this.error(message, title, options);
            case 'warning':
                return this.warning(message, title, options);
            default:
                return this.info(message, title, options);
        }
    },
    
    /**
     * Show loading message
     */
    loading: function(message = 'Processing...', title = '') {
        const options = {
            timeOut: 0,
            extendedTimeOut: 0,
            tapToDismiss: false,
            closeButton: false,
            iconClass: 'toast-info',
            onShown: function() {
                $(this).find('.toast-message').prepend('<i class="fas fa-spinner fa-spin mr-2"></i>');
            }
        };
        
        return this.info(message, title, options);
    },
    
    /**
     * Clear all toasts
     */
    clear: function() {
        toastr.clear();
    },
    
    /**
     * Remove specific toast
     */
    remove: function(toast) {
        toastr.remove(toast);
    },
    
    /**
     * Show AJAX error
     */
    ajaxError: function(xhr, textStatus, errorThrown) {
        let message = 'An error occurred';
        let title = 'Request Failed';
        
        if (xhr.status) {
            title += ` (${xhr.status})`;
        }
        
        if (xhr.responseJSON && xhr.responseJSON.message) {
            message = xhr.responseJSON.message;
        } else if (xhr.responseText) {
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.message) {
                    message = response.message;
                }
            } catch (e) {
                message = xhr.responseText;
            }
        } else if (errorThrown) {
            message = errorThrown;
        } else if (textStatus) {
            message = textStatus;
        }
        
        this.error(message, title);
    },
    
    /**
     * Show form validation errors
     */
    validationErrors: function(errors, title = 'Validation Errors') {
        if (typeof errors === 'object') {
            const errorList = Object.values(errors).join('<br>');
            this.error(errorList, title, { escapeHtml: false });
        } else {
            this.error(errors, title);
        }
    },
    
    /**
     * Show operation result
     */
    operationResult: function(response, successMessage = 'Operation completed successfully') {
        if (response && response.success) {
            this.success(response.message || successMessage);
        } else {
            this.error(response.message || 'Operation failed');
        }
    },
    
    /**
     * Show confirmation with callback
     */
    confirm: function(message, title = 'Confirm Action', onConfirm = null, onCancel = null) {
        const toast = this.warning(
            `${message}<br><br>
            <button type="button" class="btn btn-sm btn-success mr-2" onclick="PathLabToaster.handleConfirm(this, true)">
                <i class="fas fa-check mr-1"></i>Confirm
            </button>
            <button type="button" class="btn btn-sm btn-secondary" onclick="PathLabToaster.handleConfirm(this, false)">
                <i class="fas fa-times mr-1"></i>Cancel
            </button>`,
            title,
            {
                timeOut: 0,
                extendedTimeOut: 0,
                tapToDismiss: false,
                closeButton: false,
                escapeHtml: false
            }
        );
        
        // Store callbacks on the toast element
        if (toast && toast.length) {
            toast.data('onConfirm', onConfirm);
            toast.data('onCancel', onCancel);
        }
        
        return toast;
    },
    
    /**
     * Handle confirmation response
     */
    handleConfirm: function(button, confirmed) {
        const toast = $(button).closest('.toast');
        const onConfirm = toast.data('onConfirm');
        const onCancel = toast.data('onCancel');
        
        this.remove(toast);
        
        if (confirmed && typeof onConfirm === 'function') {
            onConfirm();
        } else if (!confirmed && typeof onCancel === 'function') {
            onCancel();
        }
    },
    
    /**
     * Show progress toast
     */
    progress: function(message, percentage = 0, title = '') {
        const progressHtml = `
            ${message}
            <div class="progress mt-2" style="height: 4px;">
                <div class="progress-bar" role="progressbar" style="width: ${percentage}%" 
                     aria-valuenow="${percentage}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        `;
        
        const toast = this.info(progressHtml, title, {
            timeOut: 0,
            extendedTimeOut: 0,
            tapToDismiss: false,
            closeButton: false,
            escapeHtml: false
        });
        
        // Return methods to update progress
        return {
            update: (newPercentage, newMessage = null) => {
                if (toast && toast.length) {
                    const progressBar = toast.find('.progress-bar');
                    progressBar.css('width', `${newPercentage}%`).attr('aria-valuenow', newPercentage);
                    
                    if (newMessage) {
                        const messageElement = toast.find('.toast-message');
                        messageElement.html(`
                            ${newMessage}
                            <div class="progress mt-2" style="height: 4px;">
                                <div class="progress-bar" role="progressbar" style="width: ${newPercentage}%" 
                                     aria-valuenow="${newPercentage}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        `);
                    }
                }
            },
            complete: (message = 'Completed') => {
                PathLabToaster.remove(toast);
                PathLabToaster.success(message);
            },
            error: (message = 'Failed') => {
                PathLabToaster.remove(toast);
                PathLabToaster.error(message);
            }
        };
    },
    
    /**
     * Show file upload progress
     */
    uploadProgress: function(fileName, percentage = 0) {
        return this.progress(`Uploading ${fileName}...`, percentage, 'File Upload');
    },
    
    /**
     * Show quick action feedback
     */
    quickAction: function(action, success = true) {
        const actions = {
            save: { success: 'Saved successfully', error: 'Save failed' },
            delete: { success: 'Deleted successfully', error: 'Delete failed' },
            update: { success: 'Updated successfully', error: 'Update failed' },
            create: { success: 'Created successfully', error: 'Creation failed' },
            export: { success: 'Export completed', error: 'Export failed' },
            import: { success: 'Import completed', error: 'Import failed' },
            copy: { success: 'Copied to clipboard', error: 'Copy failed' },
            send: { success: 'Sent successfully', error: 'Send failed' }
        };
        
        const message = actions[action] ? 
            (success ? actions[action].success : actions[action].error) :
            (success ? 'Action completed' : 'Action failed');
        
        if (success) {
            this.success(message);
        } else {
            this.error(message);
        }
    }
};

/**
 * Global shorthand functions for backward compatibility
 */
window.showSuccess = function(message, title, options) {
    return PathLabToaster.success(message, title, options);
};

window.showError = function(message, title, options) {
    return PathLabToaster.error(message, title, options);
};

window.showWarning = function(message, title, options) {
    return PathLabToaster.warning(message, title, options);
};

window.showInfo = function(message, title, options) {
    return PathLabToaster.info(message, title, options);
};

/**
 * Auto-initialize for common AJAX events
 */
$(document).ready(function() {
    // Global AJAX error handler
    $(document).ajaxError(function(event, xhr, settings, thrownError) {
        // Skip if the error is handled locally
        if (!settings.skipGlobalError) {
            PathLabToaster.ajaxError(xhr, settings, thrownError);
        }
    });
    
    // Global form submission success handler
    $(document).on('submit', '[data-toast-success]', function() {
        const form = $(this);
        const message = form.data('toast-success');
        
        form.one('success.form', function() {
            PathLabToaster.success(message);
        });
    });
    
    // Auto-show toasts from URL parameters (for redirects)
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('toast')) {
        const type = urlParams.get('toast_type') || 'info';
        const message = urlParams.get('toast');
        const title = urlParams.get('toast_title') || '';
        
        PathLabToaster[type](decodeURIComponent(message), decodeURIComponent(title));
        
        // Clean URL
        const newUrl = window.location.pathname;
        window.history.replaceState({}, document.title, newUrl);
    }
});

/**
 * Export for module systems
 */
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PathLabToaster;
}