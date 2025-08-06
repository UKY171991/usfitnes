<?php
// Set page title
$page_title = 'Reports';

// Include header
include 'includes/header.php';

// Include sidebar with user info
include 'includes/sidebar.php';
?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
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
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <!-- Quick Stats Row -->
        <div class="row">
          <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
              <div class="inner">
                <h3>156</h3>
                <p>Total Tests</p>
              </div>
              <div class="icon">
                <i class="fas fa-flask"></i>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
              <div class="inner">
                <h3>$12,450</h3>
                <p>Total Revenue</p>
              </div>
              <div class="icon">
                <i class="fas fa-dollar-sign"></i>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
              <div class="inner">
                <h3>23</h3>
                <p>Pending Results</p>
              </div>
              <div class="icon">
                <i class="fas fa-clock"></i>
              </div>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
              <div class="inner">
                <h3>5</h3>
                <p>Critical Results</p>
              </div>
              <div class="icon">
                <i class="fas fa-exclamation-triangle"></i>
              </div>
            </div>
          </div>
        </div>

        <!-- Report Generation -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title"><i class="fas fa-chart-line mr-2"></i>Generate Reports</h3>
            <div class="card-tools">
              <button type="button" class="btn btn-primary btn-sm" onclick="generateReport()">
                <i class="fas fa-download"></i> Generate Report
              </button>
            </div>
          </div>
          <div class="card-body">
            <form id="reportForm">
              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="report_type">Report Type</label>
                    <select class="form-control" id="report_type" name="report_type">
                      <option value="daily">Daily Summary</option>
                      <option value="weekly">Weekly Summary</option>
                      <option value="monthly">Monthly Summary</option>
                      <option value="custom">Custom Range</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="start_date">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="end_date">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label for="format">Format</label>
                    <select class="form-control" id="format" name="format">
                      <option value="pdf">PDF</option>
                      <option value="excel">Excel</option>
                      <option value="csv">CSV</option>
                    </select>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>

        <!-- Recent Reports -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title"><i class="fas fa-history mr-2"></i>Recent Reports</h3>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table id="reportsTable" class="table table-bordered table-striped table-hover">
                <thead>
                  <tr>
                    <th>Report Name</th>
                    <th>Type</th>
                    <th>Date Range</th>
                    <th>Generated</th>
                    <th>Format</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <!-- Sample data -->
                  <tr>
                    <td>Daily Summary Report</td>
                    <td><span class="badge badge-info">Daily</span></td>
                    <td>2024-01-15</td>
                    <td>2024-01-15 10:30 AM</td>
                    <td><span class="badge badge-secondary">PDF</span></td>
                    <td>
                      <div class="btn-group" role="group">
                        <button type="button" class="btn btn-success btn-sm" onclick="downloadReport(1)" title="Download">
                          <i class="fas fa-download"></i>
                        </button>
                        <button type="button" class="btn btn-info btn-sm" onclick="viewReport(1)" title="View">
                          <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteReport(1)" title="Delete">
                          <i class="fas fa-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td>Weekly Revenue Report</td>
                    <td><span class="badge badge-success">Weekly</span></td>
                    <td>2024-01-08 to 2024-01-14</td>
                    <td>2024-01-14 5:45 PM</td>
                    <td><span class="badge badge-primary">Excel</span></td>
                    <td>
                      <div class="btn-group" role="group">
                        <button type="button" class="btn btn-success btn-sm" onclick="downloadReport(2)" title="Download">
                          <i class="fas fa-download"></i>
                        </button>
                        <button type="button" class="btn btn-info btn-sm" onclick="viewReport(2)" title="View">
                          <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteReport(2)" title="Delete">
                          <i class="fas fa-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td>Monthly Patient Statistics</td>
                    <td><span class="badge badge-warning">Monthly</span></td>
                    <td>2024-01-01 to 2024-01-31</td>
                    <td>2024-01-13 2:15 PM</td>
                    <td><span class="badge badge-success">CSV</span></td>
                    <td>
                      <div class="btn-group" role="group">
                        <button type="button" class="btn btn-success btn-sm" onclick="downloadReport(3)" title="Download">
                          <i class="fas fa-download"></i>
                        </button>
                        <button type="button" class="btn btn-info btn-sm" onclick="viewReport(3)" title="View">
                          <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteReport(3)" title="Delete">
                          <i class="fas fa-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <!-- View Report Modal -->
  <div class="modal fade" id="modal-view-report" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
      <div class="modal-content">
        <div class="modal-header bg-info">
          <h4 class="modal-title text-white"><i class="fas fa-eye mr-2"></i>View Report</h4>
          <button type="button" class="close text-white" data-dismiss="modal">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" id="reportContent">
          <!-- Report content will be loaded here -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" onclick="printReport()">
            <i class="fas fa-print mr-1"></i>Print Report
          </button>
        </div>
      </div>
    </div>
  </div>

<?php
// Additional scripts specific to the reports page
$additional_scripts = <<<EOT
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#reportsTable').DataTable({
        responsive: true,
        lengthChange: false,
        autoWidth: false,
        order: [[3, 'desc']] // Sort by generated date descending
    });

    // Handle report type change
    $('#report_type').on('change', function() {
        var reportType = $(this).val();
        var today = new Date();
        var startDate = new Date();
        var endDate = new Date();

        switch(reportType) {
            case 'daily':
                startDate = today;
                endDate = today;
                break;
            case 'weekly':
                startDate.setDate(today.getDate() - 7);
                break;
            case 'monthly':
                startDate.setMonth(today.getMonth() - 1);
                break;
            case 'custom':
                // Let user select custom dates
                return;
        }

        if (reportType !== 'custom') {
            $('#start_date').val(formatDate(startDate));
            $('#end_date').val(formatDate(endDate));
        }
    });

    // Set default dates for daily report
    $('#report_type').trigger('change');
});

