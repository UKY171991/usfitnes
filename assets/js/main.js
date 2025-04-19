// Utility Functions
const debounce = (func, wait) => {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
};

const escapeHtml = (unsafe) => {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
};

// CSRF Token Management
const getCSRFToken = () => {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
};

const updateCSRFToken = (newToken) => {
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    if (metaTag) {
        metaTag.setAttribute('content', newToken);
    }
};

// Alert Management
const showAlert = (message, type = 'info') => {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} fade-in`;
    alertDiv.role = 'alert';
    alertDiv.innerHTML = message;
    
    const alertContainer = document.getElementById('alert-container');
    if (alertContainer) {
        alertContainer.appendChild(alertDiv);
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
};

// Form Validation
const validateForm = (formElement, rules) => {
    const errors = [];
    
    for (const [fieldName, rule] of Object.entries(rules)) {
        const field = formElement.querySelector(`[name="${fieldName}"]`);
        if (!field) continue;
        
        const value = field.value.trim();
        
        if (rule.required && !value) {
            errors.push(`${fieldName} is required`);
        }
        
        if (rule.minLength && value.length < rule.minLength) {
            errors.push(`${fieldName} must be at least ${rule.minLength} characters`);
        }
        
        if (rule.maxLength && value.length > rule.maxLength) {
            errors.push(`${fieldName} must not exceed ${rule.maxLength} characters`);
        }
        
        if (rule.pattern && !rule.pattern.test(value)) {
            errors.push(`${fieldName} format is invalid`);
        }
        
        if (rule.custom && !rule.custom(value)) {
            errors.push(rule.message || `${fieldName} is invalid`);
        }
    }
    
    return errors;
};

// Table Management
const initializeDataTable = (tableId, options = {}) => {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    const defaultOptions = {
        pageSize: 10,
        currentPage: 1,
        sortColumn: null,
        sortDirection: 'asc'
    };
    
    const settings = { ...defaultOptions, ...options };
    
    // Add sort handlers
    table.querySelectorAll('th[data-sort]').forEach(header => {
        header.addEventListener('click', () => {
            const column = header.dataset.sort;
            if (settings.sortColumn === column) {
                settings.sortDirection = settings.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                settings.sortColumn = column;
                settings.sortDirection = 'asc';
            }
            
            if (options.onSort) {
                options.onSort(settings.sortColumn, settings.sortDirection);
            }
        });
    });
    
    // Initialize pagination
    const paginationContainer = document.querySelector(`#${tableId}-pagination`);
    if (paginationContainer && options.totalItems) {
        const totalPages = Math.ceil(options.totalItems / settings.pageSize);
        updatePagination(paginationContainer, settings.currentPage, totalPages, (page) => {
            settings.currentPage = page;
            if (options.onPageChange) {
                options.onPageChange(page);
            }
        });
    }
    
    return settings;
};

const updatePagination = (container, currentPage, totalPages, onPageChange) => {
    container.innerHTML = '';
    
    const createPageLink = (page, text) => {
        const li = document.createElement('li');
        li.className = `page-item ${page === currentPage ? 'active' : ''}`;
        
        const a = document.createElement('a');
        a.className = 'page-link';
        a.href = '#';
        a.textContent = text || page;
        
        a.addEventListener('click', (e) => {
            e.preventDefault();
            if (page !== currentPage) {
                onPageChange(page);
            }
        });
        
        li.appendChild(a);
        return li;
    };
    
    const ul = document.createElement('ul');
    ul.className = 'pagination';
    
    // Previous button
    ul.appendChild(createPageLink(currentPage - 1, '«'));
    
    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        if (
            i === 1 ||
            i === totalPages ||
            (i >= currentPage - 2 && i <= currentPage + 2)
        ) {
            ul.appendChild(createPageLink(i));
        } else if (
            i === currentPage - 3 ||
            i === currentPage + 3
        ) {
            const li = document.createElement('li');
            li.className = 'page-item disabled';
            li.innerHTML = '<span class="page-link">...</span>';
            ul.appendChild(li);
        }
    }
    
    // Next button
    ul.appendChild(createPageLink(currentPage + 1, '»'));
    
    container.appendChild(ul);
};

// Search Functionality
const initializeSearch = (inputId, callback) => {
    const searchInput = document.getElementById(inputId);
    if (!searchInput) return;
    
    const debouncedSearch = debounce((value) => {
        callback(value);
    }, 300);
    
    searchInput.addEventListener('input', (e) => {
        debouncedSearch(e.target.value);
    });
};

// Modal Management
const initializeModal = (modalId) => {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    
    const closeModal = () => {
        modal.style.display = 'none';
        document.body.classList.remove('modal-open');
    };
    
    const openModal = () => {
        modal.style.display = 'block';
        document.body.classList.add('modal-open');
    };
    
    // Close button handlers
    modal.querySelectorAll('[data-dismiss="modal"]').forEach(button => {
        button.addEventListener('click', closeModal);
    });
    
    // Click outside modal to close
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal();
        }
    });
    
    // ESC key to close
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.style.display === 'block') {
            closeModal();
        }
    });
    
    return {
        open: openModal,
        close: closeModal
    };
};

// AJAX Request Handler
const makeRequest = async (url, options = {}) => {
    const defaultOptions = {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': getCSRFToken()
        }
    };
    
    const requestOptions = {
        ...defaultOptions,
        ...options,
        headers: {
            ...defaultOptions.headers,
            ...options.headers
        }
    };
    
    try {
        const response = await fetch(url, requestOptions);
        
        // Update CSRF token if provided in response headers
        const newToken = response.headers.get('X-CSRF-Token');
        if (newToken) {
            updateCSRFToken(newToken);
        }
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Request failed:', error);
        showAlert(error.message, 'danger');
        throw error;
    }
};

// Document Ready Handler
const onDocumentReady = (callback) => {
    if (document.readyState !== 'loading') {
        callback();
    } else {
        document.addEventListener('DOMContentLoaded', callback);
    }
};

// Export functions for use in other files
window.app = {
    debounce,
    escapeHtml,
    getCSRFToken,
    updateCSRFToken,
    showAlert,
    validateForm,
    initializeDataTable,
    initializeSearch,
    initializeModal,
    makeRequest,
    onDocumentReady
}; 