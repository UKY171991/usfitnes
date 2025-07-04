/**
 * PathLab Pro - Enhanced Common JavaScript Functions
 * AdminLTE3 Template Compatible
 * 
 * This file contains common JavaScript functions used across the application.
 */

// Global variables
let currentUser = null;
let systemSettings = {};

// Initialize when document is ready
$(document).ready(function() {
    // Initialize AdminLTE components
    initializeAdminLTE();
    
    // Initialize common components
    initializeCommonComponents();
    
    // Set up global event handlers
    setupGlobalEventHandlers();
    
    // Load user session info
    loadUserSession();
    
    // Initialize tooltips and popovers
    initializeTooltips();
    
    // Set up AJAX defaults
    setupAjaxDefaults();
});

/**
 * Initialize AdminLTE3 components
 */
function initializeAdminLTE() {
    // Initialize card widgets
    $('[data-card-widget="collapse"]').CardWidget();
    $('[data-card-widget="remove"]').CardWidget();
    $('[data-card-widget="maximize"]').CardWidget();
    
    // Initialize control sidebar
    $('[data-widget="control-sidebar"]').ControlSidebar();
    
    // Initialize pushmenu
    $('[data-widget="pushmenu"]').PushMenu();
    
    // Initialize navbar search
    $('[data-widget="navbar-search"]').NavbarSearch();
    
    // Initialize sidebar search
    $('[data-widget="sidebar-search"]').SidebarSearch();
    
    // Initialize treeview
    $('[data-widget="treeview"]').Treeview();
    
    // Initialize fullscreen
    $('[data-widget="fullscreen"]').Fullscreen();
}

/**
 * Initialize common components
 */
function initializeCommonComponents() {
    // Initialize Select2
    if ($.fn.select2) {
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });
        
        $('.select2bs4').select2({
            theme: 'bootstrap4',
            width: '100%'
        });
    }
    
    // Initialize date pickers
    if ($.fn.datetimepicker) {
        $('.datetimepicker').datetimepicker({
            format: 'YYYY-MM-DD HH:mm',
            icons: {
                time: 'fas fa-clock',
                date: 'fas fa-calendar',
                up: 'fas fa-chevron-up',
                down: 'fas fa-chevron-down',
                previous: 'fas fa-chevron-left',
                next: 'fas fa-chevron-right',
                today: 'fas fa-calendar-check',
                clear: 'fas fa-trash',
                close: 'fas fa-times'
            }
        });
        
        $('.datepicker').datetimepicker({
            format: 'YYYY-MM-DD',
            icons: {
                time: 'fas fa-clock',
                date: 'fas fa-calendar',
                up: 'fas fa-chevron-up',
                down: 'fas fa-chevron-down',
                previous: 'fas fa-chevron-left',
                next: 'fas fa-chevron-right',
                today: 'fas fa-calendar-check',
                clear: 'fas fa-trash',
                close: 'fas fa-times'
            }
        });
    }
    
    // Initialize input masks
    if ($.fn.inputmask) {
        $('[data-mask]').each(function() {
            $(this).inputmask($(this).data('mask'));
        });
        
        // Common masks
        $('.phone-mask').inputmask('(999) 999-9999');
        $('.ssn-mask').inputmask('999-99-9999');
        $('.date-mask').inputmask('99/99/9999');
    }
    
    // Initialize summernote
    if ($.fn.summernote) {
        $('.summernote').summernote({
            height: 200,
            minHeight: null,
            maxHeight: null,
            focus: false,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });
    }
    
    // Initialize DataTables common settings
    if ($.fn.DataTable) {
        $.extend(true, $.fn.dataTable.defaults, {
            responsive: true,
            autoWidth: false,
            dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>rtip',
            language: {
                processing: '<i class="fas fa-spinner fa-spin"></i> Loading...',
                emptyTable: 'No data available',
                zeroRecords: 'No matching records found'
            },
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            buttons: [
                {
                    extend: 'copy',
                    className: 'btn btn-default',
                    text: '<i class="fas fa-copy"></i> Copy'
                },
                {
                    extend: 'excel',
                    className: 'btn btn-default',
                    text: '<i class="fas fa-file-excel"></i> Excel'
                },
                {
                    extend: 'pdf',
                    className: 'btn btn-default',
                    text: '<i class="fas fa-file-pdf"></i> PDF'
                },
                {
                    extend: 'print',
                    className: 'btn btn-default',
                    text: '<i class="fas fa-print"></i> Print'
                }
            ]
        });
    }
}

/**
 * Setup global event handlers
 */
