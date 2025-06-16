// Function to load dashboard data via AJAX
function loadDashboardData() {
    const formData = new FormData(filterForm);
    const params = new URLSearchParams(formData);
    
    // Show loading indicators
    document.getElementById('dashboard-loading').style.display = 'flex';
    document.getElementById('filter-spinner').classList.remove('d-none');
    
    return fetch(`ajax/get_dashboard_data.php?${params.toString()}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                updateDashboard(data);
            } else {
                showError(data.message || 'An error occurred while loading dashboard data.');
            }
            return data;
        })
        .catch(error => {
            console.error('Error loading dashboard data:', error);
            showError(`Dashboard data loading failed: ${error.message || 'Unknown error'}`);
            throw error; // Re-throw to propagate the error
        })
        .finally(() => {
            // Hide loading indicators
            document.getElementById('dashboard-loading').style.display = 'none';
            document.getElementById('filter-spinner').classList.add('d-none');
        });
}
