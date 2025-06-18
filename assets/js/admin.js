/**
 * Admin JavaScript for US Fitness Lab
 * Admin and branch admin specific functionality
 */

// Admin-specific initialization
$(document).ready(function() {
    initializeAdminFeatures();
    setupReportGeneration();
    setupTestManagement();
    setupDashboardUpdates();
});

/**
 * Initialize admin-specific features
 */
function initializeAdminFeatures() {
    // Initialize data tables if present
    if ($('.admin-table table').length) {
        $('.admin-table table').DataTable({
            responsive: true,
            pageLength: 25,
            order: [[0, 'desc']],
            language: {
                search: "Search records:",
                lengthMenu: "Show _MENU_ records per page",
                info: "Showing _START_ to _END_ of _TOTAL_ records",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
        });
    }
    
    // Initialize date pickers
    if ($('input[type="date"]').length) {
        $('input[type="date"]').each(function() {
            if (!$(this).val()) {
                $(this).val(new Date().toISOString().split('T')[0]);
            }
        });
    }
    
    // Initialize select2 for better dropdowns
    if ($('.select2').length) {
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });
    }
}

/**
 * Setup report generation functionality
 */
function setupReportGeneration() {
    // Handle test selection and parameter loading
    $('#test_name').on('change', function() {
        var testId = $(this).val();
        if (testId) {
            loadTestParameters(testId);
            updateTestPrice(testId);
        } else {
            clearTestParameters();
        }
    });
    
    // Setup billing calculations
    $('#subtotal, #discount, #paid_amount').on('input', calculateBillingTotals);
    
    // Handle report form submission
    $('#generateReportForm').on('submit', function(e) {
        e.preventDefault();
        generateReport();
    });
    
    // Handle parameter value changes
    $(document).on('input', '.parameter-result', function() {
        validateParameterResult($(this));
    });
}

/**
 * Load test parameters via AJAX
 */
function loadTestParameters(testId) {
    showLoadingSpinner();
    
    $.ajax({
        url: AJAX_URL,
        type: 'POST',
        data: {
            action: 'getTestParameters',
            test_id: testId
        },
        success: function(response) {
            if (response.success && response.data.length > 0) {
                renderTestParameters(response.data);
            } else {
                $('#test-parameters').html(
                    '<div class="alert alert-warning">' +
                    '<i class="fas fa-exclamation-triangle me-2"></i>' +
                    'No parameters found for this test' +
                    '</div>'
                );
            }
        },
        error: function() {
            showToast('Failed to load test parameters', 'error');
        },
        complete: function() {
            hideLoadingSpinner();
        }
    });
}

/**
 * Render test parameters in the form
 */
