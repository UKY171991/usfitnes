  <!-- /.content-wrapper -->
  <footer class="main-footer">
    <strong>Copyright &copy; 2023-<?php echo date('Y'); ?> <a href="#">PathLab Pro</a>.</strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
      <b>Version</b> 1.0.0
    </div>
  </footer>

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
    <div class="p-3">
      <h5>Customize</h5>
      <hr class="mb-2">
      
      <!-- Theme Color -->
      <div class="mb-4">
        <h6>Theme Color</h6>
        <div class="d-flex">
          <div class="color-mode color-primary active mr-2" data-color="primary"></div>
          <div class="color-mode color-secondary mr-2" data-color="secondary"></div>
          <div class="color-mode color-info mr-2" data-color="info"></div>
          <div class="color-mode color-success mr-2" data-color="success"></div>
          <div class="color-mode color-warning mr-2" data-color="warning"></div>
          <div class="color-mode color-danger mr-2" data-color="danger"></div>
        </div>
      </div>
      
      <!-- Layout Options -->
      <div class="mb-4">
        <h6>Layout Options</h6>
        <div class="form-group">
          <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" id="dark-mode-switch">
            <label class="custom-control-label" for="dark-mode-switch">Dark Mode</label>
          </div>
        </div>
        <div class="form-group">
          <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" id="sidebar-mini-switch" checked>
            <label class="custom-control-label" for="sidebar-mini-switch">Sidebar Mini</label>
          </div>
        </div>
        <div class="form-group">
          <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" id="navbar-fixed-switch">
            <label class="custom-control-label" for="navbar-fixed-switch">Fixed Navbar</label>
          </div>
        </div>
        <div class="form-group">
          <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" id="footer-fixed-switch">
            <label class="custom-control-label" for="footer-fixed-switch">Fixed Footer</label>
          </div>
        </div>
      </div>
      
      <!-- Quick Stats -->
      <div class="mb-4">
        <h6>System Status</h6>
        <small class="text-muted">Server Status: <span class="text-success">Online</span></small><br>
        <small class="text-muted">Database: <span class="text-success">Connected</span></small><br>
        <small class="text-muted">Last Backup: <span class="text-info"><?php echo date('M d, Y'); ?></span></small>
      </div>
    </div>
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>

<!-- AdminLTE App -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>

<!-- DataTables & Plugins -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>

<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Toastr -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Moment.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

<!-- Tempus Dominus Bootstrap 4 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/js/tempusdominus-bootstrap-4.min.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Custom Global Scripts -->
<script src="js/adminlte-custom.js?v=<?php echo time(); ?>"></script>

<script>
$(document).ready(function() {
    // Initialize AdminLTE3 components
    initializeAdminLTE();
    
    // Initialize common components
    initializeDataTables();
    initializeSelect2();
    initializeDateTimePickers();
    initializeTooltips();
    
    // Setup CSRF token for AJAX requests
    setupCSRFToken();
    
    // Initialize theme customization
    initializeThemeCustomization();
});

// Initialize AdminLTE3 specific features
function initializeAdminLTE() {
    // Preloader
    $(window).on('load', function() {
        $('.preloader').fadeOut('slow');
    });
    
    // Sidebar menu state
    if (localStorage.getItem('sidebar-state') === 'collapsed') {
        $('body').addClass('sidebar-collapse');
    }
    
    // Save sidebar state
    $('[data-widget="pushmenu"]').on('click', function() {
        setTimeout(function() {
            if ($('body').hasClass('sidebar-collapse')) {
                localStorage.setItem('sidebar-state', 'collapsed');
            } else {
                localStorage.removeItem('sidebar-state');
            }
        }, 300);
    });
}

// Initialize DataTables with consistent settings
function initializeDataTables() {
    $('.datatable').DataTable({
        responsive: true,
        autoWidth: false,
        pageLength: 25,
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search records...",
            lengthMenu: "_MENU_ records per page",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            paginate: {
                first: '<i class="fas fa-angle-double-left"></i>',
                previous: '<i class="fas fa-angle-left"></i>',
                next: '<i class="fas fa-angle-right"></i>',
                last: '<i class="fas fa-angle-double-right"></i>'
            }
        }
    });
}

// Initialize Select2
function initializeSelect2() {
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });
}

