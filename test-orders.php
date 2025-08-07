<?php
require_once 'config.php';
require_once 'includes/init.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pageTitle = 'Test Orders Management';
$pageIcon = 'fas fa-vials';
$breadcrumbs = ['Test Orders'];

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
            <i class="<?php echo $pageIcon; ?> mr-2 text-info"></i><?php echo $pageTitle; ?>
          </h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
            <?php foreach($breadcrumbs as $index => $crumb): ?>
              <li class="breadcrumb-item active"><?php echo $crumb; ?></li>
            <?php endforeach; ?>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">
          <div class="card card-info card-outline">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-list mr-2"></i>All Test Orders
              </h3>
              <div class="card-tools">
                <button type="button" class="btn btn-info btn-sm" onclick="openAddModal()">
                  <i class="fas fa-plus mr-1"></i>Create Order
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm ml-1" onclick="refreshTable()">
                  <i class="fas fa-sync-alt mr-1"></i>Refresh
                </button>
              </div>
            </div>
            <div class="card-body">
              <table id="testOrdersTable" class="table table-bordered table-striped table-hover">
                <thead>
                  <tr>
                    <th>Order #</th>
                    <th>Patient</th>
                    <th>Doctor</th>
                    <th>Tests</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Date</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <!-- Data will be loaded via AJAX -->
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<!-- Test Order Modal -->
<div class="modal fade" id="testOrderModal" tabindex="-1" role="dialog" aria-labelledby="testOrderModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header bg-info">
        <h4 class="modal-title text-white" id="testOrderModalLabel">
          <i class="fas fa-vials mr-2"></i>Create Test Order
        </h4>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="testOrderForm">
        <div class="modal-body">
          <input type="hidden" name="id" id="testOrderId">
          
          <!-- Patient and Doctor Selection -->
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="patientSelect">Patient <span class="text-danger">*</span></label>
                <select class="form-control select2" id="patientSelect" name="patient_id" required style="width: 100%;">
                  <option value="">Select Patient</option>
                </select>
                <div class="invalid-feedback">Please select a patient.</div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="doctorSelect">Doctor</label>
                <select class="form-control select2" id="doctorSelect" name="doctor_id" style="width: 100%;">
                  <option value="">Select Doctor</option>
                </select>
              </div>
            </div>
          </div>
          
          <!-- Order Details -->
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="priority">Priority</label>
                <select class="form-control" id="priority" name="priority">
                  <option value="normal">Normal</option>
                  <option value="high">High</option>
                  <option value="urgent">Urgent</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="orderDate">Order Date</label>
                <input type="datetime-local" class="form-control" id="orderDate" name="order_date" value="<?php echo date('Y-m-d\TH:i'); ?>">
              </div>
            </div>
          </div>
          
          <!-- Tests Selection -->
          <div class="form-group">
            <label>Tests <span class="text-danger">*</span></label>
            <div class="card">
              <div class="card-header">
                <h5 class="card-title mb-0">Available Tests</h5>
              </div>
              <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                <div id="testsContainer">
                  <div class="text-center">
                    <i class="fas fa-spinner fa-spin"></i> Loading tests...
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Notes -->
          <div class="form-group">
            <label for="notes">Notes</label>
            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Additional notes for this test order"></textarea>
          </div>
          
          <!-- Pricing -->
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Total Amount</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">$</span>
                  </div>
                  <input type="number" class="form-control" id="totalAmount" name="total_amount" step="0.01" readonly>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label for="discount">Discount</label>
                <div class="input-group">
                  <div class="input-group-prepend">
                    <span class="input-group-text">$</span>
                  </div>
                  <input type="number" class="form-control" id="discount" name="discount" step="0.01" value="0">
                </div>
              </div>
            </div>
          </div>
          
          <!-- Final Amount Display -->
          <div class="alert alert-info">
            <strong>Final Amount: $<span id="finalAmount">0.00</span></strong>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i>Cancel
          </button>
          <button type="submit" class="btn btn-info">
            <i class="fas fa-save mr-1"></i>Create Order
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Page specific JavaScript -->
<script src="js/test-orders.js?v=<?php echo time(); ?>"></script>

<?php include 'includes/adminlte_template_footer.php'; ?>