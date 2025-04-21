<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="col-md-2 sidebar">
    <div class="position-sticky">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" 
                   href="/usfitnes/branch-admin/dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'patients.php' ? 'active' : ''; ?>" 
                   href="/usfitnes/branch-admin/patients.php">
                    <i class="fas fa-users"></i> Patients
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'tests.php' ? 'active' : ''; ?>" 
                   href="/usfitnes/branch-admin/tests.php">
                    <i class="fas fa-vial"></i> Tests
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'reports.php' ? 'active' : ''; ?>" 
                   href="/usfitnes/branch-admin/reports.php">
                    <i class="fas fa-file-medical-alt"></i> Reports
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'payments.php' ? 'active' : ''; ?>" 
                   href="/usfitnes/branch-admin/payments.php">
                    <i class="fas fa-money-bill"></i> Payments
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'profile.php' ? 'active' : ''; ?>" 
                   href="/usfitnes/branch-admin/profile.php">
                    <i class="fas fa-user-cog"></i> Profile
                </a>
            </li>
        </ul>
    </div>
</nav> 