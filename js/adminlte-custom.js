/**
 * AdminLTE Custom JavaScript for PathLab Pro
 * Enhances AdminLTE3 functionality with custom features
 */

(function($) {
    'use strict';

    // Global PathLab Pro Object
    window.PathLabPro = {
        config: {
            apiUrl: 'api/',
            version: '1.0.0',
            debug: false
        },
        
        // Utility functions
        utils: {
            // Format currency
            formatCurrency: function(amount) {
                return new Intl.NumberFormat('en-US', {
                    style: 'currency',
                    currency: 'USD'
                }).format(amount);
            },
            
            // Format date
            formatDate: function(date, format = 'MMM DD, YYYY') {
                return moment(date).format(format);
            },
            
            // Debounce function
            debounce: function(func, wait, immediate) {
                var timeout;
                return function() {
                    var context = this, args = arguments;
                    var later = function() {
                        timeout = null;
                        if (!immediate) func.apply(context, args);
                    };
                    var callNow = immediate && !timeout;
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                    if (callNow) func.apply(context, args);
                };
            },
            
            // Generate random ID
            generateId: function() {
                return Math.random().toString(36).substr(2, 9);
            },
            
            // Validate email
            isValidEmail: function(email) {
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
            },
            
            // Validate phone
            isValidPhone: function(phone) {
                return /^[\+]?[1-9][\d]{0,15}$/.test(phone.replace(/\s/g, ''));
            }
        },
        
        // API helper functions
        api: {
            request: function(endpoint, options = {}) {
                const defaults = {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                };
                
                const config = Object.assign({}, defaults, options);
                const url = PathLabPro.config.apiUrl + endpoint;
                
                return fetch(url, config)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .catch(error => {
                        console.error('API Error:', error);
                        PathLabPro.notifications.error('An error occurred while processing your request.');
                        throw error;
                    });
            },
            
            get: function(endpoint) {
                return this.request(endpoint);
            },
            
            post: function(endpoint, data) {
                return this.request(endpoint, {
                    method: 'POST',
                    body: JSON.stringify(data)
                });
            },
            
            put: function(endpoint, data) {
                return this.request(endpoint, {
                    method: 'PUT',
                    body: JSON.stringify(data)
                });
            },
            
            delete: function(endpoint) {
                return this.request(endpoint, {
                    method: 'DELETE'
                });
            }
        },
        
        // Notification system
        notifications: {
            success: function(message, title = 'Success') {
                toastr.success(message, title);
            },
            
            error: function(message, title = 'Error') {
                toastr.error(message, title);
            },
            
            warning: function(message, title = 'Warning') {
                toastr.warning(message, title);
            },
            
            info: function(message, title = 'Information') {
                toastr.info(message, title);
            }
        },
        
        // Modal helper
        modal: {
            confirm: function(options = {}) {
                const defaults = {
                    title: 'Are you sure?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, proceed!'
                };
                
                const config = Object.assign({}, defaults, options);
                
                return Swal.fire(config);
            },
            
            alert: function(options = {}) {
                const defaults = {
                    icon: 'info',
                    confirmButtonColor: '#007bff'
                };
                
                const config = Object.assign({}, defaults, options);
                
                return Swal.fire(config);
            },
            
            loading: function(title = 'Processing...') {
                return Swal.fire({
                    title: title,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            }
        },
        
        // Form helpers
        forms: {
            serialize: function(form) {
                const formData = new FormData(form);
                const data = {};
                
                for (let [key, value] of formData.entries()) {
                    if (data[key]) {
                        if (Array.isArray(data[key])) {
                            data[key].push(value);
                        } else {
                            data[key] = [data[key], value];
                        }
                    } else {
                        data[key] = value;
                    }
                }
                
                return data;
            },
            
            validate: function(form) {
                let isValid = true;
                const errors = [];
                
                $(form).find('[required]').each(function() {
                    const $field = $(this);
                    const value = $field.val().trim();
                    const fieldName = $field.attr('name') || $field.attr('id') || 'Field';
                    
                    if (!value) {
                        isValid = false;
                        errors.push(`${fieldName} is required.`);
                        $field.addClass('is-invalid');
                    } else {
                        $field.removeClass('is-invalid');
                    }
                    
                    // Email validation
                    if ($field.attr('type') === 'email' && value && !PathLabPro.utils.isValidEmail(value)) {
                        isValid = false;
                        errors.push(`${fieldName} must be a valid email address.`);
                        $field.addClass('is-invalid');
                    }
                });
                
                return { isValid, errors };
            }
        },
        
        // Dashboard specific functions
        dashboard: {
            updateStats: function() {
                PathLabPro.api.get('dashboard/stats')
                    .then(data => {
                        if (data.patients) $('#total-patients').text(data.patients);
                        if (data.pending_tests) $('#pending-tests').text(data.pending_tests);
                        if (data.completed_tests) $('#completed-tests').text(data.completed_tests);
                        if (data.revenue) $('#monthly-revenue').text(PathLabPro.utils.formatCurrency(data.revenue));
                    });
            },
            
            initCharts: function() {
                // Revenue Chart
                if ($('#revenueChart').length) {
                    const ctx = document.getElementById('revenueChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                            datasets: [{
                                label: 'Revenue',
                                data: [12000, 19000, 15000, 25000, 22000, 30000],
                                borderColor: '#007bff',
                                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return PathLabPro.utils.formatCurrency(value);
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
                
                // Test Types Chart
                if ($('#testTypesChart').length) {
                    const ctx = document.getElementById('testTypesChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Blood Test', 'Urine Test', 'X-Ray', 'MRI', 'Others'],
                            datasets: [{
                                data: [45, 25, 15, 10, 5],
                                backgroundColor: [
                                    '#007bff',
                                    '#28a745',
                                    '#ffc107',
                                    '#dc3545',
                                    '#6c757d'
                                ]
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                }
                            }
                        }
                    });
                }
            }
        },
        
        // DataTable configurations
        datatables: {
            defaultConfig: {
                responsive: true,
                autoWidth: false,
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                     '<"row"<"col-sm-12"tr>>' +
                     '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search records...",
                    lengthMenu: "_MENU_ records per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "No entries to show",
                    infoFiltered: "(filtered from _MAX_ total entries)",
                    zeroRecords: "No matching records found",
                    emptyTable: "No data available in table",
                    paginate: {
                        first: '<i class="fas fa-angle-double-left"></i>',
                        previous: '<i class="fas fa-angle-left"></i>',
                        next: '<i class="fas fa-angle-right"></i>',
                        last: '<i class="fas fa-angle-double-right"></i>'
                    }
                }
            },
            
            init: function(selector, config = {}) {
                const finalConfig = Object.assign({}, this.defaultConfig, config);
                return $(selector).DataTable(finalConfig);
            }
        }
    };
    
    // Initialize when document is ready
    $(document).ready(function() {
        // Initialize AdminLTE features
        initializeAdminLTE();
        
        // Set up global event handlers
        setupGlobalEventHandlers();
        
        // Configure toastr
        configureToastr();
        
        // Initialize page-specific features
        initializePageFeatures();
        
        // Auto-update dashboard if on dashboard page
        if (window.location.pathname.includes('dashboard.php')) {
            PathLabPro.dashboard.updateStats();
            PathLabPro.dashboard.initCharts();
            
            // Update stats every 5 minutes
            setInterval(() => {
                PathLabPro.dashboard.updateStats();
            }, 300000);
        }
    });
    
    function initializeAdminLTE() {
        // Preloader
        $(window).on('load', function() {
            if ($('.preloader').length) {
                $('.preloader').delay(500).fadeOut('slow');
            }
        });
        
        // Sidebar state management
        const sidebarState = localStorage.getItem('sidebar-state');
        if (sidebarState === 'collapsed') {
            $('body').addClass('sidebar-collapse');
        }
        
        // Save sidebar state on toggle
        $(document).on('click', '[data-widget="pushmenu"]', function() {
            setTimeout(function() {
                if ($('body').hasClass('sidebar-collapse')) {
                    localStorage.setItem('sidebar-state', 'collapsed');
                } else {
                    localStorage.removeItem('sidebar-state');
                }
            }, 300);
        });
        
        // Initialize tooltips and popovers
        $('[data-toggle="tooltip"]').tooltip();
        $('[data-toggle="popover"]').popover();
        
        // Auto-hide alerts after 5 seconds
        $('.alert').delay(5000).fadeOut('slow');
        
        // Animate cards on load
        $('.card').addClass('fade-in');
        
        // Back to top button
        addBackToTopButton();
    }
    
    function setupGlobalEventHandlers() {
        // Global AJAX error handler
        $(document).ajaxError(function(event, xhr, settings, thrownError) {
            if (xhr.status === 401) {
                PathLabPro.notifications.error('Session expired. Please login again.');
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 2000);
            } else if (xhr.status === 403) {
                PathLabPro.notifications.error('Access denied.');
            } else if (xhr.status === 500) {
                PathLabPro.notifications.error('Server error. Please try again later.');
            }
        });
        
        // Form validation on submit
        $(document).on('submit', 'form[data-validate="true"]', function(e) {
            const validation = PathLabPro.forms.validate(this);
            if (!validation.isValid) {
                e.preventDefault();
                validation.errors.forEach(error => {
                    PathLabPro.notifications.error(error);
                });
            }
        });
        
        // Auto-save functionality
        $(document).on('input', '[data-autosave]', PathLabPro.utils.debounce(function() {
            const $field = $(this);
            const endpoint = $field.data('autosave');
            const data = {
                field: $field.attr('name'),
                value: $field.val()
            };
            
            PathLabPro.api.post(endpoint, data)
                .then(() => {
                    $field.addClass('text-success');
                    setTimeout(() => $field.removeClass('text-success'), 1000);
                })
                .catch(() => {
                    $field.addClass('text-danger');
                    setTimeout(() => $field.removeClass('text-danger'), 2000);
                });
        }, 1000));
        
        // Confirm delete actions
        $(document).on('click', '[data-confirm-delete]', function(e) {
            e.preventDefault();
            const $btn = $(this);
            const message = $btn.data('confirm-delete') || 'This item will be permanently deleted.';
            
            PathLabPro.modal.confirm({
                title: 'Delete Confirmation',
                text: message,
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // If it's a link, navigate to href
                    if ($btn.is('a')) {
                        window.location.href = $btn.attr('href');
                    }
                    // If it's a form button, submit the form
                    else if ($btn.closest('form').length) {
                        $btn.closest('form').submit();
                    }
                }
            });
        });
    }
    
    function configureToastr() {
        toastr.options = {
            closeButton: true,
            debug: false,
            newestOnTop: true,
            progressBar: true,
            positionClass: "toast-top-right",
            preventDuplicates: false,
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
    }
    
    function initializePageFeatures() {
        // Initialize DataTables
        if ($('.datatable').length) {
            $('.datatable').each(function() {
                const $table = $(this);
                const config = $table.data('config') || {};
                PathLabPro.datatables.init($table, config);
            });
        }
        
        // Initialize Select2
        if ($('.select2').length) {
            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%'
            });
        }
        
        // Initialize date pickers
        if ($('.datepicker').length) {
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
        
        // Initialize datetime pickers
        if ($('.datetimepicker').length) {
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
        }
        
        // Initialize input masks
        if ($('[data-mask]').length) {
            $('[data-mask]').each(function() {
                const $input = $(this);
                const mask = $input.data('mask');
                $input.mask(mask);
            });
        }
        
        // Initialize file upload areas
        initializeFileUploads();
        
        // Initialize search functionality
        initializeSearch();
    }
    
    function initializeFileUploads() {
        $('.file-upload-area').on('dragover', function(e) {
            e.preventDefault();
            $(this).addClass('dragover');
        }).on('dragleave', function(e) {
            e.preventDefault();
            $(this).removeClass('dragover');
        }).on('drop', function(e) {
            e.preventDefault();
            $(this).removeClass('dragover');
            const files = e.originalEvent.dataTransfer.files;
            handleFileUpload(files, this);
        });
        
        $('.file-input').on('change', function() {
            const files = this.files;
            const uploadArea = $(this).closest('.file-upload-area')[0];
            handleFileUpload(files, uploadArea);
        });
    }
    
    function handleFileUpload(files, uploadArea) {
        const $uploadArea = $(uploadArea);
        const maxSize = parseInt($uploadArea.data('max-size')) || 5242880; // 5MB default
        const allowedTypes = $uploadArea.data('allowed-types') || '';
        
        Array.from(files).forEach(file => {
            // Validate file size
            if (file.size > maxSize) {
                PathLabPro.notifications.error(`File ${file.name} is too large. Maximum size is ${maxSize / 1024 / 1024}MB.`);
                return;
            }
            
            // Validate file type
            if (allowedTypes && !allowedTypes.split(',').includes(file.type)) {
                PathLabPro.notifications.error(`File ${file.name} type is not allowed.`);
                return;
            }
            
            // Create preview
            createFilePreview(file, $uploadArea);
        });
    }
    
    function createFilePreview(file, $uploadArea) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = $(`
                <div class="file-preview">
                    <div class="file-info">
                        <i class="fas fa-file"></i>
                        <span class="file-name">${file.name}</span>
                        <span class="file-size">(${(file.size / 1024).toFixed(1)} KB)</span>
                    </div>
                    <button type="button" class="btn btn-sm btn-danger remove-file">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `);
            
            $uploadArea.append(preview);
        };
        reader.readAsDataURL(file);
    }
    
    function initializeSearch() {
        $('.global-search').on('input', PathLabPro.utils.debounce(function() {
            const query = $(this).val();
            if (query.length >= 3) {
                performGlobalSearch(query);
            }
        }, 500));
    }
    
    function performGlobalSearch(query) {
        PathLabPro.api.get(`search?q=${encodeURIComponent(query)}`)
            .then(results => {
                displaySearchResults(results);
            });
    }
    
    function displaySearchResults(results) {
        const $resultsContainer = $('.search-results');
        $resultsContainer.empty();
        
        if (results.length === 0) {
            $resultsContainer.html('<div class="text-muted">No results found</div>');
            return;
        }
        
        results.forEach(result => {
            const item = $(`
                <div class="search-result-item">
                    <h6><a href="${result.url}">${result.title}</a></h6>
                    <p class="text-muted">${result.description}</p>
                </div>
            `);
            $resultsContainer.append(item);
        });
    }
    
    function addBackToTopButton() {
        $('body').append('<button id="back-to-top" class="btn btn-primary"><i class="fas fa-arrow-up"></i></button>');
        
        $(window).scroll(function() {
            if ($(this).scrollTop() > 300) {
                $('#back-to-top').fadeIn();
            } else {
                $('#back-to-top').fadeOut();
            }
        });
        
        $('#back-to-top').click(function() {
            $('html, body').animate({scrollTop: 0}, 500);
        });
    }
    
    // Expose some functions globally for backward compatibility
    window.showSuccess = PathLabPro.notifications.success;
    window.showError = PathLabPro.notifications.error;
    window.showWarning = PathLabPro.notifications.warning;
    window.showInfo = PathLabPro.notifications.info;
    window.confirmDelete = function(callback, title, text) {
        PathLabPro.modal.confirm({
            title: title || 'Are you sure?',
            text: text || 'You won\'t be able to revert this!',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed && callback) {
                callback();
            }
        });
    };

})(jQuery);
