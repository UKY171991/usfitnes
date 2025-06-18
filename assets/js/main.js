/**
 * Main JavaScript for US Fitness Lab
 * Common functionality across all pages
 */

// Global configuration
window.USFitnessLab = {
    ajaxUrl: AJAX_URL,
    baseUrl: BASE_URL,
    csrfToken: CSRF_TOKEN
};

// Initialize when document is ready
$(document).ready(function() {
    initializeCommonFeatures();
    setupAjaxDefaults();
    setupGlobalEventHandlers();
});

/**
 * Initialize common features
 */
function initializeCommonFeatures() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Auto-hide alerts after 5 seconds
    $('.alert').each(function() {
        var alert = $(this);
        setTimeout(function() {
            alert.fadeOut();
        }, 5000);
    });
}

/**
 * Setup AJAX defaults
 */
function setupAjaxDefaults() {
    // Set CSRF token for all AJAX requests
    $.ajaxSetup({
        beforeSend: function(xhr, settings) {
            if (!/^(GET|HEAD|OPTIONS|TRACE)$/i.test(settings.type) && !this.crossDomain) {
                xhr.setRequestHeader("X-CSRFToken", USFitnessLab.csrfToken);
                xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
            }
        }
    });
    
    // Global AJAX error handler
    $(document).ajaxError(function(event, jqXHR, ajaxSettings, thrownError) {
        hideLoadingSpinner();
        
        if (jqXHR.status === 401) {
            showToast('Session expired. Please login again.', 'error');
            setTimeout(function() {
                window.location.href = USFitnessLab.baseUrl + 'patient/login';
            }, 2000);
        } else if (jqXHR.status === 403) {
            showToast('Access denied.', 'error');
        } else if (jqXHR.status >= 500) {
            showToast('Server error. Please try again later.', 'error');
        } else {
            try {
                var response = JSON.parse(jqXHR.responseText);
                showToast(response.message || 'An error occurred', 'error');
            } catch (e) {
                showToast('An unexpected error occurred', 'error');
            }
        }
    });
}

/**
 * Setup global event handlers
 */
function setupGlobalEventHandlers() {
    // Handle forms with AJAX class
    $(document).on('submit', 'form.ajax-form', function(e) {
        e.preventDefault();
        submitAjaxForm($(this));
    });
    
    // Handle AJAX buttons
    $(document).on('click', '[data-action]', function(e) {
        e.preventDefault();
        var $button = $(this);
        var action = $button.data('action');
        var data = $button.data();
        
        if (action) {
            performAjaxAction(action, data, $button);
        }
    });
    
    // Handle logout
    $(document).on('click', '.logout-btn', function(e) {
        e.preventDefault();
        logout();
    });
}

/**
 * Submit AJAX form
 */
function submitAjaxForm($form) {
    var formData = new FormData($form[0]);
    var submitBtn = $form.find('[type="submit"]');
    var originalText = submitBtn.text();
    
    // Show loading state
    submitBtn.prop('disabled', true).text('Processing...');
    showLoadingSpinner();
    
    $.ajax({
        url: USFitnessLab.ajaxUrl,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                showToast(response.message || 'Operation successful', 'success');
                
                // Handle redirect
                if (response.redirect) {
                    setTimeout(function() {
                        window.location.href = response.redirect;
                    }, 1000);
                }
                
                // Handle form reset
                if (response.reset_form) {
                    $form[0].reset();
                }
                
                // Handle callback
                if (response.callback && typeof window[response.callback] === 'function') {
                    window[response.callback](response);
                }
            } else {
                showToast(response.message || 'Operation failed', 'error');
            }
        },
        error: function() {
            showToast('An error occurred. Please try again.', 'error');
        },
        complete: function() {
            submitBtn.prop('disabled', false).text(originalText);
            hideLoadingSpinner();
        }
    });
}

/**
 * Perform AJAX action
 */
function performAjaxAction(action, data, $element) {
    var originalText = $element.text();
    $element.prop('disabled', true);
    
    if ($element.is('button')) {
        $element.text('Processing...');
    }
    
    showLoadingSpinner();
    
    $.ajax({
        url: USFitnessLab.ajaxUrl,
        type: 'POST',
        data: $.extend({action: action}, data),
        success: function(response) {
            if (response.success) {
                showToast(response.message || 'Operation successful', 'success');
                
                if (response.redirect) {
                    setTimeout(function() {
                        window.location.href = response.redirect;
                    }, 1000);
                } else if (response.reload) {
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                }
                
                // Handle callback
                if (response.callback && typeof window[response.callback] === 'function') {
                    window[response.callback](response);
                }
            } else {
                showToast(response.message || 'Operation failed', 'error');
            }
        },
        complete: function() {
            $element.prop('disabled', false);
            if ($element.is('button')) {
                $element.text(originalText);
            }
            hideLoadingSpinner();
        }
    });
}

/**
 * Show loading spinner
 */
function showLoadingSpinner() {
    $('#loading-spinner').show();
}

/**
 * Hide loading spinner
 */
function hideLoadingSpinner() {
    $('#loading-spinner').hide();
}

/**
 * Show toast notification
 */
function showToast(message, type = 'info') {
    var toastHtml = `
        <div class="toast align-items-center text-white bg-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'primary'} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    var $toast = $(toastHtml);
    $('#toast-container').append($toast);
    
    var toast = new bootstrap.Toast($toast[0]);
    toast.show();
    
    // Remove toast element after it's hidden
    $toast.on('hidden.bs.toast', function() {
        $(this).remove();
    });
}

/**
 * Logout function
 */
function logout() {
    $.ajax({
        url: USFitnessLab.ajaxUrl,
        type: 'POST',
        data: {action: 'logout'},
        success: function(response) {
            showToast('Logged out successfully', 'success');
            setTimeout(function() {
                window.location.href = USFitnessLab.baseUrl + 'patient/login';
            }, 1000);
        }
    });
}

/**
 * Utility functions
 */
function formatCurrency(amount) {
    return '₹' + parseFloat(amount).toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function formatDate(dateString) {
    var date = new Date(dateString);
    return date.toLocaleDateString('en-IN', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}

function formatDateTime(dateTimeString) {
    var date = new Date(dateTimeString);
    return date.toLocaleDateString('en-IN', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}
