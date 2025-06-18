<?php
// Get current user info
$currentUser = $_SESSION['user_name'] ?? 'User';
$currentRole = $_SESSION['user_role'] ?? 'patient';
$isLoggedIn = isset($_SESSION['user_id']);
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <!-- Brand -->
        <a class="navbar-brand fw-bold" href="<?php echo BASE_URL; ?>">
            <i class="fas fa-heartbeat me-2"></i>
            US Fitness Lab
        </a>
        
        <!-- Mobile Toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Navigation Items -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <?php if ($isLoggedIn): ?>
                    <?php if ($currentRole === USER_ROLE_PATIENT): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>patient/dashboard">
                                <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>patient/book-test">
                                <i class="fas fa-calendar-plus me-1"></i>Book Test
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>patient/bookings">
                                <i class="fas fa-calendar-check me-1"></i>My Bookings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>patient/reports">
                                <i class="fas fa-file-medical me-1"></i>My Reports
                            </a>
                        </li>
                    <?php elseif ($currentRole === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/dashboard">
                                <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="testsDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-vial me-1"></i>Tests
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="/admin/tests">Manage Tests</a></li>
                                <li><a class="dropdown-item" href="/admin/test-categories">Categories</a></li>
                                <li><a class="dropdown-item" href="/admin/test-parameters">Parameters</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="bookingsDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-calendar-check me-1"></i>Bookings
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="/admin/bookings">All Bookings</a></li>
                                <li><a class="dropdown-item" href="/admin/bookings?status=pending">Pending</a></li>
                                <li><a class="dropdown-item" href="/admin/bookings?status=confirmed">Confirmed</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/reports">
                                <i class="fas fa-file-medical me-1"></i>Reports
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/payments">
                                <i class="fas fa-credit-card me-1"></i>Payments
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="managementDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-cogs me-1"></i>Management
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="/admin/branches">Branches</a></li>
                                <li><a class="dropdown-item" href="/admin/users">Users</a></li>
                                <li><a class="dropdown-item" href="/admin/settings">Settings</a></li>
                            </ul>
                        </li>
                    <?php elseif ($currentRole === 'branch_admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/branch/dashboard">
                                <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/branch/bookings">
                                <i class="fas fa-calendar-check me-1"></i>Bookings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/branch/reports">
                                <i class="fas fa-file-medical me-1"></i>Reports
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/branch/payments">
                                <i class="fas fa-credit-card me-1"></i>Payments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/branch/patients">
                                <i class="fas fa-users me-1"></i>Patients
                            </a>
                        </li>
                    <?php elseif ($currentRole === 'lab_technician'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/technician/dashboard">
                                <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/technician/reports">
                                <i class="fas fa-file-medical me-1"></i>Reports
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/technician/tests">
                                <i class="fas fa-vial me-1"></i>Tests
                            </a>
                        </li>
                    <?php endif; ?>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/tests">
                            <i class="fas fa-vial me-1"></i>Our Tests
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/branches">
                            <i class="fas fa-map-marker-alt me-1"></i>Locations
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/about">
                            <i class="fas fa-info-circle me-1"></i>About
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/contact">
                            <i class="fas fa-phone me-1"></i>Contact
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
            
            <!-- Right Side Navigation -->
            <ul class="navbar-nav">
                <?php if ($isLoggedIn): ?>
                    <!-- Notifications -->
                    <li class="nav-item dropdown">
                        <a class="nav-link position-relative" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-bell"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notificationCount" style="display: none;">
                                0
                            </span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" style="width: 300px;">
                            <li><h6 class="dropdown-header">Notifications</h6></li>
                            <li><div id="notificationList" class="px-3 py-2 text-muted">No new notifications</div></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-center" href="/notifications">View All</a></li>
                        </ul>
                    </li>
                    
                    <!-- User Menu -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i>
                            <?= htmlspecialchars($currentUser) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header"><?= ucfirst($currentRole) ?></h6></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/profile">
                                <i class="fas fa-user me-2"></i>Profile
                            </a></li>
                            <?php if ($currentRole === 'patient'): ?>
                                <li><a class="dropdown-item" href="/bookings">
                                    <i class="fas fa-calendar-check me-2"></i>My Bookings
                                </a></li>
                                <li><a class="dropdown-item" href="/reports">
                                    <i class="fas fa-file-medical me-2"></i>My Reports
                                </a></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="/settings">
                                <i class="fas fa-cog me-2"></i>Settings
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="/logout">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/login">
                            <i class="fas fa-sign-in-alt me-1"></i>Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-light btn-sm ms-2" href="/register">
                            <i class="fas fa-user-plus me-1"></i>Register
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Quick Actions Bar (for logged in users) -->
<?php if ($isLoggedIn && $currentRole === 'patient'): ?>
<div class="bg-light border-bottom py-2">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <small class="text-muted">Quick Actions:</small>
                <a href="/booking/create" class="btn btn-sm btn-outline-primary ms-2">
                    <i class="fas fa-plus me-1"></i>Book Test
                </a>
                <a href="/reports" class="btn btn-sm btn-outline-success ms-1">
                    <i class="fas fa-download me-1"></i>Download Reports
                </a>
            </div>
            <div class="col-md-4 text-end">
                <small class="text-muted">
                    <i class="fas fa-phone me-1"></i>
                    Support: <a href="tel:+1234567890" class="text-decoration-none">+1 (234) 567-890</a>
                </small>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// Load notifications on page load
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($isLoggedIn): ?>
        loadNotifications();
        // Refresh notifications every 5 minutes
        setInterval(loadNotifications, 300000);
    <?php endif; ?>
});

function loadNotifications() {
    fetch('/api/notifications', {
        headers: {
            'X-CSRF-Token': window.csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateNotificationUI(data.notifications);
        }
    })
    .catch(error => console.error('Error loading notifications:', error));
}

function updateNotificationUI(notifications) {
    const countElement = document.getElementById('notificationCount');
    const listElement = document.getElementById('notificationList');
    
    if (notifications.length > 0) {
        countElement.textContent = notifications.length;
        countElement.style.display = 'block';
        
        listElement.innerHTML = notifications.map(notification => `
            <div class="notification-item py-2 border-bottom">
                <div class="fw-bold">${notification.title}</div>
                <div class="text-muted small">${notification.message}</div>
                <div class="text-muted small">${notification.time_ago}</div>
            </div>
        `).join('');
    } else {
        countElement.style.display = 'none';
        listElement.innerHTML = '<div class="text-muted">No new notifications</div>';
    }
}
</script>
