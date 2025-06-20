<?php
// Set page title and active menu
$page_title = 'Reports';
$active_menu = 'reports';

// Include header and sidebar
include 'includes/header.php';
include 'includes/sidebar.php';
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
  <!-- Content Header -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">Laboratory Reports</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
            <li class="breadcrumb-item active">Reports</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      <!-- Report Statistics -->
      <div class="row">
        <div class="col-lg-3 col-6">
          <div class="small-box bg-info">
            <div class="inner">
              <h3 id="totalTests">0</h3>
              <p>Total Tests</p>
            </div>
            <div class="icon">
              <i class="fas fa-flask"></i>
            </div>
            <a href="#" class="small-box-footer">View Details <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-success">
            <div class="inner">
              <h3 id="totalRevenue">$0</h3>
              <p>Total Revenue</p>
            </div>
            <div class="icon">
              <i class="fas fa-dollar-sign"></i>
            </div>
            <a href="#" class="small-box-footer">View Details <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-warning">
            <div class="inner">
              <h3 id="totalPatients">0</h3>
              <p>Total Patients</p>
            </div>
            <div class="icon">
              <i class="fas fa-user-injured"></i>
            </div>
            <a href="#" class="small-box-footer">View Details <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-danger">
            <div class="inner">
              <h3 id="avgTAT">0</h3>
              <p>Avg TAT (hours)</p>
            </div>
            <div class="icon">
              <i class="fas fa-clock"></i>
            </div>
            <a href="#" class="small-box-footer">View Details <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
      </div>

      <!-- Report Filters -->
      <div class="card card-primary card-outline">
        <div class="card-header">
          <h3 class="card-title">
            <i class="fas fa-filter"></i> Report Filters
          </h3>
          <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
              <i class="fas fa-minus"></i>
            </button>
          </div>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-3">
              <div class="form-group">
                <label for="reportType">Report Type</label>
                <select class="form-control" id="reportType">
                  <option value="test-summary">Test Summary</option>
                  <option value="revenue">Revenue Report</option>
                  <option value="patient-demographics">Patient Demographics</option>
                  <option value="doctor-referrals">Doctor Referrals</option>
                  <option value="test-orders">Test Orders</option>
                  <option value="critical-results">Critical Results</option>
                </select>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label for="dateRange">Date Range</label>
                <select class="form-control" id="dateRange">
                  <option value="today">Today</option>
                  <option value="yesterday">Yesterday</option>
                  <option value="this-week">This Week</option>
                  <option value="last-week">Last Week</option>
                  <option value="this-month" selected>This Month</option>
                  <option value="last-month">Last Month</option>
                  <option value="this-year">This Year</option>
                  <option value="custom">Custom Range</option>
                </select>
              </div>
            </div>
            <div class="col-md-4 custom-date-range" style="display: none;">
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="dateFrom">From Date</label>
                    <input type="date" class="form-control" id="dateFrom">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="dateTo">To Date</label>
                    <input type="date" class="form-control" id="dateTo">
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-2">
              <div class="form-group">
                <label>&nbsp;</label>
                <button type="button" class="btn btn-primary btn-block" id="generateReport">
                  <i class="fas fa-chart-bar"></i> Generate
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Report Results -->
      <div class="row">
        <div class="col-md-8">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-chart-bar"></i> Report Visualization
              </h3>
              <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                  <i class="fas fa-minus"></i>
                </button>
                <button type="button" class="btn btn-tool" data-card-widget="maximize">
                  <i class="fas fa-expand"></i>
                </button>
              </div>
            </div>
            <div class="card-body">
              <div class="chart-container">
                <canvas id="reportChart" style="min-height: 350px; height: 350px;"></canvas>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-info-circle"></i> Summary
              </h3>
            </div>
            <div class="card-body">
              <div class="info-box mb-3 bg-info">
                <span class="info-box-icon"><i class="fas fa-flask"></i></span>
                <div class="info-box-content">
                  <span class="info-box-text">Total Tests</span>
                  <span class="info-box-number" id="summaryTests">0</span>
                </div>
              </div>
              <div class="info-box mb-3 bg-success">
                <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                <div class="info-box-content">
                  <span class="info-box-text">Revenue</span>
                  <span class="info-box-number" id="summaryRevenue">$0</span>
                </div>
              </div>
              <div class="info-box mb-3 bg-warning">
                <span class="info-box-icon"><i class="fas fa-user-injured"></i></span>
                <div class="info-box-content">
                  <span class="info-box-text">Patients</span>
                  <span class="info-box-number" id="summaryPatients">0</span>
                </div>
              </div>
              <div class="info-box mb-3 bg-danger">
                <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                <div class="info-box-content">
                  <span class="info-box-text">Critical Results</span>
                  <span class="info-box-number" id="summaryCritical">0</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Report Data Table -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">
            <i class="fas fa-table"></i> Report Data
          </h3>
          <div class="card-tools">
            <button type="button" class="btn btn-success btn-sm" id="exportExcel">
              <i class="fas fa-file-excel"></i> Export Excel
            </button>
            <button type="button" class="btn btn-danger btn-sm ml-1" id="exportPdf">
              <i class="fas fa-file-pdf"></i> Export PDF
            </button>
            <button type="button" class="btn btn-info btn-sm ml-1" id="printReport">
              <i class="fas fa-print"></i> Print
            </button>
          </div>
        </div>
        <div class="card-body">
          <!-- Loading indicator -->
          <div id="reportLoading" class="text-center p-4" style="display: none;">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
            <p class="mt-2">Generating report...</p>
          </div>
          
          <!-- Report table -->
          <div class="table-responsive">
            <table id="reportTable" class="table table-bordered table-striped table-hover">
              <thead id="reportTableHead">
                <!-- Dynamic headers will be inserted here -->
              </thead>
              <tbody id="reportTableBody">
                <!-- Dynamic content will be inserted here -->
              </tbody>
              <tfoot id="reportTableFoot">
                <!-- Dynamic footer will be inserted here -->
              </tfoot>
            </table>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<!-- Print Modal -->
