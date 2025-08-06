# AdminLTE3 Implementation Guide for PathLab Pro

## Overview
This guide documents the complete AdminLTE3 implementation for PathLab Pro laboratory management system. AdminLTE3 provides a modern, responsive admin dashboard with extensive UI components and features.

## Files Structure

### Core Layout Files
```
includes/
├── adminlte_header.php    # Main header with navbar and navigation
├── adminlte_sidebar.php   # Sidebar with navigation menu
├── adminlte_footer.php    # Footer with scripts and closing tags
└── init.php              # Initialization and configuration

css/
└── adminlte-custom.css   # Custom AdminLTE3 styles and overrides

js/
└── adminlte-custom.js    # Custom JavaScript functionality

api/
└── get_counts.php        # API endpoint for dashboard statistics
```

### Example Pages
- `dashboard_adminlte.php` - Complete AdminLTE3 dashboard example
- `dashboard.php` - Updated existing dashboard
- All other pages automatically converted

## Key Features Implemented

### 1. Responsive Design
- Mobile-first approach
- Collapsible sidebar
- Responsive navigation
- Touch-friendly interface

### 2. Navigation System
- Multi-level sidebar menu
- Breadcrumb navigation
- Active state management
- Search functionality

### 3. Dashboard Components
- Statistics cards (Info boxes)
- Interactive charts (Chart.js)
- Data tables with advanced features
- Real-time updates via API

### 4. UI Components
- AdminLTE3 cards and widgets
- Bootstrap 4 components
- Font Awesome icons
- Toastr notifications
- SweetAlert2 modals

### 5. Advanced Features
- Dark mode support
- Theme customization
- Sidebar state persistence
- AJAX-powered content
- Form validation
- File upload areas

## Usage Guide

### Basic Page Structure
```php
<?php
// Set page title
$page_title = 'Page Name - PathLab Pro';

// Include AdminLTE header and sidebar
include 'includes/adminlte_header.php';
include 'includes/adminlte_sidebar.php';
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
  <!-- Content Header -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">
            <i class="fas fa-icon-name mr-2 text-primary"></i>Page Title
          </h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
            <li class="breadcrumb-item active">Page Name</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      <!-- Your page content here -->
    </div>
  </section>
</div>

<?php include 'includes/adminlte_footer.php'; ?>
```

### Creating Info Boxes
```html
<div class="info-box">
  <span class="info-box-icon bg-info"><i class="fas fa-icon"></i></span>
  <div class="info-box-content">
    <span class="info-box-text">Label</span>
    <span class="info-box-number">1,234</span>
    <div class="progress">
      <div class="progress-bar bg-info" style="width: 70%"></div>
    </div>
    <span class="progress-description">Description text</span>
  </div>
</div>
```

### Creating Cards
```html
<div class="card">
  <div class="card-header">
    <h3 class="card-title">
      <i class="fas fa-icon mr-1"></i>Card Title
    </h3>
    <div class="card-tools">
      <button type="button" class="btn btn-tool" data-card-widget="collapse">
        <i class="fas fa-minus"></i>
      </button>
    </div>
  </div>
  <div class="card-body">
    <!-- Card content -->
  </div>
</div>
```

### DataTables Integration
```html
<table class="table table-bordered table-striped datatable">
  <thead>
    <tr>
      <th>Column 1</th>
      <th>Column 2</th>
    </tr>
  </thead>
  <tbody>
    <!-- Table data -->
  </tbody>
</table>
```

### Form Components
```html
<div class="form-group">
  <label for="input">Label</label>
  <input type="text" class="form-control" id="input" placeholder="Placeholder">
</div>

<div class="form-group">
  <label for="select">Select</label>
  <select class="form-control select2" id="select">
    <option>Option 1</option>
  </select>
</div>

<div class="form-group">
  <label for="date">Date</label>
  <input type="text" class="form-control datepicker" id="date">
</div>
```

## JavaScript Functionality

### Global Object
The `PathLabPro` global object provides utility functions:

