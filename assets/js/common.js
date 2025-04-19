// Function to load form content into modal
async function loadForm(formPath, title, params = {}) {
    try {
        // Build query string from params
        const queryString = Object.keys(params)
            .map(key => `${encodeURIComponent(key)}=${encodeURIComponent(params[key])}`)
            .join('&');
        
        const url = queryString ? `${formPath}?${queryString}` : formPath;
        
        const response = await fetch(url);
        const html = await response.text();
        
        showModal(title, html);
    } catch (error) {
        console.error('Error loading form:', error);
        showErrorToast('Failed to load form. Please try again.');
    }
}

// Function to refresh DataTable
function refreshTable() {
    if ($.fn.DataTable.isDataTable('#dataTable')) {
        $('#dataTable').DataTable().ajax.reload();
    }
}

// Function to delete record
async function deleteRecord(url, id) {
    if (!confirm('Are you sure you want to delete this record?')) {
        return;
    }

    try {
        const response = await fetch(`${url}?id=${id}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            showSuccessToast('Record deleted successfully');
            refreshTable();
        } else {
            showErrorToast(result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        showErrorToast('An error occurred while deleting the record.');
    }
}

// Function to format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR'
    }).format(amount);
}

// Function to format date
function formatDate(date) {
    return new Date(date).toLocaleDateString('en-IN', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// Function to format datetime
function formatDateTime(datetime) {
    return new Date(datetime).toLocaleString('en-IN', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Initialize DataTables with common settings
function initDataTable(selector, options = {}) {
    const defaultOptions = {
        processing: true,
        serverSide: true,
        responsive: true,
        pageLength: 25,
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
        },
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        buttons: [
            {
                extend: 'collection',
                text: 'Export',
                buttons: ['copy', 'excel', 'pdf', 'print']
            }
        ]
    };

    return $(selector).DataTable({...defaultOptions, ...options});
}

// Initialize Select2 with common settings
function initSelect2(selector, options = {}) {
    const defaultOptions = {
        theme: 'bootstrap-5',
        width: '100%'
    };

    return $(selector).select2({...defaultOptions, ...options});
}

// Initialize date picker with common settings
function initDatePicker(selector, options = {}) {
    const defaultOptions = {
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true
    };

    return $(selector).datepicker({...defaultOptions, ...options});
}

// Function to handle form validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;

    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return false;
    }

    return true;
}

// Function to serialize form data to JSON
function serializeForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return null;

    const formData = new FormData(form);
    const data = {};
    
    for (const [key, value] of formData.entries()) {
        if (data[key]) {
            if (!Array.isArray(data[key])) {
                data[key] = [data[key]];
            }
            data[key].push(value);
        } else {
            data[key] = value;
        }
    }

    return data;
}

// Function to handle AJAX form submission
async function submitForm(formId, url, method = 'POST') {
    if (!validateForm(formId)) return;

    const data = serializeForm(formId);
    if (!data) return;

    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            showSuccessToast(result.message || 'Record saved successfully');
            bootstrap.Modal.getInstance(document.getElementById('genericModal')).hide();
            if (typeof refreshTable === 'function') {
                refreshTable();
            }
            return true;
        } else {
            showErrorToast(result.message);
            return false;
        }
    } catch (error) {
        console.error('Error:', error);
        showErrorToast('An error occurred while saving the record.');
        return false;
    }
} 