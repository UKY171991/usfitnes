// Global JavaScript functions for PathLab Pro
// AdminLTE3 Template with AJAX Operations

// Global variables
let currentPage = 1;
let itemsPerPage = 10;
let currentSearch = '';
let currentSort = '';
let currentOrder = 'asc';
let globalDataTable = null;

// Initialize global components
function initializeGlobalComponents() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Initialize popovers
    $('[data-toggle="popover"]').popover();
    
    // Initialize select2
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });
    
    // Initialize date pickers
    $('.datepicker').datetimepicker({
        format: 'YYYY-MM-DD',
        icons: {
            time: 'far fa-clock',
            date: 'far fa-calendar',
            up: 'fas fa-arrow-up',
            down: 'fas fa-arrow-down',
            previous: 'fas fa-chevron-left',
            next: 'fas fa-chevron-right',
            today: 'far fa-calendar-check-o',
            clear: 'far fa-trash',
            close: 'far fa-times'
        }
    });
    
    // Initialize datetime pickers
    $('.datetimepicker').datetimepicker({
        format: 'YYYY-MM-DD HH:mm',
        icons: {
            time: 'far fa-clock',
            date: 'far fa-calendar',
            up: 'fas fa-arrow-up',
            down: 'fas fa-arrow-down',
            previous: 'fas fa-chevron-left',
            next: 'fas fa-chevron-right',
            today: 'far fa-calendar-check-o',
            clear: 'far fa-trash',
            close: 'far fa-times'
        }
    });
    
    // Global AJAX setup
    $.ajaxSetup({
        beforeSend: function(xhr, settings) {
            if (settings.showLoader !== false) {
                showLoader();
            }
        },
        complete: function() {
            hideLoader();
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            hideLoader();
            
            if (xhr.status === 401) {
                showToast('error', 'Session expired. Please login again.');
                setTimeout(() => window.location.href = 'login.php', 2000);
            } else if (xhr.status === 403) {
                showToast('error', 'Access denied');
            } else if (xhr.status === 422) {
                const response = xhr.responseJSON;
                if (response && response.message) {
                    showToast('error', response.message);
                } else {
                    showToast('error', 'Validation error occurred');
                }
            } else {
                showToast('error', 'An error occurred. Please try again.');
            }
        }
    });
    
    // Initialize form validation
    initializeFormValidation();
    
    // Initialize modal handlers
    initializeModalHandlers();
}

// Enhanced DataTable initialization
function initializeDataTable(tableId, ajaxUrl, columns, options = {}) {
    const defaultOptions = {
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: ajaxUrl,
            type: 'POST',
            data: function(d) {
                d.search_value = d.search.value;
                d.length = d.length;
                d.start = d.start;
                d.order_column = d.columns[d.order[0].column].data;
                d.order_dir = d.order[0].dir;
                
                // Add custom filters
                if (typeof getCustomFilters === 'function') {
                    Object.assign(d, getCustomFilters());
                }
            },
            error: function(xhr, error, thrown) {
                console.error('DataTable AJAX error:', error);
                showToast('error', 'Failed to load data');
            }
        },
        columns: columns,
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>',
            emptyTable: 'No data available',
            zeroRecords: 'No matching records found',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ entries',
            infoEmpty: 'Showing 0 to 0 of 0 entries',
            infoFiltered: '(filtered from _MAX_ total entries)',
            search: 'Search:',
            paginate: {
                first: 'First',
                last: 'Last',
                next: 'Next',
                previous: 'Previous'
            }
        },
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [[0, 'desc']],
        drawCallback: function(settings) {
            // Reinitialize tooltips after table redraw
            $('[data-toggle="tooltip"]').tooltip();
        }
    };
    
    // Merge custom options
    const finalOptions = Object.assign({}, defaultOptions, options);
    
    // Initialize DataTable
    globalDataTable = $(tableId).DataTable(finalOptions);
    
    return globalDataTable;
}

// Enhanced modal functions
function showModal(modalId, title = '', size = '') {
    const modal = $(modalId);
    if (title) {
        modal.find('.modal-title').text(title);
    }
    if (size) {
        modal.find('.modal-dialog').removeClass('modal-sm modal-lg modal-xl').addClass(size);
    }
    modal.modal('show');
}

function hideModal(modalId) {
    $(modalId).modal('hide');
}

function resetForm(formId) {
    const form = $(formId);
    form[0].reset();
    form.find('.is-invalid').removeClass('is-invalid');
    form.find('.invalid-feedback').remove();
    form.find('.select2').val(null).trigger('change');
}

// Enhanced AJAX form submission
function submitForm(formId, url, method = 'POST', callback = null) {
    const form = $(formId);
    const formData = new FormData(form[0]);
    
    // Clear previous validation errors
    form.find('.is-invalid').removeClass('is-invalid');
    form.find('.invalid-feedback').remove();
    
    $.ajax({
        url: url,
        type: method,
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                showToast('success', response.message);
                if (callback) {
                    callback(response);
                } else {
                    // Default behavior: hide modal and refresh table
                    $('.modal').modal('hide');
                    if (globalDataTable) {
                        globalDataTable.ajax.reload(null, false);
                    }
                }
            } else {
                if (response.errors) {
                    displayFormErrors(formId, response.errors);
                } else {
                    showToast('error', response.message);
                }
            }
        },
        error: function(xhr) {
            if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                displayFormErrors(formId, xhr.responseJSON.errors);
            } else {
                showToast('error', 'Failed to submit form');
            }
        }
    });
}

