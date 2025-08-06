<?php
// Set page title
$page_title = 'Test Results';

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
            <h1 class="m-0">Test Results Management</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
              <li class="breadcrumb-item active">Test Results</li>
            </ol>
          </div>
        </div>
      </div>
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <!-- Test Results DataTable -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title"><i class="fas fa-file-medical mr-2"></i>Test Results</h3>
            <div class="card-tools">
              <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-add-result">
                <i class="fas fa-plus"></i> Add Result
              </button>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table id="resultsTable" class="table table-bordered table-striped table-hover">
                <thead>
                  <tr>
                    <th>Order ID</th>
                    <th>Patient</th>
                    <th>Test Name</th>
                    <th>Result Date</th>
                    <th>Status</th>
                    <th>Critical</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <!-- Sample data -->
                  <tr>
                    <td><span class="badge badge-info">#ORD001</span></td>
                    <td>John Doe</td>
                    <td>Complete Blood Count</td>
                    <td>2024-01-15</td>
                    <td><span class="badge badge-success">Completed</span></td>
                    <td><span class="badge badge-secondary">Normal</span></td>
                    <td>
                      <div class="btn-group" role="group">
                        <button type="button" class="btn btn-info btn-sm" onclick="viewResult(1)" title="View">
                          <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="btn btn-warning btn-sm" onclick="editResult(1)" title="Edit">
                          <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteResult(1)" title="Delete">
                          <i class="fas fa-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td><span class="badge badge-info">#ORD002</span></td>
                    <td>Jane Smith</td>
                    <td>Glucose Fasting</td>
                    <td>2024-01-14</td>
                    <td><span class="badge badge-warning">Pending</span></td>
                    <td><span class="badge badge-danger">Critical</span></td>
                    <td>
                      <div class="btn-group" role="group">
                        <button type="button" class="btn btn-info btn-sm" onclick="viewResult(2)" title="View">
                          <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="btn btn-warning btn-sm" onclick="editResult(2)" title="Edit">
                          <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteResult(2)" title="Delete">
                          <i class="fas fa-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td><span class="badge badge-info">#ORD003</span></td>
                    <td>Mike Johnson</td>
                    <td>Urinalysis Complete</td>
                    <td>2024-01-13</td>
                    <td><span class="badge badge-primary">Verified</span></td>
                    <td><span class="badge badge-secondary">Normal</span></td>
                    <td>
                      <div class="btn-group" role="group">
                        <button type="button" class="btn btn-info btn-sm" onclick="viewResult(3)" title="View">
                          <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="btn btn-warning btn-sm" onclick="editResult(3)" title="Edit">
                          <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteResult(3)" title="Delete">
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

  <!-- Add Result Modal -->
  <div class="modal fade" id="modal-add-result" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <h4 class="modal-title text-white"><i class="fas fa-plus mr-2"></i>Add Test Result</h4>
          <button type="button" class="close text-white" data-dismiss="modal">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="addResultForm">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="order_id">Order ID <span class="text-danger">*</span></label>
                  <select class="form-control" id="order_id" name="order_id" required>
                    <option value="">Select Order</option>
                    <option value="ORD001">ORD001 - John Doe - CBC</option>
                    <option value="ORD002">ORD002 - Jane Smith - Glucose</option>
                    <option value="ORD003">ORD003 - Mike Johnson - Urine</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="result_date">Result Date <span class="text-danger">*</span></label>
                  <input type="date" class="form-control" id="result_date" name="result_date" required>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="status">Status <span class="text-danger">*</span></label>
                  <select class="form-control" id="status" name="status" required>
                    <option value="">Select Status</option>
                    <option value="pending">Pending</option>
                    <option value="completed">Completed</option>
                    <option value="verified">Verified</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="is_critical">Critical Result</label>
                  <select class="form-control" id="is_critical" name="is_critical">
                    <option value="0">Normal</option>
                    <option value="1">Critical</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label for="result_values">Result Values <span class="text-danger">*</span></label>
              <textarea class="form-control" id="result_values" name="result_values" rows="3" 
                        placeholder="Enter test result values..." required></textarea>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="saveResultBtn">
            <i class="fas fa-save mr-1"></i>Save Result
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Result Modal -->
  <div class="modal fade" id="modal-edit-result" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header bg-warning">
          <h4 class="modal-title text-white"><i class="fas fa-edit mr-2"></i>Edit Test Result</h4>
          <button type="button" class="close text-white" data-dismiss="modal">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="editResultForm">
            <input type="hidden" id="edit_result_id" name="id">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="edit_order_id">Order ID <span class="text-danger">*</span></label>
                  <select class="form-control" id="edit_order_id" name="order_id" required>
                    <option value="">Select Order</option>
                    <option value="ORD001">ORD001 - John Doe - CBC</option>
                    <option value="ORD002">ORD002 - Jane Smith - Glucose</option>
                    <option value="ORD003">ORD003 - Mike Johnson - Urine</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="edit_result_date">Result Date <span class="text-danger">*</span></label>
                  <input type="date" class="form-control" id="edit_result_date" name="result_date" required>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="edit_status">Status <span class="text-danger">*</span></label>
                  <select class="form-control" id="edit_status" name="status" required>
                    <option value="">Select Status</option>
                    <option value="pending">Pending</option>
                    <option value="completed">Completed</option>
                    <option value="verified">Verified</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="edit_is_critical">Critical Result</label>
                  <select class="form-control" id="edit_is_critical" name="is_critical">
                    <option value="0">Normal</option>
                    <option value="1">Critical</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label for="edit_result_values">Result Values <span class="text-danger">*</span></label>
              <textarea class="form-control" id="edit_result_values" name="result_values" rows="3" required></textarea>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-warning" id="updateResultBtn">
            <i class="fas fa-save mr-1"></i>Update Result
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- View Result Modal -->
  <div class="modal fade" id="modal-view-result" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header bg-info">
          <h4 class="modal-title text-white"><i class="fas fa-eye mr-2"></i>View Test Result</h4>
          <button type="button" class="close text-white" data-dismiss="modal">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" id="viewResultContent">
          <!-- Content will be loaded dynamically -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" onclick="printResult()">
            <i class="fas fa-print mr-1"></i>Print Result
          </button>
        </div>
      </div>
    </div>
  </div>

