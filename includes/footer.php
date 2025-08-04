  <footer class="main-footer">
    <div class="float-right d-none d-sm-block">
      <b>Version</b> 1.2.0 | <span class="text-muted">PathLab Pro Dynamic</span>
    </div>
    <strong>Copyright &copy; 2023-<?php echo date('Y'); ?> <a href="#" class="text-primary">PathLab Pro</a>.</strong> All rights reserved.
  </footer>

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <div class="p-3">
      <h5><i class="fas fa-cog mr-2"></i>Settings</h5>
      <hr class="mb-2">
      <div class="mb-3">
        <label class="form-check-label">
          <input type="checkbox" class="form-check-input" id="darkModeToggle">
          <i class="fas fa-moon mr-2"></i>Dark Mode
        </label>
      </div>
      <div class="mb-3">
        <label class="form-check-label">
          <input type="checkbox" class="form-check-input" id="autoRefresh" checked>
          <i class="fas fa-sync mr-2"></i>Auto Refresh
        </label>
      </div>
      <div class="mb-3">
        <label class="form-check-label">
          <input type="checkbox" class="form-check-input" id="soundNotifications">
          <i class="fas fa-volume-up mr-2"></i>Sound Alerts
        </label>
      </div>
      <hr>
      <button class="btn btn-sm btn-primary btn-block" onclick="DynamicUtils.notify('info', 'Settings saved!')">
        <i class="fas fa-save mr-1"></i>Save Settings
      </button>
    </div>
  </aside>
</div>
<!-- ./wrapper -->

<!-- Loading Overlay -->
<div class="loading-overlay" id="globalLoadingOverlay">
    <div class="d-flex flex-column align-items-center">
        <div class="spinner-border text-light mb-3" role="status" style="width: 3rem; height: 3rem;">
            <span class="sr-only">Loading...</span>
        </div>
        <div class="text-light">
            <span id="loadingMessage">Loading...</span>
        </div>
    </div>
</div>

<!-- Core JavaScript Libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>

<!-- AdminLTE and UI Components -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

<!-- DataTables and Extensions -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<!-- Form and Input Components -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/js/tempusdominus-bootstrap-4.min.js"></script>

<!-- Charts and Visualization -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.min.js"></script>

<!-- Utilities -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/6.0.0/bootbox.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.11/clipboard.min.js"></script>

<!-- PathLab Pro Dynamic Utilities -->
<script src="js/dynamic-utils.js"></script>
<!-- daterangepicker -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.js"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- Summernote -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs4.min.js"></script>
<!-- overlayScrollbars -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/overlayscrollbars/1.13.1/js/jquery.overlayScrollbars.min.js"></script>
<!-- DataTables  & Plugins -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables.net/1.12.1/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables.net-bs4/1.12.1/dataTables.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables.net-responsive/2.4.1/dataTables.responsive.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables.net-responsive-bs4/2.4.1/responsive.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables.net-buttons/2.2.3/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables.net-buttons-bs4/2.2.3/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables.net-buttons/2.2.3/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables.net-buttons/2.2.3/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables.net-buttons/2.2.3/js/buttons.colVis.min.js"></script>

<!-- Select2 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.full.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Toastr -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js"></script>

<!-- jquery-validation -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/additional-methods.min.js"></script>

<!-- InputMask -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/inputmask/5.0.8/jquery.inputmask.min.js"></script>

<!-- AdminLTE App -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>

<!-- PathLab Pro Common JS -->
<script src="js/common.js"></script>

<!-- PathLab Pro Toaster Alerts -->
<script src="js/toaster.js"></script>

<!-- Enhanced Sidebar Functionality -->
<script>
$(document).ready(function() {
    // Enhanced sidebar navigation
    initEnhancedSidebar();
    
    // Initialize search functionality
    initSidebarSearch();
    
    // Load quick stats
    loadQuickStats();
    
    // Auto-update badges
    updateSidebarBadges();
    
    // Initialize enhanced user experience
    initSidebarUX();
});

function initEnhancedSidebar() {
    // Enhanced hover effects and navigation
    $('.nav-sidebar .nav-item > .nav-link').on('mouseenter', function() {
        if (!$(this).hasClass('active')) {
            $(this).find('i').addClass('animated pulse');
        }
    }).on('mouseleave', function() {
        $(this).find('i').removeClass('animated pulse');
    });
    
    // Smart expand/collapse for tree items
    $('.nav-sidebar .nav-item.has-treeview > .nav-link').on('click', function(e) {
        e.preventDefault();
        const parent = $(this).parent();
        const isOpen = parent.hasClass('menu-open');
        
        // Close other open menus in the same section
        parent.siblings('.has-treeview.menu-open').removeClass('menu-open')
              .find('.nav-treeview').slideUp(300);
        
        if (!isOpen) {
            parent.addClass('menu-open');
            parent.find('.nav-treeview').slideDown(300);
        } else {
            parent.removeClass('menu-open');
            parent.find('.nav-treeview').slideUp(300);
        }
    });
}

