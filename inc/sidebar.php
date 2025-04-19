<?php
$current_page = basename($_SERVER['PHP_SELF']);
$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';
?>
<div class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <?php if($user_role == 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" 
                       href="<?php echo SITE_URL; ?>/admin/dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'branches.php' ? 'active' : ''; ?>" 
                       href="<?php echo SITE_URL; ?>/admin/branches.php">
                        <i class="fas fa-code-branch"></i> Branches
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'users.php' ? 'active' : ''; ?>" 
                       href="<?php echo SITE_URL; ?>/admin/users.php">
                        <i class="fas fa-users"></i> Users
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'test-categories.php' ? 'active' : ''; ?>" 
                       href="<?php echo SITE_URL; ?>/admin/test-categories.php">
                        <i class="fas fa-list"></i> Test Categories
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'test-master.php' ? 'active' : ''; ?>" 
                       href="<?php echo SITE_URL; ?>/admin/test-master.php">
                        <i class="fas fa-vial"></i> Test Master
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'reports.php' ? 'active' : ''; ?>" 
                       href="<?php echo SITE_URL; ?>/admin/reports.php">
                        <i class="fas fa-chart-bar"></i> Reports
                    </a>
                </li>
            <?php elseif($user_role == 'branch_admin'): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" 
                       href="<?php echo SITE_URL; ?>/branch-admin/dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'patients.php' ? 'active' : ''; ?>" 
                       href="<?php echo SITE_URL; ?>/branch-admin/patients.php">
                        <i class="fas fa-user-injured"></i> Patients
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'test-reports.php' ? 'active' : ''; ?>" 
                       href="<?php echo SITE_URL; ?>/branch-admin/test-reports.php">
                        <i class="fas fa-file-medical"></i> Test Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'payments.php' ? 'active' : ''; ?>" 
                       href="<?php echo SITE_URL; ?>/branch-admin/payments.php">
                        <i class="fas fa-money-bill-wave"></i> Payments
                    </a>
                </li>
            <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'add-patient.php' ? 'active' : ''; ?>" 
                       href="<?php echo SITE_URL; ?>/users/add-patient.php">
                        <i class="fas fa-user-plus"></i> Add Patient
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'view-patients.php' ? 'active' : ''; ?>" 
                       href="<?php echo SITE_URL; ?>/users/view-patients.php">
                        <i class="fas fa-users"></i> View Patients
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'generate-report.php' ? 'active' : ''; ?>" 
                       href="<?php echo SITE_URL; ?>/users/generate-report.php">
                        <i class="fas fa-file-medical"></i> Generate Report
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'upload-result.php' ? 'active' : ''; ?>" 
                       href="<?php echo SITE_URL; ?>/users/upload-result.php">
                        <i class="fas fa-upload"></i> Upload Result
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'print-report.php' ? 'active' : ''; ?>" 
                       href="<?php echo SITE_URL; ?>/users/print-report.php">
                        <i class="fas fa-print"></i> Print Report
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</div> 