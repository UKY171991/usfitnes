<!-- inc/top.php -->
<?php
require_once 'config.php';
require_once 'includes/Auth.php';

// Initialize authentication
$auth = Auth::getInstance();

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Get user data
try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    $stmt = $pdo->prepare("SELECT name, role, profile_image FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
} catch (Exception $e) {
    error_log("Top Navigation Error: " . $e->getMessage());
}

$profile_image = !empty($user['profile_image']) ? $user['profile_image'] : 'assets/img/default-avatar.png';
?>

<nav class="app-header navbar navbar-expand bg-body">
    <div class="container-fluid">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link sidebar-toggle" data-lte-toggle="sidebar" href="#" role="button">
                    <i class="bi bi-list"></i>
                </a>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ms-auto">
            <!-- Notifications Dropdown -->
            <li class="nav-item dropdown">
                <a class="nav-link" data-bs-toggle="dropdown" href="#">
                    <i class="bi bi-bell"></i>
                    <?php if (isset($notifications_count) && $notifications_count > 0): ?>
                    <span class="navbar-badge badge text-bg-danger"><?php echo $notifications_count; ?></span>
                    <?php endif; ?>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                    <span class="dropdown-item dropdown-header">Notifications</span>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item">
                        <i class="bi bi-envelope me-2"></i> New test requests
                        <span class="float-end text-secondary fs-7">3 mins</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
                </div>
            </li>

            <!-- User Dropdown -->
            <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                    <img src="<?php echo htmlspecialchars($profile_image); ?>" 
                         class="user-image rounded-circle shadow" 
                         alt="User Image" 
                         style="width: 30px; height: 30px; object-fit: cover;">
                    <span class="d-none d-md-inline"><?php echo htmlspecialchars($user['name'] ?? 'User'); ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                    <!-- User image -->
                    <li class="user-header bg-primary text-white p-3 text-center">
                        <img src="<?php echo htmlspecialchars($profile_image); ?>" 
                             class="rounded-circle shadow mb-3" 
                             alt="User Image"
                             style="width: 90px; height: 90px; object-fit: cover;">
                        <p>
                            <?php echo htmlspecialchars($user['name'] ?? 'User'); ?>
                            <small><?php echo htmlspecialchars($user['role'] ?? 'Staff'); ?></small>
                        </p>
                    </li>
                    <!-- Menu Body -->
                    <li class="user-body">
                        <div class="row text-center">
                            <div class="col-12">
                                <a href="profile.php" class="btn btn-default btn-flat w-100">Profile</a>
                            </div>
                        </div>
                    </li>
                    <!-- Menu Footer -->
                    <li class="user-footer d-flex">
                        <a href="profile.php" class="btn btn-default btn-flat">Profile</a>
                        <a href="logout.php" class="btn btn-danger btn-flat ms-auto">Sign out</a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>