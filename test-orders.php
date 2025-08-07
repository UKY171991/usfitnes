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
                <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#testOrderModal" onclick="openTestOrderModal()">
                  <i class="fas fa-plus mr-1"></i>Create Order
                </button>
              </div>
            </div>
            <div class="card-body">
              <table id="testOrdersTable" class="table table-bordered table-striped">
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
      <div class="modal-header">
        <h4 class="modal-title" id="testOrderModalLabel">Create Test Order</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="testOrderForm">
        <div class="modal-body">
          <input type="hidden" id="testOrderId" name="id">
          
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label for="patientSelect">Patient <span class="text-danger">*</span></label>
                <select class="form-control select2" id="patientSelect" name="patient_id" required style="width: 100%;">
                  <option value="">Select Patient</option>
                </select>
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
          
          <div class="form-group">
            <label>Tests <span class="text-danger">*</span></label>
            <div class="card">
              <div class="card-body">
                <div id="testsContainer">
                  <!-- Tests will be loaded here -->
                </div>
              </div>
            </div>
          </div>
          
          <div class="form-group">
            <label for="notes">Notes</label>
            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
          </div>
          
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
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-info">Create Order</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#testOrdersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: 'ajax/test_orders_datatable.php',
            type: 'POST'
          
                coor);
                toastr.error('Failed 
            }
        },
        columns: [
            { data: 'order_numb
            { data: 'patient_name' },
            { data: 'doctor_name' },
            { data: 'test_count' },
          
            { data: 'priority' },
            { data: 'or
            { data: 'act
       
    
        pageLength: 25,
        responsive: true
    });
    
    
    $('.select2').select2({
        theme: 'bootstrap4'
    });
    
    // on
    {
        e.preventDefault();
        saveTestOrder();
    });
    
    // Load patients
    $(' {
    ;
        loadDoctors();
        loadTests();
    });
    
   
n() {
        calculateTotal();
    });
});

function openTestOrderModal(id
    if (id) {
        // Edit mod
        $('#testOrderModalLabel').text('Edit Test Order');
        loadTestOrderData(id);
    } else {
        // Add mode
     t Order');
 et();

        $('#orderDate').v
    }
}

function loadPatients() {
    $.ajax({
        url: 'api/patients_api.php',
        type: 'GET',
        data: { limit: 1000, status: 'active' },
        success: function(response) {
            if (response.success) {
                con;
             ;
         nt) {
       ;
 
  }
        },
        erroction() {
            toastr.error('Failed to
        }
    });
}

function loadDoctors() {
    $.ajax({
        url: 'api/doctors_api.php',
        type: 'GET',
        data: { lim
        succe
         
       ');
 on>');
tor) {
                    se);
              });
            }
        },
        error: function() {
            toastr.error('Failed to
        }
    });
}

function loadTests() {
    $.ajax({
        url: 'api/tests_api.php',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const container = $('#testsContainer');
                container.empty();
                
                respon{
                    const testHtml = `
                   >
             " 
         ">
       .id}">
 e}
}</small>
                           
                  div>
                    `;
                    container.append(testHtml);
       
     }
        },
        error: function() {
    ');
        }
   });
}

function calculateTotal() {
    let total = 0;
    $('.test-checkbox:checked').each(funct) {
       
    });
    
    const discount = parseFloat($('#discount').val()) ||;
    const final
    
    ;
}

funcer() {
    const selectedTests = [];
    
        sele));
    });
    
    if (selectedTests.l
        toastr.error('Pleas
        return;
    }
    
    const formData = new FormData($('#testOrderFo
    formData.append('tests', JSON.stringify(selecte
    
    const isEdit = $== '';
    
    $.ajax({
        ur,
        type: isEdit ? 'PUT
        data: formData,
        p: false,
       ,
 ) {
cess) {
                toastr.successe);
               ide');
                $('#testOrdersT
            } else {
                toastr.esage);
            }
        },
        error: function() {
            toastr.error('Error saving test ');
        }
    });
}

function deleteTestOrder(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This will cancel the test orr!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: 
        cancelButtonColor: '#d33',
        confirmButtonit!'
    }).then((resul => {
        if (result.isConfirmed) {
            $.ajax({
                u
               'DELETE',
         id: id },
       {
 {
         ge);
();
                    } else {
                   e);
                    }
                },
                error: function() {
                    toast
                }
            });
        }
    });
}

function viewTestOrder(id) {
    // View test order details (can be implemented later)
    toastr.info('View tes;
}
</script>

<?php include 'includes/adminlte_temp> ?p';footer.phlate_