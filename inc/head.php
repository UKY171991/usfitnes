<?php
// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Pathology Lab Management System">
<meta name="theme-color" content="#4361ee">
<meta name="csrf-token" content="<?php echo $_SESSION['csrf_token']; ?>">

<!-- Favicon -->
<link rel="icon" type="image/png" href="assets/img/favicon.png">

<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<!-- Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<!-- Styles -->
<link rel="stylesheet" href="assets/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/css/custom.css">

<!-- Scripts -->
<script src="assets/js/bootstrap.bundle.min.js" defer></script>
<script src="assets/js/main.js" defer></script>
<script src="assets/js/init.js" defer></script>

<!-- Alert Container -->
<div id="alert-container" class="position-fixed top-0 end-0 p-3" style="z-index: 1050;"></div>

<!-- Mobile Menu Toggle -->
<button id="sidebar-toggle" class="btn btn-link d-md-none position-fixed top-0 start-0 mt-2 ms-2 z-3" style="z-index: 1040;">
    <i class="bi bi-list fs-4"></i>
</button>

<script>
// Initialize mobile menu toggle
document.getElementById('sidebar-toggle')?.addEventListener('click', () => {
    const sidebar = document.querySelector('.sidebar');
    sidebar?.classList.toggle('show');
});

// Close sidebar when clicking outside on mobile
document.addEventListener('click', (e) => {
    const sidebar = document.querySelector('.sidebar');
    const toggle = document.getElementById('sidebar-toggle');
    
    if (window.innerWidth <= 768 && 
        sidebar?.classList.contains('show') && 
        !sidebar.contains(e.target) && 
        !toggle.contains(e.target)) {
        sidebar.classList.remove('show');
    }
});

// Handle alerts
window.showAlert = (message, type = 'info') => {
    const alertContainer = document.getElementById('alert-container');
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} fade-in`;
    alert.innerHTML = message;
    
    alertContainer.appendChild(alert);
    
    setTimeout(() => {
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 300);
    }, 5000);
};
</script>

<!--begin::Fonts-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
      integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q="
      crossorigin="anonymous"
    />
    <!--end::Fonts-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.10.1/styles/overlayscrollbars.min.css"
      integrity="sha256-tZHrRjVqNSRyWg2wbppGnT833E/Ys0DHWGwT04GiqQg="
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(OverlayScrollbars)-->
    <!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
      integrity="sha256-9kPW/n5nn53j4WMRYAxe9c1rCY96Oogo/MKSVdKzPmI="
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(Bootstrap Icons)-->
    <!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="css/adminlte.css" />
    <!--end::Required Plugin(AdminLTE)-->
    <!-- apexcharts -->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/apexcharts@3.37.1/dist/apexcharts.css"
      integrity="sha256-4MX+61mt9NVvvuPjUWdUdyfZfxSB1/Rf9WtqRHgG5S0="
      crossorigin="anonymous"
    />
    <!-- jsvectormap -->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/css/jsvectormap.min.css"
      integrity="sha256-+uGLJmmTKOqBr+2E6KDYs/NRsHxSkONXFHUL0fy2O/4="
      crossorigin="anonymous"
    />

    <!-- Inside inc/head.php -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">

<!-- Custom CSS -->
<link href="assets/css/style.css" rel="stylesheet">

<!-- Additional CSS -->
<style>
    .app-wrapper {
        display: flex;
        min-height: 100vh;
    }
    .app-content {
        flex: 1;
        padding: 20px;
    }
    .sidebar {
        width: 250px;
        background: #343a40;
    }
    .nav-link {
        color: rgba(255,255,255,.75);
    }
    .nav-link:hover {
        color: rgba(255,255,255,1);
    }
    .nav-link.active {
        color: #fff;
        background: rgba(255,255,255,.1);
    }
</style>