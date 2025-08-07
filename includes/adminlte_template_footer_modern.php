    </div>
    <!-- /.wrapper -->

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <!-- Bootstrap 4 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
    
    <!-- AdminLTE App -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>
    
    <!-- DataTables & Plugins -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    
    <!-- Select2 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    
    <!-- Toastr -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    
    <script>
        // Global configurations
        $(document).ready(function() {
            // Configure Toastr globally
            toastr.options = {
                "closeButton": true,
                "debug": false,
                "newestOnTop": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "preventDuplicates": false,
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            };
            
            // Configure Select2 globally
            if ($.fn.select2) {
                $('.select2').select2({
                    theme: 'bootstrap4',
                    width: '100%'
                });
            }
            
            // Add loading states to buttons
            $(document).on('click', 'button[type="submit"], .btn-submit', function() {
                const $btn = $(this);
                const originalHtml = $btn.html();
                const loadingHtml = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                
                if (!$btn.hasClass('loading')) {
                    $btn.addClass('loading').html(loadingHtml).prop('disabled', true);
                    
                    // Auto-restore after 10 seconds as failsafe
                    setTimeout(function() {
                        $btn.removeClass('loading').html(originalHtml).prop('disabled', false);
                    }, 10000);
                }
            });
            
            // Form validation styling
            $('form').on('submit', function() {
                const $form = $(this);
                $form.find('.is-invalid').removeClass('is-invalid');
                $form.find('.invalid-feedback').remove();
            });
            
            // Auto-dismiss alerts
            setTimeout(function() {
                $('.alert-dismissible').fadeOut('slow');
            }, 5000);
            
            // Confirm delete actions
            $(document).on('click', '.btn-delete, [data-action="delete"]', function(e) {
                if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            });
            
            // Auto-refresh indicators
            $('.auto-refresh').each(function() {
                const $element = $(this);
                const refreshInterval = $element.data('refresh-interval') || 30000; // Default 30 seconds
                
                setInterval(function() {
                    if ($element.is(':visible')) {
                        // Add refresh logic here based on element type
                        if ($element.hasClass('datatable')) {
                            if ($.fn.DataTable.isDataTable($element)) {
                                $element.DataTable().ajax.reload(null, false);
                            }
                        }
                    }
                }, refreshInterval);
            });
        });
        
        // Global utility functions
        window.showToast = function(type, message, title = null) {
            switch (type.toLowerCase()) {
                case 'success':
                    toastr.success(message, title);
                    break;
                case 'error':
                case 'danger':
                    toastr.error(message, title);
                    break;
                case 'warning':
                    toastr.warning(message, title);
                    break;
                case 'info':
                    toastr.info(message, title);
                    break;
                default:
                    toastr.info(message, title);
            }
        };
        
        window.showConfirm = function(message, callback, title = 'Confirm Action') {
            const confirmed = confirm(title + '\n\n' + message);
            if (confirmed && typeof callback === 'function') {
                callback();
            }
            return confirmed;
        };
        
        window.formatCurrency = function(amount) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD',
                minimumFractionDigits: 2
            }).format(amount);
        };
        
        window.formatDate = function(date, format = 'MMM DD, YYYY') {
            if (!date) return '-';
            const dateObj = new Date(date);
            if (isNaN(dateObj.getTime())) return '-';
            
            const options = {};
            if (format.includes('MMM')) {
                options.month = 'short';
                options.day = '2-digit';
                options.year = 'numeric';
            }
            
            return dateObj.toLocaleDateString('en-US', options);
        };
        
        window.debounce = function(func, wait, immediate) {
            let timeout;
            return function executedFunction() {
                const context = this;
                const args = arguments;
                const later = function() {
                    timeout = null;
                    if (!immediate) func.apply(context, args);
                };
                const callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) func.apply(context, args);
            };
        };
        
        // AJAX error handler
        $(document).ajaxError(function(event, jqXHR, ajaxSettings, thrownError) {
            if (jqXHR.status === 401) {
                showToast('error', 'Session expired. Please log in again.');
                setTimeout(function() {
                    window.location.href = 'login.php';
                }, 2000);
            } else if (jqXHR.status === 500) {
                showToast('error', 'Server error occurred. Please try again.');
            } else if (jqXHR.status === 0) {
                showToast('error', 'Network error. Please check your connection.');
            }
        });
        
        // Page visibility API for pausing timers when tab is hidden
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                // Page is hidden, pause any intervals
                window.pageHidden = true;
            } else {
                // Page is visible, resume intervals
                window.pageHidden = false;
            }
        });
    </script>
    
</body>
</html>
