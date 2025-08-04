  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
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
<!-- DataTables & Plugins -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Toastr -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js"></script>

<!-- Custom Scripts -->
<script>
$(document).ready(function() {
    // Initialize DataTables
    if ($.fn.DataTable) {
        $('.table').DataTable({
            responsive: true,
            lengthChange: false,
            autoWidth: false,
            pageLength: 25,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search records..."
            }
        });
    }
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert:not(.alert-permanent)').fadeOut('slow');
    }, 5000);
    
    // Confirm delete actions
    $('.btn-delete, .delete-btn').on('click', function(e) {
        e.preventDefault();
        const href = $(this).attr('href') || $(this).data('href');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                if (href) {
                    window.location.href = href;
                } else {
                    $(this).closest('form').submit();
                }
            }
        });
    });
    
    // Form validation helper
    $('.form-required').on('submit', function(e) {
        let isValid = true;
        $(this).find('input[required], select[required], textarea[required]').each(function() {
            if (!$(this).val()) {
                isValid = false;
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            toastr.error('Please fill in all required fields.');
        }
    });
    
    // Loading state for forms
    $('.btn-submit').on('click', function() {
        const btn = $(this);
        const originalText = btn.text();
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        
        setTimeout(function() {
            btn.prop('disabled', false).text(originalText);
        }, 5000);
    });
});

// Success/Error message functions
function showSuccess(message) {
    toastr.success(message || 'Operation completed successfully!');
}

function showError(message) {
    toastr.error(message || 'An error occurred. Please try again.');
}

function showInfo(message) {
    toastr.info(message);
}

function showWarning(message) {
    toastr.warning(message);
}
</script>

<?php if (isset($additional_scripts)): ?>
    <?php echo $additional_scripts; ?>
<?php endif; ?>

</body>
</html>