function setupGlobalEventHandlers() {
    // Handle form submissions with loading states
    $(document).on('submit', '.ajax-form', function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('[type="submit"]');
        
        // Show loading state
        setButtonLoading(submitBtn, true);
        
        // Submit form via AJAX
        const formData = new FormData(this);
        
        $.ajax({
            url: form.attr('action') || window.location.href,
            method: form.attr('method') || 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                setButtonLoading(submitBtn, false);
                
                if (response.success) {
                    showAlert('success', response.message || 'Operation completed successfully');
                    
                    // Handle redirect
                    if (response.redirect) {
                        setTimeout(() => {
                            window.location.href = response.redirect;
                        }, 1000);
                    }
                    
                    // Handle modal close
                    if (form.closest('.modal').length) {
                        form.closest('.modal').modal('hide');
                    }
                    
                    // Handle table refresh
                    if (response.refresh_table && window.table) {
                        table.ajax.reload();
                    }
                } else {
                    showAlert('error', response.message || 'Operation failed');
                }
            },
            error: function(xhr, status, error) {
                setButtonLoading(submitBtn, false);
                handleAjaxError(xhr, status, error);
            }
        });
    });
    
    // Handle delete confirmations
    $(document).on('click', '.delete-confirm', function(e) {
        e.preventDefault();
        const element = $(this);
        const message = element.data('message') || 'Are you sure you want to delete this item?';
        
        Swal.fire({
            title: 'Are you sure?',
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Trigger the actual delete action
                if (element.attr('href')) {
                    window.location.href = element.attr('href');
                } else if (element.data('url')) {
                    performDelete(element.data('url'));
                }
            }
        });
    });
    
    // Handle print buttons
    $(document).on('click', '.print-btn', function(e) {
        e.preventDefault();
        window.print();
    });
    
    // Handle refresh buttons
    $(document).on('click', '.refresh-btn', function(e) {
        e.preventDefault();
        location.reload();
    });
    
    // Auto-hide alerts
    $(document).on('click', '.alert .close', function() {
        $(this).closest('.alert').fadeOut();
    });
    
    // Handle logout
    $(document).on('click', '.logout-btn', function(e) {
        e.preventDefault();
        logout();
    });
}

/**
 * Initialize tooltips and popovers
 */
function initializeTooltips() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Initialize popovers
    $('[data-toggle="popover"]').popover();
    
    // Auto-enable tooltips on elements with title attribute
    $('[title]').not('[data-toggle="tooltip"]').tooltip();
}

/**
 * Setup AJAX defaults
 */
function setupAjaxDefaults() {
    // Set CSRF token for all AJAX requests
    $.ajaxSetup({
        beforeSend: function(xhr, settings) {
            if (!/^(GET|HEAD|OPTIONS|TRACE)$/i.test(settings.type) && !this.crossDomain) {
                const token = $('meta[name="csrf-token"]').attr('content');
                if (token) {
                    xhr.setRequestHeader("X-CSRF-Token", token);
                }
            }
        }
    });
    
    // Global AJAX error handler
    $(document).ajaxError(function(event, xhr, settings, error) {
        if (xhr.status === 401) {
            showAlert('error', 'Session expired. Please log in again.');
            setTimeout(() => {
                window.location.href = 'index.php';
            }, 2000);
        }
    });
}

/**
 * Load user session information
 */
function loadUserSession() {
    $.ajax({
        url: 'api/auth_api.php',
        method: 'POST',
        data: JSON.stringify({ action: 'getSession' }),
        contentType: 'application/json',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                currentUser = response.data;
                updateUserInterface();
            }
        },
        error: function(xhr, status, error) {
            // Handle silently - user might not be logged in
        }
    });
}

/**
 * Update user interface based on current user
 */
function updateUserInterface() {
    if (!currentUser) return;
    
    // Update user info in sidebar
    $('.user-panel .info a').text(currentUser.full_name);
    $('.user-role').text(currentUser.user_type);
    
    // Update user avatar
    const initial = currentUser.full_name.charAt(0).toUpperCase();
    $('.user-image, .user-header img').attr('src', 
        `https://via.placeholder.com/160x160/2c5aa0/ffffff?text=${initial}`
    );
    
    // Show/hide menu items based on user role
    if (currentUser.user_type !== 'admin') {
        $('.admin-only').hide();
    }
}

/**
 * Show alert message
 */
