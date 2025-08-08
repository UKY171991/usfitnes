/**
 * Test Orders JavaScript - AdminLTE3 with AJAX
 */

$(document).ready(function() {
    // Initialize DataTable with server-side processing
    window.testOrdersTable = $('#testOrdersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: 'api/test_orders_api.php',
            type: 'POST',
            data: function(d) {
                d.action = 'list';
            }
        },
        columns: [
            { 
                data: 'order_number',
                render: function(data, type, row) {
                    return '<strong>' + data + '</strong>';
                }
            },
            { 
                data: 'patient_name',
                defaultContent: 'N/A'
            },
            { 
                data: 'doctor_name',
                defaultContent: 'N/A'
            },
            { 
                data: 'test_type'
            },
            { 
                data: 'status',
                render: function(data, type, row) {
                    let badgeClass = 'secondary';
                    switch(data) {
                        case 'pending': badgeClass = 'warning'; break;
                        case 'in_progress': badgeClass = 'info'; break;
                        case 'completed': badgeClass = 'success'; break;
                        case 'cancelled': badgeClass = 'danger'; break;
                    }
                    return '<span class="badge badge-' + badgeClass + '">' + data.toUpperCase() + '</span>';
                }
            },
            {
                data: 'created_at',
                render: function(data, type, row) {
                    return new Date(data).toLocaleDateString();
                }
            },
            {
                data: null,
                orderable: false,
                width: '120px',
                render: function(data, type, row) {
                    return `
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-info btn-sm" onclick="viewTestOrder(${row.id})" title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-warning btn-sm" onclick="editTestOrder(${row.id})" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" onclick="deleteTestOrder(${row.id})" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true,
        language: {
            processing: '<i class="fas fa-spinner fa-spin"></i> Loading...'
        }
    });

    // Search functionality
    $('#searchInput').on('keyup', function() {
        testOrdersTable.search(this.value).draw();
    });

    // Status filter
    $('#statusFilter').on('change', function() {
        var status = this.value;
        if (status) {
            testOrdersTable.column(4).search(status).draw();
        } else {
            testOrdersTable.column(4).search('').draw();
        }
    });

    // Test type filter  
    $('#testTypeFilter').on('change', function() {
        var testType = this.value;
        if (testType) {
            testOrdersTable.column(3).search(testType).draw();
        } else {
            testOrdersTable.column(3).search('').draw();
        }
    });

    // Refresh button
    $('#refreshBtn').on('click', function() {
        testOrdersTable.ajax.reload(null, false);
        toastr.success('Test orders list refreshed');
    });
});

// Add new test order
function addTestOrder() {
    $.ajax({
        url: 'api/test_orders_api.php',
        type: 'POST',
        data: { action: 'add_form' },
        success: function(response) {
            $('#testOrderModal .modal-content').html(response);
            $('#testOrderModal').modal('show');
        },
        error: function(xhr, status, error) {
            toastr.error('Error loading form: ' + error);
        }
    });
}

// Edit test order
function editTestOrder(id) {
    $.ajax({
        url: 'api/test_orders_api.php',
        type: 'POST',
        data: { 
            action: 'edit_form',
            id: id 
        },
        success: function(response) {
            $('#testOrderModal .modal-content').html(response);
            $('#testOrderModal').modal('show');
        },
        error: function(xhr, status, error) {
            toastr.error('Error loading form: ' + error);
        }
    });
}

// View test order
function viewTestOrder(id) {
    $.ajax({
        url: 'api/test_orders_api.php',
        type: 'POST',
        data: { 
            action: 'view',
            id: id 
        },
        success: function(response) {
            $('#testOrderModal .modal-content').html(response);
            $('#testOrderModal').modal('show');
        },
        error: function(xhr, status, error) {
            toastr.error('Error loading test order details: ' + error);
        }
    });
}

// Save test order (called from form submit)
function saveTestOrder() {
    event.preventDefault();
    
    var formData = new FormData($('#testOrderForm')[0]);
    
    $.ajax({
        url: 'api/test_orders_api.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            try {
                var result = typeof response === 'string' ? JSON.parse(response) : response;
                
                if (result.success) {
                    $('#testOrderModal').modal('hide');
                    testOrdersTable.ajax.reload(null, false);
                    toastr.success(result.message);
                } else {
                    toastr.error(result.message || 'Error saving test order');
                }
            } catch (e) {
                toastr.error('Error processing response');
            }
        },
        error: function(xhr, status, error) {
            toastr.error('Error saving test order: ' + error);
        }
    });
    
    return false;
}

// Delete test order
function deleteTestOrder(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'api/test_orders_api.php',
                type: 'POST',
                data: { 
                    action: 'delete',
                    id: id 
                },
                success: function(response) {
                    try {
                        var result = typeof response === 'string' ? JSON.parse(response) : response;
                        
                        if (result.success) {
                            testOrdersTable.ajax.reload(null, false);
                            Swal.fire('Deleted!', result.message, 'success');
                        } else {
                            Swal.fire('Error!', result.message || 'Error deleting test order', 'error');
                        }
                    } catch (e) {
                        Swal.fire('Error!', 'Error processing response', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire('Error!', 'Error deleting test order: ' + error, 'error');
                }
            });
        }
    });
}

// Export functions
function exportTestOrders(format) {
    var searchValue = testOrdersTable.search();
    var url = 'api/test_orders_api.php?action=export&format=' + format;
    
    if (searchValue) {
        url += '&search=' + encodeURIComponent(searchValue);
    }
    
    window.open(url, '_blank');
    toastr.info('Export started...');
}

// Print test orders
function printTestOrders() {
    var searchValue = testOrdersTable.search();
    var url = 'api/test_orders_api.php?action=print';
    
    if (searchValue) {
        url += '&search=' + encodeURIComponent(searchValue);
    }
    
    var printWindow = window.open(url, '_blank');
    printWindow.onload = function() {
        printWindow.print();
    };
}

// Bulk actions
function handleBulkAction() {
    var action = $('#bulkAction').val();
    var selectedIds = [];
    
    $('input[name="order_ids[]"]:checked').each(function() {
        selectedIds.push($(this).val());
    });
    
    if (selectedIds.length === 0) {
        toastr.warning('Please select at least one test order');
        return;
    }
    
    if (action === '') {
        toastr.warning('Please select an action');
        return;
    }
    
    var confirmText = 'Are you sure you want to ' + action + ' ' + selectedIds.length + ' test order(s)?';
    
    Swal.fire({
        title: 'Confirm Action',
        text: confirmText,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, proceed!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'api/test_orders_api.php',
                type: 'POST',
                data: {
                    action: 'bulk_action',
                    bulk_action: action,
                    ids: selectedIds
                },
                success: function(response) {
                    try {
                        var result = typeof response === 'string' ? JSON.parse(response) : response;
                        
                        if (result.success) {
                            testOrdersTable.ajax.reload(null, false);
                            toastr.success(result.message);
                            $('#bulkAction').val('');
                            $('input[name="order_ids[]"]').prop('checked', false);
                        } else {
                            toastr.error(result.message || 'Error performing bulk action');
                        }
                    } catch (e) {
                        toastr.error('Error processing response');
                    }
                },
                error: function(xhr, status, error) {
                    toastr.error('Error performing bulk action: ' + error);
                }
            });
        }
    });
}

// Select all checkboxes
function toggleSelectAll(source) {
    $('input[name="order_ids[]"]').prop('checked', source.checked);
}

// Keyboard shortcuts
$(document).on('keydown', function(e) {
    // Ctrl+N for new test order
    if (e.ctrlKey && e.which === 78) {
        e.preventDefault();
        addTestOrder();
    }
    
    // F5 for refresh
    if (e.which === 116) {
        e.preventDefault();
        $('#refreshBtn').click();
    }
});

// Auto-refresh every 5 minutes
setInterval(function() {
    if (testOrdersTable) {
        testOrdersTable.ajax.reload(null, false);
    }
}, 300000);
