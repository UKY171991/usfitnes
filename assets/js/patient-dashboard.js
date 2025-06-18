/**
 * Patient Dashboard JavaScript
 * Handles dashboard interactions and AJAX calls
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize dashboard
    initDashboard();
    
    // Auto-refresh data every 5 minutes
    setInterval(refreshDashboardData, 300000);
});

/**
 * Initialize dashboard functionality
 */
function initDashboard() {
    // Set up event listeners
    setupEventListeners();
    
    // Load initial data
    loadDashboardData();
    
    // Initialize tooltips and popovers
    initializeBootstrapComponents();
}

/**
 * Set up event listeners
 */
function setupEventListeners() {
    // Quick action buttons
    document.querySelectorAll('.quick-action-btn').forEach(btn => {
        btn.addEventListener('click', handleQuickAction);
    });
    
    // Booking status badges
    document.querySelectorAll('.booking-status').forEach(badge => {
        badge.addEventListener('click', showBookingDetails);
    });
    
    // Report download buttons
    document.querySelectorAll('.download-report').forEach(btn => {
        btn.addEventListener('click', downloadReport);
    });
    
    // Refresh button
    const refreshBtn = document.getElementById('refreshDashboard');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', refreshDashboardData);
    }
}

/**
 * Load dashboard data
 */
function loadDashboardData() {
    showLoading();
    
    fetch('/ajax/dashboard-data', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateDashboardStats(data.stats);
            updateRecentBookings(data.bookings);
            updateUpcomingAppointments(data.appointments);
        } else {
            showError(data.message || 'Failed to load dashboard data');
        }
    })
    .catch(error => {
        console.error('Dashboard data error:', error);
        showError('Failed to load dashboard data');
    })
    .finally(() => {
        hideLoading();
    });
}

/**
 * Refresh dashboard data
 */
function refreshDashboardData() {
    loadDashboardData();
    showSuccess('Dashboard refreshed successfully');
}

/**
 * Update dashboard statistics
 */
function updateDashboardStats(stats) {
    if (!stats) return;
    
    // Update stat cards
    updateStatCard('total-bookings', stats.total_bookings || 0);
    updateStatCard('completed-tests', stats.completed_tests || 0);
    updateStatCard('pending-tests', stats.pending_tests || 0);
    updateStatCard('available-reports', stats.available_reports || 0);
}

/**
 * Update individual stat card
 */
function updateStatCard(cardId, value) {
    const card = document.getElementById(cardId);
    if (card) {
        const valueElement = card.querySelector('.stat-value');
        if (valueElement) {
            // Animate number change
            animateNumber(valueElement, parseInt(valueElement.textContent) || 0, value);
        }
    }
}

/**
 * Animate number change
 */
function animateNumber(element, start, end) {
    const duration = 1000;
    const increment = (end - start) / (duration / 16);
    let current = start;
    
    const timer = setInterval(() => {
        current += increment;
        if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
            current = end;
            clearInterval(timer);
        }
        element.textContent = Math.floor(current);
    }, 16);
}

/**
 * Update recent bookings
 */
