// Common AJAX operations for AdminLTE3 Template
// This file contains reusable AJAX functions for CRUD operations

// Global AJAX configuration
const AJAX_CONFIG = {
    timeout: 30000,
    retryAttempts: 3,
    retryDelay: 1000
};

// Enhanced AJAX wrapper with retry logic
function ajaxRequest(options) {
    const defaultOptions = {
        type: 'GET',
        dataType: 'json',
        timeout: AJAX_CONFIG.timeout,
        cache: false,
        beforeSend: function(xhr) {
            if (options.showLoader !== false) {
                showLoader();
            }
        },
        complete: function() {
            if (options.showLoader !== false) {
                hideLoader();
            }
        }
    };
    
    const finalOptions = Object.assign({}, defaultOptions, options);
    
    return new Promise((resolve, reject) => {
        function makeRequest(attempt = 1) {
            $.ajax(finalOptions)
                .done(function(response) {
                    resolve(response);
                })
                .fail(function(xhr, status, error) {
                    if (attempt < AJAX_CONFIG.retryAttempts && (status === 'timeout' || xhr.status >= 500)) {
                        setTimeout(() => {
                            makeRequest(attempt + 1);
                        }, AJAX_CONFIG.retryDelay * attempt);
                    } else {
                        reject({ xhr, status, error, attempt });
                    }
                });
        }
        
        makeRequest();
    });
}

// Generic CRUD operations
class CrudOperations {
    constructor(baseUrl, entityName = 'Record') {
        this.baseUrl = baseUrl;
        this.entityName = entityName;
    }
    
    // Get all records with pagination
    async getAll(params = {}) {
        try {
            const response = await ajaxRequest({
                url: this.baseUrl,
                type: 'GET',
                data: params
            });
            
            return response;
        } catch (error) {
            showError(`Failed to load ${this.entityName.toLowerCase()}s`);
            throw error;
        }
    }
    
    // Get single record
    async getById(id) {
        try {
            const response = await ajaxRequest({
                url: this.baseUrl,
                type: 'GET',
                data: { action: 'get', id: id }
            });
            
            if (response.success) {
                return response.data;
            } else {
                showError(response.message);
                throw new Error(response.message);
            }
        } catch (error) {
            showError(`Failed to load ${this.entityName.toLowerCase()}`);
            throw error;
        }
    }
    
    // Create new record
    async create(data) {
        try {
            const formData = new FormData();
            
            // Handle different data types
            if (data instanceof FormData) {
                formData = data;
            } else if (typeof data === 'object') {
                Object.keys(data).forEach(key => {
                    if (data[key] !== null && data[key] !== undefined) {
                        formData.append(key, data[key]);
                    }
                });
            }
            
            const response = await ajaxRequest({
                url: this.baseUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false
            });
            
            if (response.success) {
                showSaveSuccess(this.entityName);
                return response;
            } else {
                if (response.errors) {
                    this.handleValidationErrors(response.errors);
                } else {
                    showError(response.message);
                }
                throw new Error(response.message);
            }
        } catch (error) {
            if (error.xhr && error.xhr.status === 422) {
                const response = error.xhr.responseJSON;
                if (response && response.errors) {
                    this.handleValidationErrors(response.errors);
                }
            } else {
                showError(`Failed to create ${this.entityName.toLowerCase()}`);
            }
            throw error;
        }
    }
    
    // Update existing record
    async update(id, data) {
        try {
            const formData = new FormData();
            formData.append('id', id);
            
            // Handle different data types
            if (data instanceof FormData) {
                for (let [key, value] of data.entries()) {
                    formData.append(key, value);
                }
            } else if (typeof data === 'object') {
                Object.keys(data).forEach(key => {
                    if (data[key] !== null && data[key] !== undefined) {
                        formData.append(key, data[key]);
                    }
                });
            }
            
            const response = await ajaxRequest({
                url: this.baseUrl,
                type: 'PUT',
                data: formData,
                processData: false,
                contentType: false
            });
            
            if (response.success) {
                showUpdateSuccess(this.entityName);
                return response;
            } else {
                if (response.errors) {
                    this.handleValidationErrors(response.errors);
                } else {
                    showError(response.message);
                }
                throw new Error(response.message);
            }
        } catch (error) {
            if (error.xhr && error.xhr.status === 422) {
                const response = error.xhr.responseJSON;
                if (response && response.errors) {
                    this.handleValidationErrors(response.errors);
                }
            } else {
                showError(`Failed to update ${this.entityName.toLowerCase()}`);
            }
            throw error;
        }
    }
    
