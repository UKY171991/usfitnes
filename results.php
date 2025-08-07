<?php
// Set page title
$page_title = 'Test Results Management - PathLab Pro';

// Include database connection
require_once 'config.php';

// Get action parameter
$action = $_GET['action'] ?? 'list';
$result_id = $_GET['id'] ?? null;

// Get test results data
$test_results = [];
try {
    $query = "SELECT r.*, p.first_name, p.last_name, t.test_type 
              FROM test_results r 
              LEFT JOIN patients p ON r.patient_id = p.id 
              LEFT JOIN test_orders t ON r.order_id = t.id 
              WHERE r.status != 'deleted' 
              ORDER BY r.created_at DESC";
    $result = mysqli_query($conn, $query);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $test_results[] = $row;
        }
    }
} catch (Exception $e) {
    error_log("Test results query error: " . $e->getMessage());
}

// Include AdminLTE header and sidebar
include 'includes/adminlte_template_header.php';
include 'includes/adminlte_sidebar.php';
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">
            <i class="fas fa-chart-line mr-2 text-success"></i>
            Test Results Management
          </h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item">
              <a href="dashboard.php">
                <i class="fas fa-home"></i> Home
              </a>
            </li>
            <li class="breadcrumb-item active">Test Results</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      
      <?php if ($action === 'list'): ?>
      <!-- List View -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-list mr-1"></i>
                All Test Results
              </h3>
              <div class="card-tools">
                <a href="?action=add" class="btn btn-primary btn-sm">
                  <i class="fas fa-plus mr-1"></i>Add New Result
                </a>
              </div>
            </div>
            <div class="card-body">
              <?php if (empty($test_results)): ?>
                <div class="text-center p-4">
                  <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                  <h5 class="text-muted">No Test Results Found</h5>
                  <p class="text-muted">Test results will appear here once tests are completed.</p>
                  <a href="?action=add" class="btn btn-primary">
                    <i class="fas fa-plus mr-2"></i>Add First Result
                  </a>
                </div>
              <?php else: ?>
                <table id="testResultsTable" class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th>Result ID</th>
                      <th>Patient</th>
                      <th>Test Type</th>
                      <th>Result Status</th>
                      <th>Test Date</th>
                      <th>Result Date</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($test_results as $result): ?>
                    <tr>
                      <td><strong>#<?php echo htmlspecialchars($result['id'] ?? ''); ?></strong></td>
                      <td>
                        <div>
                          <strong><?php echo htmlspecialchars(($result['first_name'] ?? '') . ' ' . ($result['last_name'] ?? '')); ?></strong>
                        </div>
                      </td>
                      <td><?php echo htmlspecialchars($result['test_type'] ?? ''); ?></td>
                      <td>
                        <?php
                        $status = $result['result_status'] ?? 'normal';
                        $badge_classes = [
                            'normal' => 'badge-success',
                            'abnormal' => 'badge-warning',
                            'critical' => 'badge-danger',
                            'pending' => 'badge-info'
                        ];
                        $badge_class = $badge_classes[$status] ?? 'badge-secondary';
                        echo "<span class=\"badge {$badge_class}\">" . ucfirst($status) . "</span>";
                        ?>
                      </td>
                      <td><?php echo $result['test_date'] ? date('M d, Y', strtotime($result['test_date'])) : ''; ?></td>
                      <td><?php echo $result['result_date'] ? date('M d, Y H:i', strtotime($result['result_date'])) : ''; ?></td>
                      <td>
                        <div class="btn-group">
                          <a href="?action=view&id=<?php echo $result['id']; ?>" class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i>
                          </a>
                          <a href="?action=edit&id=<?php echo $result['id']; ?>" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i>
                          </a>
                          <button class="btn btn-primary btn-sm" onclick="downloadResult(<?php echo $result['id']; ?>)">
                            <i class="fas fa-download"></i>
                          </button>
                          <button class="btn btn-secondary btn-sm" onclick="emailResult(<?php echo $result['id']; ?>)">
                            <i class="fas fa-envelope"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      
      <?php elseif ($action === 'add' || $action === 'edit'): ?>
      <!-- Add/Edit Form -->
      <div class="row">
        <div class="col-md-10 offset-md-1">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-<?php echo $action === 'add' ? 'plus' : 'edit'; ?> mr-1"></i>
                <?php echo $action === 'add' ? 'Add New' : 'Edit'; ?> Test Result
              </h3>
              <div class="card-tools">
                <a href="?" class="btn btn-secondary btn-sm">
                  <i class="fas fa-arrow-left mr-1"></i>Back to List
                </a>
              </div>
            </div>
            <form id="testResultForm" method="POST">
              <div class="card-body">
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="order_id">Test Order <span class="text-danger">*</span></label>
                      <select class="form-control select2" id="order_id" name="order_id" required>
                        <option value="">Select Test Order</option>
                        <!-- Options would be populated from database -->
                      </select>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="result_status">Result Status <span class="text-danger">*</span></label>
                      <select class="form-control" id="result_status" name="result_status" required>
                        <option value="normal">Normal</option>
                        <option value="abnormal">Abnormal</option>
                        <option value="critical">Critical</option>
                        <option value="pending">Pending</option>
                      </select>
                    </div>
                  </div>
                </div>
                
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="test_date">Test Date <span class="text-danger">*</span></label>
                      <input type="date" class="form-control" id="test_date" name="test_date" required>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="result_date">Result Date <span class="text-danger">*</span></label>
                      <input type="datetime-local" class="form-control" id="result_date" name="result_date" required>
                    </div>
                  </div>
                </div>
                
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="technician_name">Technician Name</label>
                      <input type="text" class="form-control" id="technician_name" name="technician_name">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="reviewed_by">Reviewed By</label>
                      <input type="text" class="form-control" id="reviewed_by" name="reviewed_by">
                    </div>
                  </div>
                </div>
                
                <div class="form-group">
                  <label for="result_values">Result Values</label>
                  <textarea class="form-control" id="result_values" name="result_values" rows="6" 
                           placeholder="Enter test result values, measurements, and findings..."></textarea>
                </div>
                
                <div class="form-group">
                  <label for="interpretation">Interpretation</label>
                  <textarea class="form-control" id="interpretation" name="interpretation" rows="4" 
                           placeholder="Clinical interpretation of the results..."></textarea>
                </div>
                
                <div class="form-group">
                  <label for="recommendations">Recommendations</label>
                  <textarea class="form-control" id="recommendations" name="recommendations" rows="3" 
                           placeholder="Medical recommendations based on results..."></textarea>
                </div>
                
                <div class="form-group">
                  <label for="attachments">Attachments</label>
                  <input type="file" class="form-control-file" id="attachments" name="attachments[]" multiple 
                         accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                  <small class="form-text text-muted">Upload result files, images, or documents (PDF, JPG, PNG, DOC, DOCX)</small>
                </div>
                
                <div class="form-group">
                  <label for="notes">Internal Notes</label>
                  <textarea class="form-control" id="notes" name="notes" rows="3" 
                           placeholder="Internal notes for staff..."></textarea>
                </div>
              </div>
              
              <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-save mr-1"></i>
                  <?php echo $action === 'add' ? 'Save' : 'Update'; ?> Result
                </button>
                <a href="?" class="btn btn-secondary">
                  <i class="fas fa-times mr-1"></i>Cancel
                </a>
              </div>
            </form>
          </div>
        </div>
      </div>
      
      <?php elseif ($action === 'view'): ?>
      <!-- View Result Details -->
      <div class="row">
        <div class="col-md-10 offset-md-1">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-chart-line mr-1"></i>
                Test Result Details - #<?php echo $result_id; ?>
              </h3>
              <div class="card-tools">
                <a href="?" class="btn btn-secondary btn-sm">
                  <i class="fas fa-arrow-left mr-1"></i>Back to List
                </a>
                <a href="?action=edit&id=<?php echo $result_id; ?>" class="btn btn-warning btn-sm">
                  <i class="fas fa-edit mr-1"></i>Edit
                </a>
                <button class="btn btn-primary btn-sm" onclick="downloadResult(<?php echo $result_id; ?>)">
                  <i class="fas fa-download mr-1"></i>Download
                </button>
                <button class="btn btn-info btn-sm" onclick="emailResult(<?php echo $result_id; ?>)">
                  <i class="fas fa-envelope mr-1"></i>Email
                </button>
              </div>
            </div>
            <div class="card-body">
              <!-- Result details would be loaded here -->
              <div class="alert alert-info">
                <i class="fas fa-info-circle mr-2"></i>
                Test result details will be displayed here when integrated with the database.
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>
      
    </div><!-- /.container-fluid -->
  </section>
  <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<script>