// Format date for input
function formatDate(date) {
    var year = date.getFullYear();
    var month = String(date.getMonth() + 1).padStart(2, '0');
    var day = String(date.getDate()).padStart(2, '0');
    return year + '-' + month + '-' + day;
}

// Generate report function
function generateReport() {
    var reportType = $('#report_type').val();
    var startDate = $('#start_date').val();
    var endDate = $('#end_date').val();
    var format = $('#format').val();

    // Validation
    if (!startDate || !endDate) {
        toastr.error('Please select start and end dates.');
        return;
    }

    if (new Date(startDate) > new Date(endDate)) {
        toastr.error('Start date cannot be after end date.');
        return;
    }

    // Show loading
    toastr.info('Generating report... Please wait.');

    // Simulate report generation - in real implementation, make API call
    setTimeout(function() {
        // Add new report to table
        var table = $('#reportsTable').DataTable();
        var reportName = reportType.charAt(0).toUpperCase() + reportType.slice(1) + ' Report';
        var dateRange = startDate === endDate ? startDate : startDate + ' to ' + endDate;
        var now = new Date();
        var generatedTime = formatDateTime(now);
        
        var typeBadge = '';
        switch(reportType) {
            case 'daily': typeBadge = '<span class="badge badge-info">Daily</span>'; break;
            case 'weekly': typeBadge = '<span class="badge badge-success">Weekly</span>'; break;
            case 'monthly': typeBadge = '<span class="badge badge-warning">Monthly</span>'; break;
            case 'custom': typeBadge = '<span class="badge badge-secondary">Custom</span>'; break;
        }

        var formatBadge = '';
        switch(format) {
            case 'pdf': formatBadge = '<span class="badge badge-secondary">PDF</span>'; break;
            case 'excel': formatBadge = '<span class="badge badge-primary">Excel</span>'; break;
            case 'csv': formatBadge = '<span class="badge badge-success">CSV</span>'; break;
        }

        var newRow = [
            reportName,
            typeBadge,
            dateRange,
            generatedTime,
            formatBadge,
            '<div class="btn-group" role="group">' +
                '<button type="button" class="btn btn-success btn-sm" onclick="downloadReport(' + (table.rows().count() + 1) + ')" title="Download">' +
                    '<i class="fas fa-download"></i>' +
                '</button>' +
                '<button type="button" class="btn btn-info btn-sm" onclick="viewReport(' + (table.rows().count() + 1) + ')" title="View">' +
                    '<i class="fas fa-eye"></i>' +
                '</button>' +
                '<button type="button" class="btn btn-danger btn-sm" onclick="deleteReport(' + (table.rows().count() + 1) + ')" title="Delete">' +
                    '<i class="fas fa-trash"></i>' +
                '</button>' +
            '</div>'
        ];

        table.row.add(newRow).draw();
        toastr.success('Report generated successfully!');
    }, 2000);
}

// Format date and time
function formatDateTime(date) {
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
}

// Download report function
function downloadReport(id) {
    toastr.info('Downloading report...');
    // In real implementation, trigger actual download
    setTimeout(function() {
        toastr.success('Report downloaded successfully!');
    }, 1000);
}

// View report function
function viewReport(id) {
    // Sample report content - in real implementation, fetch actual report data
    var reportContent = '<div class="report-header text-center mb-4">' +
        '<h3>Laboratory Report #' + id + '</h3>' +
        '<p class="text-muted">Generated on ' + new Date().toLocaleDateString() + '</p>' +
        '</div>' +
        '<div class="row">' +
        '<div class="col-md-6">' +
        '<h5>Summary Statistics</h5>' +
        '<ul class="list-unstyled">' +
        '<li><strong>Total Tests:</strong> 156</li>' +
        '<li><strong>Completed Tests:</strong> 133</li>' +
        '<li><strong>Pending Tests:</strong> 23</li>' +
        '<li><strong>Critical Results:</strong> 5</li>' +
        '</ul>' +
        '</div>' +
        '<div class="col-md-6">' +
        '<h5>Revenue Information</h5>' +
        '<ul class="list-unstyled">' +
        '<li><strong>Total Revenue:</strong> $12,450</li>' +
        '<li><strong>Average per Test:</strong> $93.60</li>' +
        '<li><strong>Top Test Category:</strong> Hematology</li>' +
        '</ul>' +
        '</div>' +
        '</div>';

    $('#reportContent').html(reportContent);
    $('#modal-view-report').modal('show');
}

// Delete report function
function deleteReport(id) {
    if (confirm('Are you sure you want to delete this report?')) {
        // Find and remove the row
        var table = $('#reportsTable').DataTable();
        var row = table.row(function(idx, data, node) {
            return $(node).find('button[onclick="deleteReport(' + id + ')"]').length > 0;
        });
        
        if (row.length) {
            row.remove().draw();
            toastr.success('Report deleted successfully!');
        }
    }
}

// Print report function
function printReport() {
    var printContent = $('#reportContent').html();
    var printWindow = window.open('', '_blank');
    printWindow.document.write('<html><head><title>Laboratory Report</title></head><body>' + printContent + '</body></html>');
    printWindow.document.close();
    printWindow.print();
}
</script>
EOT;

// Include footer
include 'includes/footer.php';
?>
