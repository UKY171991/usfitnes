// Initialize DataTable for test requests
let requestsTable;

document.addEventListener('DOMContentLoaded', function() {
    requestsTable = $('#requestsTable').DataTable({
        responsive: true,
        order: [[4, 'desc']], // Sort by request date descending
        columns: [
            { data: 'request_id' },
            { 
                data: null,
                render: function(data) {
                    return `${data.first_name} ${data.last_name}`;
                }
            },
            { data: 'test_name' },
            { data: 'ordered_by' },
            { 
                data: 'created_at',
                render: function(data) {
                    return new Date(data).toLocaleString();
                }
            },
            { 
                data: 'status',
                render: function(data) {
                    let badgeClass = {
                        'Pending': 'warning',
                        'In Progress': 'primary',
                        'Completed': 'success'
                    }[data] || 'secondary';
                    return `<span class="badge bg-${badgeClass}">${data}</span>`;
                }
            },
            {
                data: 'priority',
                render: function(data) {
                    let badgeClass = data === 'Urgent' ? 'danger' : 'info';
                    return `<span class="badge bg-${badgeClass}">${data}</span>`;
                }
            },
            {
                data: null,
                render: function(data) {
                    let buttons = `<button class="btn btn-sm btn-primary me-1" onclick="editRequest(${data.request_id})">
                        <i class="bi bi-pencil"></i>
                    </button>`;
                    
                    if (data.can_delete) {
                        buttons += `<button class="btn btn-sm btn-danger" onclick="deleteRequest(${data.request_id})">
                            <i class="bi bi-trash"></i>
                        </button>`;
                    }
                    
                    return buttons;
                }
            }
        ]
    });

    // Load initial data
    loadRequests();

    // Add request form submission
    $('#addRequestForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('csrf_token', csrfToken);

        fetch('includes/process_request.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            
            $('#addRequestModal').modal('hide');
            showAlert('success', 'Test request added successfully');
            loadRequests();
            this.reset();
        })
        .catch(error => {
            showAlert('danger', error.message);
        });
    });

    // Update request form submission
    $('#updateRequestForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('csrf_token', csrfToken);
        formData.append('action', 'update');

        fetch('includes/process_request.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            
            $('#updateRequestModal').modal('hide');
            showAlert('success', 'Test request updated successfully');
            loadRequests();
        })
        .catch(error => {
            showAlert('danger', error.message);
        });
    });
});

// Load all test requests
function loadRequests() {
    fetch('includes/fetch_requests.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            
            requestsTable.clear().rows.add(data).draw();
        })
        .catch(error => {
            showAlert('danger', 'Failed to load test requests: ' + error.message);
        });
}

// Edit request
function editRequest(requestId) {
    fetch(`includes/process_request.php?action=get&request_id=${requestId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            
            // Populate the update form
            $('#updateRequestId').val(data.request_id);
            $('#updateStatus').val(data.status);
            $('#updatePriority').val(data.priority);
            
            // Show patient and test details
            $('#updatePatientName').text(`${data.first_name} ${data.last_name}`);
            $('#updateTestName').text(data.test_name);
            
            $('#updateRequestModal').modal('show');
        })
        .catch(error => {
            showAlert('danger', 'Failed to load request details: ' + error.message);
        });
}

// Delete request
function deleteRequest(requestId) {
    if (!confirm('Are you sure you want to delete this test request?')) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('request_id', requestId);
    formData.append('csrf_token', csrfToken);

    fetch('includes/process_request.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            throw new Error(data.error);
        }
        
        showAlert('success', 'Test request deleted successfully');
        loadRequests();
    })
    .catch(error => {
        showAlert('danger', 'Failed to delete request: ' + error.message);
    });
}

// Helper function to show alerts
function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.querySelector('#alertContainer').appendChild(alertDiv);
    
    // Auto dismiss after 5 seconds
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
} 