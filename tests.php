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
        <!-- Alert Messages -->
        <div id="alertContainer"></div>
        
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
                    <th>Test ID</th>
                    <th>Test Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Turnaround Time</th>
                    <th>Sample Type</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="testsTableBody">
                  <tr>
                    <td><span class="badge badge-info">CBC001</span></td>
                    <td>Complete Blood Count</td>
                    <td><span class="badge badge-secondary">Hematology</span></td>
                    <td><strong>$25.00</strong></td>
                    <td>24 hours</td>
                    <td><span class="badge badge-danger">Blood</span></td>
                    <td><span class="badge badge-success">Active</span></td>
                    <td>
                      <div class="btn-group" role="group">
                        <button class="btn btn-sm btn-info btn-edit-test" data-id="CBC001" title="Edit">
                          <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-warning btn-view-test" data-id="CBC001" title="View Details">
                          <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-danger btn-delete-test" data-id="CBC001" title="Delete">
                          <i class="fas fa-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td><span class="badge badge-info">GLU001</span></td>
                    <td>Glucose Test</td>
                    <td><span class="badge badge-secondary">Chemistry</span></td>
                    <td><strong>$15.00</strong></td>
                    <td>12 hours</td>
                    <td><span class="badge badge-danger">Blood</span></td>
                    <td><span class="badge badge-success">Active</span></td>
                    <td>
                      <div class="btn-group" role="group">
                        <button class="btn btn-sm btn-info btn-edit-test" data-id="GLU001" title="Edit">
                          <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-warning btn-view-test" data-id="GLU001" title="View Details">
                          <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-danger btn-delete-test" data-id="GLU001" title="Delete">
                          <i class="fas fa-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td><span class="badge badge-info">LFT001</span></td>
                    <td>Liver Function Test</td>
                    <td><span class="badge badge-secondary">Chemistry</span></td>
                    <td><strong>$45.00</strong></td>
                    <td>24 hours</td>
                    <td><span class="badge badge-danger">Blood</span></td>
                    <td><span class="badge badge-success">Active</span></td>
                    <td>
                      <div class="btn-group" role="group">
                        <button class="btn btn-sm btn-info btn-edit-test" data-id="LFT001" title="Edit">
                          <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-warning btn-view-test" data-id="LFT001" title="View Details">
                          <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-danger btn-delete-test" data-id="LFT001" title="Delete">
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
                  <label for="testId">Test ID <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="testId" name="test_id" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="testName">Test Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="testName" name="test_name" required>
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
                  <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="turnaroundTime">Turnaround Time <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="turnaroundTime" name="turnaround_time" placeholder="e.g., 24 hours" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="sampleType">Sample Type <span class="text-danger">*</span></label>
                  <select class="form-control" id="sampleType" name="sample_type" required>
                    <option value="">Select Sample Type</option>
                    <option value="Blood">Blood</option>
                    <option value="Urine">Urine</option>
                    <option value="Stool">Stool</option>
                    <option value="Saliva">Saliva</option>
                    <option value="Tissue">Tissue</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label for="description">Description</label>
              <textarea class="form-control" id="description" name="description" rows="3" placeholder="Test description and instructions"></textarea>
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

<?php
// Additional scripts specific to the tests page
$additional_scripts = <<<EOT
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#testsTable').DataTable({
        responsive: true,
        lengthChange: false,
        autoWidth: false,
        buttons: [
            {
                extend: 'copy',
                className: 'btn btn-sm btn-secondary'
            },
            {
                extend: 'csv',
                className: 'btn btn-sm btn-secondary'
            },
            {
                extend: 'excel',
                className: 'btn btn-sm btn-secondary'
            },
            {
                extend: 'pdf',
                className: 'btn btn-sm btn-secondary'
            },
            {
                extend: 'print',
                className: 'btn btn-sm btn-secondary'
            }
        ]
    }).buttons().container().appendTo('#testsTable_wrapper .col-md-6:eq(0)');    // Handle form submission
    $('#saveTestBtn').on('click', function() {
        var formData = {
            action: 'create',
            test_name: $('#testName').val(),
            test_code: $('#testId').val(),
            category: $('#category').val(),
            price: $('#price').val(),
            turn_around_time: $('#turnaroundTime').val(),
            sample_type: $('#sampleType').val(),
            description: $('#description').val()
        };        // Basic validation
        if (!formData.test_code || !formData.test_name || !formData.category || !formData.price) {
            showToaster('danger', 'Please fill in all required fields.');
            return;
        }

        // Send data to API
        $.ajax({
            url: 'api/tests_api.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showToaster('success', 'Test added successfully!');
                    $('#modal-add-test').modal('hide');
                    $('#addTestForm')[0].reset();
                    loadTests(); // Reload the tests table
                } else {
                    showToaster('danger', response.message || 'Failed to add test');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error adding test:', error);
                showToaster('danger', 'Failed to add test. Please try again.');
            }
        });
    });

    // Handle edit button clicks
    $(document).on('click', '.btn-edit-test', function() {
        var testId = $(this).data('id');
        // TODO: Implement edit functionality
        showToaster('info', 'Edit functionality for test ' + testId + ' will be implemented.');
    });

    // Handle view button clicks
    $(document).on('click', '.btn-view-test', function() {
        var testId = $(this).data('id');
        // TODO: Implement view functionality
        showToaster('info', 'View details for test ' + testId + ' will be implemented.');
    });

    // Handle delete button clicks
    $(document).on('click', '.btn-delete-test', function() {
        var testId = $(this).data('id');
        if (confirm('Are you sure you want to delete this test?')) {
            $.ajax({
                url: 'api/tests_api.php',
                method: 'POST',
                data: {
                    action: 'delete',
                    id: testId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showToaster('success', 'Test deleted successfully!');
                        loadTests(); // Reload the tests table
                    } else {
                        showToaster('danger', response.message || 'Failed to delete test');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error deleting test:', error);
                    showToaster('danger', 'Failed to delete test. Please try again.');
                }
            });
        }
    });

    // Load tests function
    function loadTests() {
        $.ajax({
            url: 'api/tests_api.php?action=list',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var table = $('#testsTable').DataTable();
                    table.clear();
                    
                    response.data.forEach(function(test) {
                        table.row.add([
                            test.test_code,
                            test.test_name,
                            test.category,
                            '$' + parseFloat(test.price).toFixed(2),
                            test.turn_around_time || 'N/A',
                            test.sample_type || 'N/A',
                            `<div class="btn-group" role="group">
                                <button type="button" class="btn btn-info btn-sm btn-view-test" data-id="${test.id}" title="View">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button" class="btn btn-warning btn-sm btn-edit-test" data-id="${test.id}" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm btn-delete-test" data-id="${test.id}" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>`
                        ]);
                    });
                    
                    table.draw();
                } else {
                    showToaster('danger', 'Failed to load tests');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading tests:', error);
                showToaster('danger', 'Failed to load tests');
            }
        });
    }    // Load tests on page load
    loadTests();
    });
});
</script>
EOT;

// Include footer
include 'includes/footer.php';
?>