function renderTestParameters(parameters) {
    var html = '<div class="test-parameters-container">';
    
    parameters.forEach(function(param, index) {
        html += `
            <div class="parameter-group mb-3 p-3 border rounded">
                <div class="row g-2">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Parameter</label>
                        <input type="text" class="form-control" name="parameters[${index}][name]" 
                               value="${escapeHtml(param.name)}" readonly>
                        <input type="hidden" name="parameters[${index}][id]" value="${param.id}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Specimen</label>
                        <input type="text" class="form-control" name="parameters[${index}][specimen]" 
                               value="${escapeHtml(param.specimen || '')}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Result *</label>
                        <input type="text" class="form-control parameter-result" 
                               name="parameters[${index}][result]" required 
                               data-ref-range="${escapeHtml(param.ref_range || '')}"
                               placeholder="Enter result">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Unit</label>
                        <input type="text" class="form-control" name="parameters[${index}][unit]" 
                               value="${escapeHtml(param.unit || '')}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Reference Range</label>
                        <input type="text" class="form-control" name="parameters[${index}][ref_range]" 
                               value="${escapeHtml(param.ref_range || '')}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Method</label>
                        <input type="text" class="form-control" name="parameters[${index}][method]" 
                               value="${escapeHtml(param.method || '')}">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Comments</label>
                        <textarea class="form-control" name="parameters[${index}][comments]" 
                                  rows="1" placeholder="Additional comments"></textarea>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    $('#test-parameters').html(html);
}

/**
 * Clear test parameters
 */
function clearTestParameters() {
    $('#test-parameters').html(
        '<div class="text-center text-muted py-4">' +
        '<i class="fas fa-vial fa-3x mb-3"></i>' +
        '<p>Select a test to load parameters</p>' +
        '</div>'
    );
}

/**
 * Update test price when test is selected
 */
function updateTestPrice(testId) {
    var selectedOption = $('#test_name option:selected');
    var price = selectedOption.data('price');
    
    if (price) {
        $('#subtotal').val(parseFloat(price).toFixed(2));
        calculateBillingTotals();
    }
}

/**
 * Calculate billing totals
 */
function calculateBillingTotals() {
    var subtotal = parseFloat($('#subtotal').val()) || 0;
    var discount = parseFloat($('#discount').val()) || 0;
    var paidAmount = parseFloat($('#paid_amount').val()) || 0;
    
    var totalAmount = Math.max(0, subtotal - discount);
    var balance = totalAmount - paidAmount;
    
    $('#total_amount').val(totalAmount.toFixed(2));
    $('#balance').val(balance.toFixed(2));
    
    // Update balance color based on amount
    var balanceField = $('#balance');
    balanceField.removeClass('text-success text-warning text-danger');
    
    if (balance > 0) {
        balanceField.addClass('text-danger');
    } else if (balance < 0) {
        balanceField.addClass('text-warning');
    } else {
        balanceField.addClass('text-success');
    }
}

/**
 * Validate parameter result
 */
function validateParameterResult(input) {
    var result = input.val();
    var refRange = input.data('ref-range');
    
    // Remove previous validation classes
    input.removeClass('is-valid is-invalid');
    
    if (result && refRange) {
        // Simple validation - you can enhance this based on your needs
        if (result.trim().length > 0) {
            input.addClass('is-valid');
        }
    }
}

/**
 * Generate report
 */
function generateReport() {
    var form = $('#generateReportForm');
    var formData = new FormData(form[0]);
    
    // Validate required fields
    var isValid = true;
    form.find('[required]').each(function() {
        if (!$(this).val().trim()) {
            $(this).addClass('is-invalid');
            isValid = false;
        } else {
            $(this).removeClass('is-invalid');
        }
    });
    
    if (!isValid) {
        showToast('Please fill all required fields', 'error');
        return;
    }
    
    // Show loading state
    var submitBtn = form.find('[type="submit"]');
    var originalText = submitBtn.text();
    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Generating...');
    
    showLoadingSpinner();
    
    $.ajax({
        url: AJAX_URL,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                showToast('Report generated successfully', 'success');
                
                // Reset form or redirect
                if (response.redirect) {
                    setTimeout(function() {
                        window.location.href = response.redirect;
                    }, 2000);
                } else if (response.report_id) {
                    // Show download link
                    showReportDownloadLink(response.report_id, response.download_token);
                }
            } else {
                showToast(response.message || 'Failed to generate report', 'error');
            }
        },
        error: function(xhr) {
            var message = 'An error occurred while generating the report';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            showToast(message, 'error');
        },
        complete: function() {
            submitBtn.prop('disabled', false).html(originalText);
            hideLoadingSpinner();
        }
    });
}

/**
 * Show report download link
 */
function showReportDownloadLink(reportId, token) {
    var downloadUrl = BASE_URL + 'download-report/' + reportId + '?token=' + token;
    
    var modal = `
        <div class="modal fade" id="reportModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-file-pdf text-danger me-2"></i>
                            Report Generated Successfully
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Your report has been generated successfully. You can download it using the link below:</p>
                        <div class="d-grid">
                            <a href="${downloadUrl}" class="btn btn-primary" target="_blank">
                                <i class="fas fa-download me-2"></i>Download Report
                            </a>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-success" onclick="resetReportForm()">Generate Another Report</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    $('body').append(modal);
    var modalElement = new bootstrap.Modal(document.getElementById('reportModal'));
    modalElement.show();
    
    // Remove modal from DOM when hidden
    $('#reportModal').on('hidden.bs.modal', function() {
        $(this).remove();
    });
}

/**
 * Reset report form
 */
function resetReportForm() {
    $('#generateReportForm')[0].reset();
    clearTestParameters();
    calculateBillingTotals();
    $('#reportModal').modal('hide');
}

/**
 * Setup test management functionality
 */
function setupTestManagement() {
    // Handle test form submission
    $('#testForm').on('submit', function(e) {
        e.preventDefault();
        saveTest();
    });
    
    // Handle test deletion
    $(document).on('click', '.delete-test', function(e) {
        e.preventDefault();
        var testId = $(this).data('test-id');
        var testName = $(this).data('test-name');
        deleteTest(testId, testName);
    });
    
    // Handle test editing
    $(document).on('click', '.edit-test', function(e) {
        e.preventDefault();
        var testId = $(this).data('test-id');
        editTest(testId);
    });
}

/**
 * Setup dashboard updates
 */
function setupDashboardUpdates() {
    // Auto-refresh dashboard data every 5 minutes
    if ($('#dashboard').length) {
        setInterval(function() {
            refreshDashboardData();
        }, 300000); // 5 minutes
    }
    
    // Handle quick stats refresh
    $('.refresh-stats').on('click', function(e) {
        e.preventDefault();
        refreshDashboardData();
    });
}

/**
 * Refresh dashboard data
 */
function refreshDashboardData() {
    $.ajax({
        url: AJAX_URL,
        type: 'POST',
        data: {action: 'getDashboardData'},
        success: function(response) {
            if (response.success) {
                updateDashboardStats(response.data);
            }
        }
    });
}

/**
 * Update dashboard statistics
 */
function updateDashboardStats(data) {
    // Update stat cards
    $('.total-patients .stats-number').text(data.total_patients || 0);
    $('.total-tests .stats-number').text(data.total_tests || 0);
    $('.pending-reports .stats-number').text(data.pending_reports || 0);
    $('.total-revenue .stats-number').text(formatCurrency(data.total_revenue || 0));
    
    // Update charts if present
    if (window.updateCharts && typeof window.updateCharts === 'function') {
        window.updateCharts(data);
    }
}

/**
 * Utility function to escape HTML
 */
function escapeHtml(unsafe) {
    if (typeof unsafe !== 'string') return '';
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

/**
 * Format currency for display
 */
function formatCurrency(amount) {
    return '₹' + parseFloat(amount).toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

/**
 * Handle print functionality
 */
function printReport(reportId) {
    var printWindow = window.open(BASE_URL + 'admin/print-report.php?id=' + reportId, '_blank');
    printWindow.onload = function() {
        printWindow.print();
    };
}

/**
 * Export data to CSV
 */
function exportToCSV(endpoint, filename) {
    showLoadingSpinner();
    
    $.ajax({
        url: AJAX_URL,
        type: 'POST',
        data: {action: endpoint},
        success: function(response) {
            if (response.success && response.csv_data) {
                downloadCSV(response.csv_data, filename);
            } else {
                showToast('Failed to export data', 'error');
            }
        },
        complete: function() {
            hideLoadingSpinner();
        }
    });
}

/**
 * Download CSV data
 */
function downloadCSV(csvData, filename) {
    var blob = new Blob([csvData], { type: 'text/csv' });
    var url = window.URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = filename || 'export.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}