// Display form validation errors
function displayFormErrors(formId, errors) {
    const form = $(formId);
    
    Object.keys(errors).forEach(field => {
        const input = form.find(`[name="${field}"]`);
        if (input.length) {
            input.addClass('is-invalid');
            input.after(`<div class="invalid-feedback">${errors[field]}</div>`);
        }
    });
}

// Enhanced delete function with SweetAlert2
function deleteRecord(id, url, title = 'Delete Record', text = 'Are you sure you want to delete this record?') {
    Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: url,
                type: 'DELETE',
                data: { id: id },
                success: function(response) {
                    if (response.success) {
                        showToast('success', response.message);
                        if (globalDataTable) {
                            globalDataTable.ajax.reload(null, false);
                        }
                    } else {
                        showToast('error', response.message);
                    }
                },
                error: function() {
                    showToast('error', 'Failed to delete record');
                }
            });
        }
    });
}

// Load data for edit modal
function loadEditData(id, url, modalId, callback = null) {
    $.ajax({
        url: url,
        type: 'GET',
        data: { action: 'get', id: id },
        success: function(response) {
            if (response.success) {
                if (callback) {
                    callback(response.data);
                } else {
                    populateForm(modalId + ' form', response.data);
                }
                showModal(modalId, 'Edit Record');
            } else {
                showToast('error', response.message);
            }
        },
        error: function() {
            showToast('error', 'Failed to load record data');
        }
    });
}

// Populate form with data
function populateForm(formSelector, data) {
    const form = $(formSelector);
    
    Object.keys(data).forEach(key => {
        const input = form.find(`[name="${key}"]`);
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

// Form validation initialization
function initializeFormValidation() {
    // Real-time validation
    $(document).on('blur', 'input[required], select[required], textarea[required]', function() {
        validateField($(this));
    });
    
    $(document).on('input', 'input[type="email"]', function() {
        validateEmailField($(this));
    });
    
    $(document).on('input', 'input[type="tel"], input[data-type="phone"]', function() {
        validatePhoneField($(this));
    });
}

// Field validation
function validateField(field) {
    const value = field.val().trim();
    const isRequired = field.prop('required');
    
    field.removeClass('is-invalid');
    field.siblings('.invalid-feedback').remove();
    
    if (isRequired && !value) {
        field.addClass('is-invalid');
        field.after('<div class="invalid-feedback">This field is required</div>');
        return false;
    }
    
    return true;
}

// Email validation
function validateEmailField(field) {
    const email = field.val().trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    field.removeClass('is-invalid');
    field.siblings('.invalid-feedback').remove();
    
    if (email && !emailRegex.test(email)) {
        field.addClass('is-invalid');
        field.after('<div class="invalid-feedback">Please enter a valid email address</div>');
        return false;
    }
    
    return true;
}

// Phone validation
function validatePhoneField(field) {
    const phone = field.val().trim();
    const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
    
    field.removeClass('is-invalid');
    field.siblings('.invalid-feedback').remove();
    
    if (phone && !phoneRegex.test(phone.replace(/\s/g, ''))) {
        field.addClass('is-invalid');
        field.after('<div class="invalid-feedback">Please enter a valid phone number</div>');
        return false;
    }
    
    return true;
}

// Modal handlers initialization
function initializeModalHandlers() {
    // Reset form when modal is hidden
    $('.modal').on('hidden.bs.modal', function() {
        const form = $(this).find('form');
        if (form.length) {
            resetForm(form);
        }
    });
    
    // Focus first input when modal is shown
    $('.modal').on('shown.bs.modal', function() {
        $(this).find('input:not([readonly]):not([disabled]):first').focus();
    });
}

// Show/Hide loader
function showLoader(message = 'Loading...') {
    if ($('#globalLoader').length === 0) {
        $('body').append(`
            <div id="globalLoader" class="global-loader">
                <div class="loader-content">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <div class="mt-2 loader-message">${message}</div>
                </div>
            </div>
        `);
    } else {
        $('#globalLoader .loader-message').text(message);
    }
    $('#globalLoader').show();
}

function hideLoader() {
    $('#globalLoader').hide();
}

// Utility functions
function formatDate(dateString, format = 'YYYY-MM-DD') {
    if (!dateString) return '';
    return moment(dateString).format(format);
}

function formatDateTime(dateString, format = 'YYYY-MM-DD HH:mm') {
    if (!dateString) return '';
    return moment(dateString).format(format);
}

function formatCurrency(amount, currency = '$') {
    if (isNaN(amount)) return currency + '0.00';
    return currency + parseFloat(amount).toFixed(2);
}

function capitalizeFirst(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function truncateText(text, length = 50) {
    if (!text || text.length <= length) return text;
    return text.substring(0, length) + '...';
}

function generateRandomId(length = 8) {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let result = '';
    for (let i = 0; i < length; i++) {
        result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return result;
}

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

// Export functions
function exportTableData(format = 'csv', filename = 'export') {
    if (!globalDataTable) {
        showToast('error', 'No data table found');
        return;
    }
    
    const buttons = globalDataTable.buttons();
    if (format === 'csv') {
        buttons.exportData({ format: 'csv' });
    } else if (format === 'excel') {
        buttons.exportData({ format: 'excel' });
    } else if (format === 'pdf') {
        buttons.exportData({ format: 'pdf' });
    }
}

// Print function
function printElement(elementId) {
    const printContents = document.getElementById(elementId).innerHTML;
    const originalContents = document.body.innerHTML;
    
    document.body.innerHTML = `
        <html>
        <head>
            <title>Print</title>
            <style>
                body { font-family: Arial, sans-serif; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
            </style>
        </head>
        <body>${printContents}</body>
        </html>
    `;
    
    window.print();
    document.body.innerHTML = originalContents;
    location.reload();
}