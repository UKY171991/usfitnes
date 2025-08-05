<?php
// Include initialization file
require_once __DIR__ . '/init.php';

// Get user information for header
$user_id = $_SESSION['user_id'] ?? '';
$full_name = $_SESSION['full_name'] ?? $_SESSION['name'] ?? 'User';
$user_type = $_SESSION['user_type'] ?? $_SESSION['role'] ?? 'user';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>PathLab Pro | <?php echo $page_title ?? 'Laboratory Management System'; ?></title>
  
  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="data:image/x-icon;base64,AAABAAEAEBAAAAEAIABoBAAAFgAAACgAAAAQAAAAIAAAAAEAIAAAAAAAAAQAABILAAASCwAAAAAAAAAAAAD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AP///wD///8A////AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <!-- AdminLTE CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/css/bootstrap.min.css">
  <!-- Google Fonts -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
  
  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
  
  <!-- Toastr CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
  
  <!-- SweetAlert2 CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  
  <!-- Global Custom CSS with cache busting -->
  <link rel="stylesheet" href="css/global.css?v=<?php echo time(); ?>">
  
  <!-- Page-specific CSS Override -->
  <style>
    /* AdminLTE 3 Compatible Design System */
    :root {
      --primary: #007bff;
      --secondary: #6c757d;
      --success: #28a745;
      --info: #17a2b8;
      --warning: #ffc107;
      --danger: #dc3545;
      --light: #f8f9fa;
      --dark: #343a40;
      --pathlab-primary: #2c5aa0;
      --pathlab-dark: #1e3c72;
      --pathlab-light: #4b6cb7;
    }
    
    /* Override AdminLTE colors with PathLab branding */
    .btn-primary {
      background-color: var(--pathlab-primary) !important;
      border-color: var(--pathlab-primary) !important;
    }
    
    .btn-primary:hover, .btn-primary:focus {
      background-color: var(--pathlab-dark) !important;
      border-color: var(--pathlab-dark) !important;
    }
    
    /* Main Header - AdminLTE 3 Style */
    .main-header.navbar {
      background: linear-gradient(135deg, var(--pathlab-primary) 0%, var(--pathlab-dark) 100%) !important;
      border-bottom: none !important;
    }
    
    .navbar-light .navbar-nav .nav-link {
      color: rgba(255,255,255,.8) !important;
    }
    
    .navbar-light .navbar-nav .nav-link:hover {
      color: rgba(255,255,255,1) !important;
    }
    
    /* Sidebar - AdminLTE 3 Compatible */
    .main-sidebar {
      background: #343a40 !important;
    }
    
    .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link.active {
      background-color: var(--pathlab-primary) !important;
      color: #fff !important;
    }
    
    .sidebar-dark-primary .nav-sidebar > .nav-item > .nav-link:hover {
      background-color: rgba(255,255,255,.1) !important;
      color: #fff !important;
    }
    
    /* Brand Link */
    .brand-link {
      border-bottom: 1px solid #4b545c !important;
    }
    
    .brand-text {
      font-weight: 300 !important;
    }
    
    /* Content Wrapper */
    .content-wrapper {
      background: #f4f4f4 !important;
    }
    
    /* Cards - AdminLTE 3 Style */
    .card {
      box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2) !important;
      margin-bottom: 1rem !important;
    }
    
    .card-primary .card-header {
      background-color: var(--pathlab-primary) !important;
      border-color: var(--pathlab-primary) !important;
    }
    
    .card-header {
      background-color: transparent !important;
      border-bottom: 1px solid rgba(0,0,0,.125) !important;
    }
    
    /* Small Boxes - AdminLTE 3 Style */
    .small-box {
      border-radius: .25rem !important;
      position: relative !important;
      display: block !important;
      margin-bottom: 20px !important;
      box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2) !important;
    }
    
    .small-box > .inner {
      padding: 10px !important;
    }
    
    .small-box > .small-box-footer {
      position: relative !important;
      text-align: center !important;
      padding: 3px 0 !important;
      color: rgba(255,255,255,.8) !important;
      background: rgba(0,0,0,.1) !important;
      text-decoration: none !important;
      z-index: 10 !important;
      border-radius: 0 0 .25rem .25rem !important;
    }
    
    .small-box > .small-box-footer:hover {
      color: #fff !important;
      background: rgba(0,0,0,.15) !important;
    }
    
    .small-box .icon {
      transition: all .3s linear !important;
      position: absolute !important;
      top: -10px !important;
      right: 10px !important;
      z-index: 0 !important;
      font-size: 90px !important;
      color: rgba(0,0,0,.15) !important;
    }
    
    .small-box:hover {
      text-decoration: none !important;
      color: #fff !important;
    }
    
    .small-box:hover .icon {
      font-size: 95px !important;
    }
    
    /* Buttons - AdminLTE 3 Consistency */
    .btn {
      border-radius: .25rem !important;
    }
    
    .btn-app {
      border-radius: .25rem !important;
      position: relative !important;
      padding: 15px 5px !important;
      margin: 0 0 10px 10px !important;
      min-width: 80px !important;
      height: 60px !important;
      text-align: center !important;
      color: #666 !important;
      border: 1px solid #ddd !important;
      background-color: #f4f4f4 !important;
      font-size: 12px !important;
    }
    
    /* Tables - AdminLTE 3 Style */
    .table-hover tbody tr:hover {
      background-color: rgba(0,0,0,.075) !important;
    }
    
    /* Forms - AdminLTE 3 Style */
    .form-control:focus {
      border-color: var(--pathlab-light) !important;
      box-shadow: 0 0 0 .2rem rgba(44, 90, 160, .25) !important;
    }
    
    /* Badges */
    .badge-primary {
      background-color: var(--pathlab-primary) !important;
    }
    
    /* Breadcrumbs */
    .breadcrumb {
      background: transparent !important;
      margin-bottom: 0 !important;
    }
    
    /* Content Header */
    .content-header {
      padding: 15px .5rem 0 .5rem !important;
    }
    
    .content-header h1 {
      margin: 0 !important;
      font-size: 1.8em !important;
      color: #343a40 !important;
    }
    
    /* DataTables Integration */
    .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
      color: white !important;
      border: 1px solid var(--pathlab-primary) !important;
      background-color: var(--pathlab-primary) !important;
      background: var(--pathlab-primary) !important;
    }
    
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
      color: white !important;
      border: 1px solid var(--pathlab-light) !important;
      background-color: var(--pathlab-light) !important;
      background: var(--pathlab-light) !important;
    }
    
    /* Modal - AdminLTE 3 Style */
    .modal-header {
      border-bottom: 1px solid #dee2e6 !important;
      border-top-left-radius: calc(.3rem - 1px) !important;
      border-top-right-radius: calc(.3rem - 1px) !important;
    }
    
    .modal-footer {
      border-top: 1px solid #dee2e6 !important;
      border-bottom-right-radius: calc(.3rem - 1px) !important;
      border-bottom-left-radius: calc(.3rem - 1px) !important;
    }
    
    /* Responsive adjustments */
    @media (max-width: 767.98px) {
      .content-wrapper, .main-footer, .main-header {
        margin-left: 0 !important;
      }
      
      body.sidebar-open .content-wrapper,
      body.sidebar-open .main-footer,
      body.sidebar-open .main-header {
        margin-left: 250px !important;
      }
    }
    
    /* Custom PathLab additions while maintaining AdminLTE structure */
    .info-box {
      box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2) !important;
      border-radius: .25rem !important;
      background: #fff !important;
      display: flex !important;
      margin-bottom: 1rem !important;
      min-height: 80px !important;
      padding: .5rem !important;
      position: relative !important;
      width: 100% !important;
    }
    
    .info-box .info-box-icon {
      border-radius: .25rem !important;
      align-items: center !important;
      display: flex !important;
      font-size: 1.875rem !important;
      justify-content: center !important;
      text-align: center !important;
      width: 70px !important;
    }
    
    .info-box .info-box-content {
      display: flex !important;
      flex-direction: column !important;
      justify-content: center !important;
      line-height: 1.8 !important;
      flex: 1 !important;
      padding: 0 10px !important;
    }
    
    .progress-description,
    .info-box-text {
      display: block !important;
      font-size: .875rem !important;
      white-space: nowrap !important;
      overflow: hidden !important;
      text-overflow: ellipsis !important;
    }
    
    .info-box-number {
      display: block !important;
      margin-top: .25rem !important;
      font-weight: 700 !important;
    }
  </style>
    
    /* Enhanced Brand Link */
    .brand-link {
      display: flex !important;
      align-items: center !important;
      padding: 1rem 1.5rem !important;
      background: rgba(0, 0, 0, 0.1) !important;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
      transition: all 0.3s ease !important;
      text-decoration: none !important;
    }
    
    .brand-link:hover {
      background: rgba(0, 0, 0, 0.2) !important;
      text-decoration: none !important;
      transform: none !important;
    }
    
    .brand-text {
      color: #ffffff !important;
      font-weight: 600 !important;
      font-size: 1.25rem !important;
      margin-left: 0.75rem !important;
      transition: all 0.3s ease !important;
    }
    
    .brand-image {
      width: 35px !important;
      height: 35px !important;
      border-radius: 50% !important;
      transition: all 0.3s ease !important;
    }
    
    .brand-link:hover .brand-image {
      transform: scale(1.05) !important;
    }
    
    /* Enhanced User Panel */
    .user-panel {
      padding: 1.25rem !important;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
      background: rgba(255, 255, 255, 0.05) !important;
      border-radius: 0.5rem !important;
      margin: 1rem !important;
      transition: all 0.3s ease !important;
    }
    
    .user-panel:hover {
      background: rgba(255, 255, 255, 0.08) !important;
      transform: translateY(-1px) !important;
    }
    
    .user-panel .image img {
      width: 45px !important;
      height: 45px !important;
      border: 2px solid rgba(255, 255, 255, 0.2) !important;
      transition: all 0.3s ease !important;
    }
    
    .user-panel:hover .image img {
      border-color: rgba(255, 255, 255, 0.4) !important;
      transform: scale(1.05) !important;
    }
    
    .user-panel .info a {
      color: #ffffff !important;
      font-weight: 600 !important;
      font-size: 0.95rem !important;
      transition: all 0.3s ease !important;
      text-decoration: none !important;
    }
    
    .user-panel .info a:hover {
      color: #4b6cb7 !important;
      text-decoration: none !important;
    }
    
    .user-panel .info small {
      color: rgba(255, 255, 255, 0.7) !important;
      font-size: 0.8rem !important;
      display: block !important;
      margin-top: 0.25rem !important;
    }
    
    /* Enhanced Navigation Headers */
    .nav-header {
      color: rgba(255, 255, 255, 0.6) !important;
      font-size: 0.75rem !important;
      font-weight: 700 !important;
      text-transform: uppercase !important;
      letter-spacing: 1px !important;
      padding: 1.25rem 1.5rem 0.75rem !important;
      margin-top: 1.5rem !important;
      position: relative !important;
    }
    
    .nav-header:first-of-type {
      margin-top: 0.5rem !important;
    }
    
    .nav-header::after {
      content: '' !important;
      position: absolute !important;
      bottom: 0 !important;
      left: 1.5rem !important;
      right: 1.5rem !important;
      height: 1px !important;
      background: linear-gradient(90deg, rgba(255, 255, 255, 0.2) 0%, transparent 100%) !important;
    }
    
    /* Enhanced Navigation Items */
    .nav-sidebar .nav-item {
      margin: 0.25rem 0.75rem !important;
    }
    
    .nav-sidebar .nav-link {
      color: rgba(255, 255, 255, 0.85) !important;
      border-radius: 0.5rem !important;
      padding: 0.75rem 1rem !important;
      transition: all 0.3s ease !important;
      display: flex !important;
      align-items: center !important;
      position: relative !important;
      overflow: hidden !important;
    }
    
    /* Hover effect with sliding background */
    .nav-sidebar .nav-link::before {
      content: '' !important;
      position: absolute !important;
      top: 0 !important;
      left: -100% !important;
      width: 100% !important;
      height: 100% !important;
      background: linear-gradient(135deg, rgba(44, 90, 160, 0.8) 0%, rgba(30, 60, 114, 0.8) 100%) !important;
      transition: left 0.3s ease !important;
      z-index: -1 !important;
    }
    
    .nav-sidebar .nav-link:hover::before {
      left: 0 !important;
    }
    
    .nav-sidebar .nav-link:hover {
      color: #ffffff !important;
      transform: translateX(5px) !important;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2) !important;
    }
    
    /* Active state */
    .nav-sidebar .nav-link.active {
      background: linear-gradient(135deg, #2c5aa0 0%, #1e3c72 100%) !important;
      color: #ffffff !important;
      box-shadow: 0 3px 12px rgba(44, 90, 160, 0.4) !important;
      transform: translateX(3px) !important;
    }
    
    .nav-sidebar .nav-link.active::before {
      left: 0 !important;
      background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, transparent 100%) !important;
    }
    
    /* Navigation Icons */
    .nav-icon {
      margin-right: 0.75rem !important;
      width: 1.5rem !important;
      text-align: center !important;
      font-size: 1rem !important;
      transition: all 0.3s ease !important;
    }
    
    .nav-sidebar .nav-link:hover .nav-icon {
      transform: scale(1.1) !important;
      color: #4b6cb7 !important;
    }
    
    .nav-sidebar .nav-link.active .nav-icon {
      color: #ffffff !important;
      text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3) !important;
    }
    
    /* Navigation Text */
    .nav-sidebar .nav-link p {
      margin: 0 !important;
      font-weight: 500 !important;
      font-size: 0.9rem !important;
      transition: all 0.3s ease !important;
    }
    
    .nav-sidebar .nav-link:hover p {
      font-weight: 600 !important;
    }
    
    /* Collapsed Sidebar Styles */
    .sidebar-mini.sidebar-collapse .main-sidebar {
      width: 4.6rem !important;
    }
    
    .sidebar-mini.sidebar-collapse .content-wrapper {
      margin-left: 4.6rem !important;
    }
    
    .sidebar-mini.sidebar-collapse .brand-text {
      display: none !important;
    }
    
    .sidebar-mini.sidebar-collapse .nav-sidebar .nav-link p {
      display: none !important;
    }
    
    .sidebar-mini.sidebar-collapse .user-panel .info {
      display: none !important;
    }
    
    .sidebar-mini.sidebar-collapse .nav-header {
      display: none !important;
    }
    
    /* Tooltip for collapsed sidebar */
    .sidebar-mini.sidebar-collapse .nav-sidebar .nav-link {
      position: relative !important;
    }
    
    .sidebar-mini.sidebar-collapse .nav-sidebar .nav-link:hover::after {
      content: attr(data-title) !important;
      position: absolute !important;
      left: 100% !important;
      top: 50% !important;
      transform: translateY(-50%) !important;
      background: rgba(0, 0, 0, 0.8) !important;
      color: white !important;
      padding: 0.5rem 0.75rem !important;
      border-radius: 0.25rem !important;
      font-size: 0.8rem !important;
      white-space: nowrap !important;
      z-index: 1000 !important;
      margin-left: 0.5rem !important;
    }
    
    /* Scrollbar for sidebar */
    .sidebar::-webkit-scrollbar {
      width: 4px !important;
    }
    
    .sidebar::-webkit-scrollbar-track {
      background: rgba(255, 255, 255, 0.1) !important;
    }
    
    .sidebar::-webkit-scrollbar-thumb {
      background: rgba(255, 255, 255, 0.3) !important;
      border-radius: 2px !important;
    }
    
    .sidebar::-webkit-scrollbar-thumb:hover {
      background: rgba(255, 255, 255, 0.5) !important;
    }
    
    /* Navigation Badges */
    .nav-sidebar .badge {
      font-size: 0.7rem !important;
      padding: 0.25rem 0.5rem !important;
      border-radius: 0.75rem !important;
      font-weight: 600 !important;
      margin-left: auto !important;
      transition: all 0.3s ease !important;
    }
    
    .nav-sidebar .badge.right {
      margin-left: auto !important;
    }
    
    .nav-sidebar .nav-link:hover .badge {
      transform: scale(1.1) !important;
    }
    
    .nav-sidebar .badge-info {
      background: #17a2b8 !important;
      color: white !important;
    }
    
    .nav-sidebar .badge-warning {
      background: #ffc107 !important;
      color: #212529 !important;
    }
    
    .nav-sidebar .badge-success {
      background: #28a745 !important;
      color: white !important;
    }
    
    /* Special Logout Button Styling */
    .logout-link {
      border: 1px solid rgba(220, 53, 69, 0.3) !important;
      margin-top: 1rem !important;
    }
    
    .logout-link::before {
      background: linear-gradient(135deg, rgba(220, 53, 69, 0.8) 0%, rgba(189, 33, 48, 0.8) 100%) !important;
    }
    
    .logout-link:hover {
      color: #ffffff !important;
      border-color: rgba(220, 53, 69, 0.6) !important;
    }
    
    .logout-link:hover .nav-icon {
      color: #ffffff !important;
    }
    
    /* Enhanced Brand Logo Animation */
    .brand-link .fas.fa-microscope {
      color: #4b6cb7 !important;
      transition: all 0.3s ease !important;
    }
    
    .brand-link:hover .fas.fa-microscope {
      color: #ffffff !important;
      transform: rotate(5deg) scale(1.1) !important;
    }
    
    /* Smooth transitions for all sidebar elements */
    .sidebar * {
      transition: all 0.3s ease !important;
    }
    
    /* Mobile responsive sidebar */
    @media (max-width: 768px) {
      .main-sidebar {
        transform: translateX(-100%) !important;
      }
      
      .main-sidebar.sidebar-open {
        transform: translateX(0) !important;
      }
      
      .sidebar-overlay {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        height: 100% !important;
        background: rgba(0, 0, 0, 0.5) !important;
        z-index: 1010 !important;
        display: none !important;
      }
      
      .sidebar-overlay.active {
        display: block !important;
      }
    }
    
    .sidebar-mini.sidebar-collapse .main-sidebar {
      width: 4.6rem !important;
    }
    
    .sidebar-mini.sidebar-collapse .content-wrapper {
      margin-left: 4.6rem !important;
    }
    
    .card {
      border: none !important;
      border-radius: 0.75rem !important;
      box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
      transition: all 0.3s ease !important;
      background: white !important;
      margin-bottom: 1.5rem !important;
    }
    
    .card:hover {
      transform: translateY(-2px) !important;
      box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    
    .card-header {
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
      border-bottom: 1px solid #e9ecef !important;
      font-weight: 600 !important;
      color: #2c5aa0 !important;
      padding: 1rem 1.5rem !important;
    }
    
    .card-title {
      color: #2c5aa0 !important;
      font-weight: 600 !important;
      display: flex !important;
      align-items: center !important;
      gap: 0.5rem !important;
      margin: 0 !important;
    }
    
    .card-body {
      padding: 1.5rem !important;
    }
    
    .btn {
      border-radius: 0.375rem !important;
      font-weight: 500 !important;
      transition: all 0.3s ease !important;
    }
    
    .btn:hover {
      transform: translateY(-1px) !important;
    }
    
    .btn-primary {
      background: linear-gradient(135deg, #2c5aa0 0%, #1e3c72 100%) !important;
      border-color: #2c5aa0 !important;
      color: white !important;
    }
    
    .btn-primary:hover {
      background: linear-gradient(135deg, #1e3c72 0%, #2c5aa0 100%) !important;
      box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
      color: white !important;
    }
    
    .btn-success {
      background: #28a745 !important;
      border-color: #28a745 !important;
      color: white !important;
    }
    
    .btn-info {
      background: #17a2b8 !important;
      border-color: #17a2b8 !important;
      color: white !important;
    }
    
    .btn-warning {
      background: #ffc107 !important;
      border-color: #ffc107 !important;
      color: #212529 !important;
    }
    
    .btn-danger {
      background: #dc3545 !important;
      border-color: #dc3545 !important;
      color: white !important;
    }
    
    .form-control {
      border-radius: 0.375rem !important;
      transition: all 0.3s ease !important;
      border: 1px solid #dee2e6 !important;
    }
    
    .form-control:focus {
      border-color: #2c5aa0 !important;
      box-shadow: 0 0 0 0.2rem rgba(44, 90, 160, 0.25) !important;
    }
    
    .table thead th {
      background: linear-gradient(135deg, #2c5aa0 0%, #1e3c72 100%) !important;
      color: white !important;
      font-weight: 600 !important;
      border: none !important;
    }
    
    .table-responsive {
      border-radius: 0.75rem !important;
      overflow: hidden !important;
      box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
      margin-top: 0 !important;
    }
    
    .table {
      margin-bottom: 0 !important;
      background: white !important;
      border-collapse: separate !important;
      border-spacing: 0 !important;
    }
    
    .table tbody td {
      padding: 0.75rem !important;
      vertical-align: middle !important;
      border-top: 1px solid #e9ecef !important;
      font-size: 0.875rem !important;
    }
    
    .table tbody tr:hover {
      background-color: rgba(44, 90, 160, 0.05) !important;
    }
    
    /* Table button groups */
    .btn-group .btn {
      margin-right: 0 !important;
      padding: 0.25rem 0.5rem !important;
      font-size: 0.75rem !important;
    }
    
    .btn-sm {
      padding: 0.25rem 0.5rem !important;
      font-size: 0.75rem !important;
      line-height: 1.25 !important;
    }
    
    .small-box {
      border-radius: 0.75rem !important;
      transition: all 0.3s ease !important;
      box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
      position: relative !important;
      overflow: hidden !important;
      margin-bottom: 1rem !important;
      height: 140px !important;
    }
    
    .small-box:hover {
      transform: translateY(-5px) !important;
      box-shadow: 0 1rem 2rem rgba(0, 0, 0, 0.2) !important;
    }
    
    .small-box .inner {
      padding: 1.5rem !important;
      position: relative !important;
      z-index: 2 !important;
      height: 100% !important;
      display: flex !important;
      flex-direction: column !important;
      justify-content: center !important;
    }
    
    .small-box h3 {
      font-weight: 700 !important;
      font-size: 2rem !important;
      margin: 0 0 0.5rem 0 !important;
      color: white !important;
      text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3) !important;
      line-height: 1 !important;
    }
    
    .small-box p {
      font-size: 1rem !important;
      color: rgba(255, 255, 255, 0.9) !important;
      margin: 0 !important;
      font-weight: 500 !important;
      line-height: 1.2 !important;
    }
    
    .small-box .icon {
      position: absolute !important;
      top: 50% !important;
      right: 1rem !important;
      font-size: 3rem !important;
      color: rgba(255, 255, 255, 0.2) !important;
      z-index: 1 !important;
      transform: translateY(-50%) !important;
    }
    
    /* Fix specific small-box backgrounds */
    .small-box.bg-info {
      background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
    }
    
    .small-box.bg-success {
      background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%) !important;
    }
    
    .small-box.bg-primary {
      background: linear-gradient(135deg, #2c5aa0 0%, #1e3c72 100%) !important;
    }
    
    .small-box.bg-danger {
      background: linear-gradient(135deg, #dc3545 0%, #bd2130 100%) !important;
    }
    
    .modal-content {
      border: none !important;
      border-radius: 0.75rem !important;
      box-shadow: 0 1rem 2rem rgba(0, 0, 0, 0.2) !important;
      overflow: hidden !important;
    }
    
    .modal-header {
      border-radius: 0.75rem 0.75rem 0 0 !important;
      padding: 1.5rem !important;
    }
    
    .modal-body {
      padding: 1.5rem !important;
    }
    
    .modal-footer {
      padding: 1.5rem !important;
      background: #f8f9fa !important;
      border-top: 1px solid #e9ecef !important;
    }
    
    .badge {
      border-radius: 0.375rem !important;
      font-weight: 500 !important;
      padding: 0.25rem 0.5rem !important;
    }
    
    .content-header h1 {
      color: #2c5aa0 !important;
      font-weight: 600 !important;
      display: flex !important;
      align-items: center !important;
      gap: 0.5rem !important;
      font-size: 1.8rem !important;
      margin: 0 !important;
    }
    
    .content-header {
      padding: 1.5rem 1.5rem 0 !important;
      background: transparent !important;
    }
    
    .content {
      padding: 1.5rem !important;
    }
    
    .breadcrumb {
      background: transparent !important;
      padding: 0 !important;
      margin: 0 !important;
    }
    
    .breadcrumb-item a {
      color: #2c5aa0 !important;
      text-decoration: none !important;
    }
    
    .breadcrumb-item a:hover {
      color: #1e3c72 !important;
      text-decoration: underline !important;
    }
    
    /* DataTables styling */
    .dataTables_wrapper .dataTables_filter input,
    .dataTables_wrapper .dataTables_length select {
      border-radius: 0.375rem !important;
      border: 1px solid #dee2e6 !important;
    }
    
    .dataTables_wrapper .dataTables_filter input:focus {
      border-color: #2c5aa0 !important;
      box-shadow: 0 0 0 0.2rem rgba(44, 90, 160, 0.25) !important;
    }
    
    .dataTables_paginate .paginate_button {
      border-radius: 0.375rem !important;
      margin: 0 0.25rem !important;
    }
    
    .dataTables_paginate .paginate_button:hover {
      background: #2c5aa0 !important;
      color: white !important;
      border-color: #2c5aa0 !important;
    }
    
    .dataTables_paginate .paginate_button.current {
      background: #2c5aa0 !important;
      color: white !important;
      border-color: #2c5aa0 !important;
    }
    
    /* Content sections spacing */
    .content .row {
      margin-bottom: 1.5rem !important;
    }
    
    .content .row.mb-3 {
      margin-bottom: 1.5rem !important;
    }
    
    /* Card header improvements */
    .card-header .card-title {
      display: flex !important;
      align-items: center !important;
      gap: 0.5rem !important;
      margin: 0 !important;
      font-size: 1.125rem !important;
    }
    
    .card-tools {
      display: flex !important;
      gap: 0.5rem !important;
    }
    
    .card-tools .btn-tool {
      color: var(--primary-color) !important;
      border: 1px solid var(--primary-color) !important;
      border-radius: 0.375rem !important;
      padding: 0.25rem 0.5rem !important;
      transition: all 0.3s ease !important;
    }
    
    .card-tools .btn-tool:hover {
      background: var(--primary-color) !important;
      color: white !important;
      transform: translateY(-1px) !important;
    }
    
    /* Statistics section spacing */
    .row.mb-3 {
      margin-bottom: 1.5rem !important;
    }
    
    /* Filter section improvements */
    .col-md-6 .row {
      margin: 0 !important;
    }
    
    .col-md-4 {
      padding-left: 0.375rem !important;
      padding-right: 0.375rem !important;
    }
    
    /* Loading spinner for statistics */
    .fa-spinner.fa-spin {
      animation: spin 1s linear infinite !important;
      color: rgba(255, 255, 255, 0.8) !important;
    }
    
    /* Responsive improvements */
    @media (max-width: 768px) {
      .content-wrapper {
        margin-left: 0 !important;
      }
      
      .content-header h1 {
        font-size: 1.5rem !important;
      }
      
      .small-box h3 {
        font-size: 1.8rem !important;
      }
      
      .card-body {
        padding: 1rem !important;
      }
      
      .content-header {
        padding: 1rem 1rem 0 !important;
      }
      
      .content {
        padding: 1rem !important;
      }
      
      .col-lg-3 {
        margin-bottom: 1rem !important;
      }
      
      .btn-group .btn {
        padding: 0.25rem 0.375rem !important;
        font-size: 0.75rem !important;
      }
      
      .table {
        font-size: 0.8rem !important;
      }
      
      .small-box {
        height: 120px !important;
      }
      
      .small-box .inner {
        padding: 1rem !important;
      }
      
      .small-box h3 {
        font-size: 1.5rem !important;
      }
      
      .small-box .icon {
        font-size: 2.5rem !important;
      }
    }
    
    @media (max-width: 576px) {
      .content-header,
      .content {
        padding-left: 0.75rem !important;
        padding-right: 0.75rem !important;
      }
      
      .container-fluid {
        padding-left: 0.75rem !important;
        padding-right: 0.75rem !important;
      }
      
      .row {
        margin-left: -0.375rem !important;
        margin-right: -0.375rem !important;
      }
      
      .col-lg-3, .col-6 {
        padding-left: 0.375rem !important;
        padding-right: 0.375rem !important;
      }
      
      .small-box {
        height: 100px !important;
      }
      
      .small-box h3 {
        font-size: 1.25rem !important;
      }
      
      .small-box p {
        font-size: 0.875rem !important;
      }
      
      .small-box .icon {
        font-size: 2rem !important;
      }
    }
    
    /* Ensure proper z-index for navigation */
    .main-header {
      z-index: 1030 !important;
    }
    
    /* Fix for AdminLTE body classes */
    .hold-transition .content-wrapper {
      transition: margin-left 0.3s ease-in-out !important;
    }
    
    /* Search and filter section styling */
    .input-group {
      border-radius: 0.375rem !important;
      overflow: hidden !important;
      box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
      margin-bottom: 1rem !important;
    }
    
    .input-group .form-control {
      border: 1px solid #dee2e6 !important;
      border-right: none !important;
      border-radius: 0 !important;
      padding: 0.5rem 0.75rem !important;
      height: auto !important;
    }
    
    .input-group .form-control:not(:last-child) {
      border-top-right-radius: 0 !important;
      border-bottom-right-radius: 0 !important;
    }
    
    .input-group .btn:not(:first-child) {
      border-top-left-radius: 0 !important;
      border-bottom-left-radius: 0 !important;
      border-left: none !important;
    }
    
    .input-group-append .btn {
      border-radius: 0 !important;
    }
    
    .input-group-append .btn:last-child {
      border-top-right-radius: 0.375rem !important;
      border-bottom-right-radius: 0.375rem !important;
    }
    
    /* Form controls in filter section */
    .row .form-control {
      margin-bottom: 0.5rem !important;
    }
    
    /* Add Patient button styling */
    .btn-success {
      background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%) !important;
      border-color: #28a745 !important;
      color: white !important;
      box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
    }
    
    .btn-success:hover {
      background: linear-gradient(135deg, #1e7e34 0%, #28a745 100%) !important;
      transform: translateY(-1px) !important;
      box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
  </style>
  
</head>

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

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
      <!-- User Dropdown Menu -->
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="far fa-user"></i>
          <?php echo htmlspecialchars($full_name ?? 'User'); ?>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <span class="dropdown-item dropdown-header"><?php echo ucfirst(htmlspecialchars($user_type ?? 'user')); ?></span>
          <div class="dropdown-divider"></div>
          <a href="settings.php" class="dropdown-item">
            <i class="fas fa-user mr-2"></i> Profile
          </a>
          <div class="dropdown-divider"></div>
          <a href="logout.php" class="dropdown-item" onclick="return confirm('Are you sure you want to logout?')">
            <i class="fas fa-sign-out-alt mr-2"></i> Logout
          </a>
        </div>
      </li>
      
      <!-- Fullscreen toggle -->
      <li class="nav-item">
        <a class="nav-link" data-widget="fullscreen" href="#" role="button">
          <i class="fas fa-expand-arrows-alt"></i>
        </a>
      </li>
    </ul>
  </nav>
  <!-- /.navbar -->