// Initialize DateTime Pickers
function initializeDateTimePickers() {
    $('.datetimepicker').datetimepicker({
        format: 'YYYY-MM-DD HH:mm',
        icons: {
            time: 'far fa-clock',
            date: 'far fa-calendar',
            up: 'fas fa-arrow-up',
            down: 'fas fa-arrow-down',
            previous: 'fas fa-chevron-left',
            next: 'fas fa-chevron-right',
            today: 'fas fa-calendar-check',
            clear: 'far fa-trash-alt',
            close: 'fas fa-times'
        }
    });
    
    $('.datepicker').datetimepicker({
        format: 'YYYY-MM-DD',
        icons: {
            date: 'far fa-calendar',
            previous: 'fas fa-chevron-left',
            next: 'fas fa-chevron-right',
            today: 'fas fa-calendar-check',
            clear: 'far fa-trash-alt',
            close: 'fas fa-times'
        }
    });
}

// Initialize Tooltips
function initializeTooltips() {
    $('[data-toggle="tooltip"]').tooltip();
    $('[data-toggle="popover"]').popover();
}

// Setup CSRF Token for AJAX
function setupCSRFToken() {
    $.ajaxSetup({
        beforeSend: function(xhr, settings) {
            if (!/^(GET|HEAD|OPTIONS|TRACE)$/i.test(settings.type) && !this.crossDomain) {
                xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
            }
        }
    });
}

// Theme Customization
function initializeThemeCustomization() {
    // Color mode switcher
    $('.color-mode').on('click', function() {
        var color = $(this).data('color');
        $('.color-mode').removeClass('active');
        $(this).addClass('active');
        
        // Apply color theme
        $('body').removeClass('sidebar-dark-primary sidebar-dark-secondary sidebar-dark-info sidebar-dark-success sidebar-dark-warning sidebar-dark-danger');
        $('body').addClass('sidebar-dark-' + color);
        
        // Save preference
        localStorage.setItem('theme-color', color);
    });
    
    // Load saved theme color
    var savedColor = localStorage.getItem('theme-color');
    if (savedColor) {
        $('.color-mode[data-color="' + savedColor + '"]').click();
    }
    
    // Dark mode toggle
    $('#dark-mode-switch').on('change', function() {
        if ($(this).is(':checked')) {
            $('body').addClass('dark-mode');
            localStorage.setItem('dark-mode', 'enabled');
        } else {
            $('body').removeClass('dark-mode');
            localStorage.removeItem('dark-mode');
        }
    });
    
    // Load dark mode preference
    if (localStorage.getItem('dark-mode') === 'enabled') {
        $('#dark-mode-switch').prop('checked', true);
        $('body').addClass('dark-mode');
    }
    
    // Layout options
    $('#sidebar-mini-switch').on('change', function() {
        $('body').toggleClass('sidebar-mini', $(this).is(':checked'));
    });
    
    $('#navbar-fixed-switch').on('change', function() {
        $('body').toggleClass('layout-navbar-fixed', $(this).is(':checked'));
    });
    
    $('#footer-fixed-switch').on('change', function() {
        $('body').toggleClass('layout-footer-fixed', $(this).is(':checked'));
    });
}

// Notification helpers
window.showSuccess = function(message, title = 'Success') {
    toastr.success(message, title);
};

window.showError = function(message, title = 'Error') {
    toastr.error(message, title);
};

window.showWarning = function(message, title = 'Warning') {
    toastr.warning(message, title);
};

window.showInfo = function(message, title = 'Info') {
    toastr.info(message, title);
};

// SweetAlert2 helpers
window.confirmDelete = function(callback, title = 'Are you sure?', text = 'You won\'t be able to revert this!') {
    Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed && callback) {
            callback();
        }
    });
};
</script>

<style>
/* Control Sidebar Customization */
.color-mode {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    cursor: pointer;
    border: 2px solid transparent;
    transition: all 0.2s;
}

.color-mode.active {
    border-color: #fff;
    transform: scale(1.2);
}

.color-mode.color-primary { background-color: #007bff; }
.color-mode.color-secondary { background-color: #6c757d; }
.color-mode.color-info { background-color: #17a2b8; }
.color-mode.color-success { background-color: #28a745; }
.color-mode.color-warning { background-color: #ffc107; }
.color-mode.color-danger { background-color: #dc3545; }

/* Toastr customization */
.toast-success {
    background-color: #28a745;
}

.toast-error {
    background-color: #dc3545;
}

.toast-warning {
    background-color: #ffc107;
    color: #212529;
}

.toast-info {
    background-color: #17a2b8;
}
</style>

</body>
</html>
