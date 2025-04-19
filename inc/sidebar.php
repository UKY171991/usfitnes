<aside class="sidebar">
    <div class="sidebar-brand d-flex align-items-center">
        <img src="assets/img/logo.png" alt="Logo" class="me-2" style="height: 30px;">
        <span>Lab Management</span>
        </div>
    
    <div class="nav-sidebar">
        <?php
        $current_page = basename($_SERVER['PHP_SELF']);
        
        // Define navigation items with icons and access roles
        $nav_items = [
            [
                'title' => 'Dashboard',
                'icon' => 'bi-speedometer2',
                'link' => 'dashboard.php',
                'roles' => ['Admin', 'Doctor', 'Technician', 'Receptionist']
            ],
            [
                'title' => 'Patients',
                'icon' => 'bi-people',
                'link' => 'patients.php',
                'roles' => ['Admin', 'Doctor', 'Technician', 'Receptionist']
            ],
            [
                'title' => 'Test Categories',
                'icon' => 'bi-folder',
                'link' => 'test_categories.php',
                'roles' => ['Admin']
            ],
            [
                'title' => 'Test Management',
                'icon' => 'bi-clipboard-data',
                'link' => 'test.php',
                'roles' => ['Admin', 'Doctor', 'Technician']
            ],
            [
                'title' => 'Test Requests',
                'icon' => 'bi-file-earmark-text',
                'link' => 'test_requests.php',
                'roles' => ['Admin', 'Doctor', 'Technician']
            ],
            [
                'title' => 'Test Results',
                'icon' => 'bi-clipboard2-check',
                'link' => 'test_results.php',
                'roles' => ['Admin', 'Doctor', 'Technician']
            ],
            [
                'title' => 'Reports',
                'icon' => 'bi-file-earmark-bar-graph',
                'link' => 'reports.php',
                'roles' => ['Admin', 'Doctor']
            ],
            [
                'title' => 'Inventory',
                'icon' => 'bi-box-seam',
                'link' => 'inventory.php',
                'roles' => ['Admin', 'Technician']
            ],
            [
                'title' => 'Doctor & Referrals',
                'icon' => 'bi-person-vcard',
                'link' => 'doctors.php',
                'roles' => ['Admin']
            ],
            [
                'title' => 'Analytics',
                'icon' => 'bi-graph-up',
                'link' => 'analytics.php',
                'roles' => ['Admin']
            ],
            [
                'title' => 'Users',
                'icon' => 'bi-people-fill',
                'link' => 'users.php',
                'roles' => ['Admin']
            ],
            [
                'title' => 'Settings',
                'icon' => 'bi-gear',
                'link' => 'settings.php',
                'roles' => ['Admin']
            ]
        ];
        
        // Output navigation items
        foreach ($nav_items as $item) {
            // Check if user has access to this item
            if (in_array($_SESSION['role'], $item['roles'])) {
                $is_active = $current_page === $item['link'];
                ?>
                <div class="nav-item">
                    <a href="<?php echo $item['link']; ?>" 
                       class="nav-link <?php echo $is_active ? 'active' : ''; ?>">
                        <i class="bi <?php echo $item['icon']; ?>"></i>
                        <span><?php echo $item['title']; ?></span>
                    </a>
                </div>
                <?php
            }
        }
        ?>
        
        <div class="nav-item mt-auto">
            <a href="profile.php" class="nav-link">
                <i class="bi bi-person-circle"></i>
                <span>Profile</span>
            </a>
        </div>
        
        <div class="nav-item">
            <a href="logout.php" class="nav-link text-danger">
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>
      </aside>

<style>
/* Additional sidebar styles */
.sidebar {
    display: flex;
    flex-direction: column;
    background: var(--sidebar-bg);
}

.nav-sidebar {
    flex: 1;
    display: flex;
    flex-direction: column;
    padding: 1rem 0;
    overflow-y: auto;
}

.nav-item {
    padding: 0.25rem 1rem;
}

.nav-link {
    text-decoration: none;
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
    transition: all 0.3s ease;
    color: rgba(255,255,255,0.7);
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.nav-link:hover,
.nav-link.active {
    color: white;
    background: rgba(255,255,255,0.1);
}

.nav-link i {
    font-size: 1.25rem;
    width: 1.5rem;
    text-align: center;
}

/* Responsive sidebar */
@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }
    
    .sidebar.show {
        transform: translateX(0);
    }
}
</style>