$(document).ready(function() {
    // Initialize DataTable for test results list
    if ($('#testResultsTable').length) {
        $('#testResultsTable').DataTable({
            responsive: true,
            autoWidth: false,
            pageLength: 25,
            order: [[0, 'desc']], // Order by ID descending
            columnDefs: [
                { 
                    targets: -1, // Last column (Actions)
                    orderable: false,
                    searchable: false
                }
            ]
        });
    }
    
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });
    
    // Form validation
    $('#testResultForm').on('submit', function(e) {
        e.preventDefault();
        
        // Basic validation
        let isValid = true;
        $(this).find('[required]').each(function() {
            if (!$(this).val()) {
                isValid = false;
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        if (isValid) {
            // Submit form (you would typically send this to a handler)
            PathLabPro.notifications.success('Test result saved successfully!');
            // window.location.href = '?';
        } else {
            PathLabPro.notifications.error('Please fill in all required fields.');
        }
    });
});

function downloadResult(id) {
    // Download result as PDF
    PathLabPro.notifications.info('Generating PDF report...');
    // window.open('download_result.php?id=' + id, '_blank');
}

function emailResult(id) {
    PathLabPro.modal.prompt({
        title: 'Email Test Result',
        text: 'Enter the email address to send the result to:',
        inputPlaceholder: 'patient@example.com'
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            // Email result logic here
            PathLabPro.notifications.success('Result emailed successfully to ' + result.value);
        }
    });
}
</script>

<?php include 'includes/adminlte_template_footer.php'; ?>
