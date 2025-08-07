/**
 * PathLab Pro - AdminLTE Initialization JavaScript
 * External JS file for AdminLTE template initialization
 */

$(document).ready(function() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Initialize popovers
    $('[data-toggle="popover"]').popover();
    
    // Configure toastr
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
    
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });
    
    // Initialize DataTables with default settings
    if ($.fn.DataTable) {
        $.extend(true, $.fn.dataTable.defaults, {
            responsive: true,
            lengthChange: true,
            autoWidth: false,
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "No entries available",
                infoFiltered: "(filtered from _MAX_ total entries)",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            },
            buttons: [
                {
                    extend: 'copy',
                    className: 'btn btn-sm btn-primary'
                },
                {
                    extend: 'csv',
                    className: 'btn btn-sm btn-success'
                },
                {
                    extend: 'excel',
                    className: 'btn btn-sm btn-success'
                },
                {
                    extend: 'pdf',
                    className: 'btn btn-sm btn-danger'
                },
                {
                    extend: 'print',
                    className: 'btn btn-sm btn-info'
                }
            ]
        });
    }
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert:not(.alert-permanent)').fadeOut();
    }, 5000);
    
    // Loading state helper
    window.showLoading = function(element) {
        if (typeof element === 'string') {
            element = $(element);
        }
        element.prop('disabled', true);
        element.html('<span class="loading-spinner"></span> Loading...');
    };
    
    window.hideLoading = function(element, originalText) {
        if (typeof element === 'string') {
            element = $(element);
        }
        element.prop('disabled', false);
        element.html(originalText || 'Submit');
    };
    
    // AJAX error handler
    $(document).ajaxError(function(event, xhr, settings) {
        if (xhr.status === 401) {
            Swal.fire({
                icon: 'error',
                title: 'Session Expired',
                text: 'Your session has expired. Please log in again.',
                confirmButtonText: 'Login'
            }).then(() => {
                window.location.href = 'login.php';
            });
        } else if (xhr.status === 403) {
            toastr.error('Access denied. You do not have permission to perform this action.');
        } else if (xhr.status >= 500) {
            toastr.error('Server error. Please try again later.');
        }
    });
    
    // Form validation helper
    window.validateForm = function(formId) {
        let isValid = true;
        $(formId + ' input[required], ' + formId + ' select[required], ' + formId + ' textarea[required]').each(function() {
            if (!$(this).val().trim()) {
                $(this).addClass('is-invalid');
                isValid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        return isValid;
    };
    
    // Number formatting helper
    window.formatNumber = function(num, decimals = 2) {
        return parseFloat(num).toFixed(decimals).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    };
    
    // Date formatting helper
    window.formatDate = function(date, format = 'YYYY-MM-DD') {
        return moment(date).format(format);
    };
    
    // Auto-refresh functionality
    let autoRefreshInterval;
    $('#auto-refresh-switch').change(function() {
        if ($(this).is(':checked')) {
            autoRefreshInterval = setInterval(function() {
                if (typeof refreshData === 'function') {
                    refreshData();
                }
            }, 30000); // 30 seconds
            toastr.info('Auto-refresh enabled (30 seconds)');
        } else {
            clearInterval(autoRefreshInterval);
            toastr.info('Auto-refresh disabled');
        }
    });
    
    // Dark mode toggle
    $('#dark-mode-switch').change(function() {
        if ($(this).is(':checked')) {
            $('body').addClass('dark-mode');
            localStorage.setItem('dark-mode', 'enabled');
        } else {
            $('body').removeClass('dark-mode');
            localStorage.setItem('dark-mode', 'disabled');
        }
    });
    
    // Load dark mode preference
    if (localStorage.getItem('dark-mode') === 'enabled') {
        $('body').addClass('dark-mode');
        $('#dark-mode-switch').prop('checked', true);
    }
    
    // Sidebar collapse toggle
    $('#sidebar-collapse-switch').change(function() {
        if ($(this).is(':checked')) {
            $('body').addClass('sidebar-collapse');
            localStorage.setItem('sidebar-collapse', 'enabled');
        } else {
            $('body').removeClass('sidebar-collapse');
            localStorage.setItem('sidebar-collapse', 'disabled');
        }
    });
    
    // Load sidebar preference
    if (localStorage.getItem('sidebar-collapse') === 'enabled') {
        $('body').addClass('sidebar-collapse');
        $('#sidebar-collapse-switch').prop('checked', true);
    }
    
    // Update notification count and system status
    updateSystemStatus();
    setInterval(updateSystemStatus, 60000); // Update every minute
});

// System status update function
function updateSystemStatus() {
    $.get('api/system_status.php')
        .done(function(data) {
            if (data.success) {
                $('#notification-count').text(data.notifications || 0);
                $('#online-users').text(data.online_users || 0);
                $('#db-status').text(data.db_status || 'OK');
                
                // Update notification list
                if (data.recent_notifications && data.recent_notifications.length > 0) {
                    let notificationHtml = '';
                    data.recent_notifications.forEach(function(notification) {
                        notificationHtml += `
                            <a href="#" class="dropdown-item">
                                <i class="fas fa-${notification.icon || 'info'} mr-2"></i>
                                ${notification.message}
                                <span class="float-right text-muted text-sm">${notification.time}</span>
                            </a>
                            <div class="dropdown-divider"></div>
                        `;
                    });
                    $('#notifications-list').html(notificationHtml);
                }
            }
        })
        .fail(function() {
            console.log('Failed to update system status');
        });
}

// Common confirmation dialog
window.confirmAction = function(message, callback, title = 'Confirm Action') {
    Swal.fire({
        title: title,
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, proceed!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed && typeof callback === 'function') {
            callback();
        }
    });
};

// Success message helper
window.showSuccess = function(message, title = 'Success') {
    toastr.success(message, title);
};

// Error message helper
window.showError = function(message, title = 'Error') {
    toastr.error(message, title);
};

// Info message helper
window.showInfo = function(message, title = 'Information') {
    toastr.info(message, title);
};

// Warning message helper
window.showWarning = function(message, title = 'Warning') {
    toastr.warning(message, title);
};