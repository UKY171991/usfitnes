<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'config.php';

// Get user information
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>PathLab Pro</title>
    
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- AdminLTE Theme style -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap4.min.css">
    <!-- Select2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css">
    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- Tempus Dominus Bootstrap 4 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/css/tempusdominus-bootstrap-4.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/adminlte-custom.css">
    <link rel="stylesheet" href="css/global.css">
    
    <?php if (isset($additional_css)): ?>
        <?php foreach ($additional_css as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <!-- Preloader -->
    <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src="img/logo.png" alt="PathLab Pro" height="60" width="60">
    </div>

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="dashboard.php" class="nav-link">Home</a>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <!-- Navbar Search -->
            <li class="nav-item">
                <a class="nav-link" data-widget="navbar-search" href="#" role="button">
                    <i class="fas fa-search"></i>
                </a>
                <div class="navbar-search-block">
                    <form class="form-inline">
                        <div class="input-group input-group-sm">
                            <input class="form-control form-control-navbar" type="search" placeholder="Search" aria-label="Search">
                            <div class="input-group-append">
                                <button class="btn btn-navbar" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                                <button class="btn btn-navbar" type="button" data-widget="navbar-search">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </li>

            <!-- Notifications Dropdown Menu -->
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="far fa-bell"></i>
                    <span class="badge badge-warning navbar-badge" id="notification-count">0</span>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <span class="dropdown-item dropdown-header">Notifications</span>
                    <div class="dropdown-divider"></div>
                    <div id="notification-list">
                        <!-- Notifications will be loaded here -->
                    </div>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
                </div>
            </li>

            <!-- User Menu -->
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="far fa-user"></i>
                    <span class="d-none d-md-inline"><?php echo htmlspecialchars($user['name']); ?></span>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <div class="dropdown-header">
                        <strong><?php echo htmlspecialchars($user['name']); ?></strong><br>
                        <small class="text-muted"><?php echo ucfirst($user['user_type']); ?></small>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item" onclick="showProfileModal()">
                        <i class="fas fa-user mr-2"></i> Profile
                    </a>
                    <a href="settings.php" class="dropdown-item">
                        <i class="fas fa-cog mr-2"></i> Settings
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item" onclick="logout()">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </a>
                </div>
            </li>
        </ul>
    </nav>

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="dashboard.php" class="brand-link">
            <img src="img/logo.png" alt="PathLab Pro" class="brand-image img-circle elevation-3" style="opacity: .8">
            <span class="brand-text font-weight-light">PathLab Pro</span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar user panel -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <img src="img/user-avatar.png" class="img-circle elevation-2" alt="User Image">
                </div>
                <div class="info">
                    <a href="#" class="d-block"><?php echo htmlspecialchars($user['name']); ?></a>
                </div>
            </div>

            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="patients.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'patients.php' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Patients</p>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="doctors.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'doctors.php' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-user-md"></i>
                            <p>Doctors</p>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="test-orders.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'test-orders.php' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-clipboard-list"></i>
                            <p>Test Orders</p>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="results.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'results.php' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-flask"></i>
                            <p>Results</p>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="equipment.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'equipment.php' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-tools"></i>
                            <p>Equipment</p>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="reports.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-chart-bar"></i>
                            <p>Reports</p>
                        </a>
                    </li>
                    
                    <?php if ($user['user_type'] == 'admin'): ?>
                    <li class="nav-item">
                        <a href="users.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-users-cog"></i>
                            <p>Users</p>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="settings.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                            <i class="nav-icon fas fa-cog"></i>
                            <p>Settings</p>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Content Header -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0"><?php echo isset($page_title) ? $page_title : 'Dashboard'; ?></h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <?php if (isset($breadcrumbs)): ?>
                                <?php foreach ($breadcrumbs as $breadcrumb): ?>
                                    <?php if (isset($breadcrumb['url'])): ?>
                                        <li class="breadcrumb-item"><a href="<?php echo $breadcrumb['url']; ?>"><?php echo $breadcrumb['title']; ?></a></li>
                                    <?php else: ?>
                                        <li class="breadcrumb-item active"><?php echo $breadcrumb['title']; ?></li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                                <li class="breadcrumb-item active"><?php echo isset($page_title) ? $page_title : 'Dashboard'; ?></li>
                            <?php endif; ?>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <?php echo $content; ?>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="main-footer">
        <strong>Copyright &copy; <?php echo date('Y'); ?> <a href="#">PathLab Pro</a>.</strong>
        All rights reserved.
        <div class="float-right d-none d-sm-inline-block">
            <b>Version</b> 3.0.0
        </div>
    </footer>

    <!-- Control Sidebar -->
    <aside class="control-sidebar control-sidebar-dark">
        <!-- Control sidebar content goes here -->
    </aside>
</div>

<!-- Profile Modal -->
<div class="modal fade" id="profileModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">User Profile</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="profileForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Phone</label>
                                <input type="text" class="form-control" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>User Type</label>
                                <input type="text" class="form-control" value="<?php echo ucfirst($user['user_type']); ?>" readonly>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <h5>Change Password</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Current Password</label>
                                <input type="password" class="form-control" name="current_password">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>New Password</label>
                                <input type="password" class="form-control" name="new_password">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Confirm Password</label>
                                <input type="password" class="form-control" name="confirm_password">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- jQuery UI -->
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- Toastr -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Moment.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<!-- Tempus Dominus Bootstrap 4 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/js/tempusdominus-bootstrap-4.min.js"></script>

<!-- Global Scripts -->
<script src="js/global.js"></script>
<script src="js/common-ajax.js"></script>
<script src="js/toaster.js"></script>

<?php if (isset($additional_js)): ?>
    <?php foreach ($additional_js as $js): ?>
        <script src="<?php echo $js; ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

<script>
$(document).ready(function() {
    // Initialize global components
    initializeGlobalComponents();
    
    // Load notifications
    loadNotifications();
    
    // Profile form handler
    $('#profileForm').on('submit', function(e) {
        e.preventDefault();
        updateProfile();
    });
});

function showProfileModal() {
    $('#profileModal').modal('show');
}

function updateProfile() {
    const formData = new FormData($('#profileForm')[0]);
    
    $.ajax({
        url: 'api/auth_api.php?action=update_profile',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                showToast('success', 'Profile updated successfully');
                $('#profileModal').modal('hide');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast('error', response.message);
            }
        },
        error: function() {
            showToast('error', 'Failed to update profile');
        }
    });
}

function logout() {
    Swal.fire({
        title: 'Are you sure?',
        text: 'You will be logged out of the system',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, logout'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'api/auth_api.php?action=logout',
                type: 'POST',
                success: function() {
                    window.location.href = 'login.php';
                }
            });
        }
    });
}

function loadNotifications() {
    $.ajax({
        url: 'api/notifications_api.php',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                updateNotificationUI(response.data);
            }
        }
    });
}

function updateNotificationUI(notifications) {
    const count = notifications.length;
    $('#notification-count').text(count);
    
    const notificationList = $('#notification-list');
    notificationList.empty();
    
    if (count === 0) {
        notificationList.append('<div class="dropdown-item text-center text-muted">No new notifications</div>');
    } else {
        notifications.slice(0, 5).forEach(notification => {
            notificationList.append(`
                <a href="#" class="dropdown-item">
                    <i class="fas fa-info-circle mr-2"></i> ${notification.message}
                    <span class="float-right text-muted text-sm">${notification.time_ago}</span>
                </a>
            `);
        });
    }
}
</script>

</body>
</html>