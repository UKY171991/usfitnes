// App-specific functionality
const App = {
    init() {
        this.initializeTheme();
        this.initializeTooltips();
        this.initializeDropdowns();
        this.initializeModals();
        this.initializeAlerts();
        this.initializeAnimations();
        this.initializeSearch();
        this.initializeDataTables();
        this.initializeFormValidation();
    },

    // Theme Management
    initializeTheme() {
        const theme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', theme);
        
        document.getElementById('theme-toggle')?.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        });
    },

    // Bootstrap Components
    initializeTooltips() {
        const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltips.forEach(tooltip => {
            new bootstrap.Tooltip(tooltip);
        });
    },

    initializeDropdowns() {
        const dropdowns = document.querySelectorAll('.dropdown-toggle');
        dropdowns.forEach(dropdown => {
            new bootstrap.Dropdown(dropdown);
        });
    },

    initializeModals() {
        document.querySelectorAll('[data-modal-target]').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const modalId = button.getAttribute('data-modal-target');
                const modal = new bootstrap.Modal(document.getElementById(modalId));
                modal.show();
            });
        });
    },

    // Alert System
    initializeAlerts() {
        document.addEventListener('showAlert', (e) => {
            const { message, type = 'info', duration = 5000 } = e.detail;
            
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            
            const alertContainer = document.getElementById('alert-container');
            alertContainer.insertAdjacentHTML('beforeend', alertHtml);
            
            setTimeout(() => {
                const alerts = alertContainer.getElementsByClassName('alert');
                if (alerts.length) {
                    const alert = alerts[alerts.length - 1];
                    alert.classList.remove('show');
                    setTimeout(() => alert.remove(), 300);
                }
            }, duration);
        });
    },

    // Animations
    initializeAnimations() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '50px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);
        
        document.querySelectorAll('.animate').forEach(element => {
            observer.observe(element);
        });
    },

    // Search Functionality
    initializeSearch() {
        const searchInputs = document.querySelectorAll('[data-search]');
        
        searchInputs.forEach(input => {
            let debounceTimer;
            
            input.addEventListener('input', (e) => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    const event = new CustomEvent('search', {
                        detail: {
                            query: e.target.value,
                            target: input.getAttribute('data-search')
                        }
                    });
                    document.dispatchEvent(event);
                }, 300);
            });
        });
    },

    // DataTable Enhancement
    initializeDataTables() {
        document.querySelectorAll('[data-table]').forEach(table => {
            const options = {
                pageLength: parseInt(table.getAttribute('data-page-length') || '10'),
                responsive: true,
                dom: '<"table-header"<"row"<"col-md-6"l><"col-md-6"f>>>rt<"table-footer"<"row"<"col-md-6"i><"col-md-6"p>>>',
                language: {
                    search: "",
                    searchPlaceholder: "Search...",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    paginate: {
                        first: '<i class="bi bi-chevron-double-left"></i>',
                        last: '<i class="bi bi-chevron-double-right"></i>',
                        next: '<i class="bi bi-chevron-right"></i>',
                        previous: '<i class="bi bi-chevron-left"></i>'
                    }
                }
            };
            
            $(table).DataTable(options);
        });
    },

    // Form Validation
    initializeFormValidation() {
        document.querySelectorAll('form[data-validate]').forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                
                form.classList.add('was-validated');
            });
            
            // Custom validation messages
            form.querySelectorAll('[data-validate-message]').forEach(input => {
                input.addEventListener('invalid', (e) => {
                    e.target.setCustomValidity(input.getAttribute('data-validate-message'));
                });
                
                input.addEventListener('input', (e) => {
                    e.target.setCustomValidity('');
                });
            });
        });
    },

    // Utility Functions
    showAlert(message, type = 'info', duration = 5000) {
        const event = new CustomEvent('showAlert', {
            detail: { message, type, duration }
        });
        document.dispatchEvent(event);
    },

    formatDate(date) {
        return new Date(date).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    },

    formatCurrency(amount) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(amount);
    }
};

// Initialize app when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    App.init();
}); 