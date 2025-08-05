/**
 * Sidebar Enhancements for PathLab Pro
 * AdminLTE 3 Compatible sidebar functionality
 */

$(document).ready(function() {
    // Initialize sidebar enhancements
    SidebarManager.init();
});

const SidebarManager = {
    // Badge update intervals
    intervals: {},
    
    init: function() {
        console.log('Initializing Sidebar Manager...');
        this.loadDynamicCounts();
        this.initializeSearchFunctionality();
        this.startAutoRefresh();
        this.enhanceNavigation();
        console.log('Sidebar Manager initialized successfully');
    },
    
    // Load dynamic counts for badges
    loadDynamicCounts: function() {
        // Load patient count
        this.updatePatientCount();
        
        // Load pending orders count
        this.updatePendingOrders();
        
        // Load completed tests count
        this.updateCompletedTests();
        
        // Load system status
        this.updateSystemStatus();
        
        // Load notifications count
        this.updateNotifications();
        
        // Load low stock alerts
        this.updateLowStockAlerts();
    },
    
    // Update patient count
    updatePatientCount: function() {
        $.ajax({
            url: 'api/dashboard_api.php',
            method: 'GET',
            data: { action: 'patient_count' },
            dataType: 'json',
            timeout: 5000,
            success: function(response) {
                if (response.success) {
                    const count = response.data.count || 0;
                    $('#patients-count').html(count);
                    $('#patients-count').removeClass('badge-secondary').addClass('badge-info');
                } else {
                    $('#patients-count').html('--');
                    $('#patients-count').removeClass('badge-info').addClass('badge-secondary');
                }
            },
            error: function() {
                $('#patients-count').html('--');
                $('#patients-count').removeClass('badge-info').addClass('badge-secondary');
            }
        });
    },
    
    // Update pending orders count
    updatePendingOrders: function() {
        $.ajax({
            url: 'api/dashboard_api.php',
            method: 'GET',
            data: { action: 'pending_orders' },
            dataType: 'json',
            timeout: 5000,
            success: function(response) {
                if (response.success) {
                    const count = response.data.count || 0;
                    $('#pending-orders').html(count);
                    if (count > 0) {
                        $('#pending-orders').removeClass('badge-secondary').addClass('badge-warning');
                    } else {
                        $('#pending-orders').removeClass('badge-warning').addClass('badge-secondary');
                    }
                } else {
                    $('#pending-orders').html('--');
                    $('#pending-orders').removeClass('badge-warning').addClass('badge-secondary');
                }
            },
            error: function() {
                $('#pending-orders').html('--');
                $('#pending-orders').removeClass('badge-warning').addClass('badge-secondary');
            }
        });
    },
    
    // Update completed tests count
    updateCompletedTests: function() {
        $.ajax({
            url: 'api/dashboard_api.php',
            method: 'GET',
            data: { action: 'completed_tests' },
            dataType: 'json',
            timeout: 5000,
            success: function(response) {
                if (response.success) {
                    const count = response.data.count || 0;
                    $('#completed-tests').html(count);
                    $('#completed-tests').removeClass('badge-secondary').addClass('badge-success');
                } else {
                    $('#completed-tests').html('--');
                    $('#completed-tests').removeClass('badge-success').addClass('badge-secondary');
                }
            },
            error: function() {
                $('#completed-tests').html('--');
                $('#completed-tests').removeClass('badge-success').addClass('badge-secondary');
            }
        });
    },
    
    // Update system status
    updateSystemStatus: function() {
        $.ajax({
            url: 'api/system_api.php',
            method: 'GET',
            data: { action: 'status' },
            dataType: 'json',
            timeout: 3000,
            success: function(response) {
                if (response.success && response.data.status === 'online') {
                    $('#system-status').removeClass('badge-danger badge-warning').addClass('badge-success');
                    $('#system-status').attr('title', 'System Online');
                } else {
                    $('#system-status').removeClass('badge-success badge-danger').addClass('badge-warning');
                    $('#system-status').attr('title', 'System Warning');
                }
            },
            error: function() {
                $('#system-status').removeClass('badge-success badge-warning').addClass('badge-danger');
                $('#system-status').attr('title', 'System Offline');
            }
        });
    },
    
    // Update notifications count
    updateNotifications: function() {
        $.ajax({
            url: 'api/notifications_api.php',
            method: 'GET',
            data: { action: 'unread_count' },
            dataType: 'json',
            timeout: 5000,
            success: function(response) {
                if (response.success && response.data.count > 0) {
                    $('#unread-notifications').html(response.data.count).show();
                } else {
                    $('#unread-notifications').hide();
                }
            },
            error: function() {
                $('#unread-notifications').hide();
            }
        });
    },
    
    // Update low stock alerts
    updateLowStockAlerts: function() {
        $.ajax({
            url: 'api/inventory_api.php',
            method: 'GET',
            data: { action: 'low_stock_count' },
            dataType: 'json',
            timeout: 5000,
            success: function(response) {
                if (response.success && response.data.count > 0) {
                    $('#low-stock').html(response.data.count).show();
                } else {
                    $('#low-stock').hide();
                }
            },
            error: function() {
                $('#low-stock').hide();
            }
        });
    },
    
    // Initialize search functionality
    initializeSearchFunctionality: function() {
        const searchInput = $('.form-control-sidebar');
        const navItems = $('.nav-sidebar .nav-item');
        
        // Real-time search with debounce
        let searchTimeout;
        searchInput.on('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                SidebarManager.performSearch(searchInput.val().toLowerCase());
            }, 300);
        });
        
        // Clear search on escape
        searchInput.on('keydown', function(e) {
            if (e.keyCode === 27) { // Escape key
                $(this).val('');
                SidebarManager.clearSearch();
            }
        });
    },
    
    // Perform search in navigation
    performSearch: function(searchTerm) {
        const navItems = $('.nav-sidebar > .nav-item').not('.nav-header');
        const headers = $('.nav-sidebar .nav-header');
        
        if (!searchTerm.trim()) {
            this.clearSearch();
            return;
        }
        
        let hasVisibleItems = false;
        
        navItems.each(function() {
            const item = $(this);
            const text = item.find('p').first().text().toLowerCase();
            const isMatch = text.includes(searchTerm);
            
            if (isMatch) {
                item.show();
                hasVisibleItems = true;
                
                // Highlight matched text
                const originalText = item.find('p').first().text();
                const highlightedText = originalText.replace(
                    new RegExp(searchTerm, 'gi'),
                    '<mark>$&</mark>'
                );
                item.find('p').first().html(highlightedText);
            } else {
                item.hide();
            }
        });
        
        // Show/hide headers based on visible items
        headers.each(function() {
            const header = $(this);
            let hasVisibleNext = false;
            
            header.nextUntil('.nav-header').each(function() {
                if ($(this).is(':visible')) {
                    hasVisibleNext = true;
                    return false;
                }
            });
            
            if (hasVisibleNext) {
                header.show();
            } else {
                header.hide();
            }
        });
        
        // Show "No results" message if needed
        if (!hasVisibleItems) {
            this.showNoResults();
        } else {
            this.hideNoResults();
        }
    },
    
    // Clear search results
    clearSearch: function() {
        $('.nav-sidebar .nav-item').show();
        $('.nav-sidebar .nav-header').show();
        
        // Remove highlighting
        $('.nav-sidebar p').each(function() {
            const text = $(this).text();
            $(this).html(text);
        });
        
        this.hideNoResults();
    },
    
    // Show no results message
    showNoResults: function() {
        if ($('#no-search-results').length === 0) {
            $('.nav-sidebar').append(`
                <li class="nav-item" id="no-search-results">
                    <div class="nav-link text-muted text-center">
                        <i class="fas fa-search nav-icon"></i>
                        <p>No matching items found</p>
                    </div>
                </li>
            `);
        }
    },
    
    // Hide no results message
    hideNoResults: function() {
        $('#no-search-results').remove();
    },
    
    // Enhance navigation with tooltips and animations
    enhanceNavigation: function() {
        // Add tooltips for collapsed sidebar
        $('.nav-sidebar .nav-link').each(function() {
            const title = $(this).data('title') || $(this).find('p').first().text();
            $(this).attr('data-toggle', 'tooltip')
                   .attr('data-placement', 'right')
                   .attr('title', title);
        });
        
        // Initialize tooltips (only when sidebar is collapsed)
        $('body').on('collapsed.lte.pushmenu', function() {
            $('.nav-sidebar .nav-link').tooltip('enable');
        });
        
        $('body').on('shown.lte.pushmenu', function() {
            $('.nav-sidebar .nav-link').tooltip('disable');
        });
        
        // Add active state animations
        $('.nav-sidebar .nav-link').on('mouseenter', function() {
            if (!$(this).hasClass('active')) {
                $(this).addClass('hover-effect');
            }
        }).on('mouseleave', function() {
            $(this).removeClass('hover-effect');
        });
        
        // Smooth scrolling for long menus
        $('.sidebar').on('scroll', function() {
            const scrolled = $(this).scrollTop();
            if (scrolled > 50) {
                $('.brand-link').addClass('scrolled');
            } else {
                $('.brand-link').removeClass('scrolled');
            }
        });
    },
    
    // Start auto refresh for dynamic data
    startAutoRefresh: function() {
        // Refresh counts every 30 seconds
        this.intervals.counts = setInterval(() => {
            this.loadDynamicCounts();
        }, 30000);
        
        // Refresh system status every 60 seconds
        this.intervals.status = setInterval(() => {
            this.updateSystemStatus();
        }, 60000);
        
        // Refresh notifications every 2 minutes
        this.intervals.notifications = setInterval(() => {
            this.updateNotifications();
        }, 120000);
    },
    
    // Stop all intervals (cleanup)
    stopAutoRefresh: function() {
        Object.values(this.intervals).forEach(interval => {
            clearInterval(interval);
        });
        this.intervals = {};
    }
};