function showAlert(type, message, container = '#alertContainer') {
    const alertClass = {
        'success': 'alert-success',
        'error': 'alert-danger',
        'warning': 'alert-warning',
        'info': 'alert-info'
    }[type] || 'alert-info';
    
    const icon = {
        'success': 'fas fa-check',
        'error': 'fas fa-ban',
        'warning': 'fas fa-exclamation-triangle',
        'info': 'fas fa-info-circle'
    }[type] || 'fas fa-info-circle';
    
    const alert = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="${icon} mr-2"></i>
            ${message}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    `;
    
    $(container).html(alert);
    
    // Auto-hide after 5 seconds (except for errors)
    if (type !== 'error') {
        setTimeout(() => {
            $(container).find('.alert').fadeOut();
        }, 5000);
    }
}

/**
 * Set button loading state
 */
function setButtonLoading(button, loading) {
    if (loading) {
        button.prop('disabled', true);
        button.find('.btn-text').hide();
        button.find('.btn-loading').show();
        
        if (!button.find('.btn-loading').length) {
            button.append('<span class="btn-loading ml-2"><i class="fas fa-spinner fa-spin"></i></span>');
        }
    } else {
        button.prop('disabled', false);
        button.find('.btn-text').show();
        button.find('.btn-loading').hide();
    }
}

/**
 * Handle AJAX errors
 */
function handleAjaxError(xhr, status, error) {
    let message = 'An error occurred. Please try again.';
    
    if (xhr.status === 0) {
        message = 'Network error. Please check your connection.';
    } else if (xhr.status === 404) {
        message = 'The requested resource was not found.';
    } else if (xhr.status === 500) {
        message = 'Internal server error. Please try again later.';
    } else if (xhr.status === 403) {
        message = 'Access denied. You do not have permission to perform this action.';
    } else if (xhr.responseJSON && xhr.responseJSON.message) {
        message = xhr.responseJSON.message;
    }
    
    showAlert('error', message);
}

/**
 * Perform delete operation
 */
function performDelete(url) {
    $.ajax({
        url: url,
        method: 'DELETE',
        success: function(response) {
            if (response.success) {
                showAlert('success', response.message || 'Item deleted successfully');
                
                // Refresh table if exists
                if (window.table) {
                    table.ajax.reload();
                } else {
                    location.reload();
                }
            } else {
                showAlert('error', response.message || 'Delete failed');
            }
        },
        error: function(xhr, status, error) {
            handleAjaxError(xhr, status, error);
        }
    });
}

/**
 * Logout user
 */
function logout() {
    Swal.fire({
        title: 'Are you sure?',
        text: 'Do you want to log out?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, log out!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'api/auth_api.php',
                method: 'POST',
                data: JSON.stringify({ action: 'logout' }),
                contentType: 'application/json',
                success: function(response) {
                    window.location.href = 'index.php';
                },
                error: function() {
                    window.location.href = 'index.php';
                }
            });
        }
    });
}

/**
 * Format date for display
 */
function formatDate(dateString, format = 'MM/DD/YYYY') {
    if (!dateString) return '';
    
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return dateString;
    
    return moment(date).format(format);
}

/**
 * Format currency
 */
function formatCurrency(amount, currency = 'USD') {
    if (!amount) return '$0.00';
    
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency
    }).format(amount);
}

/**
 * Validate form fields
 */
function validateForm(formSelector) {
    const form = $(formSelector);
    let isValid = true;
    
    // Remove previous error states
    form.find('.is-invalid').removeClass('is-invalid');
    form.find('.invalid-feedback').remove();
    
    // Check required fields
    form.find('[required]').each(function() {
        const field = $(this);
        const value = field.val();
        
        if (!value || value.trim() === '') {
            field.addClass('is-invalid');
            field.after('<div class="invalid-feedback">This field is required.</div>');
            isValid = false;
        }
    });
    
    // Check email fields
    form.find('[type="email"]').each(function() {
        const field = $(this);
        const value = field.val();
        
        if (value && !isValidEmail(value)) {
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
 * Show loading overlay
 */
function showLoading() {
    if (!$('.loading-overlay').length) {
        $('body').append(`
            <div class="loading-overlay">
                <div class="loading-spinner">
                    <i class="fas fa-spinner fa-spin fa-3x"></i>
                    <p class="mt-3">Loading...</p>
                </div>
            </div>
        `);
    }
    $('.loading-overlay').show();
}

/**
 * Hide loading overlay
 */
function hideLoading() {
    $('.loading-overlay').hide();
}

/**
 * Export table data
 */
function exportTableData(tableId, format = 'excel') {
    const table = $(tableId).DataTable();
    
    switch (format) {
        case 'excel':
            table.button('.buttons-excel').trigger();
            break;
        case 'pdf':
            table.button('.buttons-pdf').trigger();
            break;
        case 'csv':
            table.button('.buttons-csv').trigger();
            break;
        case 'print':
            table.button('.buttons-print').trigger();
            break;
    }
}

/**
 * Utility functions
 */
const Utils = {
    // Debounce function
    debounce: function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },
    
    // Throttle function
    throttle: function(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    },
    
    // Generate random string
    randomString: function(length = 8) {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        let result = '';
        for (let i = 0; i < length; i++) {
            result += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return result;
    },
    
    // Copy to clipboard
    copyToClipboard: function(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(() => {
                showAlert('success', 'Copied to clipboard!');
            });
        } else {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            showAlert('success', 'Copied to clipboard!');
        }
    }
};

// Make functions available globally
window.showAlert = showAlert;
window.setButtonLoading = setButtonLoading;
window.handleAjaxError = handleAjaxError;
window.formatDate = formatDate;
window.formatCurrency = formatCurrency;
window.validateForm = validateForm;
window.showLoading = showLoading;
window.hideLoading = hideLoading;
window.exportTableData = exportTableData;
window.Utils = Utils;