    // Delete record
    async delete(id) {
        try {
            const confirmed = await this.confirmDelete();
            if (!confirmed) return false;
            
            const response = await ajaxRequest({
                url: this.baseUrl,
                type: 'DELETE',
                data: { id: id }
            });
            
            if (response.success) {
                showDeleteSuccess(this.entityName);
                return response;
            } else {
                showError(response.message);
                throw new Error(response.message);
            }
        } catch (error) {
            showError(`Failed to delete ${this.entityName.toLowerCase()}`);
            throw error;
        }
    }
    
    // Batch delete
    async batchDelete(ids) {
        try {
            const confirmed = await this.confirmBatchDelete(ids.length);
            if (!confirmed) return false;
            
            const response = await ajaxRequest({
                url: this.baseUrl,
                type: 'DELETE',
                data: { ids: ids, batch: true }
            });
            
            if (response.success) {
                showSuccess(`${ids.length} ${this.entityName.toLowerCase()}s deleted successfully`);
                return response;
            } else {
                showError(response.message);
                throw new Error(response.message);
            }
        } catch (error) {
            showError(`Failed to delete ${this.entityName.toLowerCase()}s`);
            throw error;
        }
    }
    
    // Handle validation errors
    handleValidationErrors(errors) {
        let errorMessage = 'Please fix the following errors:\n';
        Object.keys(errors).forEach(field => {
            errorMessage += `• ${errors[field]}\n`;
        });
        showValidationError(errorMessage);
    }
    
    // Confirmation dialogs
    async confirmDelete() {
        return new Promise((resolve) => {
            Swal.fire({
                title: `Delete ${this.entityName}?`,
                text: 'This action cannot be undone!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                resolve(result.isConfirmed);
            });
        });
    }
    
    async confirmBatchDelete(count) {
        return new Promise((resolve) => {
            Swal.fire({
                title: `Delete ${count} ${this.entityName.toLowerCase()}s?`,
                text: 'This action cannot be undone!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: `Yes, delete ${count} items!`,
                cancelButtonText: 'Cancel'
            }).then((result) => {
                resolve(result.isConfirmed);
            });
        });
    }
}

// Form handler class
class FormHandler {
    constructor(formSelector, apiUrl, options = {}) {
        this.form = $(formSelector);
        this.apiUrl = apiUrl;
        this.options = Object.assign({
            resetOnSuccess: true,
            hideModalOnSuccess: true,
            reloadTableOnSuccess: true,
            validateOnSubmit: true,
            showSuccessMessage: true
        }, options);
        
        this.init();
    }
    
    init() {
        this.form.on('submit', (e) => {
            e.preventDefault();
            this.handleSubmit();
        });
        
        // Real-time validation
        if (this.options.validateOnSubmit) {
            this.form.find('input, select, textarea').on('blur', (e) => {
                this.validateField($(e.target));
            });
        }
    }
    
    async handleSubmit() {
        try {
            // Clear previous errors
            this.clearErrors();
            
            // Validate form
            if (this.options.validateOnSubmit && !this.validateForm()) {
                return;
            }
            
            // Get form data
            const formData = new FormData(this.form[0]);
            
            // Determine method (POST for create, PUT for update)
            const method = formData.get('id') ? 'PUT' : 'POST';
            
            // Submit form
            const response = await ajaxRequest({
                url: this.apiUrl,
                type: method,
                data: formData,
                processData: false,
                contentType: false
            });
            
            if (response.success) {
                if (this.options.showSuccessMessage) {
                    showSuccess(response.message);
                }
                
                if (this.options.resetOnSuccess) {
                    this.resetForm();
                }
                
                if (this.options.hideModalOnSuccess) {
                    this.form.closest('.modal').modal('hide');
                }
                
                if (this.options.reloadTableOnSuccess && globalDataTable) {
                    globalDataTable.ajax.reload(null, false);
                }
                
                // Call custom success callback
                if (this.options.onSuccess) {
                    this.options.onSuccess(response);
                }
            } else {
                if (response.errors) {
                    this.displayErrors(response.errors);
                } else {
                    showError(response.message);
                }
            }
        } catch (error) {
            if (error.xhr && error.xhr.status === 422) {
                const response = error.xhr.responseJSON;
                if (response && response.errors) {
                    this.displayErrors(response.errors);
                }
            } else {
                showError('Failed to submit form');
            }
        }
    }
    
    validateForm() {
        let isValid = true;
        
        this.form.find('[required]').each((index, element) => {
            if (!this.validateField($(element))) {
                isValid = false;
            }
        });
        
        return isValid;
    }
    
    validateField(field) {
        const value = field.val().trim();
        const isRequired = field.prop('required');
        
        this.clearFieldError(field);
        
        if (isRequired && !value) {
            this.showFieldError(field, 'This field is required');
            return false;
        }
        
        // Email validation
        if (field.attr('type') === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                this.showFieldError(field, 'Please enter a valid email address');
                return false;
            }
        }
        