<?php
// Additional scripts specific to the results page
$additional_scripts = <<<EOT
<script>
$(document).ready(function() {
    // Initialize DataTable
    var resultsTable = $('#resultsTable').DataTable({
        responsive: true,
        lengthChange: false,
        autoWidth: false
    });
    
    // Handle Add Result form submission
    $('#saveResultBtn').on('click', function() {
        var formData = {
            order_id: $('#order_id').val(),
            result_date: $('#result_date').val(),
            status: $('#status').val(),
            is_critical: $('#is_critical').val(),
            result_values: $('#result_values').val()
        };
        
        // Basic validation
        if (!formData.order_id || !formData.result_date || !formData.status || !formData.result_values) {
            toastr.error('Please fill in all required fields.');
            return;
        }

        // Simulate API call - in real implementation, replace with actual API
        setTimeout(function() {
            var statusBadge = '';
            switch(formData.status) {
                case 'pending': statusBadge = '<span class="badge badge-warning">Pending</span>'; break;
                case 'completed': statusBadge = '<span class="badge badge-success">Completed</span>'; break;
                case 'verified': statusBadge = '<span class="badge badge-primary">Verified</span>'; break;
            }
            
            var criticalBadge = formData.is_critical == '1' ? 
                '<span class="badge badge-danger">Critical</span>' : 
                '<span class="badge badge-secondary">Normal</span>';
            
            // Extract order details from the selected option
            var orderText = $('#order_id option:selected').text();
            var orderParts = orderText.split(' - ');
            var patientName = orderParts[1] || 'Unknown Patient';
            var testName = orderParts[2] || 'Unknown Test';
            
            // Add new row to table
            var newRow = [
                '<span class="badge badge-info">#' + formData.order_id + '</span>',
                patientName,
                testName,
                formData.result_date,
                statusBadge,
                criticalBadge,
                '<div class="btn-group" role="group">' +
                    '<button type="button" class="btn btn-info btn-sm" onclick="viewResult(' + (resultsTable.rows().count() + 1) + ')" title="View">' +
                        '<i class="fas fa-eye"></i>' +
                    '</button>' +
                    '<button type="button" class="btn btn-warning btn-sm" onclick="editResult(' + (resultsTable.rows().count() + 1) + ')" title="Edit">' +
                        '<i class="fas fa-edit"></i>' +
                    '</button>' +
                    '<button type="button" class="btn btn-danger btn-sm" onclick="deleteResult(' + (resultsTable.rows().count() + 1) + ')" title="Delete">' +
                        '<i class="fas fa-trash"></i>' +
                    '</button>' +
                '</div>'
            ];
            
            resultsTable.row.add(newRow).draw();
            
            toastr.success('Test result added successfully!');
            $('#modal-add-result').modal('hide');
            $('#addResultForm')[0].reset();
        }, 500);
    });

    // Handle Update Result form submission
    $('#updateResultBtn').on('click', function() {
        var formData = {
            id: $('#edit_result_id').val(),
            order_id: $('#edit_order_id').val(),
            result_date: $('#edit_result_date').val(),
            status: $('#edit_status').val(),
            is_critical: $('#edit_is_critical').val(),
            result_values: $('#edit_result_values').val()
        };
        
        // Basic validation
        if (!formData.order_id || !formData.result_date || !formData.status || !formData.result_values) {
            toastr.error('Please fill in all required fields.');
            return;
        }

        // Simulate API call - in real implementation, replace with actual API
        setTimeout(function() {
            toastr.success('Test result updated successfully!');
            $('#modal-edit-result').modal('hide');
            $('#editResultForm')[0].reset();
            // In real implementation, refresh the table data
        }, 500);
    });

    // Reset form when modal is hidden
    $('#modal-add-result').on('hidden.bs.modal', function () {
        $('#addResultForm')[0].reset();
    });
    
    $('#modal-edit-result').on('hidden.bs.modal', function () {
        $('#editResultForm')[0].reset();
    });
});

