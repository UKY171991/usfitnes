<?php
// Set page title
$page_title = 'Lab Tests';

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
            <h1 class="m-0">Lab Tests Management</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
              <li class="breadcrumb-item active">Lab Tests</li>
            </ol>
          </div>
        </div>
      </div>
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <!-- Lab Tests DataTable -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title"><i class="fas fa-flask mr-2"></i>Available Lab Tests</h3>
            <div class="card-tools">
              <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal-add-test">
                <i class="fas fa-plus"></i> Add New Test
              </button>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table id="testsTable" class="table table-bordered table-striped table-hover">
                <thead>
                  <tr>
                    <th>Test Code</th>
                    <th>Test Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Sample Type</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <!-- Sample data -->
                  <tr>
                    <td><span class="badge badge-info">CBC001</span></td>
                    <td>Complete Blood Count</td>
                    <td><span class="badge badge-secondary">Hematology</span></td>
                    <td><strong>$45.00</strong></td>
                    <td><span class="badge badge-primary">Blood</span></td>
                    <td>
                      <div class="btn-group" role="group">
                        <button type="button" class="btn btn-warning btn-sm" onclick="editTest(1)" title="Edit">
                          <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteTest(1)" title="Delete">
                          <i class="fas fa-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td><span class="badge badge-info">GLUC01</span></td>
                    <td>Glucose Fasting</td>
                    <td><span class="badge badge-secondary">Chemistry</span></td>
                    <td><strong>$25.00</strong></td>
                    <td><span class="badge badge-primary">Blood</span></td>
                    <td>
                      <div class="btn-group" role="group">
                        <button type="button" class="btn btn-warning btn-sm" onclick="editTest(2)" title="Edit">
                          <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteTest(2)" title="Delete">
                          <i class="fas fa-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td><span class="badge badge-info">URIN01</span></td>
                    <td>Urinalysis Complete</td>
                    <td><span class="badge badge-secondary">Chemistry</span></td>
                    <td><strong>$35.00</strong></td>
                    <td><span class="badge badge-warning">Urine</span></td>
                    <td>
                      <div class="btn-group" role="group">
                        <button type="button" class="btn btn-warning btn-sm" onclick="editTest(3)" title="Edit">
                          <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteTest(3)" title="Delete">
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

  <!-- Add Test Modal -->
  <div class="modal fade" id="modal-add-test" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header bg-primary">
          <h4 class="modal-title text-white"><i class="fas fa-plus mr-2"></i>Add New Lab Test</h4>
          <button type="button" class="close text-white" data-dismiss="modal">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="addTestForm">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="test_code">Test Code <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="test_code" name="test_code" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="test_name">Test Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="test_name" name="test_name" required>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="category">Category <span class="text-danger">*</span></label>
                  <select class="form-control" id="category" name="category" required>
                    <option value="">Select Category</option>
                    <option value="Hematology">Hematology</option>
                    <option value="Chemistry">Chemistry</option>
                    <option value="Microbiology">Microbiology</option>
                    <option value="Immunology">Immunology</option>
                    <option value="Pathology">Pathology</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="price">Price ($) <span class="text-danger">*</span></label>
                  <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="sample_type">Sample Type <span class="text-danger">*</span></label>
                  <select class="form-control" id="sample_type" name="sample_type" required>
                    <option value="">Select Sample Type</option>
                    <option value="Blood">Blood</option>
                    <option value="Urine">Urine</option>
                    <option value="Stool">Stool</option>
                    <option value="Sputum">Sputum</option>
                    <option value="Saliva">Saliva</option>
                    <option value="Other">Other</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="turnaround_time">Turnaround Time</label>
                  <input type="text" class="form-control" id="turnaround_time" name="turnaround_time" placeholder="e.g., 24 hours">
                </div>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="saveTestBtn">
            <i class="fas fa-save mr-1"></i>Save Test
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Test Modal -->
  <div class="modal fade" id="modal-edit-test" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header bg-warning">
          <h4 class="modal-title text-white"><i class="fas fa-edit mr-2"></i>Edit Lab Test</h4>
          <button type="button" class="close text-white" data-dismiss="modal">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="editTestForm">
            <input type="hidden" id="edit_test_id" name="id">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="edit_test_code">Test Code <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="edit_test_code" name="test_code" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="edit_test_name">Test Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="edit_test_name" name="test_name" required>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="edit_category">Category <span class="text-danger">*</span></label>
                  <select class="form-control" id="edit_category" name="category" required>
                    <option value="">Select Category</option>
                    <option value="Hematology">Hematology</option>
                    <option value="Chemistry">Chemistry</option>
                    <option value="Microbiology">Microbiology</option>
                    <option value="Immunology">Immunology</option>
                    <option value="Pathology">Pathology</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="edit_price">Price ($) <span class="text-danger">*</span></label>
                  <input type="number" class="form-control" id="edit_price" name="price" step="0.01" min="0" required>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="edit_sample_type">Sample Type <span class="text-danger">*</span></label>
                  <select class="form-control" id="edit_sample_type" name="sample_type" required>
                    <option value="">Select Sample Type</option>
                    <option value="Blood">Blood</option>
                    <option value="Urine">Urine</option>
                    <option value="Stool">Stool</option>
                    <option value="Sputum">Sputum</option>
                    <option value="Saliva">Saliva</option>
                    <option value="Other">Other</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="edit_turnaround_time">Turnaround Time</label>
                  <input type="text" class="form-control" id="edit_turnaround_time" name="turnaround_time" placeholder="e.g., 24 hours">
                </div>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-warning" id="updateTestBtn">
            <i class="fas fa-save mr-1"></i>Update Test
          </button>
        </div>
      </div>
    </div>
  </div>