// Add CSS for search highlighting and animations
const additionalCSS = `
<style>
/* Search highlighting */
.nav-sidebar mark {
    background-color: #ffc107;
    color: #000;
    padding: 0 2px;
    border-radius: 2px;
    font-weight: bold;
}

/* Hover effects */
.nav-sidebar .nav-link.hover-effect {
    transform: translateX(3px);
    background-color: rgba(255, 255, 255, 0.1);
}

/* Scrolled brand link */
.brand-link.scrolled {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Loading states for badges */
.badge .fa-spinner {
    animation: spin 1s linear infinite;
}

/* Enhanced search box */
.form-control-sidebar {
    background-color: rgba(255, 255, 255, 0.1) !important;
    border: 1px solid rgba(255, 255, 255, 0.2) !important;
    color: #fff !important;
}

.form-control-sidebar::placeholder {
    color: rgba(255, 255, 255, 0.6) !important;
}

.form-control-sidebar:focus {
    background-color: rgba(255, 255, 255, 0.15) !important;
    border-color: #4b6cb7 !important;
    box-shadow: 0 0 0 0.2rem rgba(75, 108, 183, 0.25) !important;
}

/* Search button styling */
.btn-sidebar {
    background-color: rgba(255, 255, 255, 0.1) !important;
    border-color: rgba(255, 255, 255, 0.2) !important;
    color: #fff !important;
}

.btn-sidebar:hover {
    background-color: rgba(255, 255, 255, 0.2) !important;
    color: #fff !important;
}

/* Treeview enhancements */
.nav-treeview .nav-link {
    padding-left: 2.5rem !important;
}

.nav-treeview .nav-icon {
    font-size: 0.8rem !important;
}

/* Badge animations */
.badge {
    transition: all 0.3s ease !important;
}

.nav-link:hover .badge {
    transform: scale(1.1) !important;
}

/* Logout section styling */
.logout-link {
    border-top: 1px solid rgba(255, 255, 255, 0.1) !important;
    margin-top: 1rem !important;
    padding-top: 1rem !important;
}

.logout-link:hover {
    background-color: rgba(220, 53, 69, 0.1) !important;
}
</style>
`;

// Inject additional CSS
$('head').append(additionalCSS);

// Handle page unload cleanup
$(window).on('beforeunload', function() {
    if (typeof SidebarManager !== 'undefined') {
        SidebarManager.stopAutoRefresh();
    }
});
