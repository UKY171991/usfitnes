  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->

  <!-- Main Footer -->
  <footer class="main-footer text-center">
    <div class="float-right d-none d-sm-block">
      <strong>Version 1.0.0</strong>
    </div>
    <strong>Copyright &copy; <?php echo date('Y'); ?> <a href="#" class="text-primary">PathLab Pro</a>.</strong> All rights reserved.
  </footer>
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<!-- Bootstrap 4 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>

<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

<!-- DataTables & Plugins -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>

<!-- Toastr -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Custom CRUD Operations -->
<script src="js/app.js?v=<?php echo time(); ?>"></script>

<script>
// Global Toastr Configuration
toastr.options = {
  closeButton: true,
  debug: false,
  newestOnTop: true,
  progressBar: true,
  positionClass: "toast-top-right",
  preventDuplicates: true,
  onclick: null,
  showDuration: "300",
  hideDuration: "1000",
  timeOut: "5000",
  extendedTimeOut: "1000",
  showEasing: "swing",
  hideEasing: "linear",
  showMethod: "fadeIn",
  hideMethod: "fadeOut"
};

// Global AJAX Setup
$.ajaxSetup({
  beforeSend: function() {
    $('body').append('<div id="loading-overlay" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;display:flex;align-items:center;justify-content:center;"><div class="spinner-border text-light" role="status"></div></div>');
  },
  complete: function() {
    $('#loading-overlay').remove();
  },
  error: function(xhr, status, error) {
    $('#loading-overlay').remove();
    if (xhr.status === 401) {
      toastr.error('Session expired. Please login again.');
      setTimeout(() => window.location.href = 'login.php', 2000);
    } else {
      toastr.error('An error occurred: ' + error);
    }
  }
});

// Load sidebar counts on page load
$(document).ready(function() {
  loadSidebarCounts();
});

function loadSidebarCounts() {
  $.get('api/dashboard_counts.php')
    .done(function(response) {
      if (response.success) {
        $('#patients-count').text(response.data.patients || 0);
        $('#doctors-count').text(response.data.doctors || 0);
        $('#equipment-count').text(response.data.equipment || 0);
        $('#orders-count').text(response.data.orders || 0);
      }
    })
    .fail(function() {
      console.log('Failed to load sidebar counts');
    });
}
</script>

</body>
</html>
