/**
 * PathLab Pro - Error Handler
 * Catches and displays JavaScript errors gracefully
 */

// Global error handler
window.addEventListener('error', function(e) {
    console.error('JavaScript Error:', e.error);
    console.error('Source:', e.filename, 'Line:', e.lineno, 'Column:', e.colno);
    
    // Show user-friendly error message only for critical errors
    if (e.error && e.error.message && !e.error.message.includes('Script error')) {
        if (typeof showToast === 'function') {
            showToast('error', 'A JavaScript error occurred. The page may not function properly.');
        }
    }
    
    return false; // Don't prevent default error handling
});

// Promise rejection handler
window.addEventListener('unhandledrejection', function(e) {
    console.error('Unhandled Promise Rejection:', e.reason);
    
    if (typeof showToast === 'function') {
        showToast('error', 'An operation failed. Please try again.');
    }
});

// Function to safely check if libraries are loaded
function checkLibraryAvailability() {
    const libraries = {
        jQuery: typeof jQuery !== 'undefined',
        DataTables: typeof jQuery !== 'undefined' && jQuery.fn.DataTable,
        Select2: typeof jQuery !== 'undefined' && jQuery.fn.select2,
        Bootstrap: typeof jQuery !== 'undefined' && jQuery.fn.modal,
        Toastr: typeof toastr !== 'undefined',
        SweetAlert2: typeof Swal !== 'undefined'
    };
    
    const missing = Object.keys(libraries).filter(lib => !libraries[lib]);
    
    if (missing.length > 0) {
        console.warn('Missing libraries:', missing);
        return false;
    }
    
    console.log('All required libraries are loaded');
    return true;
}

// Safe function execution wrapper
function safeExecute(func, errorMessage = 'Function execution failed') {
    try {
        if (typeof func === 'function') {
            return func();
        } else {
            console.error('safeExecute: Not a function', func);
        }
    } catch (error) {
        console.error(errorMessage + ':', error);
        if (typeof showToast === 'function') {
            showToast('error', errorMessage);
        }
    }
}

// Debug information
function showDebugInfo() {
    console.log('=== PathLab Pro Debug Information ===');
    console.log('User Agent:', navigator.userAgent);
    console.log('Screen Resolution:', screen.width + 'x' + screen.height);
    console.log('Viewport Size:', window.innerWidth + 'x' + window.innerHeight);
    console.log('Document Ready State:', document.readyState);
    console.log('jQuery Version:', typeof jQuery !== 'undefined' ? jQuery.fn.jquery : 'Not loaded');
    console.log('Library Status:', checkLibraryAvailability() ? 'All loaded' : 'Some missing');
    console.log('=======================================');
}

// Call debug info on load (only in development)
if (window.location.hostname === 'localhost' || window.location.hostname.includes('127.0.0.1')) {
    document.addEventListener('DOMContentLoaded', showDebugInfo);
}
