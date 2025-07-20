/**
 * PathLab Pro - Dynamic AJAX Utilities
 * Comprehensive JavaScript library for dynamic page functionality
 */

// Global configuration
const DynamicUtils = {
    config: {
        baseUrl: window.location.origin,
        toastrDefaults: {
            "closeButton": true,
            "debug": false,
            "newestOnTop": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": true,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "4000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        },
        loadingOverlay: `
            <div class="loading-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center;">
                <div class="spinner-border text-light" role="status" style="width: 3rem; height: 3rem;">
                    <span class="sr-only">Loading...</span>
                </div>
            </div>
        `
    },

    // Initialize utilities
    init() {
        this.setupToastr();
        this.setupGlobalAjax();
        this.setupFormHandlers();
        this.setupTableHandlers();
        this.setupUtilities();
        this.setupKeyboardShortcuts();
        console.log('🚀 PathLab Pro Dynamic Utils initialized');
    },

    // Setup toastr notifications
    setupToastr() {
        if (typeof toastr !== 'undefined') {
            toastr.options = this.config.toastrDefaults;
        }
    },

    // Setup global AJAX handlers
    setupGlobalAjax() {
        // Global AJAX setup
        $.ajaxSetup({
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            timeout: 30000,
            cache: false
        });

        // Global AJAX event handlers
        $(document).ajaxStart(() => this.showLoading('Processing request...'));
        $(document).ajaxStop(() => this.hideLoading());
        $(document).ajaxError((event, xhr, settings, error) => {
            this.hideLoading();
            this.handleAjaxError(xhr, error);
        });
    },

    // Setup form handlers
    setupFormHandlers() {
        // Auto-submit forms with data-auto-submit attribute
        $(document).on('submit', 'form[data-auto-submit]', function(e) {
            e.preventDefault();
            DynamicUtils.submitForm($(this));
        });

        // Auto-validate forms
        $(document).on('input', 'input[required], select[required], textarea[required]', function() {
            DynamicUtils.validateField($(this));
        });

        // Format phone numbers
        $(document).on('input', 'input[type="tel"], input[data-format="phone"]', function() {
            DynamicUtils.formatPhone($(this));
        });
    },

    // Setup table handlers
    setupTableHandlers() {
        // Auto-refresh tables
        $(document).on('click', '[data-refresh-table]', function() {
            const tableId = $(this).data('refresh-table');
            DynamicUtils.refreshTable(tableId);
        });

        // Export functionality
        $(document).on('click', '[data-export]', function() {
            const type = $(this).data('export');
            const table = $(this).closest('.card').find('table').first();
            DynamicUtils.exportTable(table, type);
        });
    },

    // Setup utility functions
    setupUtilities() {
        // Auto-update timestamps
        this.updateTimestamps();
        setInterval(() => this.updateTimestamps(), 60000);

        // Auto-save functionality
        $(document).on('input', '[data-auto-save]', debounce(function() {
            DynamicUtils.autoSave($(this));
        }, 1000));

        // Search functionality
        $(document).on('input', '[data-live-search]', debounce(function() {
            const target = $(this).data('live-search');
            DynamicUtils.liveSearch($(this).val(), target);
        }, 300));
    },

    // Setup keyboard shortcuts
    setupKeyboardShortcuts() {
        $(document).keydown(function(e) {
            // Ctrl/Cmd + S = Save
            if ((e.ctrlKey || e.metaKey) && e.which === 83) {
                e.preventDefault();
                const activeForm = $('form:visible').first();
                if (activeForm.length) {
                    DynamicUtils.submitForm(activeForm);
                }
            }

            // Ctrl/Cmd + N = New
            if ((e.ctrlKey || e.metaKey) && e.which === 78) {
                e.preventDefault();
                const addBtn = $('[data-action="add"], .btn-add, #addBtn, [data-toggle="modal"][data-target*="add"]').first();
                if (addBtn.length) {
                    addBtn.click();
                }
            }

            // Escape = Close modal
            if (e.which === 27) {
                $('.modal:visible').modal('hide');
            }
        });
    },

    // Show loading overlay
    showLoading(message = 'Loading...') {
        this.hideLoading(); // Remove any existing overlay
        
        const overlay = $(this.config.loadingOverlay);
        if (message !== 'Loading...') {
            overlay.find('.sr-only').text(message);
        }
        
        $('body').append(overlay);
        
        // Auto-hide after 30 seconds
        setTimeout(() => this.hideLoading(), 30000);
    },

    // Hide loading overlay
    hideLoading() {
        $('.loading-overlay').remove();
    },

    // Show toastr notification
    notify(type, message, title = null) {
        if (typeof toastr === 'undefined') {
            console.log(`${type.toUpperCase()}: ${message}`);
            return;
        }

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
    },

    // Submit form via AJAX
    submitForm(form) {
        const url = form.attr('action') || window.location.href;
        const method = form.attr('method') || 'POST';
        const formData = new FormData(form[0]);

        // Add action if not present
        if (!formData.get('action')) {
            const action = form.data('action') || 'submit';
            formData.append('action', action);
        }

        this.showLoading('Submitting form...');

        $.ajax({
            url: url,
            type: method,
            data: formData,
            processData: false,
            contentType: false,
            success: (response) => {
                this.hideLoading();
                this.handleResponse(response, form);
            },
            error: (xhr, status, error) => {
                this.hideLoading();
                this.handleAjaxError(xhr, error);
            }
        });
    },

    // Handle AJAX response
    handleResponse(response, context = null) {
        if (typeof response === 'string') {
            try {
                response = JSON.parse(response);
            } catch (e) {
                this.notify('error', 'Invalid server response');
                return;
            }
        }

        if (response.success) {
            this.notify('success', response.message);
            
            // Handle various response actions
            if (response.action) {
                this.handleResponseAction(response.action, response.data, context);
            }
            
            // Refresh tables if needed
            if (response.refresh_table) {
                this.refreshTable(response.refresh_table);
            }
            
            // Close modals if needed
            if (response.close_modal) {
                $('.modal:visible').modal('hide');
            }
            
            // Redirect if needed
            if (response.redirect) {
                setTimeout(() => {
                    window.location.href = response.redirect;
                }, 1000);
            }
            
        } else {
            this.notify('error', response.message || 'Operation failed');
        }
    },

    // Handle response actions
    handleResponseAction(action, data, context) {
        switch(action) {
            case 'reload':
                setTimeout(() => window.location.reload(), 1000);
                break;
            case 'redirect':
                setTimeout(() => window.location.href = data.url, 1000);
                break;
            case 'update_stats':
                this.updateStatistics(data);
                break;
            case 'reset_form':
                if (context && context.is('form')) {
                    context[0].reset();
                }
                break;
        }
    },

    // Handle AJAX errors
    handleAjaxError(xhr, error) {
        let message = 'An error occurred. Please try again.';
        
        if (xhr.status === 401) {
            message = 'You are not authorized. Please login again.';
            setTimeout(() => window.location.href = 'login.php', 2000);
        } else if (xhr.status === 403) {
            message = 'Access denied. You do not have permission to perform this action.';
        } else if (xhr.status === 404) {
            message = 'The requested resource was not found.';
        } else if (xhr.status === 500) {
            message = 'Server error. Please contact the administrator.';
        } else if (xhr.status === 0) {
            message = 'Network error. Please check your connection.';
        } else if (xhr.responseJSON && xhr.responseJSON.message) {
            message = xhr.responseJSON.message;
        }

        this.notify('error', message);
        console.error('AJAX Error:', xhr, error);
    },

    // Validate form field
    validateField(field) {
        const value = field.val().trim();
        const type = field.attr('type');
        let isValid = true;
        let message = '';

        // Required validation
        if (field.prop('required') && !value) {
            isValid = false;
            message = 'This field is required';
        }

        // Email validation
        if (type === 'email' && value && !this.validateEmail(value)) {
            isValid = false;
            message = 'Please enter a valid email address';
        }

        // Phone validation
        if ((type === 'tel' || field.data('format') === 'phone') && value && !this.validatePhone(value)) {
            isValid = false;
            message = 'Please enter a valid phone number';
        }

        // Update field state
        field.toggleClass('is-invalid', !isValid);
        field.toggleClass('is-valid', isValid && value);
        
        const feedback = field.siblings('.invalid-feedback');
        if (feedback.length) {
            feedback.text(message);
        }

        return isValid;
    },

    // Validate email
    validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    },

    // Validate phone
    validatePhone(phone) {
        const cleaned = phone.replace(/\D/g, '');
        return cleaned.length >= 10 && cleaned.length <= 15;
    },

    // Format phone number
    formatPhone(field) {
        let value = field.val().replace(/\D/g, '');
        if (value.length >= 6) {
            value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
        } else if (value.length >= 3) {
            value = value.replace(/(\d{3})(\d{0,3})/, '($1) $2');
        }
        field.val(value);
    },

    // Refresh DataTable
    refreshTable(tableId) {
        const table = $(`#${tableId}`);
        if (table.length && $.fn.DataTable && $.fn.DataTable.isDataTable(table)) {
            table.DataTable().ajax.reload(null, false);
            this.notify('info', 'Table refreshed');
        }
    },

    // Export table data
    exportTable(table, type) {
        if (!table.length) return;

        const filename = `export_${new Date().getTime()}`;
        
        if ($.fn.DataTable && $.fn.DataTable.isDataTable(table)) {
            const dt = table.DataTable();
            
            switch(type) {
                case 'csv':
                    dt.button('.buttons-csv').trigger();
                    break;
                case 'excel':
                    dt.button('.buttons-excel').trigger();
                    break;
                case 'pdf':
                    dt.button('.buttons-pdf').trigger();
                    break;
                default:
                    this.notify('warning', 'Export type not supported');
            }
        } else {
            this.notify('warning', 'Table export not available');
        }
    },

    // Live search functionality
    liveSearch(query, target) {
        const targetElement = $(target);
        if (targetElement.length && $.fn.DataTable && $.fn.DataTable.isDataTable(targetElement)) {
            targetElement.DataTable().search(query).draw();
        }
    },

    // Auto-save functionality
    autoSave(field) {
        const formData = new FormData();
        formData.append('action', 'auto_save');
        formData.append('field', field.attr('name'));
        formData.append('value', field.val());
        formData.append('id', field.data('id'));

        $.ajax({
            url: window.location.href,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: (response) => {
                if (response.success) {
                    field.addClass('auto-saved');
                    setTimeout(() => field.removeClass('auto-saved'), 2000);
                }
            }
        });
    },

    // Update timestamps
    updateTimestamps() {
        $('[data-timestamp]').each(function() {
            const timestamp = $(this).data('timestamp');
            $(this).text(DynamicUtils.timeAgo(timestamp));
        });
    },

    // Time ago helper
    timeAgo(dateTime) {
        const now = new Date();
        const date = new Date(dateTime);
        const diff = now - date;
        
        const seconds = Math.floor(diff / 1000);
        const minutes = Math.floor(seconds / 60);
        const hours = Math.floor(minutes / 60);
        const days = Math.floor(hours / 24);
        
        if (seconds < 60) return 'just now';
        if (minutes < 60) return `${minutes}m ago`;
        if (hours < 24) return `${hours}h ago`;
        if (days < 30) return `${days}d ago`;
        
        return date.toLocaleDateString();
    },

    // Update statistics
    updateStatistics(data) {
        Object.keys(data).forEach(key => {
            const element = $(`#${key}, [data-stat="${key}"]`);
            if (element.length) {
                element.text(data[key]);
                element.addClass('stat-updated');
                setTimeout(() => element.removeClass('stat-updated'), 1000);
            }
        });
    },

    // Confirmation dialog
    confirm(message, callback, options = {}) {
        const defaults = {
            title: 'Confirm Action',
            confirmText: 'Yes',
            cancelText: 'Cancel',
            type: 'warning'
        };
        
        const settings = Object.assign(defaults, options);
        
        if (typeof bootbox !== 'undefined') {
            bootbox.confirm({
                title: settings.title,
                message: message,
                buttons: {
                    confirm: {
                        label: settings.confirmText,
                        className: `btn-${settings.type}`
                    },
                    cancel: {
                        label: settings.cancelText,
                        className: 'btn-secondary'
                    }
                },
                callback: callback
            });
        } else {
            // Fallback to native confirm
            if (confirm(message)) {
                callback(true);
            } else {
                callback(false);
            }
        }
    },

    // Format number
    formatNumber(num, decimals = 0) {
        return new Intl.NumberFormat('en-US', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        }).format(num);
    },

    // Format currency
    formatCurrency(amount, currency = '$') {
        return currency + this.formatNumber(amount, 2);
    }
};

// Debounce function
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

// Initialize when document is ready
$(document).ready(function() {
    DynamicUtils.init();
});

// Global access
window.DynamicUtils = DynamicUtils;
window.notify = DynamicUtils.notify.bind(DynamicUtils);
window.showLoading = DynamicUtils.showLoading.bind(DynamicUtils);
window.hideLoading = DynamicUtils.hideLoading.bind(DynamicUtils);