// View result function
function viewResult(id) {
    // Sample result details - in real implementation, fetch from API
    var resultDetails = '<div class="row">' +
        '<div class="col-md-6"><strong>Order ID:</strong> #ORD00' + id + '</div>' +
        '<div class="col-md-6"><strong>Patient:</strong> Patient ' + id + '</div>' +
        '</div><hr>' +
        '<div class="row">' +
        '<div class="col-md-6"><strong>Test:</strong> Test Name ' + id + '</div>' +
        '<div class="col-md-6"><strong>Date:</strong> 2024-01-15</div>' +
        '</div><hr>' +
        '<div class="row">' +
        '<div class="col-12"><strong>Result Values:</strong></div>' +
        '<div class="col-12">Sample test results and values would be displayed here.</div>' +
        '</div>';
    
    $('#viewResultContent').html(resultDetails);
    $('#modal-view-result').modal('show');
}

// Edit result function
function editResult(id) {
    // Get the row data
    var table = $('#resultsTable').DataTable();
    var row = table.row(function(idx, data, node) {
        return $(node).find('button[onclick="editResult(' + id + ')"]').length > 0;
    });
    
    if (row.length) {
        var data = row.data();
        
        // Extract data from the row and populate edit form
        $('#edit_result_id').val(id);
        $('#edit_order_id').val($(data[0]).text().replace('#', '')); // Extract order ID
        $('#edit_result_date').val(data[3]);
        
        // Extract status from badge
        var statusText = $(data[4]).text().toLowerCase();
        $('#edit_status').val(statusText);
        
        // Extract critical status from badge
        var criticalText = $(data[5]).text().toLowerCase();
        $('#edit_is_critical').val(criticalText === 'critical' ? '1' : '0');
        
        // Set sample result values
        $('#edit_result_values').val('Sample test result values for editing...');
        
        $('#modal-edit-result').modal('show');
    }
}

// Delete result function
function deleteResult(id) {
    if (confirm('Are you sure you want to delete this test result?')) {
        // Simulate API call - in real implementation, replace with actual API
        setTimeout(function() {
            // Find and remove the row
            var table = $('#resultsTable').DataTable();
            var row = table.row(function(idx, data, node) {
                return $(node).find('button[onclick="deleteResult(' + id + ')"]').length > 0;
            });
            
            if (row.length) {
                row.remove().draw();
                toastr.success('Test result deleted successfully!');
            }
        }, 300);
    }
}

// Print result function
function printResult() {
    var printContent = $('#viewResultContent').html();
    var printWindow = window.open('', '_blank');
    printWindow.document.write('<html><head><title>Test Result</title></head><body>' + printContent + '</body></html>');
    printWindow.document.close();
    printWindow.print();
}
</script>
EOT;

// Include footer
include 'includes/footer.php';
?>