        // Phone validation
        if ((field.attr('type') === 'tel' || field.data('type') === 'phone') && value) {
            const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
            if (!phoneRegex.test(value.replace(/\s/g, ''))) {
                this.showFieldError(field, 'Please enter a valid phone number');
                return false;
            }
        }
        
        return true;
    }
    
    displayErrors(errors) {
        Object.keys(errors).forEach(field => {
            const input = this.form.find(`[name="${field}"]`);
            if (input.length) {
                this.showFieldError(input, errors[field]);
            }
        });
    }
    
    showFieldError(field, message) {
        field.addClass('is-invalid');
        field.after(`<div class="invalid-feedback">${message}</div>`);
    }
    
    clearFieldError(field) {
        field.removeClass('is-invalid');
        field.siblings('.invalid-feedback').remove();
    }
    
    clearErrors() {
        this.form.find('.is-invalid').removeClass('is-invalid');
        this.form.find('.invalid-feedback').remove();
    }
    
    resetForm() {
        this.form[0].reset();
        this.clearErrors();
        this.form.find('.select2').val(null).trigger('change');
    }
    
    populateForm(data) {
        Object.keys(data).forEach(key => {
            const input = this.form.find(`[name="${key}"]`);
            if (input.length) {
                if (input.is('select')) {
                    input.val(data[key]).trigger('change');
                } else if (input.attr('type') === 'checkbox') {
                    input.prop('checked', data[key] == 1);
                } else {
                    input.val(data[key]);
                }
            }
        });
    }
}

// DataTable handler class
class DataTableHandler {
    constructor(tableSelector, ajaxUrl, columns, options = {}) {
        this.table = $(tableSelector);
        this.ajaxUrl = ajaxUrl;
        this.columns = columns;
        this.options = options;
        this.dataTable = null;
        
        this.init();
    }
    
    init() {
        this.dataTable = initializeDataTable(this.table.selector, this.ajaxUrl, this.columns, this.options);
        return this.dataTable;
    }
    
    reload(resetPaging = false) {
        if (this.dataTable) {
            this.dataTable.ajax.reload(null, resetPaging);
        }
    }
    
    getSelectedRows() {
        if (this.dataTable) {
            return this.dataTable.rows('.selected').data().toArray();
        }
        return [];
    }
    
    getSelectedIds() {
        const selectedRows = this.getSelectedRows();
        return selectedRows.map(row => row.id);
    }
    
    clearSelection() {
        if (this.dataTable) {
            this.table.find('tbody tr').removeClass('selected');
        }
    }
    
    destroy() {
        if (this.dataTable) {
            this.dataTable.destroy();
            this.dataTable = null;
        }
    }
}

// Utility functions for common operations
const AjaxUtils = {
    // Load options for select elements
    async loadSelectOptions(selectSelector, url, params = {}) {
        try {
            const response = await ajaxRequest({
                url: url,
                type: 'GET',
                data: params,
                showLoader: false
            });
            
            if (response.success) {
                const select = $(selectSelector);
                select.empty();
                
                if (select.data('placeholder')) {
                    select.append(`<option value="">${select.data('placeholder')}</option>`);
                }
                
                response.data.forEach(item => {
                    select.append(`<option value="${item.id}">${item.name}</option>`);
                });
                
                select.trigger('change');
            }
        } catch (error) {
            console.error('Failed to load select options:', error);
        }
    },
    
    // Upload file with progress
    async uploadFile(file, url, onProgress = null) {
        return new Promise((resolve, reject) => {
            const formData = new FormData();
            formData.append('file', file);
            
            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function() {
                    const xhr = new window.XMLHttpRequest();
                    if (onProgress) {
                        xhr.upload.addEventListener('progress', function(evt) {
                            if (evt.lengthComputable) {
                                const percentComplete = (evt.loaded / evt.total) * 100;
                                onProgress(percentComplete);
                            }
                        }, false);
                    }
                    return xhr;
                },
                success: function(response) {
                    resolve(response);
                },
                error: function(xhr, status, error) {
                    reject({ xhr, status, error });
                }
            });
        });
    },
    
    // Export data
    async exportData(url, format = 'csv', params = {}) {
        try {
            const exportParams = Object.assign({ format: format }, params);
            
            const response = await ajaxRequest({
                url: url,
                type: 'GET',
                data: exportParams
            });
            
            if (response.success && response.data.download_url) {
                // Create temporary download link
                const link = document.createElement('a');
                link.href = response.data.download_url;
                link.download = response.data.filename || `export.${format}`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                showSuccess('Export completed successfully');
            } else {
                showError('Export failed');
            }
        } catch (error) {
            showError('Export failed');
        }
    }
};

// Global instances for easy access
window.CrudOperations = CrudOperations;
window.FormHandler = FormHandler;
window.DataTableHandler = DataTableHandler;
window.AjaxUtils = AjaxUtils;