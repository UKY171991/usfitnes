// Test Orders Management JavaScript
// AdminLTE3 Template with AJAX Operations

let testOrdersTable;
let testOrdersCrud;
let testOrdersFormHandler;

$(document).ready(function() {
    initializeTestOrdersPage();
});

function initializeTestOrdersPage() {
    // Initialize CRUD operations
    testOrdersCrud = new CrudOperations('api/test_orders_api.php', 'Test Order');
    
    // Initialize form handler
    testOrdersFormHandler = new FormHandler('#testOrderForm', 'api/test_orders_api.php', {
        onSuccess: function(response) {
            $('#testOrderModal').modal('hide');
            testOrdersTable.ajax.reload(null, false);
            showSuccess(response.message);
        }
    });
    
    // Initialize DataTable
    initializeTestOrdersTable();
    
    // Initialize filters
    initializeFilters();
    
    // Load dropdown options
    loadDropdownOptions();
}

function initializeTestOrdersTable() {
    const columns = [
        {
            data: 'order_number',
            name: 'order_number',
            title: 'Order #',
            width: '120px',
            render: function(data, type, row) {
                return `<strong>${data}</strong>`;
            }
        },
        {
            data: 'patient_name',
            name: 'patient_name',
            title: 'Patient',
            render: function(data, type, row) {
                return `<strong>${data}</strong>`;
            }
        },
        {
            data: 'doctor_name',
            name: 'doctor_name',
            title: 'Doctor',
            render: function(data, type, row) {
                return data || '-';
            }
        },
        {
            data: 'test_count',
            name: 'test_count',
            title: 'Tests',
            render: function(data, type, row) {
                return `<span class="badge badge-info">${data} test(s)</span>`;
            }
        },
        {
            data: 'status',
            name: 'status',
            title: 'Status',
            render: function(data, type, row) {
                const statusClass = getStatusClass(data);
                return `<span class="badge badge-${statusClass}">${capitalizeFirst(data.replace('_', ' '))}</span>`;
            }
        },
        {
            data: 'priority',
            name: 'priority',
            title: 'Priority',
            render: function(data, type, row) {
                const priorityClass = getPriorityClass(data);
                return `<span class="badge badge-${priorityClass}">${capitalizeFirst(data)}</span>`;
            }
        },
        {
            data: 'created_at',
            name: 'created_at',
            title: 'Date',
            render: function(data, type, row) {
                return formatDate(data);
            }
        },
        {
            data: null,
            name: 'actions',
            title: 'Actions',
            orderable: false,
            searchable: false,
            width: '150px',
            render: function(data, type, row) {
                return `
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-info btn-action" onclick="viewTestOrder(${row.id})" title="View">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="btn btn-warning btn-action" onclick="editTestOrder(${row.id})" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-action" onclick="deleteTestOrder(${row.id})" title="Cancel">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
            }
        }
    ];

    testOrdersTable = initializeDataTable('#testOrdersTable', 'ajax/test_orders_datatable.php', columns, {
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true,
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        buttons: [
            {
                extend: 'csv',
                text: '<i class="fas fa-file-csv"></i> CSV',
                className: 'btn btn-success btn-sm'
            },
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-success btn-sm'
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn btn-danger btn-sm'
            }
        ]
    });
    
    // Store reference globally
    globalDataTable = testOrdersTable;
}

function initializeFilters() {
    $('#statusFilter, #priorityFilter').on('change', function() {
        applyFilters();
    });
    
    $('#dateFilter').on('change', function() {
        applyFilters();
    });
}

function loadDropdownOptions() {
    // Load patients
    loadSelectOptions('select[name="patient_id"]', 'api/patients_api.php?action=list');
    
    // Load doctors
    loadSelectOptions('select[name="doctor_id"]', 'api/doctors_api.php?action=list');
    
    // Load tests
    loadSelectOptions('select[name="tests[]"]', 'api/tests_api.php?action=list');
}

async function loadSelectOptions(selectSelector, url) {
    try {
        const response = await $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json'
        });
        
        if (response.success && response.data) {
            const select = $(selectSelector);
            
            // Don't clear if it's the tests multiple select
            if (!select.prop('multiple')) {
                select.empty();
                if (select.data('placeholder') || selectSelector.includes('patient_id')) {
                    select.append('<option value="">Select Patient</option>');
                } else if (selectSelector.includes('doctor_id')) {
                    select.append('<option value="">Select Doctor</option>');
                }
            }
            
            response.data.forEach(item => {
                if (selectSelector.includes('tests')) {
                    select.append(`<option value="${item.id}">${item.name} - $${item.price}</option>`);
                } else {
                    select.append(`<option value="${item.id}">${item.name}</option>`);
                }
            });
            
            select.trigger('change');
        }
    } catch (error) {
        console.error('Failed to load select options:', error);
        // Add some default options for testing
        const select = $(selectSelector);
        if (selectSelector.includes('patient_id')) {
            select.append('<option value="1">John Doe</option>');
            select.append('<option value="2">Jane Smith</option>');
        } else if (selectSelector.includes('doctor_id')) {
            select.append('<option value="1">Dr. Johnson</option>');
            select.append('<option value="2">Dr. Williams</option>');
        } else if (selectSelector.includes('tests')) {
            select.append('<option value="1">Complete Blood Count - $25</option>');
            select.append('<option value="2">Blood Sugar Test - $15</option>');
        }
    }
}

function getCustomFilters() {
    return {
        status: $('#statusFilter').val(),
        priority: $('#priorityFilter').val(),
        order_date: $('#dateFilter').val()
    };
}

function applyFilters() {
    if (testOrdersTable) {
        testOrdersTable.ajax.reload();
    }
}

function clearFilters() {
    $('#statusFilter, #priorityFilter, #dateFilter').val('').trigger('change');
    applyFilters();
}

// Modal Functions
function showAddTestOrderModal() {
    resetForm('#testOrderForm');
    $('#testOrderId').val('');
    $('#testOrderModal .modal-title').text('Create New Test Order');
    
    // Set default order date
    const now = new Date();
    const dateString = now.toISOString().slice(0, 16);
    $('input[name="order_date"]').val(dateString);
    
    $('#testOrderModal').modal('show');
}

async function editTestOrder(id) {
    try {
        const testOrder = await testOrdersCrud.getById(id);
        
        // Populate form
        testOrdersFormHandler.populateForm(testOrder);
        
        $('#testOrderModal .modal-title').text('Edit Test Order');
        $('#testOrderModal').modal('show');
    } catch (error) {
        showError('Failed to load test order data');
    }
}

async function viewTestOrder(id) {
    try {
        showLoader('Loading test order details...');
        const testOrder = await testOrdersCrud.getById(id);
        
        const detailsHtml = `
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Order Number:</th>
                            <td><strong>${testOrder.order_number}</strong></td>
                        </tr>
                        <tr>
                            <th>Patient:</th>
                            <td><strong>${testOrder.patient_name}</strong></td>
                        </tr>
                        <tr>
                            <th>Doctor:</th>
                            <td>${testOrder.doctor_name || 'N/A'}</td>
                        </tr>
                        <tr>
                            <th>Priority:</th>
                            <td><span class="badge badge-${getPriorityClass(testOrder.priority)}">${capitalizeFirst(testOrder.priority)}</span></td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td><span class="badge badge-${getStatusClass(testOrder.status)}">${capitalizeFirst(testOrder.status.replace('_', ' '))}</span></td>
                        </tr>
                        <tr>
                            <th>Order Date:</th>
                            <td>${formatDateTime(testOrder.order_date)}</td>
                        </tr>
                        <tr>
                            <th>Created:</th>
                            <td>${formatDateTime(testOrder.created_at)}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Tests:</th>
                            <td>
                                ${testOrder.tests ? testOrder.tests.map(test => 
                                    `<span class="badge badge-info mr-1">${test.name}</span>`
                                ).join('') : 'N/A'}
                            </td>
                        </tr>
                        <tr>
                            <th>Total Amount:</th>
                            <td><strong>${formatCurrency(testOrder.total_amount || 0)}</strong></td>
                        </tr>
                        <tr>
                            <th>Discount:</th>
                            <td>${formatCurrency(testOrder.discount || 0)}</td>
                        </tr>
                        <tr>
                            <th>Final Amount:</th>
                            <td><strong class="text-success">${formatCurrency((testOrder.total_amount || 0) - (testOrder.discount || 0))}</strong></td>
                        </tr>
                        <tr>
                            <th>Notes:</th>
                            <td>${testOrder.notes || 'N/A'}</td>
                        </tr>
                    </table>
                </div>
            </div>
        `;
        
        $('#testOrderDetails').html(detailsHtml);
        $('#viewTestOrderModal').modal('show');
        
    } catch (error) {
        showError('Failed to load test order details');
    } finally {
        hideLoader();
    }
}

async function deleteTestOrder(id) {
    try {
        const confirmed = await Swal.fire({
            title: 'Cancel Test Order?',
            text: 'This will cancel the test order. This action cannot be undone!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, cancel it!',
            cancelButtonText: 'No, keep it'
        });
        
        if (confirmed.isConfirmed) {
            await testOrdersCrud.delete(id);
            testOrdersTable.ajax.reload(null, false);
        }
    } catch (error) {
        // Error handling is done in CrudOperations class
    }
}

// Export Functions
function exportTestOrders() {
    const format = 'csv'; // Can be made dynamic
    const filters = getCustomFilters();
    
    AjaxUtils.exportData('api/test_orders_api.php?action=export', format, filters);
}

// Utility Functions
function getStatusClass(status) {
    const statusClasses = {
        'pending': 'warning',
        'in_progress': 'info',
        'completed': 'success',
        'cancelled': 'danger'
    };
    return statusClasses[status] || 'secondary';
}

function getPriorityClass(priority) {
    const priorityClasses = {
        'normal': 'secondary',
        'high': 'warning',
        'urgent': 'danger'
    };
    return priorityClasses[priority] || 'secondary';
}

function resetTestOrderForm() {
    resetForm('#testOrderForm');
}

// Global functions for external access
window.showAddTestOrderModal = showAddTestOrderModal;
window.editTestOrder = editTestOrder;
window.viewTestOrder = viewTestOrder;
window.deleteTestOrder = deleteTestOrder;
window.exportTestOrders = exportTestOrders;
window.applyFilters = applyFilters;
window.clearFilters = clearFilters;