function updateRecentBookings(bookings) {
    const container = document.getElementById('recent-bookings-container');
    if (!container || !bookings) return;
    
    if (bookings.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-calendar-times fa-2x text-muted mb-3"></i>
                <p class="text-muted">No recent bookings</p>
                <a href="/patient/book-test" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Book Your First Test
                </a>
            </div>
        `;
        return;
    }
    
    let html = '';
    bookings.forEach(booking => {
        html += createBookingCard(booking);
    });
    
    container.innerHTML = html;
}

/**
 * Create booking card HTML
 */
function createBookingCard(booking) {
    const statusColor = getStatusColor(booking.status);
    const formattedDate = formatDate(booking.appointment_date);
    const formattedTime = formatTime(booking.appointment_time);
    
    return `
        <div class="col-md-6 mb-3">
            <div class="card h-100 booking-card" data-booking-id="${booking.id}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="card-title text-primary mb-0">${escapeHtml(booking.test_name)}</h6>
                        <span class="badge bg-${statusColor}">${booking.status}</span>
                    </div>
                    <p class="card-text small text-muted mb-2">
                        <i class="fas fa-calendar me-1"></i>${formattedDate} at ${formattedTime}<br>
                        <i class="fas fa-map-marker-alt me-1"></i>${escapeHtml(booking.branch_name)}
                    </p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold">₹${formatNumber(booking.final_amount)}</span>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" onclick="viewBooking(${booking.id})">
                                <i class="fas fa-eye"></i>
                            </button>
                            ${booking.status === 'completed' && booking.report_status === 'ready' ? 
                                `<button class="btn btn-outline-success" onclick="downloadReport(${booking.id})">
                                    <i class="fas fa-download"></i>
                                </button>` : ''
                            }
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

/**
 * Update upcoming appointments
 */
function updateUpcomingAppointments(appointments) {
    const container = document.getElementById('upcoming-appointments-container');
    if (!container || !appointments) return;
    
    if (appointments.length === 0) {
        container.style.display = 'none';
        return;
    }
    
    container.style.display = 'block';
    const listContainer = container.querySelector('.appointments-list');
    
    let html = '';
    appointments.forEach(appointment => {
        html += createAppointmentCard(appointment);
    });
    
    listContainer.innerHTML = html;
}

/**
 * Create appointment card HTML
 */
function createAppointmentCard(appointment) {
    const formattedDate = formatDate(appointment.appointment_date);
    const formattedTime = formatTime(appointment.appointment_time);
    
    return `
        <div class="col-md-4 mb-3">
            <div class="card border-success">
                <div class="card-body">
                    <h6 class="card-title text-success">${escapeHtml(appointment.test_name)}</h6>
                    <p class="card-text small">
                        <i class="fas fa-calendar me-1"></i>${formattedDate}<br>
                        <i class="fas fa-clock me-1"></i>${formattedTime}<br>
                        <i class="fas fa-map-marker-alt me-1"></i>${escapeHtml(appointment.branch_name)}
                    </p>
                    <button class="btn btn-sm btn-outline-success" onclick="viewBooking(${appointment.id})">
                        View Details
                    </button>
                </div>
            </div>
        </div>
    `;
}

/**
 * Handle quick action clicks
 */
function handleQuickAction(event) {
    const action = event.currentTarget.dataset.action;
    
    switch (action) {
        case 'book-test':
            window.location.href = '/patient/book-test';
            break;
        case 'view-reports':
            window.location.href = '/patient/reports';
            break;
        case 'view-bookings':
            window.location.href = '/patient/bookings';
            break;
        case 'update-profile':
            window.location.href = '/patient/profile';
            break;
    }
}

/**
 * Show booking details
 */
function showBookingDetails(event) {
    const bookingId = event.currentTarget.dataset.bookingId;
    if (bookingId) {
        viewBooking(bookingId);
    }
}

/**
 * View booking details
 */
function viewBooking(bookingId) {
    window.location.href = `/patient/booking/${bookingId}`;
}

/**
 * Download report
 */
function downloadReport(bookingId) {
    showLoading();
    
    // Create temporary link to download report
    const link = document.createElement('a');
    link.href = `/patient/report/${bookingId}/download`;
    link.download = `report_${bookingId}.pdf`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    hideLoading();
    showSuccess('Report download started');
}

/**
 * Initialize Bootstrap components
 */
function initializeBootstrapComponents() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
}

/**
 * Utility functions
 */
function getStatusColor(status) {
    switch (status.toLowerCase()) {
        case 'completed': return 'success';
        case 'confirmed': return 'info';
        case 'pending': return 'warning';
        case 'cancelled': return 'danger';
        default: return 'secondary';
    }
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function formatTime(timeString) {
    const time = new Date(`1970-01-01T${timeString}`);
    return time.toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
}

function formatNumber(number) {
    return new Intl.NumberFormat('en-IN').format(number);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showLoading() {
    // Show loading spinner
    const loadingElement = document.getElementById('loading-spinner');
    if (loadingElement) {
        loadingElement.style.display = 'block';
    }
}

function hideLoading() {
    // Hide loading spinner
    const loadingElement = document.getElementById('loading-spinner');
    if (loadingElement) {
        loadingElement.style.display = 'none';
    }
}

function showSuccess(message) {
    showToast(message, 'success');
}

function showError(message) {
    showToast(message, 'error');
}

function showToast(message, type = 'info') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info'} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    // Add to toast container
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(toastContainer);
    }
    
    toastContainer.appendChild(toast);
    
    // Show toast
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    // Remove toast after it's hidden
    toast.addEventListener('hidden.bs.toast', () => {
        toast.remove();
    });
}