```javascript
// Notifications
PathLabPro.notifications.success('Success message');
PathLabPro.notifications.error('Error message');

// API calls
PathLabPro.api.get('endpoint').then(data => {
  // Handle response
});

// Modal confirmations
PathLabPro.modal.confirm({
  title: 'Are you sure?',
  text: 'This action cannot be undone.'
}).then(result => {
  if (result.isConfirmed) {
    // Proceed with action
  }
});

// Form validation
const validation = PathLabPro.forms.validate(formElement);
if (validation.isValid) {
  // Submit form
}
```

### Auto-initialization
Components are automatically initialized:
- DataTables with `.datatable` class
- Select2 with `.select2` class
- Date pickers with `.datepicker` class
- DateTime pickers with `.datetimepicker` class

## API Integration

### Dashboard Statistics
The `/api/get_counts.php` endpoint provides:
- Total patients count
- Pending test orders
- Completed tests
- Monthly revenue
- Equipment count
- Test statistics

### Usage
```javascript
fetch('api/get_counts.php')
  .then(response => response.json())
  .then(data => {
    // Update dashboard elements
    document.getElementById('patients-count').textContent = data.patients;
  });
```

## Customization

### Theme Colors
Modify CSS variables in `adminlte-custom.css`:
```css
:root {
  --primary: #007bff;
  --secondary: #6c757d;
  --success: #28a745;
  /* Add more colors */
}
```

### Sidebar Menu
Edit `includes/adminlte_sidebar.php` to:
- Add new menu items
- Modify menu structure
- Update icons and labels

### Custom Styles
Add custom styles to `css/adminlte-custom.css` following the existing structure.

## Migration from Previous Layout

### Automatic Conversion
Run the conversion script:
```bash
php convert_to_adminlte.php
```

This script:
- Updates include statements
- Adds "- PathLab Pro" to page titles
- Creates backups of original files

### Manual Updates Required
After conversion, manually update:
1. Custom CSS classes
2. JavaScript event handlers
3. Form validation
4. AJAX endpoints

## Best Practices

### 1. Page Structure
- Always use the content-wrapper div
- Include proper breadcrumbs
- Use consistent icon classes
- Follow the card-based layout

### 2. Performance
- Load scripts at the bottom
- Use CDN for external libraries
- Minimize custom CSS
- Optimize images and assets

### 3. Responsive Design
- Test on multiple devices
- Use Bootstrap grid system
- Implement mobile-first approach
- Consider touch interactions

### 4. Accessibility
- Use proper ARIA labels
- Ensure keyboard navigation
- Maintain color contrast
- Provide alternative text

## Troubleshooting

### Common Issues
1. **Scripts not loading**: Check CDN links and internet connection
2. **Sidebar not working**: Ensure AdminLTE JS is loaded after jQuery
3. **Charts not displaying**: Verify Chart.js is included and canvas elements exist
4. **Mobile layout issues**: Check viewport meta tag and responsive classes

### Debug Mode
Enable debug mode in `adminlte-custom.js`:
```javascript
PathLabPro.config.debug = true;
```

### Browser Console
Check browser console for JavaScript errors and network issues.

## Updates and Maintenance

### Regular Updates
- Monitor AdminLTE releases
- Update CDN links for security patches
- Test new features before deployment
- Backup before major updates

### Performance Monitoring
- Monitor page load times
- Check for JavaScript errors
- Validate responsive design
- Test cross-browser compatibility

## Support and Resources

### Official Documentation
- [AdminLTE3 Documentation](https://adminlte.io/docs/3.0/)
- [Bootstrap 4 Documentation](https://getbootstrap.com/docs/4.6/)
- [Font Awesome Icons](https://fontawesome.com/icons)

### Community Resources
- AdminLTE GitHub repository
- Bootstrap community forums
- Stack Overflow for specific issues

## Conclusion

This AdminLTE3 implementation provides a modern, feature-rich admin interface for PathLab Pro. The modular structure allows for easy maintenance and customization while maintaining consistency across all pages.

For additional customization or troubleshooting, refer to the official AdminLTE3 documentation or modify the provided base files to meet specific requirements.
