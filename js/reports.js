/**
 * Reports JavaScript - AdminLTE3 with AJAX and Charts
 */

$(document).ready(function() {
    // Load initial stats
    loadReportStats();
    
    // Initialize DataTable
    window.reportsTable = $('#reportsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: 'api/reports_api.php',
            type: 'POST',
            data: function(d) {
                d.action = 'list';
                d.date_range = $('#dateRange').val();
                d.report_type = $('#reportType').val();
            }
        },
        columns: [
            { 
                data: 'report_id',
                render: function(data, type, row) {
                    return '<strong>' + data + '</strong>';
                }
            },
            { 
                data: 'date',
                render: function(data, type, row) {
                    return new Date(data).toLocaleDateString();
                }
            },
            { 
                data: 'type',
                render: function(data, type, row) {
                    return '<span class="badge badge-info">' + data + '</span>';
                }
            },
            { 
                data: 'patient_name',
                defaultContent: 'N/A'
            },
            { 
                data: 'test',
                defaultContent: 'N/A'
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
                data: 'doctor_name',
                defaultContent: 'N/A'
            },
            {
                data: null,
                orderable: false,
                width: '100px',
                render: function(data, type, row) {
                    return `
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-info btn-sm" onclick="viewReport(${row.id})" title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-success btn-sm" onclick="downloadReport(${row.id})" title="Download">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        order: [[1, 'desc']],
        pageLength: 25,
        responsive: true,
        language: {
            processing: '<i class="fas fa-spinner fa-spin"></i> Loading reports...'
        }
    });

    // Date range change handler
    $('#dateRange').on('change', function() {
        var range = $(this).val();
        var fromDate = $('#fromDate');
        var toDate = $('#toDate');
        var today = new Date();
        
        // Show/hide custom date inputs
        if (range === 'custom') {
            fromDate.parent().show();
            toDate.parent().show();
        } else {
            fromDate.parent().hide();
            toDate.parent().hide();
            
            // Set default dates based on selection
            switch(range) {
                case 'today':
                    fromDate.val(formatDate(today));
                    toDate.val(formatDate(today));
                    break;
                case 'yesterday':
                    var yesterday = new Date(today);
                    yesterday.setDate(yesterday.getDate() - 1);
                    fromDate.val(formatDate(yesterday));
                    toDate.val(formatDate(yesterday));
                    break;
                case 'this_week':
                    var startOfWeek = new Date(today);
                    startOfWeek.setDate(today.getDate() - today.getDay());
                    fromDate.val(formatDate(startOfWeek));
                    toDate.val(formatDate(today));
                    break;
                case 'this_month':
                    var startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
                    fromDate.val(formatDate(startOfMonth));
                    toDate.val(formatDate(today));
                    break;
            }
        }
        
        // Reload table data
        reportsTable.ajax.reload();
    });

    // Initialize date range
    $('#dateRange').trigger('change');
    
    // Load charts
    loadCharts();
});

// Load report statistics
function loadReportStats() {
    $.ajax({
        url: 'api/reports_api.php',
        type: 'POST',
        data: { action: 'stats' },
        success: function(response) {
            if (response.success) {
                $('#totalTests').text(response.data.totalTests);
                $('#completedTests').text(response.data.completedTests);
                $('#pendingTests').text(response.data.pendingTests);
                $('#urgentTests').text(response.data.urgentTests);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading stats:', error);
        }
    });
}

// Load charts
function loadCharts() {
    $.ajax({
        url: 'api/reports_api.php',
        type: 'POST',
        data: { action: 'charts' },
        success: function(response) {
            if (response.success) {
                createTestDistributionChart(response.data.testDistribution);
                createMonthlyTrendsChart(response.data.monthlyTrends);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading charts:', error);
        }
    });
}

// Create test distribution pie chart
function createTestDistributionChart(data) {
    var ctx = $('#testDistributionChart')[0].getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.labels,
            datasets: [{
                data: data.data,
                backgroundColor: [
                    '#3c8dbc', '#00c0ef', '#00a65a', '#f39c12', 
                    '#dd4b39', '#605ca8', '#39cccc', '#ff7675'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                position: 'bottom'
            }
        }
    });
}

// Create monthly trends line chart
function createMonthlyTrendsChart(data) {
    var ctx = $('#monthlyTrendsChart')[0].getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Test Orders',
                data: data.data,
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                borderWidth: 2,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Generate reports with filters
function generateReports() {
    var dateRange = $('#dateRange').val();
    var fromDate = $('#fromDate').val();
    var toDate = $('#toDate').val();
    var reportType = $('#reportType').val();
    
    // Validate custom date range
    if (dateRange === 'custom' && (!fromDate || !toDate)) {
        toastr.error('Please select both from and to dates for custom range');
        return;
    }
    
    $.ajax({
        url: 'api/reports_api.php',
        type: 'POST',
        data: {
            action: 'generate',
            date_range: dateRange,
            from_date: fromDate,
            to_date: toDate,
            report_type: reportType
        },
        success: function(response) {
            if (response.success) {
                toastr.success(response.message);
                reportsTable.ajax.reload();
                loadReportStats();
                loadCharts();
            } else {
                toastr.error(response.message || 'Error generating report');
            }
        },
        error: function(xhr, status, error) {
            toastr.error('Error generating report: ' + error);
        }
    });
}

// Clear all filters
function clearFilters() {
    $('#dateRange').val('this_month').trigger('change');
    $('#reportType').val('all');
    reportsTable.ajax.reload();
    toastr.info('Filters cleared');
}

// Export reports
function exportReports(format) {
    var dateRange = $('#dateRange').val();
    var fromDate = $('#fromDate').val();
    var toDate = $('#toDate').val();
    var reportType = $('#reportType').val();
    
    var url = 'api/reports_api.php?action=export&format=' + format;
    url += '&date_range=' + dateRange;
    url += '&report_type=' + reportType;
    
    if (dateRange === 'custom' && fromDate && toDate) {
        url += '&from_date=' + fromDate + '&to_date=' + toDate;
    }
    
    window.open(url, '_blank');
    toastr.info('Export started...');
}

// Print reports
function printReports() {
    var reportContent = $('#reportsContent').html();
    var printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Laboratory Reports</title>
                <style>
                    body { font-family: Arial, sans-serif; }
                    table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    th { background-color: #f2f2f2; }
                    .badge { padding: 4px 8px; border-radius: 4px; }
                    .badge-success { background-color: #28a745; color: white; }
                    .badge-warning { background-color: #ffc107; color: black; }
                    .badge-info { background-color: #17a2b8; color: white; }
                    .badge-danger { background-color: #dc3545; color: white; }
                </style>
            </head>
            <body>
                <h1>Laboratory Reports</h1>
                <p>Generated on: ${new Date().toLocaleDateString()}</p>
                ${reportContent}
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}

// View individual report
function viewReport(id) {
    $.ajax({
        url: 'api/test_orders_api.php',
        type: 'POST',
        data: { 
            action: 'view',
            id: id 
        },
        success: function(response) {
            $('#reportModal .modal-content').html(response);
            $('#reportModal').modal('show');
        },
        error: function(xhr, status, error) {
            toastr.error('Error loading report details: ' + error);
        }
    });
}

// Download individual report
function downloadReport(id) {
    var url = 'api/reports_api.php?action=export&format=pdf&report_id=' + id;
    window.open(url, '_blank');
    toastr.info('Download started...');
}

// Utility function to format date
function formatDate(date) {
    var d = new Date(date);
    var month = '' + (d.getMonth() + 1);
    var day = '' + d.getDate();
    var year = d.getFullYear();

    if (month.length < 2) month = '0' + month;
    if (day.length < 2) day = '0' + day;

    return [year, month, day].join('-');
}

// Keyboard shortcuts
$(document).on('keydown', function(e) {
    // Ctrl+G for generate reports
    if (e.ctrlKey && e.which === 71) {
        e.preventDefault();
        generateReports();
    }
    
    // Ctrl+E for export
    if (e.ctrlKey && e.which === 69) {
        e.preventDefault();
        exportReports('excel');
    }
    
    // F5 for refresh
    if (e.which === 116) {
        e.preventDefault();
        location.reload();
    }
});

// Auto-refresh stats every 5 minutes
setInterval(function() {
    loadReportStats();
}, 300000);
