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
                    <th>Test Code</th>
                    <th>Test Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Sample Type</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody id="testsTableBody">
                  <!-- Dynamic content will be loaded here -->
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
                  <label for="category_id">Category <span class="text-danger">*</span></label>
                  <select class="form-control" id="category_id" name="category_id" required>
                    <option value="">Select Category</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="price">Price <span class="text-danger">*</span></label>
                  <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label for="sample_type">Sample Type</label>
                  <select class="form-control" id="sample_type" name="sample_type">
                    <option value="Blood">Blood</option>
                    <option value="Urine">Urine</option>
                    <option value="Stool">Stool</option>
                    <option value="Sputum">Sputum</option>
                    <option value="Other">Other</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="normal_range">Normal Range</label>
                  <input type="text" class="form-control" id="normal_range" name="normal_range" placeholder="e.g., 4.5-5.5 million/μL">
                </div>
              </div>
            </div>
            <div class="form-group">
              <label for="description">Description</label>
              <textarea class="form-control" id="description" name="description" rows="2" placeholder="Brief description of the test"></textarea>
            </div>
          </form>
        </div>
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
    // Load categories
    loadCategories();
    
    // Initialize DataTable
    $('#testsTable').DataTable({
        responsive: true,
        lengthChange: false,
        autoWidth: false
    });
    
    // Handle form submission
    $('#saveTestBtn').on('click', function() {
        var formData = {
            action: 'create',
            test_name: $('#test_name').val(),
            test_code: $('#test_code').val(),
            category_id: $('#category_id').val(),
            price: $('#price').val(),
            sample_type: $('#sample_type').val(),
            normal_range: $('#normal_range').val(),
            description: $('#description').val()
        };
        
        // Basic validation
        if (!formData.test_code || !formData.test_name || !formData.category_id || !formData.price) {
            alert('Please fill in all required fields.');
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
                    alert('Test added successfully!');
                    $('#modal-add-test').modal('hide');
                    $('#addTestForm')[0].reset();
                    loadTests(); // Reload the tests table
                } else {
                    alert('Error: ' + (response.message || 'Failed to add test'));
                }
            },
            error: function(xhr, status, error) {
                console.error('Error adding test:', error);
                alert('Failed to add test. Please try again.');
            }
        });
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
                        alert('Test deleted successfully!');
                        loadTests(); // Reload the tests table
                    } else {
                        alert('Error: ' + (response.message || 'Failed to delete test'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error deleting test:', error);
                    alert('Failed to delete test. Please try again.');
                }
            });
        }
    });

    // Load categories function
    function loadCategories() {
        $.ajax({
            url: 'api/tests_api.php?action=categories',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var categorySelect = $('#category_id');
                    categorySelect.empty().append('<option value="">Select Category</option>');
                    
                    response.data.forEach(function(category) {
                        categorySelect.append('<option value="' + category.id + '">' + category.category_name + '</option>');
                    });
                } else {
                    console.error('Failed to load categories');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading categories:', error);
            }
        });
    }

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
                            '<span class="badge badge-info">' + test.test_code + '</span>',
                            test.test_name,
                            '<span class="badge badge-secondary">' + (test.category_name || 'N/A') + '</span>',
                            '<strong>$' + parseFloat(test.price).toFixed(2) + '</strong>',
                            '<span class="badge badge-primary">' + (test.sample_type || 'Blood') + '</span>',
                            '<div class="btn-group" role="group">' +
                                '<button type="button" class="btn btn-danger btn-sm btn-delete-test" data-id="' + test.id + '" title="Delete">' +
                                    '<i class="fas fa-trash"></i>' +
                                '</button>' +
                            '</div>'
                        ]);
                    });
                    
                    table.draw();
                } else {
                    console.error('Failed to load tests:', response.message);
                    $('#testsTableBody').html('<tr><td colspan="6" class="text-center text-danger">Error loading tests: ' + response.message + '</td></tr>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading tests:', error);
                $('#testsTableBody').html('<tr><td colspan="6" class="text-center text-danger">Failed to load tests. Please check console for details.</td></tr>');
            }
        });
    }

    // Load tests on page load
    loadTests();
});
</script>
EOT;

// Include footer
include 'includes/footer.php';
?>
