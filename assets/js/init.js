// Initialize common features when the document is ready
app.onDocumentReady(() => {
    // Initialize all data tables
    document.querySelectorAll('[data-table]').forEach(table => {
        const tableId = table.id;
        const options = {
            pageSize: parseInt(table.dataset.pageSize || '10'),
            onSort: async (column, direction) => {
                const url = table.dataset.url;
                if (!url) return;
                
                try {
                    const data = await app.makeRequest(`${url}?sort=${column}&direction=${direction}`);
                    updateTableContent(tableId, data);
                } catch (error) {
                    app.showAlert('Failed to sort table', 'danger');
                }
            },
            onPageChange: async (page) => {
                const url = table.dataset.url;
                if (!url) return;
                
                try {
                    const data = await app.makeRequest(`${url}?page=${page}`);
                    updateTableContent(tableId, data);
                } catch (error) {
                    app.showAlert('Failed to change page', 'danger');
                }
            }
        };
        
        app.initializeDataTable(tableId, options);
    });
    
    // Initialize all search inputs
    document.querySelectorAll('[data-search]').forEach(input => {
        const targetId = input.dataset.search;
        const url = input.dataset.url;
        
        app.initializeSearch(input.id, async (value) => {
            if (!url) return;
            
            try {
                const data = await app.makeRequest(`${url}?search=${encodeURIComponent(value)}`);
                updateTableContent(targetId, data);
            } catch (error) {
                app.showAlert('Search failed', 'danger');
            }
        });
    });
    
    // Initialize all modals
    document.querySelectorAll('[data-modal]').forEach(modalElement => {
        const modal = app.initializeModal(modalElement.id);
        
        // Setup modal triggers
        document.querySelectorAll(`[data-modal-target="${modalElement.id}"]`).forEach(trigger => {
            trigger.addEventListener('click', (e) => {
                e.preventDefault();
                modal.open();
            });
        });
    });
    
    // Initialize all forms
    document.querySelectorAll('form[data-validate]').forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const rules = JSON.parse(form.dataset.validate || '{}');
            const errors = app.validateForm(form, rules);
            
            if (errors.length > 0) {
                app.showAlert(errors.join('<br>'), 'danger');
                return;
            }
            
            const submitButton = form.querySelector('[type="submit"]');
            const originalText = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = 'Processing...';
            
            try {
                const formData = new FormData(form);
                const response = await app.makeRequest(form.action, {
                    method: form.method,
                    body: formData
                });
                
                if (response.success) {
                    app.showAlert(response.message || 'Success!', 'success');
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    }
                } else {
                    app.showAlert(response.message || 'Operation failed', 'danger');
                }
            } catch (error) {
                app.showAlert('An error occurred', 'danger');
            } finally {
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
            }
        });
    });
    
    // Initialize all delete buttons
    document.querySelectorAll('[data-delete]').forEach(button => {
        button.addEventListener('click', async (e) => {
            e.preventDefault();
            
            if (!confirm('Are you sure you want to delete this item?')) {
                return;
            }
            
            const url = button.href || button.dataset.delete;
            if (!url) return;
            
            try {
                const response = await app.makeRequest(url, {
                    method: 'DELETE'
                });
                
                if (response.success) {
                    app.showAlert(response.message || 'Item deleted successfully', 'success');
                    const row = button.closest('tr');
                    if (row) {
                        row.remove();
                    }
                } else {
                    app.showAlert(response.message || 'Failed to delete item', 'danger');
                }
            } catch (error) {
                app.showAlert('Failed to delete item', 'danger');
            }
        });
    });
});

// Helper function to update table content
function updateTableContent(tableId, data) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    const tbody = table.querySelector('tbody');
    if (!tbody) return;
    
    // Update table body
    tbody.innerHTML = '';
    data.items.forEach(item => {
        const row = document.createElement('tr');
        row.innerHTML = item;
        tbody.appendChild(row);
    });
    
    // Update pagination if provided
    if (data.pagination) {
        const paginationContainer = document.querySelector(`#${tableId}-pagination`);
        if (paginationContainer) {
            paginationContainer.innerHTML = data.pagination;
        }
    }
    
    // Update total count if provided
    if (data.totalItems !== undefined) {
        const totalElement = document.querySelector(`#${tableId}-total`);
        if (totalElement) {
            totalElement.textContent = data.totalItems;
        }
    }
} 