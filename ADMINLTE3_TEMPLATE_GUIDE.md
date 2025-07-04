# PathLab Pro - AdminLTE3 Template Guide

This document explains how to use the AdminLTE3 templates in the PathLab Pro Laboratory Management System.

## Overview

PathLab Pro now follows AdminLTE3 design patterns and best practices for a modern, professional, and responsive user interface.

## Key Features

### 1. Modern Design
- Clean and professional AdminLTE3 theme
- Responsive design that works on all devices
- Modern color scheme with primary colors: #2c5aa0 and #1e3c72
- Enhanced typography and spacing

### 2. Enhanced Navigation
- Structured sidebar with grouped menu items
- User dropdown menu with profile information
- Breadcrumb navigation
- Search functionality in navbar and sidebar

### 3. Improved Components
- Modern card designs with hover effects
- Enhanced forms with better validation
- Advanced DataTables with export functionality
- Interactive charts and dashboards
- Loading states and animations

### 4. Better UX
- SweetAlert2 for modern alerts and confirmations
- Toast notifications for user feedback
- Loading overlays and button states
- Auto-hiding alerts
- Keyboard shortcuts and accessibility

## File Structure

```
includes/
├── header.php          # Main header with navbar and CSS includes
├── sidebar.php         # Sidebar navigation with user panel
├── footer.php          # Footer with script includes
└── init.php           # Initialization and utility functions

css/
└── custom.css         # Enhanced custom styles for AdminLTE3

js/
├── common.js          # Original common functions
├── common_enhanced.js # Enhanced common functions with AdminLTE3 features
└── toaster.js         # Toast notification functions

Pages/
├── index.php              # Modern login page
├── dashboard.php          # Current dashboard
├── dashboard_modern.php   # Enhanced dashboard with AdminLTE3 features
├── patients.php           # Current patients page
├── patients_modern.php    # Enhanced patients page with modern features
└── [other pages]          # Other application pages
```

## Usage Guide

### 1. Creating New Pages

When creating a new page, use this template structure:

```php
<?php
// Set page title
$page_title = 'Your Page Title';

// Include header
include 'includes/header.php';
// Include sidebar with user info
include 'includes/sidebar.php';
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>
                        <i class="fas fa-icon-name mr-2"></i>
                        Page Title
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                        <li class="breadcrumb-item active">Page Title</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Alert Messages -->
            <div id="alertContainer"></div>
            
            <!-- Your page content here -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-icon-name mr-1"></i>
                                Card Title
                            </h3>
                        </div>
                        <div class="card-body">
                            <!-- Card content -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include 'includes/footer.php'; ?>

<script>
$(document).ready(function() {
    // Your page-specific JavaScript here
});
</script>
```

### 2. Using Cards

AdminLTE3 provides various card styles:

```html
<!-- Basic Card -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Card Title</h3>
    </div>
    <div class="card-body">
        Card content
    </div>
</div>

<!-- Card with Tools -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Card with Tools</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
            <button type="button" class="btn btn-tool" data-card-widget="remove">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        Card content
    </div>
</div>

<!-- Colored Card -->
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Primary Card</h3>
    </div>
    <div class="card-body">
        Card content
    </div>
</div>
```

### 3. Using Info Boxes

For dashboard statistics:

```html
<div class="info-box">
    <span class="info-box-icon bg-info elevation-1">
        <i class="fas fa-users"></i>
    </span>
    <div class="info-box-content">
        <span class="info-box-text">Total Users</span>
        <span class="info-box-number">1,410</span>
    </div>
</div>
```

### 4. Using Small Boxes

Alternative to info boxes:

```html
<div class="small-box bg-info">
    <div class="inner">
        <h3>150</h3>
        <p>New Orders</p>
    </div>
    <div class="icon">
        <i class="fas fa-shopping-cart"></i>
    </div>
    <a href="#" class="small-box-footer">
        More info <i class="fas fa-arrow-circle-right"></i>
    </a>
</div>
```

### 5. Using Alerts

Use the enhanced alert system:

```javascript
// Success alert
showAlert('success', 'Operation completed successfully!');

// Error alert
showAlert('error', 'Something went wrong!');

// Warning alert
showAlert('warning', 'Please check your input!');

// Info alert
showAlert('info', 'Here is some information.');
```

### 6. Using Modals

Enhanced modal structure:

```html
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-icon-name mr-2"></i>
                    Modal Title
                </h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Modal content -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>
```

### 7. Using Forms

Enhanced form structure with validation:

```html
<form id="exampleForm" class="ajax-form">
    <div class="form-group">
        <label for="inputField">Field Label <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="inputField" name="field" required>
        <div class="invalid-feedback">This field is required.</div>
    </div>
    
    <div class="form-group">
        <button type="submit" class="btn btn-primary">
            <span class="btn-text">
                <i class="fas fa-save"></i> Save
            </span>
            <span class="btn-loading" style="display: none;">
                <i class="fas fa-spinner fa-spin"></i> Saving...
            </span>
        </button>
    </div>
</form>
```

### 8. Using DataTables

Enhanced DataTable setup:

```javascript
$('#example-table').DataTable({
    responsive: true,
    processing: true,
    serverSide: true,
    ajax: {
        url: 'api/data_api.php',
        type: 'POST'
    },
    columns: [
        { data: 'id' },
        { data: 'name' },
        { data: 'email' },
        {
            data: 'id',
            orderable: false,
            render: function(data, type, row) {
                return `
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-info btn-sm" onclick="viewItem(${data})">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="btn btn-warning btn-sm" onclick="editItem(${data})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-sm delete-confirm" data-url="api/data_api.php?id=${data}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
            }
        }
    ]
});
```

## Color Scheme

### Primary Colors
- Primary: #2c5aa0
- Primary Dark: #1e3c72
- Primary Light: #4b6cb7

### Status Colors
- Success: #28a745
- Info: #17a2b8
- Warning: #ffc107
- Danger: #dc3545

### Neutral Colors
- Light: #f8f9fa
- Dark: #343a40
- Secondary: #6c757d

## JavaScript Functions

### Enhanced Common Functions

The enhanced common.js provides:

- `showAlert(type, message, container)` - Show styled alerts
- `setButtonLoading(button, loading)` - Button loading states
- `handleAjaxError(xhr, status, error)` - AJAX error handling
- `formatDate(dateString, format)` - Date formatting
- `formatCurrency(amount, currency)` - Currency formatting
- `validateForm(formSelector)` - Form validation
- `showLoading()` / `hideLoading()` - Loading overlays
- `exportTableData(tableId, format)` - Export table data

### Utility Functions

- `Utils.debounce(func, wait)` - Debounce function calls
- `Utils.throttle(func, limit)` - Throttle function calls
- `Utils.randomString(length)` - Generate random strings
- `Utils.copyToClipboard(text)` - Copy text to clipboard

## Best Practices

1. **Always use the header/sidebar/footer includes** for consistency
2. **Set page titles** using the `$page_title` variable
3. **Use proper breadcrumbs** for navigation context
4. **Include alert containers** for user feedback
5. **Use semantic HTML** and proper ARIA labels
6. **Follow the card structure** for content organization
7. **Use consistent button styles** and loading states
8. **Implement proper error handling** in AJAX calls
9. **Use responsive classes** for mobile compatibility
10. **Test on different screen sizes** to ensure responsiveness

## Migration from Old Templates

To migrate existing pages to the new AdminLTE3 templates:

1. Update the header/footer includes
2. Replace old alert systems with the new `showAlert()` function
3. Update card structures to use AdminLTE3 classes
4. Replace old button styles with AdminLTE3 buttons
5. Update DataTable configurations
6. Add proper loading states to forms and buttons
7. Update color classes to use the new color scheme
8. Test all functionality to ensure compatibility

## Support

For questions or issues with the AdminLTE3 templates, refer to:
- [AdminLTE3 Documentation](https://adminlte.io/docs/3.2/)
- [Bootstrap 4 Documentation](https://getbootstrap.com/docs/4.6/)
- PathLab Pro internal documentation
