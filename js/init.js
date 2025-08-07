/**
 * PathLab Pro - Core Initialization Script
 * This file ensures all libraries are loaded properly and provides fallbacks
 */

// Enhanced library loading with retry mechanism
function waitForLibraries(callback, maxAttempts = 5, currentAttempt = 0) {
    if (currentAttempt >= maxAttempts) {
        console.error('Failed to load required libraries after ' + maxAttempts + ' attempts');
        alert('JavaScript libraries failed to load. Please refresh the page.');
        return;
    }
    
    // Check if all required libraries are available
    if (typeof jQuery !== 'undefined' && 
        jQuery.fn.DataTable && 
        typeof toastr !== 'undefined' && 
        typeof Swal !== 'undefined') {
        callback();
    } else {
        console.log('Waiting for libraries... attempt ' + (currentAttempt + 1));
        setTimeout(() => {
            waitForLibraries(callback, maxAttempts, currentAttempt + 1);
        }, 1000);
    }
}

// Initialize when DOM is ready and libraries are loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        waitForLibraries(initializeApplication);
    });
} else {
    // DOM is already ready
    waitForLibraries(initializeApplication);
}

// Main application initialization
function initializeApplication() {
    try {
        // Check required libraries
        checkRequiredLibraries();
        
        // Initialize global components
        initializeGlobalComponents();
        
        // Initialize Toastr
        if (typeof toastr !== 'undefined') {
            toastr.options = {
                closeButton: true,
                debug: false,
                newestOnTop: true,
                progressBar: true,
                positionClass: "toast-top-right",
                preventDuplicates: false,
                onclick: null,
                showDuration: "300",
                hideDuration: "1000",
                timeOut: "5000",
                extendedTimeOut: "1000",
                showEasing: "swing",
                hideEasing: "linear",
                showMethod: "fadeIn",
                hideMethod: "fadeOut"
            };
        }
        
        console.log('Application initialized successfully');
        
    } catch (error) {
        console.error('Failed to initialize application:', error);
    }
}

// Check if required libraries are loaded
function checkRequiredLibraries() {
    const requiredLibraries = [
        { name: 'jQuery', check: () => typeof jQuery !== 'undefined' },
        { name: 'Bootstrap', check: () => typeof bootstrap !== 'undefined' || (jQuery && jQuery.fn.modal) },
        { name: 'DataTables', check: () => jQuery && jQuery.fn.DataTable },
        { name: 'Select2', check: () => jQuery && jQuery.fn.select2 },
        { name: 'Toastr', check: () => typeof toastr !== 'undefined' },
        { name: 'SweetAlert2', check: () => typeof Swal !== 'undefined' }
    ];
    
    const missingLibraries = [];
    requiredLibraries.forEach(lib => {
        if (!lib.check()) {
            missingLibraries.push(lib.name);
            console.warn(`${lib.name} is not loaded`);
        }
    });
    
    if (missingLibraries.length > 0) {
        console.error('Missing libraries:', missingLibraries.join(', '));
    } else {
        console.log('All required libraries are loaded');
    }
}

// Initialize global components
function initializeGlobalComponents() {
    if (typeof jQuery === 'undefined') return;
    
    // Initialize tooltips
    if (jQuery.fn.tooltip) {
        $('[data-toggle="tooltip"]').tooltip();
    }
    
    // Initialize popovers
    if (jQuery.fn.popover) {
        $('[data-toggle="popover"]').popover();
    }
    
    // Initialize Select2
    if (jQuery.fn.select2) {
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });
    }
}

// Toast notification function with fallback
function showToast(type, message, title = '') {
    if (typeof toastr !== 'undefined') {
        const options = {
            closeButton: true,
            progressBar: true,
            positionClass: 'toast-top-right',
            timeOut: type === 'error' ? 8000 : 5000
        };
        
        switch(type) {
            case 'success':
                toastr.success(message, title || 'Success', options);
                break;
            case 'error':
                toastr.error(message, title || 'Error', options);
                break;
            case 'warning':
                toastr.warning(message, title || 'Warning', options);
                break;
            case 'info':
                toastr.info(message, title || 'Info', options);
                break;
        }
    } else {
        // Fallback to console and alert
        console.log(`${type.toUpperCase()}: ${title} - ${message}`);
        if (type === 'error') {
            alert(`Error: ${message}`);
        }
    }
}

// Show loading overlay
function showLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.style.display = 'flex';
    }
}

// Hide loading overlay
function hideLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.style.display = 'none';
    }
}

// Global logout function
function logout() {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Logout Confirmation',
            text: 'Are you sure you want to logout?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, logout!'
        }).then((result) => {
            if (result.isConfirmed) {
                showLoading();
                window.location.href = 'logout.php';
            }
        });
    } else {
        // Fallback to confirm
        if (confirm('Are you sure you want to logout?')) {
            showLoading();
            window.location.href = 'logout.php';
        }
    }
}

// Utility functions
function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
}

function calculateAge(dateOfBirth) {
    if (!dateOfBirth) return 'N/A';
    const today = new Date();
    const birthDate = new Date(dateOfBirth);
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    return age;
}