function initSidebarSearch() {
    let searchTimeout;
    
    $('#sidebarSearch').on('input', function() {
        clearTimeout(searchTimeout);
        const searchTerm = $(this).val().toLowerCase();
        
        searchTimeout = setTimeout(function() {
            if (searchTerm === '') {
                $('.nav-item').removeClass('search-hidden');
                $('.nav-sidebar p').removeClass('search-highlight');
                return;
            }
            
            $('.nav-sidebar .nav-item').each(function() {
                const text = $(this).find('p').text().toLowerCase();
                const link = $(this).find('.nav-link');
                
                if (text.includes(searchTerm)) {
                    $(this).removeClass('search-hidden');
                    // Highlight matching text
                    const originalText = $(this).find('p').first().text();
                    const regex = new RegExp(`(${searchTerm})`, 'gi');
                    const highlighted = originalText.replace(regex, '<span class="search-highlight">$1</span>');
                    $(this).find('p').first().html(highlighted);
                } else {
                    $(this).addClass('search-hidden');
                }
            });
        }, 300);
    });
    
    $('#clearSearch').on('click', function() {
        $('#sidebarSearch').val('').trigger('input');
    });
}

function loadQuickStats() {
    // Load quick statistics for dashboard
    $.ajax({
        url: 'api/quick_stats.php',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                $('#quickPatientCount').text(response.data.patients || '0');
                $('#quickTestCount').text(response.data.tests || '0');
                $('#quickDoctorCount').text(response.data.doctors || '0');
                
                // Update badges
                $('#patientBadge').text(response.data.patients || '0');
                $('#doctorsBadge').text(response.data.doctors || '0');
            }
        },
        error: function() {
            // Silent fail with placeholder values
            $('#quickPatientCount').text('0');
            $('#quickTestCount').text('0');
            $('#quickDoctorCount').text('0');
        }
    });
}

function updateSidebarBadges() {
    // Update notification badges
    $.ajax({
        url: 'api/notifications.php',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                if (response.data.new_orders > 0) {
                    $('#ordersBadge').text(response.data.new_orders);
                }
                
                // Update system status
                const status = response.data.system_status || 'OK';
                $('#systemStatus').text(status)
                    .removeClass('badge-success badge-warning badge-danger')
                    .addClass(status === 'OK' ? 'badge-success' : 
                             status === 'WARNING' ? 'badge-warning' : 'badge-danger');
            }
        },
        error: function() {
            // Silent fail
        }
    });
}

function initSidebarUX() {
    // Add tooltip for collapsed sidebar
    if ($('body').hasClass('sidebar-collapse')) {
        $('.nav-sidebar .nav-link').each(function() {
            const text = $(this).find('p').first().text().trim();
            if (text) {
                $(this).attr('title', text);
                $(this).tooltip({
                    placement: 'right',
                    trigger: 'hover'
                });
            }
        });
    }
    
    // Update tooltips when sidebar is toggled
    $('[data-widget="pushmenu"]').on('click', function() {
        setTimeout(function() {
            $('.nav-sidebar .nav-link').tooltip('dispose');
            if ($('body').hasClass('sidebar-collapse')) {
                initSidebarUX();
            }
        }, 300);
    });
    
    // Auto-scroll to active item
    const activeItem = $('.nav-sidebar .nav-link.active');
    if (activeItem.length) {
        setTimeout(function() {
            $('.sidebar').animate({
                scrollTop: activeItem.offset().top - $('.sidebar').offset().top + $('.sidebar').scrollTop() - 100
            }, 500);
        }, 300);
    }
}

// Helper functions for menu actions
function showEmergencyContacts() {
    Swal.fire({
        title: 'Emergency Contacts',
        html: `
            <div class="text-left">
                <p><strong>Medical Emergency:</strong> 911</p>
                <p><strong>Lab Emergency:</strong> +1 (555) 123-4567</p>
                <p><strong>IT Support:</strong> +1 (555) 123-4568</p>
                <p><strong>Admin Office:</strong> +1 (555) 123-4569</p>
            </div>
        `,
        icon: 'info',
        confirmButtonText: 'Close'
    });
}

function showHelp() {
    Swal.fire({
        title: 'Help & Support',
        html: `
            <div class="text-left">
                <p><strong>User Manual:</strong> <a href="#" target="_blank">Download PDF</a></p>
                <p><strong>Video Tutorials:</strong> <a href="#" target="_blank">Watch Online</a></p>
                <p><strong>Support Email:</strong> support@pathlabpro.com</p>
                <p><strong>Live Chat:</strong> Available 24/7</p>
                <hr>
                <small>PathLab Pro v1.2.0 | Build 2025.08.04</small>
            </div>
        `,
        icon: 'question',
        confirmButtonText: 'Close'
    });
}

// Auto-refresh badges every 30 seconds
setInterval(updateSidebarBadges, 30000);
</script>

<?php if (isset($additional_scripts)): ?>
<?php echo $additional_scripts; ?>
<?php endif; ?>

</body>
</html>
