/**
 * PathLab Pro - Main Application JavaScript
 * Modern AJAX CRUD Operations with AdminLTE 3
 */

// Global Application Object
const App = {
    apiUrl: 'api/',
    ajaxUrl: 'ajax/',
    
    // Initialize application
    init() {
        this.setupGlobalEvents();
        this.loadCounts();
    },

    // Setup global event listeners
    setupGlobalEvents() {
        // Form submissions
        $(document).on('submit', '.ajax-form', this.handleFormSubmit);
        
        // Delete buttons
        $(document).on('click', '.delete-btn', this.handleDelete);
        
        // Edit buttons
        $(document).on('click', '.edit-btn', this.handleEdit);
        
        // View buttons
        $(document).on('click', '.view-btn', this.handleView);
    },

    // Handle AJAX form submissions
    handleFormSubmit(e) {
        e.preventDefault();
        const form = $(this);
        const formData = new FormData(form[0]);
        const url = form.attr('action') || form.data('action');
        
        if (!url) {
            toastr.error('Form action URL not specified');
            return;
        }

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message || 'Operation completed successfully');
                    
                    // Close modal if exists
                    form.closest('.modal').modal('hide');
                    
                    // Reload DataTable if exists
                    if ($.fn.DataTable && $.fn.DataTable.isDataTable('.data-table')) {
                        $('.data-table').DataTable().ajax.reload();
                    }
                    
                    // Reset form
                    form[0].reset();
                    form.find('.is-invalid').removeClass('is-invalid');
                    
                } else {
                    toastr.error(response.message || 'Operation failed');
                    
                    // Handle validation errors
                    if (response.errors) {
                        App.showValidationErrors(form, response.errors);
                    }
                }
            },
            error: function(xhr) {
                let message = 'An error occurred';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                toastr.error(message);
            }
        });
    },

    // Handle delete operations
    handleDelete(e) {
        e.preventDefault();
        const button = $(this);
        const id = button.data('id');
        const url = button.data('url');
        const name = button.data('name') || 'record';

        if (!id || !url) {
            toastr.error('Delete operation parameters not specified');
            return;
        }

        Swal.fire({
            title: 'Are you sure?',
            text: `This will permanently delete the ${name}!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: { action: 'delete', id: id },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message || `${name} deleted successfully`);
                            
                            // Reload DataTable
                            if ($.fn.DataTable && $.fn.DataTable.isDataTable('.data-table')) {
                                $('.data-table').DataTable().ajax.reload();
                            }
                        } else {
                            toastr.error(response.message || 'Delete operation failed');
                        }
                    },
                    error: function() {
                        toastr.error('Failed to delete ' + name);
                    }
                });
            }
        });
    },

    // Handle edit operations
    handleEdit(e) {
        e.preventDefault();
        const button = $(this);
        const id = button.data('id');
        const url = button.data('url');
        const modal = button.data('modal') || '#editModal';

        if (!id || !url) {
            toastr.error('Edit operation parameters not specified');
            return;
        }

        $.ajax({
            url: url,
            type: 'POST',
            data: { action: 'get', id: id },
            success: function(response) {
                if (response.success && response.data) {
                    App.populateForm(modal + ' form', response.data);
                    $(modal).modal('show');
                    $(modal + ' .modal-title').text('Edit Record');
                } else {
                    toastr.error(response.message || 'Failed to load record data');
                }
            },
            error: function() {
                toastr.error('Failed to load record for editing');
            }
        });
    },

    // Handle view operations
    handleView(e) {
        e.preventDefault();
        const button = $(this);
        const id = button.data('id');
        const url = button.data('url');
        const modal = button.data('modal') || '#viewModal';

        if (!id || !url) {
            toastr.error('View operation parameters not specified');
            return;
        }

        $.ajax({
            url: url,
            type: 'POST',
            data: { action: 'get', id: id },
            success: function(response) {
                if (response.success && response.data) {
                    App.populateViewModal(modal, response.data);
                    $(modal).modal('show');
                } else {
                    toastr.error(response.message || 'Failed to load record data');
                }
            },
            error: function() {
                toastr.error('Failed to load record details');
            }
        });
    },

    // Initialize DataTable with standard configuration
    initDataTable(selector, ajaxUrl, columns, options = {}) {
        const defaultOptions = {
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: ajaxUrl,
                type: 'POST',
                error: function(xhr) {
                    toastr.error('Failed to load data');
                }
            },
            columns: columns,
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            order: [[0, 'desc']],
            language: {
                processing: '<div class="d-flex justify-content-center"><div class="spinner-border" role="status"></div></div>',
                emptyTable: 'No data available',
                zeroRecords: 'No matching records found'
            },
            drawCallback: function() {
                // Initialize tooltips
                $('[data-toggle="tooltip"]').tooltip();
            }
        };

        return $(selector).DataTable($.extend(true, defaultOptions, options));
    },

    // Populate form with data
    populateForm(formSelector, data) {
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

        // Set hidden ID field
        form.find('input[name="id"]').val(data.id || '');
    },

    // Populate view modal with data
    populateViewModal(modalSelector, data) {
        const modal = $(modalSelector);
        
        Object.keys(data).forEach(key => {
            const element = modal.find(`#view-${key}`);
            if (element.length) {
                if (key.includes('date') && data[key]) {
                    element.text(new Date(data[key]).toLocaleDateString());
                } else if (key === 'status') {
                    const badgeClass = data[key] === 'active' ? 'success' : 'secondary';
                    element.html(`<span class="badge badge-${badgeClass}">${data[key]}</span>`);
                } else {
                    element.text(data[key] || 'N/A');
                }
            }
        });
    },

    // Show form validation errors
    showValidationErrors(form, errors) {
        // Clear previous errors
        form.find('.is-invalid').removeClass('is-invalid');
        form.find('.invalid-feedback').text('');

        // Show new errors
        Object.keys(errors).forEach(field => {
            const input = form.find(`[name="${field}"]`);
            if (input.length) {
                input.addClass('is-invalid');
                const feedback = input.closest('.form-group').find('.invalid-feedback');
                if (feedback.length) {
                    feedback.text(errors[field]);
                }
            }
        });
    },

    // Reset form
    resetForm(formSelector) {
        const form = $(formSelector);
        form[0].reset();
        form.find('.is-invalid').removeClass('is-invalid');
        form.find('input[name="id"]').val('');
    },

    // Load dashboard counts
    loadCounts() {
        $.get('api/dashboard_counts.php')
            .done(function(response) {
                if (response.success) {
                    $('#patients-count').text(response.data.patients || 0);
                    $('#doctors-count').text(response.data.doctors || 0);
                    $('#equipment-count').text(response.data.equipment || 0);
                    $('#orders-count').text(response.data.orders || 0);
                }
            })
            .fail(function() {
                console.log('Failed to load counts');
            });
    },

    // Utility: Format date
    formatDate(dateString) {
        if (!dateString) return 'N/A';
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    },

    // Utility: Format datetime
    formatDateTime(dateString) {
        if (!dateString) return 'N/A';
        return new Date(dateString).toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    },

    // Utility: Calculate age
    calculateAge(birthDate) {
        if (!birthDate) return 'N/A';
        const today = new Date();
        const birth = new Date(birthDate);
        const age = today.getFullYear() - birth.getFullYear();
        const monthDiff = today.getMonth() - birth.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
            return age - 1;
        }
        return age;
    }
};

// Initialize app when document is ready
$(document).ready(function() {
    App.init();
});

// Global functions for backward compatibility
function openAddModal(modalId = '#addModal') {
    App.resetForm(modalId + ' form');
    $(modalId + ' .modal-title').text('Add New Record');
    $(modalId).modal('show');
}

function refreshTable() {
    if ($.fn.DataTable && $.fn.DataTable.isDataTable('.data-table')) {
        $('.data-table').DataTable().ajax.reload();
        toastr.info('Table refreshed');
    }
}