<div class="modal fade" id="printModal" tabindex="-1" role="dialog" aria-labelledby="printModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-info">
        <h4 class="modal-title" id="printModalLabel">
          <i class="fas fa-print"></i> Print Report
        </h4>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="printContent">
        <!-- Print content will be populated here -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
          <i class="fas fa-times"></i> Close
        </button>
        <button type="button" class="btn btn-primary" onclick="window.print()">
          <i class="fas fa-print"></i> Print
        </button>
      </div>
    </div>
  </div>
</div>

<!-- JavaScript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize the page
    loadReportStats();
    generateReport();
    
    // Show/hide custom date range
    $('#dateRange').change(function() {
        if ($(this).val() === 'custom') {
            $('.custom-date-range').show();
            // Set default dates
            const today = new Date();
            const lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, today.getDate());
            $('#dateFrom').val(lastMonth.toISOString().split('T')[0]);
            $('#dateTo').val(today.toISOString().split('T')[0]);
        } else {
            $('.custom-date-range').hide();
        }
    });
    
    // Generate report on button click
    $('#generateReport').click(function() {
        generateReport();
    });
    
    // Export functions
    $('#exportExcel').click(function() {
        exportReport('excel');
    });
    
    $('#exportPdf').click(function() {
        exportReport('pdf');
    });
    
    $('#printReport').click(function() {
        printReportModal();
    });
});

// Global chart variable
let reportChart = null;

// Load report statistics
function loadReportStats() {
    $.ajax({
        url: 'api/reports_api.php',
        method: 'GET',
        data: { action: 'stats' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const stats = response.data;
                $('#totalTests').text(stats.total_tests || 0);
                $('#totalRevenue').text('$' + (stats.total_revenue || 0).toLocaleString());
                $('#totalPatients').text(stats.total_patients || 0);
                $('#avgTAT').text(stats.avg_tat || 0);
            }
        },
        error: function() {
            console.log('Error loading report statistics');
        }
    });
}

// Generate report
function generateReport() {
    const reportType = $('#reportType').val();
    const dateRange = $('#dateRange').val();
    const dateFrom = $('#dateFrom').val();
    const dateTo = $('#dateTo').val();
    
    // Show loading
    $('#reportLoading').show();
    $('#reportTable').hide();
    
    // Prepare request data
    const requestData = {
        action: 'generate_report',
        report_type: reportType,
        date_range: dateRange
    };
    
    if (dateRange === 'custom') {
        requestData.date_from = dateFrom;
        requestData.date_to = dateTo;
    }
    
    $.ajax({
        url: 'api/reports_api.php',
        method: 'GET',
        data: requestData,
        dataType: 'json',
        success: function(response) {
            $('#reportLoading').hide();
            $('#reportTable').show();
            
            if (response.success) {
                displayReportData(response.data, reportType);
                updateChart(response.chart_data, reportType);
                updateSummary(response.summary);
                showAlert('Report generated successfully!', 'success');
            } else {
                showAlert('Error generating report: ' + response.message, 'danger');
            }
        },
        error: function() {
            $('#reportLoading').hide();
            $('#reportTable').show();
            
            // Show sample data for demonstration
            displaySampleData(reportType);
            showAlert('Using sample data for demonstration', 'info');
        }
    });
}