function formatPhone(phone) {
    if (!phone) return '';
    const cleaned = phone.replace(/\D/g, '');
    const match = cleaned.match(/^(\d{3})(\d{3})(\d{4})$/);
    if (match) {
        return `(${match[1]}) ${match[2]}-${match[3]}`;
    }
    return phone;
}

// CrudOperations class for API interactions
class CrudOperations {
    constructor(apiUrl, entityName = 'item') {
        this.apiUrl = apiUrl;
        this.entityName = entityName;
    }

    async create(data) {
        try {
            const response = await this.makeRequest('POST', '', data);
            if (response.success) {
                showToast('success', response.message || `${this.entityName} created successfully`);
                return response;
            } else {
                throw new Error(response.message || `Failed to create ${this.entityName}`);
            }
        } catch (error) {
            showToast('error', error.message);
            throw error;
        }
    }

    async read(id = null) {
        try {
            const url = id ? `/${id}` : '';
            const response = await this.makeRequest('GET', url);
            if (response.success) {
                return response;
            } else {
                throw new Error(response.message || `Failed to read ${this.entityName}`);
            }
        } catch (error) {
            showToast('error', error.message);
            throw error;
        }
    }

    async update(id, data) {
        try {
            const response = await this.makeRequest('PUT', `/${id}`, data);
            if (response.success) {
                showToast('success', response.message || `${this.entityName} updated successfully`);
                return response;
            } else {
                throw new Error(response.message || `Failed to update ${this.entityName}`);
            }
        } catch (error) {
            showToast('error', error.message);
            throw error;
        }
    }

    async delete(id) {
        try {
            const response = await this.makeRequest('DELETE', `/${id}`);
            if (response.success) {
                showToast('success', response.message || `${this.entityName} deleted successfully`);
                return response;
            } else {
                throw new Error(response.message || `Failed to delete ${this.entityName}`);
            }
        } catch (error) {
            showToast('error', error.message);
            throw error;
        }
    }

    async makeRequest(method, endpoint = '', data = null) {
        const url = this.apiUrl + endpoint;
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };

        if (data && (method === 'POST' || method === 'PUT')) {
            options.body = JSON.stringify(data);
        }

        try {
            const response = await fetch(url, options);
            const result = await response.json();
            return result;
        } catch (error) {
            throw new Error('Network error: ' + error.message);
        }
    }
}

// Form Handler class
class FormHandler {
    constructor(formSelector, onSubmit = null) {
        this.form = typeof formSelector === 'string' ? document.querySelector(formSelector) : formSelector;
        this.onSubmit = onSubmit;
        
        if (this.form) {
            this.form.addEventListener('submit', this.handleSubmit.bind(this));
        }
    }

    async handleSubmit(event) {
        event.preventDefault();
        
        const formData = this.getFormData();
        
        try {
            if (this.onSubmit) {
                await this.onSubmit(formData);
            }
        } catch (error) {
            showToast('error', 'Form submission failed: ' + error.message);
        }
    }

    getFormData() {
        const formData = new FormData(this.form);
        const data = {};
        
        for (const [key, value] of formData.entries()) {
            data[key] = value;
        }
        
        return data;
    }

    resetForm() {
        if (this.form) {
            this.form.reset();
            
            // Reset Select2 elements
            if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
                $(this.form).find('.select2').val(null).trigger('change');
            }
        }
    }

    setFormData(data) {
        if (!this.form) return;
        
        Object.keys(data).forEach(key => {
            const element = this.form.querySelector(`[name="${key}"]`);
            if (element) {
                if (element.type === 'checkbox') {
                    element.checked = Boolean(data[key]);
                } else if (element.type === 'radio') {
                    const radio = this.form.querySelector(`[name="${key}"][value="${data[key]}"]`);
                    if (radio) radio.checked = true;
                } else {
                    element.value = data[key] || '';
                }
                
                // Trigger change event for Select2
                if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
                    $(element).trigger('change');
                }
            }
        });
    }
}

// Fallback functions in case libraries are not loaded
if (typeof showToast === 'undefined') {
    window.showToast = function(type, message, title = '') {
        console.log(`${type.toUpperCase()}: ${title} - ${message}`);
        if (type === 'error') {
            alert(`Error: ${message}`);
        }
    };
}

if (typeof showLoading === 'undefined') {
    window.showLoading = function() {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.style.display = 'flex';
        }
        console.log('Loading...');
    };
}

if (typeof hideLoading === 'undefined') {
    window.hideLoading = function() {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.style.display = 'none';
        }
        console.log('Loading complete');
    };
}

if (typeof escapeHtml === 'undefined') {
    window.escapeHtml = function(text) {
        if (text === null || text === undefined) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
    };
}

if (typeof calculateAge === 'undefined') {
    window.calculateAge = function(dateOfBirth) {
        if (!dateOfBirth) return 'N/A';
        const today = new Date();
        const birthDate = new Date(dateOfBirth);
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        return age;
    };
}

if (typeof formatPhone === 'undefined') {
    window.formatPhone = function(phone) {
        if (!phone) return '';
        const cleaned = phone.replace(/\D/g, '');
        const match = cleaned.match(/^(\d{3})(\d{3})(\d{4})$/);
        if (match) {
            return `(${match[1]}) ${match[2]}-${match[3]}`;
        }
        return phone;
    };
}
