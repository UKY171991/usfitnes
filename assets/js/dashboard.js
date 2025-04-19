// Initialize dashboard functionality
document.addEventListener('DOMContentLoaded', function() {
    // Update dashboard stats every 30 seconds
    updateDashboardStats();
    setInterval(updateDashboardStats, 30000);
});

// Function to update dashboard statistics
function updateDashboardStats() {
    fetch('includes/fetch_dashboard_stats.php', {
        method: 'GET',
        headers: {
            'Cache-Control': 'no-cache',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update statistics cards
            document.getElementById('total-patients').textContent = data.total_patients;
            document.getElementById('pending-tests').textContent = data.pending_tests;
            document.getElementById('today-reports').textContent = data.today_reports;
            document.getElementById('monthly-revenue').textContent = data.monthly_revenue;
            
            // Update recent requests table
            updateRecentRequests(data.recent_requests);
        } else {
            console.error('Failed to fetch dashboard stats:', data.message);
        }
    })
    .catch(error => {
        console.error('Error updating dashboard:', error);
    });
}

// Function to update recent requests table
function updateRecentRequests(requests) {
    const tbody = document.querySelector('#recent-requests tbody');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    requests.forEach(request => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${request.request_id}</td>
            <td>${request.patient_name}</td>
            <td>${request.test_name}</td>
            <td><span class="badge badge-${request.status_class}">${request.status}</span></td>
            <td>${request.date_formatted}</td>
        `;
        tbody.appendChild(tr);
    });
}

// Add fade-in animation for statistics cards
document.querySelectorAll('.stat-card').forEach(card => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(20px)';
    
    setTimeout(() => {
        card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        card.style.opacity = '1';
        card.style.transform = 'translateY(0)';
    }, 100);
}); 