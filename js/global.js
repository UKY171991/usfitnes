/**
 * PathLab Pro - Global JavaScript Functions
 * Laboratory Management System
 * Version: 2.0
 * Date: August 5, 2025
 * 
 * This file contains global JavaScript functions and utilities
 */

// Global configuration
window.PathLabPro = {
    config: {
        baseUrl: window.location.origin,
        apiUrl: 'api/',
        toastrTimeout: 3000,
        animationDuration: 300,
        debounceDelay: 300
    },
    
    // Utility functions
    utils: {},
    
    // UI components
    ui: {},
    
    // Data management
    data: {},
    
    // Event handlers
    events: {}
};

// ===== UTILITY FUNCTIONS =====
PathLabPro.utils = {
    
    // Debounce function for search inputs
    debounce: function(func, delay) {
        let timeoutId;
        return function(...args) {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => func.apply(this, args), delay);
        };
    },
    
    // Format phone number
    formatPhoneNumber: function(phoneNumber) {
        const cleaned = phoneNumber.replace(/\D/g, '');
        if (cleaned.length >= 10) {
            const match = cleaned.match(/^(\d{3})(\d{3})(\d{4})$/);
            if (match) {
                return `(${match[1]}) ${match[2]}-${match[3]}`;
            }
        }
        return phoneNumber;
    },
    
    // Validate email
    validateEmail: function(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    },
    
    // Calculate age from date of birth
    calculateAge: function(dateOfBirth) {
        if (!dateOfBirth) return null;
        
        const today = new Date();
        const birthDate = new Date(dateOfBirth);
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        
        return age;
    },
    
    // Format currency
    formatCurrency: function(amount, currency = 'USD') {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currency
        }).format(amount);
    },
    
    // Format date
    formatDate: function(date, options = {}) {
        const defaultOptions = {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        };
        
        return new Date(date).toLocaleDateString('en-US', { ...defaultOptions, ...options });
    },
    
    // Format relative time
    formatRelativeTime: function(date) {
        const now = new Date();
        const target = new Date(date);
        const diffInSeconds = Math.floor((now - target) / 1000);
        
        if (diffInSeconds < 60) return 'just now';
        if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} minutes ago`;
        if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} hours ago`;
        if (diffInSeconds < 2592000) return `${Math.floor(diffInSeconds / 86400)} days ago`;
        
        return this.formatDate(date);
    },
    
    // Generate random ID
    generateId: function(prefix = 'id') {
        return `${prefix}_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
    },
    
    // Deep clone object
    deepClone: function(obj) {
        return JSON.parse(JSON.stringify(obj));
    },
    
    // Check if element is visible in viewport
    isInViewport: function(element) {
        const rect = element.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    }
};

// ===== UI COMPONENTS =====
PathLabPro.ui = {
    
    // Show loading state
    showLoading: function(element, text = 'Loading...') {
        const $element = $(element);
        $element.addClass('ajax-loading');
        
        if ($element.is('button')) {
            $element.addClass('btn-loading');
            $element.data('original-text', $element.html());
            $element.html(`<span>${text}</span>`);
            $element.prop('disabled', true);
        }
    },
    
    // Hide loading state
    hideLoading: function(element) {
        const $element = $(element);
        $element.removeClass('ajax-loading btn-loading');
        
        if ($element.is('button')) {
            const originalText = $element.data('original-text');
            if (originalText) {
                $element.html(originalText);
            }
            $element.prop('disabled', false);
        }
    },
    
    // Show toast notification
    showToast: function(message, type = 'info', options = {}) {
        const defaultOptions = {
            timeOut: PathLabPro.config.toastrTimeout,
            closeButton: true,
            progressBar: true,
            positionClass: 'toast-top-right',
            preventDuplicates: true
        };
        
        toastr.options = { ...defaultOptions, ...options };
        
        switch (type) {
            case 'success':
                toastr.success(message);
                break;
            case 'error':
                toastr.error(message);
                break;
            case 'warning':
                toastr.warning(message);
                break;
            case 'info':
            default:
                toastr.info(message);
                break;
        }
    },
    
    // Show confirmation dialog
    showConfirmation: function(options = {}) {
        const defaultOptions = {
            title: 'Are you sure?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#2c5aa0',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, continue',
            cancelButtonText: 'Cancel'
        };
        
        return Swal.fire({ ...defaultOptions, ...options });
    },
    
    // Show alert dialog
    showAlert: function(message, type = 'info', options = {}) {
        const defaultOptions = {
            title: type.charAt(0).toUpperCase() + type.slice(1),
            text: message,
            icon: type,
            confirmButtonColor: '#2c5aa0'
        };
        
        return Swal.fire({ ...defaultOptions, ...options });
    },
    
    // Animate counter
    animateCounter: function(element, targetValue, duration = 1000) {
        const $element = $(element);
        const startValue = parseInt($element.text()) || 0;
        const increment = (targetValue - startValue) / (duration / 16);
        let currentValue = startValue;
        
        const updateCounter = () => {
            currentValue += increment;
            if ((increment > 0 && currentValue >= targetValue) || 
                (increment < 0 && currentValue <= targetValue)) {
                $element.text(targetValue);
            } else {
                $element.text(Math.floor(currentValue));
                requestAnimationFrame(updateCounter);
            }
        };
        
        updateCounter();
    },
    
    // Add ripple effect to buttons
    addRippleEffect: function(element) {
        $(element).on('click', function(e) {
            const $button = $(this);
            const rect = this.getBoundingClientRect();
            const ripple = $('<span class="ripple"></span>');
            
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.css({
                width: size,
                height: size,
                left: x,
                top: y
            }).addClass('ripple-effect');
            
            $button.append(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    },
    
    // Auto-resize textarea
    autoResizeTextarea: function(element) {
        $(element).on('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    },
    
    // Format form inputs
    formatFormInputs: function(container = document) {
        // Phone number formatting
        $(container).find('input[type="tel"], input[name*="phone"]').on('input', function() {
            const formatted = PathLabPro.utils.formatPhoneNumber(this.value);
            this.value = formatted;
        });
        
        // Email validation
        $(container).find('input[type="email"]').on('blur', function() {
            const $input = $(this);
            if (this.value && !PathLabPro.utils.validateEmail(this.value)) {
                $input.addClass('is-invalid');
            } else {
                $input.removeClass('is-invalid');
            }
        });
        
        // Auto-resize textareas
        $(container).find('textarea').each(function() {
            PathLabPro.ui.autoResizeTextarea(this);
        });
    }
};

// ===== DATA MANAGEMENT =====
PathLabPro.data = {
    
    // AJAX request wrapper
    request: function(options) {
        const defaultOptions = {
            type: 'GET',
            dataType: 'json',
            timeout: 30000,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        };
        
        const settings = { ...defaultOptions, ...options };
        
        return $.ajax(settings)
            .fail(function(xhr, status, error) {
                console.error('AJAX Request failed:', {
                    url: settings.url,
                    status: xhr.status,
                    statusText: xhr.statusText,
                    error: error
                });
                
                let errorMessage = 'An error occurred while processing your request.';
                
                if (xhr.status === 0) {
                    errorMessage = 'Network error. Please check your connection.';
                } else if (xhr.status === 404) {
                    errorMessage = 'Requested resource not found.';
                } else if (xhr.status === 500) {
                    errorMessage = 'Internal server error. Please try again later.';
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                PathLabPro.ui.showToast(errorMessage, 'error');
            });
    },
    
    // Get data from API
    get: function(endpoint, params = {}) {
        return this.request({
            url: PathLabPro.config.apiUrl + endpoint,
            type: 'GET',
            data: params
        });
    },
    
    // Post data to API
    post: function(endpoint, data = {}) {
        return this.request({
            url: PathLabPro.config.apiUrl + endpoint,
            type: 'POST',
            data: data
        });
    },
    
    // Put data to API
    put: function(endpoint, data = {}) {
        return this.request({
            url: PathLabPro.config.apiUrl + endpoint,
            type: 'PUT',
            data: JSON.stringify(data),
            contentType: 'application/json'
        });
    },
    
    // Delete data from API
    delete: function(endpoint, data = {}) {
        return this.request({
            url: PathLabPro.config.apiUrl + endpoint,
            type: 'DELETE',
            data: JSON.stringify(data),
            contentType: 'application/json'
        });
    },
    
    // Upload file
    upload: function(endpoint, formData) {
        return this.request({
            url: PathLabPro.config.apiUrl + endpoint,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false
        });
    },
    
    // Local storage wrapper
    storage: {
        set: function(key, value) {
            try {
                localStorage.setItem(key, JSON.stringify(value));
                return true;
            } catch (e) {
                console.error('LocalStorage set error:', e);
                return false;
            }
        },
        
        get: function(key, defaultValue = null) {
            try {
                const item = localStorage.getItem(key);
                return item ? JSON.parse(item) : defaultValue;
            } catch (e) {
                console.error('LocalStorage get error:', e);
                return defaultValue;
            }
        },
        
        remove: function(key) {
            try {
                localStorage.removeItem(key);
                return true;
            } catch (e) {
                console.error('LocalStorage remove error:', e);
                return false;
            }
        },
        
        clear: function() {
            try {
                localStorage.clear();
                return true;
            } catch (e) {
                console.error('LocalStorage clear error:', e);
                return false;
            }
        }
    }
};

// ===== EVENT HANDLERS =====
PathLabPro.events = {
    
    // Initialize global event handlers
    init: function() {
        // Handle form submissions
        $(document).on('submit', 'form[data-ajax="true"]', this.handleAjaxForm);
        
        // Handle clickable elements
        $(document).on('click', '[data-action]', this.handleDataAction);
        
        // Handle confirmation dialogs
        $(document).on('click', '[data-confirm]', this.handleConfirmAction);
        
        // Handle tooltip initialization
        $(document).on('mouseenter', '[data-toggle="tooltip"]', function() {
            $(this).tooltip();
        });
        
        // Handle popover initialization
        $(document).on('mouseenter', '[data-toggle="popover"]', function() {
            $(this).popover();
        });
        
        // Handle search inputs with debounce
        $(document).on('input', '[data-search]', PathLabPro.utils.debounce(this.handleSearch, PathLabPro.config.debounceDelay));
        
        // Handle sidebar toggle
        $(document).on('click', '[data-widget="pushmenu"]', this.handleSidebarToggle);
        
        // Handle fullscreen toggle
        $(document).on('click', '[data-widget="fullscreen"]', this.handleFullscreenToggle);
        
        // Auto-save form data
        $(document).on('input change', '[data-autosave]', PathLabPro.utils.debounce(this.handleAutoSave, 1000));
        
        // Handle file uploads
        $(document).on('change', 'input[type="file"][data-upload]', this.handleFileUpload);
    },
    
    // Handle AJAX form submissions
    handleAjaxForm: function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const formData = new FormData(this);
        const url = $form.attr('action') || PathLabPro.config.apiUrl + $form.data('endpoint');
        const method = $form.attr('method') || 'POST';
        const submitButton = $form.find('button[type="submit"]');
        
        PathLabPro.ui.showLoading(submitButton);
        
        PathLabPro.data.request({
            url: url,
            type: method,
            data: formData,
            processData: false,
            contentType: false
        }).done(function(response) {
            if (response.success) {
                PathLabPro.ui.showToast(response.message || 'Operation completed successfully', 'success');
                
                // Trigger custom event
                $form.trigger('ajax:success', [response]);
                
                // Reset form if specified
                if ($form.data('reset') !== false) {
                    $form[0].reset();
                    $form.find('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
                }
                
                // Close modal if form is in modal
                const $modal = $form.closest('.modal');
                if ($modal.length) {
                    $modal.modal('hide');
                }
            } else {
                PathLabPro.ui.showToast(response.message || 'Operation failed', 'error');
                $form.trigger('ajax:error', [response]);
            }
        }).fail(function() {
            $form.trigger('ajax:fail');
        }).always(function() {
            PathLabPro.ui.hideLoading(submitButton);
        });
    },
    
    // Handle data action clicks
    handleDataAction: function(e) {
        e.preventDefault();
        
        const $element = $(this);
        const action = $element.data('action');
        const target = $element.data('target');
        const params = $element.data('params') || {};
        
        switch (action) {
            case 'refresh':
                if (target && typeof window[target] === 'function') {
                    window[target]();
                } else {
                    location.reload();
                }
                break;
                
            case 'delete':
                PathLabPro.ui.showConfirmation({
                    title: 'Delete Item',
                    text: 'This item will be permanently deleted.',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Perform delete action
                        const url = $element.attr('href') || $element.data('url');
                        if (url) {
                            PathLabPro.data.delete(url, params).done(function(response) {
                                if (response.success) {
                                    PathLabPro.ui.showToast(response.message || 'Item deleted successfully', 'success');
                                    $element.trigger('action:delete:success', [response]);
                                }
                            });
                        }
                    }
                });
                break;
                
            case 'export':
                const exportUrl = $element.data('url') || $element.attr('href');
                if (exportUrl) {
                    window.open(exportUrl, '_blank');
                }
                break;
        }
    },
    
    // Handle confirmation actions
    handleConfirmAction: function(e) {
        e.preventDefault();
        
        const $element = $(this);
        const message = $element.data('confirm');
        const title = $element.data('confirm-title') || 'Confirm Action';
        
        PathLabPro.ui.showConfirmation({
            title: title,
            text: message
        }).then((result) => {
            if (result.isConfirmed) {
                // Continue with original action
                if ($element.is('a')) {
                    window.location.href = $element.attr('href');
                } else if ($element.is('button[type="submit"]')) {
                    $element.closest('form').submit();
                }
            }
        });
    },
    
    // Handle search with debounce
    handleSearch: function() {
        const $input = $(this);
        const searchTerm = $input.val().trim();
        const target = $input.data('search');
        const minLength = $input.data('min-length') || 2;
        
        if (searchTerm.length >= minLength) {
            // Trigger search
            $input.trigger('search:perform', [searchTerm]);
            
            // If DataTable target is specified
            if (target && $.fn.DataTable && $.fn.DataTable.isDataTable(target)) {
                $(target).DataTable().search(searchTerm).draw();
            }
        } else if (searchTerm.length === 0) {
            // Clear search
            $input.trigger('search:clear');
            
            if (target && $.fn.DataTable && $.fn.DataTable.isDataTable(target)) {
                $(target).DataTable().search('').draw();
            }
        }
    },
    
    // Handle sidebar toggle
    handleSidebarToggle: function(e) {
        e.preventDefault();
        $('body').toggleClass('sidebar-collapse');
        
        // Save state
        PathLabPro.data.storage.set('sidebar_collapsed', $('body').hasClass('sidebar-collapse'));
    },
    
    // Handle fullscreen toggle
    handleFullscreenToggle: function(e) {
        e.preventDefault();
        
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen();
            $(this).find('i').removeClass('fa-expand-arrows-alt').addClass('fa-compress-arrows-alt');
        } else {
            document.exitFullscreen();
            $(this).find('i').removeClass('fa-compress-arrows-alt').addClass('fa-expand-arrows-alt');
        }
    },
    
    // Handle auto-save
    handleAutoSave: function() {
        const $element = $(this);
        const key = $element.data('autosave');
        const value = $element.val();
        
        PathLabPro.data.storage.set(`autosave_${key}`, {
            value: value,
            timestamp: Date.now()
        });
        
        // Show subtle indicator
        $element.addClass('autosaved');
        setTimeout(() => {
            $element.removeClass('autosaved');
        }, 1000);
    },
    
    // Handle file uploads
    handleFileUpload: function() {
        const $input = $(this);
        const file = this.files[0];
        const maxSize = $input.data('max-size') || 5 * 1024 * 1024; // 5MB default
        const allowedTypes = $input.data('allowed-types') || '';
        
        if (file) {
            // Validate file size
            if (file.size > maxSize) {
                PathLabPro.ui.showToast(`File size must be less than ${maxSize / 1024 / 1024}MB`, 'error');
                $input.val('');
                return;
            }
            
            // Validate file type
            if (allowedTypes && !allowedTypes.split(',').includes(file.type)) {
                PathLabPro.ui.showToast('Invalid file type', 'error');
                $input.val('');
                return;
            }
            
            // Show file name
            const $label = $input.siblings('label').length ? $input.siblings('label') : $input.parent().find('label');
            if ($label.length) {
                $label.text(file.name);
            }
        }
    }
};

// ===== INITIALIZATION =====
$(document).ready(function() {
    console.log('PathLab Pro Global JS initialized');
    
    // Initialize event handlers
    PathLabPro.events.init();
    
    // Initialize form formatting
    PathLabPro.ui.formatFormInputs();
    
    // Restore sidebar state
    const sidebarCollapsed = PathLabPro.data.storage.get('sidebar_collapsed', false);
    if (sidebarCollapsed) {
        $('body').addClass('sidebar-collapse');
    }
    
    // Initialize tooltips globally
    $('[data-toggle="tooltip"]').tooltip();
    
    // Initialize popovers globally
    $('[data-toggle="popover"]').popover();
    
    // Add ripple effect to buttons
    PathLabPro.ui.addRippleEffect('.btn');
    
    // Auto-hide alerts after delay
    $('.alert[data-auto-hide]').each(function() {
        const $alert = $(this);
        const delay = $alert.data('auto-hide') || 5000;
        
        setTimeout(() => {
            $alert.fadeOut();
        }, delay);
    });
    
    // Restore auto-saved form data
    $('[data-autosave]').each(function() {
        const $element = $(this);
        const key = $element.data('autosave');
        const saved = PathLabPro.data.storage.get(`autosave_${key}`);
        
        if (saved && saved.timestamp > Date.now() - 24 * 60 * 60 * 1000) { // 24 hours
            $element.val(saved.value);
        }
    });
    
    // Handle page visibility change
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            // Page became visible, refresh data if needed
            $(document).trigger('page:visible');
        }
    });
    
    // Handle online/offline status
    window.addEventListener('online', function() {
        PathLabPro.ui.showToast('Connection restored', 'success');
        $(document).trigger('connection:online');
    });
    
    window.addEventListener('offline', function() {
        PathLabPro.ui.showToast('Connection lost', 'warning');
        $(document).trigger('connection:offline');
    });
    
    console.log('PathLab Pro initialization complete');
});

// CSS for ripple effect
if (!document.getElementById('pathlab-ripple-css')) {
    const rippleCSS = `
        <style id="pathlab-ripple-css">
            .ripple {
                position: absolute;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.6);
                transform: scale(0);
                animation: ripple-animation 0.6s linear;
                pointer-events: none;
            }
            
            @keyframes ripple-animation {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
            
            .autosaved {
                box-shadow: 0 0 0 2px rgba(40, 167, 69, 0.25) !important;
                transition: box-shadow 0.3s ease;
            }
            
            .ajax-loading {
                opacity: 0.7;
                pointer-events: none;
            }
        </style>
    `;
    
    $('head').append(rippleCSS);
}

// Export PathLabPro to global scope
window.PathLabPro = PathLabPro;