// Display report data in table
function displayReportData(data, reportType) {
    let headers = '';
    let body = '';
    let footer = '';
    
    switch(reportType) {
        case 'test-summary':
            headers = `
                <tr>
                    <th>Test Name</th>
                    <th>Count</th>
                    <th>Revenue</th>
                    <th>Avg TAT</th>
                </tr>
            `;
            
            if (data && data.length > 0) {
                let totalCount = 0;
                let totalRevenue = 0;
                let avgTAT = 0;
                
                data.forEach(function(item) {
                    totalCount += parseInt(item.count || 0);
                    totalRevenue += parseFloat(item.revenue || 0);
                    
                    body += `
                        <tr>
                            <td>${item.test_name}</td>
                            <td>${item.count}</td>
                            <td>$${parseFloat(item.revenue || 0).toFixed(2)}</td>
                            <td>${item.avg_tat || 'N/A'}</td>
                        </tr>
                    `;
                });
                
                avgTAT = data.length > 0 ? (data.reduce((sum, item) => sum + parseFloat(item.avg_tat || 0), 0) / data.length).toFixed(1) : 0;
                
                footer = `
                    <tr>
                        <th>Total</th>
                        <th>${totalCount}</th>
                        <th>$${totalRevenue.toFixed(2)}</th>
                        <th>${avgTAT} hours</th>
                    </tr>
                `;
            }
            break;
            
        case 'revenue':
            headers = `
                <tr>
                    <th>Date</th>
                    <th>Tests Count</th>
                    <th>Revenue</th>
                    <th>Growth</th>
                </tr>
            `;
            break;
            
        case 'patient-demographics':
            headers = `
                <tr>
                    <th>Age Group</th>
                    <th>Male</th>
                    <th>Female</th>
                    <th>Total</th>
                </tr>
            `;
            break;
            
        case 'doctor-referrals':
            headers = `
                <tr>
                    <th>Doctor Name</th>
                    <th>Referrals</th>
                    <th>Revenue</th>
                    <th>Percentage</th>
                </tr>
            `;
            break;
    }
    
    $('#reportTableHead').html(headers);
    $('#reportTableBody').html(body);
    $('#reportTableFoot').html(footer);
    
    // Re-initialize DataTable if it exists
    if ($.fn.DataTable.isDataTable('#reportTable')) {
        $('#reportTable').DataTable().destroy();
    }
    
    $('#reportTable').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "pageLength": 25,
        "dom": '<"row"<"col-sm-6"l><"col-sm-6"f>><"row"<"col-sm-12"tr>><"row"<"col-sm-5"i><"col-sm-7"p>>'
    });
}

// Display sample data for demonstration
function displaySampleData(reportType) {
    const sampleData = {
        'test-summary': [
            { test_name: 'Complete Blood Count', count: 86, revenue: 2150, avg_tat: 12 },
            { test_name: 'Glucose Test', count: 65, revenue: 975, avg_tat: 4 },
            { test_name: 'Liver Function Test', count: 42, revenue: 1890, avg_tat: 24 },
            { test_name: 'HbA1c', count: 38, revenue: 1140, avg_tat: 12 },
            { test_name: 'Lipid Profile', count: 52, revenue: 2080, avg_tat: 12 },
            { test_name: 'Thyroid Function Test', count: 45, revenue: 4223, avg_tat: 24 }
        ]
    };
    
    displayReportData(sampleData[reportType] || [], reportType);
    
    // Update chart with sample data
    const chartData = {
        labels: sampleData[reportType]?.map(item => item.test_name) || [],
        data: sampleData[reportType]?.map(item => item.count) || []
    };
    updateChart(chartData, reportType);
    
    // Update summary
    const summary = {
        total_tests: 328,
        total_revenue: 12458,
        total_patients: 164,
        critical_results: 12
    };
    updateSummary(summary);
}

