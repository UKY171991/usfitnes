<?php
/**
 * Base AdminLTE 3 Template
 * This file provides a standardized template for all pages
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include config
require_once __DIR__ . '/config.php';

// Check if user is logged in
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
}

// Get page information
function getPageInfo($page_id) {
    $pages = [
        'dashboard' => [
            'title' => 'Dashboard',
            'breadcrumb' => ['Dashboard'],
            'icon' => 'fas fa-tachometer-alt'
        ],
        'patients' => [
            'title' => 'Patients Management',
            'breadcrumb' => ['Patients'],
            'icon' => 'fas fa-user-injured'
        ],
        'doctors' => [
            'title' => 'Doctors Management',
            'breadcrumb' => ['Medical Staff', 'Doctors'],
            'icon' => 'fas fa-user-md'
        ],
        'equipment' => [
            'title' => 'Equipment Management',
            'breadcrumb' => ['Equipment'],
            'icon' => 'fas fa-tools'
        ],
        'test-orders' => [
            'title' => 'Test Orders',
            'breadcrumb' => ['Tests', 'Orders'],
            'icon' => 'fas fa-flask'
        ],
        'results' => [
            'title' => 'Test Results',
            'breadcrumb' => ['Tests', 'Results'],
            'icon' => 'fas fa-chart-line'
        ],
        'reports' => [
            'title' => 'Reports & Analytics',
            'breadcrumb' => ['Reports'],
            'icon' => 'fas fa-chart-bar'
        ],
        'settings' => [
            'title' => 'System Settings',
            'breadcrumb' => ['Administration', 'Settings'],
            'icon' => 'fas fa-cogs'
        ],
        'users' => [
            'title' => 'User Management',
            'breadcrumb' => ['Administration', 'Users'],
            'icon' => 'fas fa-users'
        ]
    ];
    
    return $pages[$page_id] ?? [
        'title' => 'PathLab Pro',
        'breadcrumb' => ['Home'],
        'icon' => 'fas fa-home'
    ];
}

// Generate breadcrumb HTML
function generateBreadcrumb($breadcrumb_array) {
    $html = '';
    $total = count($breadcrumb_array);
    
    foreach ($breadcrumb_array as $index => $item) {
        if ($index === $total - 1) {
            $html .= '<li class="breadcrumb-item active">' . htmlspecialchars($item) . '</li>';
        } else {
            $html .= '<li class="breadcrumb-item"><a href="#">' . htmlspecialchars($item) . '</a></li>';
        }
    }
    
    return $html;
}

// Template rendering function
function renderTemplate($page_id, $content_callback, $additional_css = '', $additional_js = '') {
    requireLogin();
    
    $page_info = getPageInfo($page_id);
    $page_title = $page_info['title'] . ' - PathLab Pro';
    
    // Include header
    include 'includes/adminlte_template_header.php';
    
    // Include sidebar
    include 'includes/adminlte_sidebar.php';
    
    // Start content wrapper
    echo '<div class="content-wrapper">';
    
    // Content Header
    echo '<div class="content-header">';
    echo '<div class="container-fluid">';
    echo '<div class="row mb-2">';
    echo '<div class="col-sm-6">';
    echo '<h1 class="m-0"><i class="' . $page_info['icon'] . ' mr-2"></i>' . htmlspecialchars($page_info['title']) . '</h1>';
    echo '</div>';
    echo '<div class="col-sm-6">';
    echo '<ol class="breadcrumb float-sm-right">';
    echo generateBreadcrumb($page_info['breadcrumb']);
    echo '</ol>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    
    // Main content
    echo '<div class="content">';
    echo '<div class="container-fluid">';
    
    // Call the content callback function
    if (is_callable($content_callback)) {
        $content_callback();
    }
    
    echo '</div>';
    echo '</div>';
    echo '</div>'; // End content-wrapper
    
    // Include footer
    include 'includes/adminlte_template_footer.php';
}

// Helper function to create info boxes
function createInfoBox($title, $value, $icon, $color = 'info', $link = '#') {
    return '
    <div class="info-box">
        <span class="info-box-icon bg-' . $color . '"><i class="' . $icon . '"></i></span>
        <div class="info-box-content">
            <span class="info-box-text">' . htmlspecialchars($title) . '</span>
            <span class="info-box-number">' . htmlspecialchars($value) . '</span>
            ' . ($link !== '#' ? '<a href="' . $link . '" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>' : '') . '
        </div>
    </div>';
}

// Helper function to create cards
function createCard($title, $content, $tools = '', $footer = '', $color = '') {
    $card_class = 'card' . ($color ? ' card-' . $color : '');
    return '
    <div class="' . $card_class . '">
        <div class="card-header">
            <h3 class="card-title">' . $title . '</h3>
            ' . ($tools ? '<div class="card-tools">' . $tools . '</div>' : '') . '
        </div>
        <div class="card-body">
            ' . $content . '
        </div>
        ' . ($footer ? '<div class="card-footer">' . $footer . '</div>' : '') . '
    </div>';
}
?>
