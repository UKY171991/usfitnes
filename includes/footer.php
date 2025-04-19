    </div><!-- End container-fluid -->

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    
    <!-- Additional footer scripts -->
    <?php if (isset($additional_scripts)) echo $additional_scripts; ?>
    
    <!-- Auto-dismiss alerts -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-dismiss alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
        alerts.forEach(function(alert) {
            setTimeout(function() {
                if (alert && alert.parentNode) {
                    alert.classList.add('fade');
                    setTimeout(function() {
                        alert.remove();
                    }, 150);
                }
            }, 5000);
        });
    });
    </script>
</body>
</html>
<?php
// Flush output buffer
if (ob_get_level() > 0) {
    ob_end_flush();
}
?> 