// Update chart
function updateChart(chartData, reportType) {
    const ctx = document.getElementById('reportChart').getContext('2d');
    
    // Destroy existing chart
    if (reportChart) {
        reportChart.destroy();
    }
    
    let chartConfig = {
        type: 'bar',
        data: {
            labels: chartData.labels || [],
            datasets: [{
                label: getChartLabel(reportType),
                data: chartData.data || [],
                backgroundColor: getChartColors(reportType),
                borderColor: getChartBorderColors(reportType),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                title: {
                    display: true,
                    text: getChartTitle(reportType)
                }
            }
        }
    };
    
    // Change to pie chart for demographics
    if (reportType === 'patient-demographics') {
        chartConfig.type = 'pie';
        chartConfig.options.scales = {}; // Remove scales for pie chart
    }
    
    reportChart = new Chart(ctx, chartConfig);
}

// Helper functions for chart configuration
function getChartLabel(reportType) {
    const labels = {
        'test-summary': 'Test Count',
        'revenue': 'Revenue ($)',
        'patient-demographics': 'Patients',
        'doctor-referrals': 'Referrals'
    };
    return labels[reportType] || 'Count';
}

function getChartColors(reportType) {
    const colors = {
        'test-summary': 'rgba(60, 141, 188, 0.8)',
        'revenue': 'rgba(40, 167, 69, 0.8)',
        'patient-demographics': [
            'rgba(60, 141, 188, 0.8)',
            'rgba(210, 214, 222, 0.8)',
            'rgba(255, 193, 7, 0.8)'
        ],
        'doctor-referrals': 'rgba(156, 39, 176, 0.8)'
    };
    return colors[reportType] || 'rgba(60, 141, 188, 0.8)';
}

function getChartBorderColors(reportType) {
    const colors = {
        'test-summary': 'rgba(60, 141, 188, 1)',
        'revenue': 'rgba(40, 167, 69, 1)',
        'patient-demographics': [
            'rgba(60, 141, 188, 1)',
            'rgba(210, 214, 222, 1)',
            'rgba(255, 193, 7, 1)'
        ],
        'doctor-referrals': 'rgba(156, 39, 176, 1)'
    };
    return colors[reportType] || 'rgba(60, 141, 188, 1)';
}

function getChartTitle(reportType) {
    const titles = {
        'test-summary': 'Test Summary Report',
        'revenue': 'Revenue Report',
        'patient-demographics': 'Patient Demographics',
        'doctor-referrals': 'Doctor Referrals'
    };
    return titles[reportType] || 'Report';
}

// Update summary boxes
function updateSummary(summary) {
    $('#summaryTests').text(summary.total_tests || 0);
    $('#summaryRevenue').text('$' + (summary.total_revenue || 0).toLocaleString());
    $('#summaryPatients').text(summary.total_patients || 0);
    $('#summaryCritical').text(summary.critical_results || 0);
}

// Export functions
function exportReport(format) {
    const reportType = $('#reportType').val();
    const dateRange = $('#dateRange').val();
    
    if (format === 'excel') {
        // In a real application, this would generate an Excel file
        showAlert('Excel export feature will be available soon.', 'info');
    } else if (format === 'pdf') {
        // In a real application, this would generate a PDF file
        showAlert('PDF export feature will be available soon.', 'info');
    }
}

// Print report modal
function printReportModal() {
    const reportType = $('#reportType').val();
    const dateRange = $('#dateRange').val();
    
    let printContent = `
        <div class="text-center mb-4">
            <h2>PathLab Pro - Laboratory Report</h2>
            <h4>${getChartTitle(reportType)}</h4>
            <p>Date Range: ${dateRange}</p>
            <p>Generated on: ${new Date().toLocaleString()}</p>
        </div>
        <div class="table-responsive">
            ${$('#reportTable')[0].outerHTML}
        </div>
    `;
    
    $('#printContent').html(printContent);
    $('#printModal').modal('show');
}

// Utility function to show alerts
function showAlert(message, type) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-triangle' : 'info-circle'}"></i>
            ${message}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    `;
    
    // Remove existing alerts
    $('.alert').remove();
    
    // Add new alert
    $('.content-wrapper .content').prepend(alertHtml);
    
    // Auto dismiss after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
}
</script>

<?php
// Include footer
include 'includes/footer.php';
?>
