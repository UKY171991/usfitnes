# AdminLTE 3 Template Implementation Guide

This guide shows how to convert your existing PathLab Pro pages to use the new AdminLTE 3 template system.

## Files Created

1. **`includes/adminlte_template.php`** - Main template system
2. **`includes/adminlte_template_header.php`** - Header with navigation
3. **`includes/adminlte_template_footer.php`** - Footer with scripts
4. **`includes/adminlte_sidebar.php`** - Sidebar navigation (already exists)
5. **`api/system_status.php`** - System status API endpoint

## How to Convert Existing Pages

### Before (Old Style)
```php
<?php
require_once 'config.php';
// Check login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Page Title</title>
    <!-- CSS files -->
</head>
<body>
    <div class="wrapper">
        <!-- Include sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="content-wrapper">
            <h1>Page Content</h1>
            <!-- Your content here -->
        </div>
    </div>
    <!-- Scripts -->
</body>
</html>
```

### After (New AdminLTE 3 Template)
```php
<?php
require_once 'includes/adminlte_template.php';

function pageContent() {
    ?>
    <!-- Your page content here -->
    <div class="row">
        <div class="col-12">
            <?php echo createCard('Page Title', 'Your content here'); ?>
        </div>
    </div>
    
    <script>
    // Your page-specific JavaScript
    $(document).ready(function() {
        // Initialize page
    });
    </script>
    <?php
}

// Render the page using template
renderTemplate('page_id', 'pageContent');
?>
```

## Available Helper Functions

### `createInfoBox($title, $value, $icon, $color, $link)`
Creates AdminLTE info boxes for statistics.

### `createCard($title, $content, $tools, $footer, $color)`
Creates AdminLTE cards with optional tools and footer.

### `renderTemplate($page_id, $content_callback, $additional_css, $additional_js)`
Main function to render pages with the template.

## Page Examples

### Dashboard Example
See `dashboard_new.php` for a complete dashboard implementation.

### Patients Page Example
See `patients_adminlte.php` for a data table implementation.

## Template Features

### 1. **Responsive Design**
- Mobile-first approach
- Sidebar collapses on mobile
- Responsive tables and cards

### 2. **Modern UI Components**
- AdminLTE 3.2.0
- Bootstrap 4.6
- FontAwesome 6.5.1
- DataTables with export buttons
- SweetAlert2 for confirmations
- Toastr for notifications

### 3. **Built-in Functionality**
- Dark mode toggle
- Auto-refresh capability
- Loading states
- Form validation helpers
- AJAX error handling
- Notification system

### 4. **Consistent Navigation**
- Breadcrumb system
- Active menu highlighting
- User menu with profile
- Quick actions in sidebar

## Converting Your Existing Pages

### Step 1: Update Dashboard
```bash
# Backup current dashboard
cp dashboard.php dashboard_old.php

# Use new dashboard
cp dashboard_new.php dashboard.php
```

### Step 2: Update Patients Page
```bash
# Backup current patients page
cp patients.php patients_old.php

# Use new patients page
cp patients_adminlte.php patients.php
```

### Step 3: Update Other Pages
Follow the same pattern for:
- `doctors.php`
- `equipment.php`
- `test-orders.php`
- `results.php`
- `reports.php`
- `settings.php`
- `users.php`

## Customization

### Custom CSS
Add your custom styles to `css/adminlte-custom.css`

### Custom JavaScript
Add page-specific JavaScript in the content function or include additional JS files.

### Sidebar Menu
Modify `includes/adminlte_sidebar.php` to add/remove menu items.

## Browser Compatibility
- Chrome 60+
- Firefox 60+
- Safari 12+
- Edge 79+
- IE 11 (limited support)

## Testing Your Implementation

1. **Clear Database Issues**: Run `clear_database.php` if needed
2. **Test Configuration**: Run `test_config.php` to verify setup
3. **Check New Pages**: 
   - `dashboard_new.php`
   - `patients_adminlte.php`
4. **Verify Responsive**: Test on mobile devices

## Next Steps

1. Convert all your existing pages using this template
2. Implement API endpoints for dynamic data loading
3. Add more interactive features
4. Customize the theme colors and branding

## Support

The template includes:
- Comprehensive error handling
- Loading states for better UX
- Consistent styling across all pages
- Modern JavaScript patterns
- Accessibility improvements

Start by testing the new dashboard and patients pages, then gradually convert your other pages following the same pattern.