<?php
// Additional scripts specific to the tests page
$additional_scripts = <<<EOT
<script>
$(document).ready(function() {
    // Initialize DataTable
    var testsTable = $('#testsTable').DataTable({
        responsive: true,
        lengthChange: false,
        autoWidth: false
    });
    
    // Handle Add Test form submission
    $('#saveTestBtn').on('click', function() {
        var formData = {
            test_code: $('#test_code').val(),
            test_name: $('#test_name').val(),
            category: $('#category').val(),
            price: $('#price').val(),
            sample_type: $('#sample_type').val(),
            turnaround_time: $('#turnaround_time').val()
        };
        
        // Basic validation
        if (!formData.test_code || !formData.test_name || !formData.category || !formData.price || !formData.sample_type) {
            toastr.error('Please fill in all required fields.');
            return;
        }

        // Simulate API call - in real implementation, replace with actual API
        setTimeout(function() {
            // Add new row to table
            var newRow = [
                '<span class="badge badge-info">' + formData.test_code + '</span>',
                formData.test_name,
                '<span class="badge badge-secondary">' + formData.category + '</span>',
                '<strong>$' + parseFloat(formData.price).toFixed(2) + '</strong>',
                '<span class="badge badge-primary">' + formData.sample_type + '</span>',
                '<div class="btn-group" role="group">' +
                    '<button type="button" class="btn btn-warning btn-sm" onclick="editTest(' + (testsTable.rows().count() + 1) + ')" title="Edit">' +
                        '<i class="fas fa-edit"></i>' +
                    '</button>' +
                    '<button type="button" class="btn btn-danger btn-sm" onclick="deleteTest(' + (testsTable.rows().count() + 1) + ')" title="Delete">' +
                        '<i class="fas fa-trash"></i>' +
                    '</button>' +
                '</div>'
            ];
            
            testsTable.row.add(newRow).draw();
            
            toastr.success('Test added successfully!');
            $('#modal-add-test').modal('hide');
            $('#addTestForm')[0].reset();
        }, 500);
    });

    // Handle Update Test form submission
    $('#updateTestBtn').on('click', function() {
        var formData = {
            id: $('#edit_test_id').val(),
            test_code: $('#edit_test_code').val(),
            test_name: $('#edit_test_name').val(),
            category: $('#edit_category').val(),
            price: $('#edit_price').val(),
            sample_type: $('#edit_sample_type').val(),
            turnaround_time: $('#edit_turnaround_time').val()
        };
        
        // Basic validation
        if (!formData.test_code || !formData.test_name || !formData.category || !formData.price || !formData.sample_type) {
            toastr.error('Please fill in all required fields.');
            return;
        }

        // Simulate API call - in real implementation, replace with actual API
        setTimeout(function() {
            toastr.success('Test updated successfully!');
            $('#modal-edit-test').modal('hide');
            $('#editTestForm')[0].reset();
            // In real implementation, refresh the table data
        }, 500);
    });

    // Reset form when modal is hidden
    $('#modal-add-test').on('hidden.bs.modal', function () {
        $('#addTestForm')[0].reset();
    });
    
    $('#modal-edit-test').on('hidden.bs.modal', function () {
        $('#editTestForm')[0].reset();
    });
});

// Edit test function
function editTest(id) {
    // Get the row data
    var table = $('#testsTable').DataTable();
    var row = table.row(function(idx, data, node) {
        return $(node).find('button[onclick="editTest(' + id + ')"]').length > 0;
    });
    
    if (row.length) {
        var data = row.data();
        
        // Extract data from the row and populate edit form
        $('#edit_test_id').val(id);
        $('#edit_test_code').val($(data[0]).text()); // Extract text from badge
        $('#edit_test_name').val(data[1]);
        $('#edit_category').val($(data[2]).text()); // Extract text from badge
        $('#edit_price').val($(data[3]).text().replace('$', ''));
        $('#edit_sample_type').val($(data[4]).text()); // Extract text from badge
        
        $('#modal-edit-test').modal('show');
    }
}

// Delete test function
function deleteTest(id) {
    if (confirm('Are you sure you want to delete this test?')) {
        // Simulate API call - in real implementation, replace with actual API
        setTimeout(function() {
            // Find and remove the row
            var table = $('#testsTable').DataTable();
            var row = table.row(function(idx, data, node) {
                return $(node).find('button[onclick="deleteTest(' + id + ')"]').length > 0;
            });
            
            if (row.length) {
                row.remove().draw();
                toastr.success('Test deleted successfully!');
            }
        }, 300);
    }
}
</script>
EOT;

// Include footer
include 'includes/footer.php';
?>
