// Toaster Alert System for PathLab Pro
function showToaster(type, message, title = '') {
    // Remove any existing toasters first
    $('.toast').toast('hide');
    
    // Set default titles if not provided
    if (!title) {
        switch(type) {
            case 'success':
                title = 'Success';
                break;
            case 'error':
            case 'danger':
                title = 'Error';
                break;
            case 'warning':
                title = 'Warning';
                break;
            case 'info':
                title = 'Information';
                break;
            default:
                title = 'Notification';
        }
    }
    
    // Map type to Bootstrap classes and icons
    let typeClass, icon;
    switch(type) {
        case 'success':
            typeClass = 'bg-success';
            icon = 'fas fa-check-circle';
            break;
        case 'error':
        case 'danger':
            typeClass = 'bg-danger';
            icon = 'fas fa-exclamation-triangle';
            break;
        case 'warning':
            typeClass = 'bg-warning';
            icon = 'fas fa-exclamation-circle';
            break;
        case 'info':
            typeClass = 'bg-info';
            icon = 'fas fa-info-circle';
            break;
        default:
            typeClass = 'bg-secondary';
            icon = 'fas fa-bell';
    }
    
    // Create unique ID for this toast
    const toastId = 'toast-' + Date.now();
    
    // Create the toast HTML
    const toastHtml = `
        <div id="${toastId}" class="toast ${typeClass}" role="alert" aria-live="assertive" aria-atomic="true" data-delay="5000">
            <div class="toast-header ${typeClass} text-white">
                <i class="${icon} mr-2"></i>
                <strong class="mr-auto">${title}</strong>
                <small class="text-white-50">now</small>
                <button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="toast-body text-white">
                ${message}
            </div>
        </div>
    `;
    
    // Ensure toast container exists
    if ($('#toast-container').length === 0) {
        $('body').append(`
            <div id="toast-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            </div>
        `);
    }
    
    // Add the toast to container
    $('#toast-container').append(toastHtml);
    
    // Show the toast
    $(`#${toastId}`).toast('show');
    
    // Remove the toast element after it's hidden
    $(`#${toastId}`).on('hidden.bs.toast', function() {
        $(this).remove();
    });
}

// Backward compatibility function names
function showAlert(type, message, title = '') {
    showToaster(type, message, title);
}

function showSuccess(message, title = 'Success') {
    showToaster('success', message, title);
}

function showError(message, title = 'Error') {
    showToaster('error', message, title);
}

function showWarning(message, title = 'Warning') {
    showToaster('warning', message, title);
}

function showInfo(message, title = 'Information') {
    showToaster('info', message, title);
}
